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
  pollTimer: null,
  searchFilter: ''
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
      CHAT_STATE.messages = res.messages || [];

      updateActiveThreadHeader();
      renderMessageList(isBackground);
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
        <div style="font-size:28px;margin-bottom:8px">💬</div>
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

    return `
      <div class="chat-msg-row ${alignClass}">
        ${!isMe ? `<div class="chat-msg-av" style="background:${m.sender_color}">${escHtml(m.sender_initials)}</div>` : ''}
        <div class="chat-msg-content">
          ${!isMe ? `<div class="chat-msg-meta"><span class="chat-msg-author">${escHtml(m.sender_name)}</span> <span class="chat-msg-time">${escHtml(m.time_formatted)}</span></div>` : ''}
          <div class="chat-bubble ${bubbleClass}">
            <div class="chat-bubble-text">${formatMessageText(m.message)}</div>
            ${m.attachment_url ? `<div class="chat-attachment-chip"><a href="${escHtml(m.attachment_url)}" target="_blank">📎 ${escHtml(m.attachment_name || 'Attachment')}</a></div>` : ''}
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
// 3. SEND MESSAGE
// --------------------------------------------------------------------------
window.sendActiveChatMessage = async function() {
  const inputEl = document.getElementById('chatMessageInput');
  const sendBtn = document.getElementById('chatSendBtn');

  if (!inputEl || !CHAT_STATE.activeThreadId) return;

  const text = inputEl.value.trim();
  if (!text) return;

  inputEl.value = '';
  inputEl.focus();

  if (sendBtn) sendBtn.disabled = true;

  try {
    const res = await JMOS_API.post(`/chat/threads/${CHAT_STATE.activeThreadId}/messages`, {
      message: text
    });

    if (res.status === 'success' && res.message) {
      CHAT_STATE.messages.push(res.message);
      renderMessageList(false);
      fetchChatData(); // Refresh unread and last message previews
    }
  } catch (err) {
    showToast('Failed to Send', err.message, true);
  } finally {
    if (sendBtn) sendBtn.disabled = false;
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

  // Periodic polling timer (every 4 seconds for active thread messages + unread badges)
  if (!CHAT_STATE.pollTimer) {
    CHAT_STATE.pollTimer = setInterval(() => {
      const chatView = document.querySelector('.view[data-view="chat"]');
      const isChatVisible = chatView && !chatView.hidden;

      if (isChatVisible && CHAT_STATE.activeThreadId) {
        loadActiveThreadMessages(true);
      }
      updateGlobalChatBadge();
    }, 4000);
  }
}

window.initChat = initChat;
window.fetchChatData = fetchChatData;
