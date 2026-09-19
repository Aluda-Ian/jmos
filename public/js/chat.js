/* ==========================================================================
   JMOS — Team Chat & 1-on-1 Direct Messaging Controller
   ========================================================================== */

const CHAT_STATE = {
  currentUser: null,
  activeThreadId: null,
  activeThreadMeta: null,
  threads: [],
  teamMembers: [],
  messages: [],
  stagedAttachment: null,
  pollTimer: null,
  searchFilter: '',
  lastTotalUnread: null
};

// --------------------------------------------------------------------------
// 1. DATA FETCHING & DIRECTORY
// --------------------------------------------------------------------------
async function fetchChatData() {
  try {
    const res = await JMOS_API.get('/chat/threads');
    if (res.status === 'success') {
      CHAT_STATE.currentUser = res.current_user;
      CHAT_STATE.threads = res.threads || [];
      CHAT_STATE.teamMembers = res.team_members || [];

      renderChatDirectory();
      updateGlobalChatBadge();

      // If no active thread selected, select #general by default
      if (!CHAT_STATE.activeThreadId && CHAT_STATE.threads.length > 0) {
        const generalThread = CHAT_STATE.threads.find(t => t.title === '#general') || CHAT_STATE.threads[0];
        if (generalThread) {
          selectThread(generalThread.id);
        }
      }
    }
  } catch (err) {
    console.warn('Chat data fetch note:', err.message);
  }
}

function renderChatDirectory() {
  const channelsList = document.getElementById('chatChannelsList');
  const directList = document.getElementById('chatDirectList');
  const countPill = document.getElementById('directMembersCount');

  if (!channelsList || !directList) return;

  const query = (CHAT_STATE.searchFilter || '').toLowerCase().trim();

  // 1. Channels Rendering
  const channelThreads = CHAT_STATE.threads.filter(t => t.type === 'group');
  const filteredChannels = channelThreads.filter(t => {
    if (!query) return true;
    return (t.title || '').toLowerCase().includes(query) || (t.subtitle || '').toLowerCase().includes(query);
  });

  if (filteredChannels.length === 0) {
    channelsList.innerHTML = `<div style="padding:10px;font-size:12px;color:var(--muted);text-align:center">No channels found</div>`;
  } else {
    channelsList.innerHTML = filteredChannels.map(t => {
      const isActive = CHAT_STATE.activeThreadId === t.id;
      const unreadHtml = t.unread_count > 0 ? `<span class="chat-unread-badge">${t.unread_count}</span>` : '';
      return `
        <div class="chat-item ${isActive ? 'active' : ''}" data-chat-thread="${t.id}">
          <div class="chat-item-av" style="background:var(--red-soft);color:var(--red)">#</div>
          <div class="chat-item-info">
            <div class="chat-item-name">${escHtml(t.title)}</div>
            <div class="chat-item-last">${escHtml(t.last_message ? `${t.last_message.sender_name}: ${t.last_message.message}` : t.subtitle || 'Channel')}</div>
          </div>
          ${unreadHtml}
        </div>
      `;
    }).join('');
  }

  // 2. Direct Messages (1-on-1) Rendering
  // Merge team members list with direct threads
  const directThreads = CHAT_STATE.threads.filter(t => t.type === 'direct');
  const otherTeam = CHAT_STATE.teamMembers.filter(u => !u.is_current);

  if (countPill) countPill.textContent = otherTeam.length;

  const filteredTeam = otherTeam.filter(u => {
    if (!query) return true;
    return u.name.toLowerCase().includes(query) || (u.role || '').toLowerCase().includes(query) || (u.title || '').toLowerCase().includes(query);
  });

  if (filteredTeam.length === 0) {
    directList.innerHTML = `<div style="padding:10px;font-size:12px;color:var(--muted);text-align:center">No team members found</div>`;
  } else {
    directList.innerHTML = filteredTeam.map(u => {
      // Find direct thread if one already exists
      const existingThread = directThreads.find(t => t.other_user && t.other_user.id === u.id);
      const isActive = existingThread && CHAT_STATE.activeThreadId === existingThread.id;
      const unreadCount = existingThread ? existingThread.unread_count : 0;
      const unreadHtml = unreadCount > 0 ? `<span class="chat-unread-badge">${unreadCount}</span>` : '';

      let lastMsgText = u.title || ucfirst(u.role);
      if (existingThread && existingThread.last_message) {
        lastMsgText = `${existingThread.last_message.sender_name}: ${existingThread.last_message.message}`;
      }

      return `
        <div class="chat-item ${isActive ? 'active' : ''}" data-direct-user="${u.id}">
          <div class="chat-item-av" style="background:${u.color || 'var(--red)'}">${escHtml(u.initials || u.name.slice(0, 2))}</div>
          <div class="chat-item-info">
            <div class="chat-item-name">${escHtml(u.name)}</div>
            <div class="chat-item-last">${escHtml(lastMsgText)}</div>
          </div>
          ${unreadHtml}
        </div>
      `;
    }).join('');
  }
}

