/* ==========================================================================
   JMOS — AI Intelligence Assistant & Executive Reports Client
   ========================================================================== */

(function () {
  'use strict';

  let currentSessionId = localStorage.getItem('jmos_ai_session_id') || ('sess_' + Math.random().toString(36).substr(2, 9));
  localStorage.setItem('jmos_ai_session_id', currentSessionId);

  let currentMode = 'general_help';
  let isSending = false;

  // 1. Initialize AI UI Elements
  function initAiAssistant() {
    const fabBtn = document.getElementById('aiAssistantFab');
    const drawer = document.getElementById('aiAssistantDrawer');
    const overlay = document.getElementById('aiDrawerOverlay');
    const closeBtn = document.getElementById('aiDrawerClose');
    const clearBtn = document.getElementById('aiDrawerClear');
    const sendBtn = document.getElementById('aiSendBtn');
    const inputField = document.getElementById('aiInputField');
    const modeButtons = document.querySelectorAll('.ai-mode-btn');

    if (!fabBtn || !drawer) return;

    // Toggle Drawer
    fabBtn.addEventListener('click', () => toggleDrawer(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleDrawer(false));
    if (overlay) overlay.addEventListener('click', () => toggleDrawer(false));

    // Clear Chat
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        if (confirm('Clear current AI assistant conversation?')) {
          clearChatHistory();
        }
      });
    }

    // Mode Switches
    modeButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        modeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentMode = btn.dataset.mode || 'general_help';
        appendSystemModeNotice(currentMode);
      });
    });

    // Send Message Handlers
    if (sendBtn && inputField) {
      sendBtn.addEventListener('click', handleSendMessage);
      inputField.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          handleSendMessage();
        }
      });
    }

    // Quick Prompt Chips
    document.querySelectorAll('.ai-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        if (inputField) {
          inputField.value = chip.innerText;
          handleSendMessage();
        }
      });
    });

    // Load Existing History
    loadChatHistory();
  }

  function toggleDrawer(open) {
    const drawer = document.getElementById('aiAssistantDrawer');
    const overlay = document.getElementById('aiDrawerOverlay');
    if (!drawer || !overlay) return;

    if (open) {
      drawer.classList.add('open');
      overlay.classList.add('active');
      document.getElementById('aiInputField')?.focus();
    } else {
      drawer.classList.remove('open');
      overlay.classList.remove('active');
    }
  }

  // 2. Fetch Chat History
  async function loadChatHistory() {
    const token = JMOS_STATE.apiToken || localStorage.getItem('jmos_token');
    if (!token) return;

    try {
      const res = await fetch(`/api/ai/chat/history?session_id=${currentSessionId}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });

      if (!res.ok) return;
      const data = await res.json();

      const container = document.getElementById('aiMessagesContainer');
      if (!container) return;

      container.innerHTML = '';

      if (data.messages && data.messages.length > 0) {
        data.messages.forEach(msg => {
          renderMessage(msg.role, msg.content, msg.timestamp, msg.escalated, msg.navigation_links);
        });
      } else {
        renderWelcomeMessage();
      }

      scrollToBottom();
    } catch (_) {
      renderWelcomeMessage();
    }
  }

  function renderWelcomeMessage() {
    const user = JMOS_STATE.currentUser || { name: 'Team Member', role: 'member' };
    const greeting = `Hello **${user.name}**! 👋\n\nI am **J- ai**, your intelligent operating assistant for Jeota Media. I can help you navigate active video deliverables, manage leads, review commercial workflows, and troubleshoot issues.\n\nHow can I support your workflow today?`;
    renderMessage('assistant', greeting, new Date().toISOString());
  }

  function appendSystemModeNotice(mode) {
    const container = document.getElementById('aiMessagesContainer');
    if (!container) return;

    const names = {
      'general_help': 'General Workspace Help',
      'lead_gen': 'Client Acquisition & Lead Qualification',
      'fundraising_partner': 'Institutional Grants & Donor Sourcing'
    };

    const div = document.createElement('div');
    div.style.cssText = 'text-align:center;font-size:11px;color:var(--muted);margin:8px 0;opacity:0.8;font-family:"IBM Plex Mono",monospace';
    div.innerHTML = `— Mode Switched: <strong>${names[mode] || mode}</strong> —`;
    container.appendChild(div);
    scrollToBottom();
  }

  // 3. Send Message
  async function handleSendMessage() {
    if (isSending) return;
    const input = document.getElementById('aiInputField');
    const text = input?.value?.trim();
    if (!text) return;

    const token = JMOS_STATE.apiToken || localStorage.getItem('jmos_token');
    if (!token) {
      alert('Please sign in to interact with the AI Assistant.');
      return;
    }

    input.value = '';
    renderMessage('user', text, new Date().toISOString());
    scrollToBottom();

    isSending = true;
    showTypingIndicator(true);

    try {
      const res = await fetch('/api/ai/chat/message', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          message: text,
          session_id: currentSessionId,
          mode: currentMode
        })
      });

      showTypingIndicator(false);
      isSending = false;

      if (!res.ok) {
        renderMessage('assistant', '⚠️ AI Assistant is temporarily unreachable. Please try again shortly.', new Date().toISOString());
        return;
      }

      const data = await res.json();
      renderMessage('assistant', data.message, new Date().toISOString(), data.escalated, data.navigation_links);
      scrollToBottom();
    } catch (err) {
      showTypingIndicator(false);
      isSending = false;
      renderMessage('assistant', '⚠️ Network connection issue. Your request could not be completed.', new Date().toISOString());
    }
  }

  // 4. Render Message in DOM
  function renderMessage(role, content, timestamp, escalated = false, navLinks = []) {
    const container = document.getElementById('aiMessagesContainer');
    if (!container) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-msg ${role}`;

    let parsedHtml = formatSimpleMarkdown(content);

    // If escalated or has nav links
    if (escalated || (navLinks && navLinks.length > 0)) {
      let navHtml = '';
      if (navLinks && navLinks.length > 0) {
        navHtml = `
          <div class="ai-escalation-card">
            <div class="badge-alert">Quick Navigation & Support</div>
            <div class="ai-nav-buttons">
              ${navLinks.map(l => `<button type="button" class="ai-nav-btn" data-nav-view="${l.view || ''}">${l.label}</button>`).join('')}
            </div>
          </div>
        `;
      }
      parsedHtml += navHtml;
    }

    const timeFormatted = timestamp ? new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
    msgDiv.innerHTML = `
      <div class="ai-msg-body">${parsedHtml}</div>
      <div class="ai-msg-time">${timeFormatted}</div>
    `;

    // Attach click listeners to any generated navigation buttons
    msgDiv.querySelectorAll('.ai-nav-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const view = e.currentTarget.dataset.navView;
        if (view && typeof window.navigateToView === 'function') {
          window.navigateToView(view);
          toggleDrawer(false);
        }
      });
    });

    container.appendChild(msgDiv);
  }

  function showTypingIndicator(show) {
    const container = document.getElementById('aiMessagesContainer');
    const existing = document.getElementById('aiTypingIndicator');

    if (show && !existing && container) {
      const typingDiv = document.createElement('div');
      typingDiv.id = 'aiTypingIndicator';
      typingDiv.className = 'ai-msg assistant';
      typingDiv.innerHTML = '<div class="ai-loading-dots"><span></span><span></span><span></span></div>';
      container.appendChild(typingDiv);
      scrollToBottom();
    } else if (!show && existing) {
      existing.remove();
    }
  }

  function scrollToBottom() {
    const container = document.getElementById('aiMessagesContainer');
    if (container) {
      container.scrollTop = container.scrollHeight;
    }
  }

  async function clearChatHistory() {
    const token = JMOS_STATE.apiToken || localStorage.getItem('jmos_token');
    if (!token) return;

    try {
      await fetch('/api/ai/chat/clear', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ session_id: currentSessionId })
      });

      currentSessionId = 'sess_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('jmos_ai_session_id', currentSessionId);
      loadChatHistory();
    } catch (_) {}
  }

  function formatSimpleMarkdown(text) {
    if (!text) return '';
    let html = text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    // Headers
    html = html.replace(/^### (.*$)/gim, '<h4>$1</h4>');
    html = html.replace(/^## (.*$)/gim, '<h3>$1</h3>');

    // Bold & Italics
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/`([^`]+)`/g, '<code style="background:rgba(0,0,0,0.06);padding:2px 4px;border-radius:4px;font-family:\'IBM Plex Mono\',monospace;font-size:11.5px">$1</code>');

    // Lists
    html = html.replace(/^\s*-\s(.*)$/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>)/gims, '<ul style="margin:6px 0;padding-left:18px">$1</ul>');

    // Paragraphs / Newlines
    html = html.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');

    return html;
  }

  // 5. AI Executive Reports Trigger & Modal
  window.openAiReportModal = async function (reportType = 'executive_digest', forceRefresh = false) {
    const token = JMOS_STATE.apiToken || localStorage.getItem('jmos_token');
    if (!token) {
      alert('Please sign in to generate AI reports.');
      return;
    }

    const modal = document.getElementById('aiReportModal');
    const contentBox = document.getElementById('aiReportContent');
    const metaBox = document.getElementById('aiReportMeta');
    const titleBox = document.getElementById('aiReportModalTitle');

    if (!modal || !contentBox) return;

    const titles = {
      'executive_digest': 'J- ai Executive Intelligence Briefing',
      'financial_summary': 'J- ai Financial Ledger & Performance Summary',
      'project_status': 'J- ai Production Operations & Deliverables Digest'
    };

    if (titleBox) titleBox.innerText = titles[reportType] || 'J- ai Intelligence Report';

    modal.style.display = 'flex';
    contentBox.innerHTML = `
      <div style="padding:40px 20px;text-align:center;color:var(--muted)">
        <div class="ai-loading-dots" style="margin-bottom:12px"><span></span><span></span><span></span></div>
        <div>Generating role-scoped intelligence briefing...</div>
      </div>
    `;

    try {
      const res = await fetch('/api/ai/reports/generate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          report_type: reportType,
          refresh: forceRefresh
        })
      });

      if (!res.ok) {
        if (res.status === 403) {
          contentBox.innerHTML = '<div style="color:#ef4444;padding:20px;text-align:center">⛔ Access Restricted: You do not possess the required role permissions for this report.</div>';
          return;
        }
        contentBox.innerHTML = '<div style="color:#ef4444;padding:20px;text-align:center">⚠️ Report generation temporarily unavailable.</div>';
        return;
      }

      const report = await res.json();
      contentBox.innerHTML = `
        <div style="font-size:13.5px;line-height:1.6;color:var(--ink)">
          ${formatSimpleMarkdown(report.summary)}
        </div>
      `;

      if (metaBox) {
        metaBox.innerHTML = `
          <span>Scope: <strong>${report.scope || 'Authorized Records'}</strong></span> &middot;
          <span>Engine: <strong>${report.model || 'Gemini'}</strong></span> &middot;
          <span>Cached: <strong>${report.cached ? 'Yes (Fast)' : 'Fresh'}</strong></span>
        `;
      }
    } catch (err) {
      contentBox.innerHTML = '<div style="color:#ef4444;padding:20px;text-align:center">⚠️ Network error while fetching report.</div>';
    }
  };

  // Attach DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAiAssistant);
  } else {
    initAiAssistant();
  }
})();
