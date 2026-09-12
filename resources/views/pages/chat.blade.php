<!-- ==========================================================================
     JMOS — View: Team & 1-on-1 Direct Chat Hub
     ========================================================================== -->
<section class="view" data-view="chat" hidden>
  <!-- Page Header -->
  <div class="page-head">
    <div>
      <h1 class="pt">Team Chat &amp; Direct Messaging</h1>
      <p>Real-time team communication, shoot &amp; production channels, and 1-on-1 direct conversations with email alerts.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn" id="newChannelBtn" onclick="openChannelModal()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New Channel
      </button>
    </div>
  </div>

  <!-- Chat Hub Layout -->
  <div class="chat-layout">
    
    <!-- Left Pane: Channels & Direct Messages Directory -->
    <div class="card chat-dir-card">
      
      <!-- Search Filter -->
      <div class="chat-search-wrap">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" id="chatSearchInput" placeholder="Filter channels or people…">
      </div>

      <div class="chat-dir-scroll">
        
        <!-- Channels Section -->
        <div class="chat-section-header">
          <span>TEAM CHANNELS</span>
          <button type="button" class="chat-add-btn" onclick="openChannelModal()" title="Add Channel">+</button>
        </div>
        <div class="chat-threads-list" id="chatChannelsList">
          <div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Loading channels…</div>
        </div>

        <!-- Direct Messages (1-on-1) Section -->
        <div class="chat-section-header" style="margin-top:16px">
          <span>DIRECT MESSAGES</span>
          <span class="chat-count-pill" id="directMembersCount">7</span>
        </div>
        <div class="chat-threads-list" id="chatDirectList">
          <div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Loading team…</div>
        </div>

      </div>
    </div>

    <!-- Right Pane: Active Conversation Message Stream -->
    <div class="card chat-conv-card">
      
      <!-- Thread Header -->
      <div class="chat-conv-header" id="chatConvHeader">
        <div style="display:flex;align-items:center;gap:12px">
          <div class="chat-thread-av" id="chatActiveAvatar" style="background:var(--red)">#</div>
          <div>
            <div class="chat-active-title" id="chatActiveTitle">#general</div>
            <div class="chat-active-sub" id="chatActiveSubtitle">General team discussion &amp; announcements</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge" id="chatTypeBadge" style="background:var(--red-soft);color:var(--red);font-size:11px">Team Channel</span>
          <button type="button" class="icon-btn-sm" id="chatRefreshBtn" title="Refresh messages" onclick="loadActiveThreadMessages(true)" style="padding:4px 8px;font-size:12px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          </button>
        </div>
      </div>

      <!-- Messages Stream -->
      <div class="chat-messages-stream" id="chatMessageList">
        <div style="padding:40px 20px;text-align:center;color:var(--muted);font-size:13px">
          <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;opacity:0.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <p style="margin:0">Select a channel or team member to start chatting.</p>
        </div>
      </div>

      <!-- Message Composer Box -->
      <div class="chat-composer">
        <!-- Hidden file and camera inputs -->
        <input type="file" id="chatFileInput" style="display:none" onchange="handleChatFileSelect(event)">
        <input type="file" id="chatCameraInput" accept="image/*" capture="environment" style="display:none" onchange="handleChatFileSelect(event)">

        <!-- Attachment Staging Preview -->
        <div class="chat-staging-bar" id="chatStagingBar" style="display:none">
          <div class="chat-staging-preview" id="chatStagingPreview"></div>
          <div class="chat-staging-info">
            <span class="chat-staging-name" id="chatStagingName"></span>
            <span class="chat-staging-size" id="chatStagingSize"></span>
          </div>
          <button type="button" class="chat-staging-remove" onclick="clearChatAttachment()" title="Remove attachment">&times;</button>
        </div>

        <!-- Floating Emoji Picker -->
        <div class="chat-emoji-picker" id="chatEmojiPicker" style="display:none">
          <div class="chat-emoji-header">
            <span>Pick an emoji</span>
            <button type="button" class="chat-emoji-close" onclick="closeEmojiPicker()">&times;</button>
          </div>
          <div class="chat-emoji-grid">
            <button type="button" class="emoji-btn" onclick="insertEmoji('😀')">😀</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😃')">😃</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😄')">😄</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😁')">😁</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😆')">😆</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😅')">😅</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😂')">😂</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🤣')">🤣</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😊')">😊</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😇')">😇</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🙂')">🙂</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😉')">😉</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😍')">😍</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🥰')">🥰</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😘')">😘</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😋')">😋</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😜')">😜</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('😎')">😎</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🤩')">🤩</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🤔')">🤔</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('👍')">👍</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('👎')">👎</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('👌')">👌</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('✌️')">✌️</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🤞')">🤞</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('👏')">👏</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🙌')">🙌</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🤝')">🤝</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('👊')">👊</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🙏')">🙏</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('💪')">💪</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎬')">🎬</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📸')">📸</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📹')">📹</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎥')">🎥</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎙️')">🎙️</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎧')">🎧</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('💻')">💻</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📱')">📱</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('💡')">💡</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📝')">📝</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📅')">📅</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('📌')">📌</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎯')">🎯</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🔥')">🔥</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('⭐')">⭐</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('✨')">✨</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🎉')">🎉</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('🚀')">🚀</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('💯')">💯</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('❤️')">❤️</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('✅')">✅</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('❌')">❌</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('⚠️')">⚠️</button>
            <button type="button" class="emoji-btn" onclick="insertEmoji('⏳')">⏳</button>
          </div>
        </div>

        <div class="chat-composer-inner">
          <div class="chat-composer-tools">
            <button type="button" class="chat-tool-btn" onclick="document.getElementById('chatFileInput').click()" title="Attach file or document" aria-label="Attach file">
              <svg viewBox="0 0 24 24"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <button type="button" class="chat-tool-btn" onclick="document.getElementById('chatCameraInput').click()" title="Capture photo or camera image" aria-label="Capture photo">
              <svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </button>
            <button type="button" class="chat-tool-btn" id="chatEmojiBtn" onclick="toggleEmojiPicker(event)" title="Insert emoji" aria-label="Insert emoji">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            </button>
          </div>

          <textarea id="chatMessageInput" placeholder="Type a message… (Press Enter to send, Shift+Enter for new line)" rows="1"></textarea>

          <button type="button" class="btn primary" id="chatSendBtn" onclick="sendActiveChatMessage()">
            <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            Send
          </button>
        </div>
        <div class="chat-composer-hints">
          <span>💡 <b>Pro-Tip:</b> Attach files, camera photos, or emojis. Direct messages are strictly private to you and the recipient.</span>
        </div>
      </div>

    </div>

  </div>
</section>