// --------------------------------------------------------------------------
// 2. THREAD SELECTION & MESSAGE STREAM
// --------------------------------------------------------------------------
async function selectThread(threadId) {
  CHAT_STATE.activeThreadId = Number(threadId);
  renderChatDirectory();
  await loadActiveThreadMessages(false);
}

async function openDirectChatWith(userId) {
  try {
    const res = await JMOS_API.post('/chat/threads/direct', { recipient_id: userId });
    if (res.status === 'success' && res.thread_id) {
      await fetchChatData();
      selectThread(res.thread_id);
    }
  } catch (err) {
    showToast('Chat Error', err.message, true);
  }
}

async function loadActiveThreadMessages(isBackground = false) {
  if (!CHAT_STATE.activeThreadId) return;

  try {
    const res = await JMOS_API.get(`/chat/threads/${CHAT_STATE.activeThreadId}/messages`);
    if (res.status === 'success') {
      CHAT_STATE.activeThreadMeta = res.thread;
      const prevLength = CHAT_STATE.messages.length;
      CHAT_STATE.messages = res.messages || [];

      updateActiveThreadHeader();

      // Only re-render if message count changed or not in background
      if (!isBackground || CHAT_STATE.messages.length !== prevLength) {
        renderMessageList(isBackground);
      }
    }
  } catch (err) {
    console.warn('Messages load error:', err.message);
  }
}

function updateActiveThreadHeader() {
  const titleEl = document.getElementById('chatActiveTitle');
  const subEl = document.getElementById('chatActiveSubtitle');
  const avEl = document.getElementById('chatActiveAvatar');
  const typeBadge = document.getElementById('chatTypeBadge');

  if (!titleEl) return;

  const activeThread = CHAT_STATE.threads.find(t => t.id === CHAT_STATE.activeThreadId);
  if (activeThread) {
    titleEl.textContent = activeThread.title;
    if (subEl) subEl.textContent = activeThread.subtitle || (activeThread.type === 'direct' ? '1-on-1 Direct Chat' : 'Team Channel');
    if (avEl) {
      avEl.textContent = activeThread.avatar || (activeThread.type === 'group' ? '#' : 'JM');
      avEl.style.background = activeThread.color || 'var(--red)';
    }
    if (typeBadge) {
      typeBadge.textContent = activeThread.type === 'group' ? 'Team Channel' : '1-on-1 Direct';
    }
  } else if (CHAT_STATE.activeThreadMeta) {
    titleEl.textContent = CHAT_STATE.activeThreadMeta.title || 'Conversation';
    if (subEl) subEl.textContent = CHAT_STATE.activeThreadMeta.description || '';
  }
}

function renderMessageList(isBackground = false) {
  const streamEl = document.getElementById('chatMessageList');
  if (!streamEl) return;

  if (CHAT_STATE.messages.length === 0) {
    streamEl.innerHTML = `
      <div style="padding:40px 20px;text-align:center;color:var(--muted);font-size:13px">
        <div style="margin-bottom:10px;display:flex;justify-content:center"><svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6" style="color:var(--muted)"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <b style="color:var(--ink)">No messages here yet</b>
        <p style="margin:4px 0 0">Be the first to say hello and start the conversation!</p>
      </div>
    `;
    return;
  }

  streamEl.innerHTML = CHAT_STATE.messages.map(m => {
    const isMe = m.is_me;
    const bubbleClass = isMe ? 'bubble-me' : 'bubble-them';
    const alignClass = isMe ? 'msg-align-right' : 'msg-align-left';

    let attachmentHtml = '';
    if (m.attachment_url) {
      const ext = (m.attachment_url.split('.').pop() || '').toLowerCase();
      const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);

      if (isImage) {
        attachmentHtml = `
          <a href="${escHtml(m.attachment_url)}" target="_blank" class="chat-img-attachment" title="Click to view full photo">
            <img src="${escHtml(m.attachment_url)}" alt="${escHtml(m.attachment_name || 'Photo')}" loading="lazy">
          </a>
        `;
      } else {
        attachmentHtml = `
          <div class="chat-file-attachment">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div class="chat-file-info">
              <div class="name"><b>${escHtml(m.attachment_name || 'Document')}</b></div>
              <a href="${escHtml(m.attachment_url)}" download="${escHtml(m.attachment_name || 'attachment')}" class="download-link" target="_blank">Download file ⤓</a>
            </div>
          </div>
        `;
      }
    }

    return `
      <div class="chat-msg-row ${alignClass}">
        ${!isMe ? `<div class="chat-msg-av" style="background:${m.sender_color}">${escHtml(m.sender_initials)}</div>` : ''}
        <div class="chat-msg-content">
          ${!isMe ? `<div class="chat-msg-meta"><span class="chat-msg-author">${escHtml(m.sender_name)}</span> <span class="chat-msg-time">${escHtml(m.time_formatted)}</span></div>` : ''}
          <div class="chat-bubble ${bubbleClass}">
            ${m.message ? `<div class="chat-bubble-text">${formatMessageText(m.message)}</div>` : ''}
            ${attachmentHtml}
          </div>
          ${isMe ? `<div class="chat-msg-meta right"><span class="chat-msg-time">${escHtml(m.time_formatted)}</span></div>` : ''}
        </div>
      </div>
    `;
  }).join('');

  // Auto scroll to bottom
  if (!isBackground) {
    streamEl.scrollTop = streamEl.scrollHeight;
  }
}

function formatMessageText(text) {
  if (!text) return '';
  const escaped = escHtml(text);
  // Auto link URLs
  return escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline">$1</a>');
}

// --------------------------------------------------------------------------
// 3. ATTACHMENT & EMOJI PICKER HANDLING
// --------------------------------------------------------------------------
window.handleChatFileSelect = function(e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;

  const isImage = file.type.startsWith('image/') || /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(file.name);
  const previewUrl = isImage ? URL.createObjectURL(file) : '';

  CHAT_STATE.stagedAttachment = {
    file,
    name: file.name,
    size: formatFileSize(file.size),
    isImage,
    previewUrl,
  };

  renderStagedAttachment();
  e.target.value = '';
};

function formatFileSize(bytes) {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function renderStagedAttachment() {
  const stagingBar = document.getElementById('chatStagingBar');
  const previewEl = document.getElementById('chatStagingPreview');
  const nameEl = document.getElementById('chatStagingName');
  const sizeEl = document.getElementById('chatStagingSize');

  if (!stagingBar) return;

  if (!CHAT_STATE.stagedAttachment) {
    stagingBar.style.display = 'none';
    return;
  }

  const att = CHAT_STATE.stagedAttachment;
  stagingBar.style.display = 'flex';
  if (nameEl) nameEl.textContent = att.name;
  if (sizeEl) sizeEl.textContent = att.size;

  if (previewEl) {
    if (att.isImage && att.previewUrl) {
      previewEl.innerHTML = `<img src="${att.previewUrl}" alt="Preview">`;
    } else {
      previewEl.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>';
    }
  }
}

window.clearChatAttachment = function() {
  if (CHAT_STATE.stagedAttachment && CHAT_STATE.stagedAttachment.previewUrl) {
    URL.revokeObjectURL(CHAT_STATE.stagedAttachment.previewUrl);
  }
  CHAT_STATE.stagedAttachment = null;
  const fileInput = document.getElementById('chatFileInput');
  if (fileInput) fileInput.value = '';
  const camInput = document.getElementById('chatCameraInput');
  if (camInput) camInput.value = '';
  renderStagedAttachment();
};

window.toggleEmojiPicker = function(e) {
  if (e) e.stopPropagation();
  const picker = document.getElementById('chatEmojiPicker');
  const btn = document.getElementById('chatEmojiBtn');
  if (!picker) return;

  const isOpen = picker.style.display === 'block';
  if (isOpen) {
    window.closeEmojiPicker();
  } else {
    picker.style.display = 'block';
    if (btn) btn.classList.add('active');
  }
};

window.closeEmojiPicker = function() {
  const picker = document.getElementById('chatEmojiPicker');
  const btn = document.getElementById('chatEmojiBtn');
  if (picker) picker.style.display = 'none';
  if (btn) btn.classList.remove('active');
};

window.insertEmoji = function(emoji) {
  const input = document.getElementById('chatMessageInput');
  if (!input) return;

  const start = input.selectionStart || 0;
  const end = input.selectionEnd || 0;
  const text = input.value;
  input.value = text.substring(0, start) + emoji + text.substring(end);
  input.selectionStart = input.selectionEnd = start + emoji.length;
  input.focus();
};

// --------------------------------------------------------------------------
// 4. SEND MESSAGE
// --------------------------------------------------------------------------
window.sendActiveChatMessage = async function() {
  const inputEl = document.getElementById('chatMessageInput');
  const sendBtn = document.getElementById('chatSendBtn');

  if (!inputEl || !CHAT_STATE.activeThreadId) return;

  const text = inputEl.value.trim();
  const staged = CHAT_STATE.stagedAttachment;

  if (!text && !staged) return;

  if (sendBtn) {
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending…';
  }

  try {
    let attachmentName = null;
    let attachmentUrl = null;

    // Upload attachment if staged
    if (staged && staged.file) {
      const formData = new FormData();
      formData.append('file', staged.file);

      const uploadJson = await JMOS_API.upload('/chat/upload', formData);
      if (!uploadJson || uploadJson.status !== 'success') {
        throw new Error((uploadJson && uploadJson.message) || 'File upload failed');
      }

      attachmentName = uploadJson.attachment_name;
      attachmentUrl = uploadJson.attachment_url;
    }

    const res = await JMOS_API.post(`/chat/threads/${CHAT_STATE.activeThreadId}/messages`, {
      message: text,
      attachment_name: attachmentName,
      attachment_url: attachmentUrl
    });

    if (res.status === 'success' && res.message) {
      CHAT_STATE.messages.push(res.message);
      renderMessageList(false);
      inputEl.value = '';
      clearChatAttachment();
      closeEmojiPicker();
      fetchChatData();
    }
  } catch (err) {
    showToast('Failed to Send', err.message, true);
  } finally {
    if (sendBtn) {
      sendBtn.disabled = false;
      sendBtn.innerHTML = `<svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send`;
    }
    inputEl.focus();
  }
};

// --------------------------------------------------------------------------
// 4. CREATE NEW GROUP CHANNEL MODAL
// --------------------------------------------------------------------------
window.openChannelModal = function() {
  const nameInput = document.getElementById('nchanName');
  const descInput = document.getElementById('nchanDesc');
  if (nameInput) nameInput.value = '';
  if (descInput) descInput.value = '';
  openModal('channelModal');
};

window.saveNewChannel = async function() {
  const nameInput = document.getElementById('nchanName');
  const descInput = document.getElementById('nchanDesc');
  const btn = document.getElementById('saveChannelBtn');

  if (!nameInput || !nameInput.value.trim()) {
    showToast('Channel Name Required', 'Please provide a channel title', true);
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Creating…';
  }

  try {
    const res = await JMOS_API.post('/chat/threads', {
      title: nameInput.value.trim(),
      description: descInput ? descInput.value.trim() : null
    });

    if (res.status === 'success' && res.thread_id) {
      closeModal('channelModal');
      showToast('Channel Created', res.message || 'New group channel ready');
      await fetchChatData();
      selectThread(res.thread_id);
    }
  } catch (err) {
    showToast('Creation Failed', err.message, true);
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Create Channel';
    }
  }
};

// --------------------------------------------------------------------------
// 5. UNREAD COUNT POLLER & GLOBAL BADGES
// --------------------------------------------------------------------------
async function updateGlobalChatBadge() {
  try {
    const res = await JMOS_API.get('/chat/unread-count');
    if (res.status === 'success') {
      const unread = Number(res.unread_count) || 0;
      const sidebarBadge = document.getElementById('sidebarChatBadge');
      if (sidebarBadge) {
        if (unread > 0) {
          sidebarBadge.textContent = unread > 99 ? '99+' : unread;
          sidebarBadge.style.display = 'inline-flex';
        } else {
          sidebarBadge.style.display = 'none';
        }
      }

      // Seamless alert: if new chat arrives while user is on another view
      const chatView = document.querySelector('.view[data-view="chat"]');
      const isChatVisible = chatView && !chatView.hidden;

      if (!isChatVisible && CHAT_STATE.lastTotalUnread !== null && unread > CHAT_STATE.lastTotalUnread) {
        if (typeof showToast === 'function') {
          showToast('New Chat Message', 'You have new unread messages in Team Chat');
        }
        if (typeof fetchNotifications === 'function') {
          fetchNotifications();
        }
      }
      CHAT_STATE.lastTotalUnread = unread;
    }
  } catch (err) {
    // Silent catch
  }
}

// --------------------------------------------------------------------------
// 6. INITIALIZATION & EVENT DELEGATION
// --------------------------------------------------------------------------
function initChat() {
  // Delegate thread selection from sidebar directory
  document.addEventListener('click', (e) => {
    const threadItem = e.target.closest('[data-chat-thread]');
    if (threadItem) {
      const threadId = threadItem.getAttribute('data-chat-thread');
      selectThread(threadId);
      return;
    }

    const directItem = e.target.closest('[data-direct-user]');
    if (directItem) {
      const userId = directItem.getAttribute('data-direct-user');
      openDirectChatWith(userId);
      return;
    }

    // Dismiss emoji picker if clicked outside
    const picker = document.getElementById('chatEmojiPicker');
    const emojiBtn = document.getElementById('chatEmojiBtn');
    if (picker && picker.style.display === 'block') {
      if (!picker.contains(e.target) && !emojiBtn?.contains(e.target)) {
        window.closeEmojiPicker();
      }
    }
  });

  // Search input filter
  const searchInput = document.getElementById('chatSearchInput');
  if (searchInput) {
    searchInput.oninput = (e) => {
      CHAT_STATE.searchFilter = e.target.value;
      renderChatDirectory();
    };
  }

  // Keyboard shortcut for message sending (Enter to send, Shift+Enter for newline)
  const msgInput = document.getElementById('chatMessageInput');
  if (msgInput) {
    msgInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendActiveChatMessage();
      }
    });
  }

  // Initial fetch
  fetchChatData();

  // Periodic polling timer (every 3 seconds for active thread messages + unread badges)
  if (!CHAT_STATE.pollTimer) {
    CHAT_STATE.pollTimer = setInterval(() => {
      const chatView = document.querySelector('.view[data-view="chat"]');
      const isChatVisible = chatView && !chatView.hidden;

      if (isChatVisible && CHAT_STATE.activeThreadId) {
        loadActiveThreadMessages(true);
      }
      updateGlobalChatBadge();
    }, 3000);
  }
}

window.initChat = initChat;
window.fetchChatData = fetchChatData;
