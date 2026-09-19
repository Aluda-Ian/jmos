/* ==========================================================================
   JMOS — Operational Notifications Center & Bell Badge Controller
   ========================================================================== */

window.JMOS_NOTIFS = {
  currentFilter: 'all',
  items: [],
  unreadCount: 0,
  totalCount: 0,
  pollTimer: null
};

// 1. Format Relative Time
function formatRelativeTime(dateStr) {
  if (!dateStr) return 'Recently';
  const date = new Date(dateStr);
  const now = new Date();
  const diffSec = Math.floor((now - date) / 1000);

  if (diffSec < 60) return 'Just now';
  const diffMin = Math.floor(diffSec / 60);
  if (diffMin < 60) return `${diffMin}m ago`;
  const diffHours = Math.floor(diffMin / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// 2. Fetch Notifications from API
async function fetchNotifications(filter = null) {
  if (!JMOS_STATE.apiToken) return;

  const targetFilter = filter !== null ? filter : window.JMOS_NOTIFS.currentFilter;
  let endpoint = '/notifications';
  if (targetFilter && targetFilter !== 'all') {
    endpoint += `?filter=${targetFilter}`;
  }

  try {
    const res = await JMOS_API.get(endpoint);
    if (res && res.status === 'success') {
      window.JMOS_NOTIFS.items = res.data || [];
      window.JMOS_NOTIFS.unreadCount = res.unread_count || 0;
      window.JMOS_NOTIFS.totalCount = res.total_count || 0;
      
      updateNotificationBellBadge();
      updateNotificationTabCounts();

      // If panel/modal is open, re-render items
      const notifPanel = document.getElementById('notifPanel') || document.getElementById('notifModal');
      if (notifPanel && (notifPanel.classList.contains('open') || notifPanel.classList.contains('on'))) {
        renderNotificationItems();
      }

      // Dispatch native browser push notification for new arrivals
      if (window.JMOS_PUSH && window.JMOS_PUSH.permission === 'granted') {
        if (!window.JMOS_PUSH.isInitialLoad) {
          (res.data || []).forEach(item => {
            if (!item.read_at && !window.JMOS_PUSH.seenIds.has(item.id)) {
              window.JMOS_PUSH.send({
                title: item.title || 'JMOS Notification',
                body: item.message || '',
                url: item.link || '/'
              });
            }
          });
        }
        (res.data || []).forEach(item => window.JMOS_PUSH.seenIds.add(item.id));
        window.JMOS_PUSH.isInitialLoad = false;
      }
    }
  } catch (err) {
    console.warn('Failed to fetch notifications:', err);
  }
}

// 3. Update Bell Icon Badge & Ping
function updateNotificationBellBadge() {
  const badge = document.getElementById('notifBadge');
  const ping = document.getElementById('notifPing');
  const modalPill = document.getElementById('notifModalUnreadPill');
  const count = window.JMOS_NOTIFS.unreadCount || 0;

  if (badge) {
    if (count > 0) {
      badge.style.display = 'inline-flex';
      badge.textContent = count > 99 ? '99+' : count;
    } else {
      badge.style.display = 'none';
      badge.textContent = '0';
    }
  }

  if (ping) {
    ping.style.display = count > 0 ? 'block' : 'none';
  }

  if (modalPill) {
    modalPill.textContent = `${count} unread`;
    modalPill.style.display = count > 0 ? 'inline-block' : 'none';
  }
}

// 4. Update Tab Count Badges in Modal
function updateNotificationTabCounts() {
  const badgeAll = document.getElementById('notifBadgeAll');
  const badgeUnread = document.getElementById('notifBadgeUnread');
  const badgeRead = document.getElementById('notifBadgeRead');

  const unreadCount = window.JMOS_NOTIFS.unreadCount;
  const totalCount = window.JMOS_NOTIFS.totalCount;
  const readCount = Math.max(0, totalCount - unreadCount);

  if (badgeAll) badgeAll.textContent = totalCount;
  if (badgeUnread) badgeUnread.textContent = unreadCount;
  if (badgeRead) badgeRead.textContent = readCount;
}

// 5. Open / Toggle Notification Popover Panel
window.toggleNotificationPanel = function(e) {
  if (e) {
    if (typeof e.stopPropagation === 'function') e.stopPropagation();
    if (typeof e.preventDefault === 'function') e.preventDefault();
  }
  const panel = document.getElementById('notifPanel');
  if (!panel) return;

  const isOpen = panel.classList.contains('open');
  if (isOpen) {
    closeNotificationPanel();
  } else {
    openNotificationPanel();
  }
};

function positionNotificationPanel() {
  const panel = document.getElementById('notifPanel');
  if (!panel) return;

  const isMobile = window.innerWidth <= 860;
  if (isMobile) {
    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('top', '50%', 'important');
    panel.style.setProperty('left', '50%', 'important');
    panel.style.setProperty('right', 'auto', 'important');
    panel.style.setProperty('bottom', 'auto', 'important');
    panel.style.setProperty('transform', 'translate(-50%, -50%)', 'important');
    panel.style.setProperty('width', 'calc(100vw - 28px)', 'important');
    panel.style.setProperty('max-width', '420px', 'important');
    panel.style.setProperty('max-height', 'calc(100vh - 50px)', 'important');
    panel.style.setProperty('margin', '0', 'important');
    panel.style.setProperty('z-index', '999999', 'important');
    panel.style.setProperty('box-shadow', '0 24px 70px rgba(0, 0, 0, 0.75), 0 0 0 100vmax rgba(0, 0, 0, 0.65)', 'important');
  } else {
    panel.style.removeProperty('position');
    panel.style.removeProperty('top');
    panel.style.removeProperty('left');
    panel.style.removeProperty('right');
    panel.style.removeProperty('bottom');
    panel.style.removeProperty('transform');
    panel.style.removeProperty('width');
    panel.style.removeProperty('max-width');
    panel.style.removeProperty('max-height');
    panel.style.removeProperty('margin');
    panel.style.removeProperty('z-index');
    panel.style.removeProperty('box-shadow');
  }
}

window.openNotificationPanel = function() {
  const panel = document.getElementById('notifPanel');
  const bell = document.getElementById('notifBellBtn');
  if (!panel) return;

  positionNotificationPanel();
  panel.classList.add('open');
  if (bell) bell.classList.add('active');
  renderNotificationItems();
  fetchNotifications(window.JMOS_NOTIFS.currentFilter || 'all');
};

window.closeNotificationPanel = function() {
  const panel = document.getElementById('notifPanel');
  const bell = document.getElementById('notifBellBtn');
  if (panel) panel.classList.remove('open');
  if (bell) bell.classList.remove('active');
};

window.openNotificationModal = function(e) {
  if (e) {
    if (typeof e.stopPropagation === 'function') e.stopPropagation();
    if (typeof e.preventDefault === 'function') e.preventDefault();
  }
  window.openNotificationPanel();
};

window.addEventListener('resize', () => {
  const panel = document.getElementById('notifPanel');
  if (panel && panel.classList.contains('open')) {
    positionNotificationPanel();
  }
});

// 6. Filter Notifications Tab
window.filterNotifications = function(filter) {
  window.JMOS_NOTIFS.currentFilter = filter;

  // Update button active classes
  ['all', 'unread', 'read'].forEach(f => {
    const btn = document.getElementById('notifTab' + f.charAt(0).toUpperCase() + f.slice(1));
    if (btn) {
      if (f === filter) btn.classList.add('active');
      else btn.classList.remove('active');
    }
  });

  fetchNotifications(filter);
};

// 7. Render Notifications in List
function renderNotificationItems() {
  const listEl = document.getElementById('notifItemsList');
  if (!listEl) return;

  const items = window.JMOS_NOTIFS.items || [];
  const filter = window.JMOS_NOTIFS.currentFilter;

  if (items.length === 0) {
    let emptyTitle = 'No notifications';
    let emptyMsg = 'You are completely caught up!';
    if (filter === 'unread') {
      emptyTitle = 'No unread notifications';
      emptyMsg = 'All operational updates have been read.';
    } else if (filter === 'read') {
      emptyTitle = 'No read notifications';
      emptyMsg = 'Notifications marked as read will appear here.';
    }

    listEl.innerHTML = `
      <div class="notif-empty-state">
        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
        <h4>${escHtml(emptyTitle)}</h4>
        <p>${escHtml(emptyMsg)}</p>
      </div>
    `;
    return;
  }

  const html = items.map(item => {
    const isUnread = !item.read_at;
    const type = item.type || 'system';

    // Type icons
    let typeIcon = `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>`;
    if (type === 'calendar') {
      typeIcon = `<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>`;
    } else if (type === 'invoice') {
      typeIcon = `<svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18M8 14h2"/></svg>`;
    } else if (type === 'chat') {
      typeIcon = `<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`;
    } else if (type === 'deal') {
      typeIcon = `<svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>`;
    }

    // Direct link button
    let linkBtnHtml = '';
    if (item.link) {
      const linkLabel = getNotificationLinkLabel(item.link);
      linkBtnHtml = `
        <button type="button" class="notif-btn-link" onclick="jumpToNotificationLink('${escHtml(item.link)}')">
          ${escHtml(linkLabel)}
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      `;
    }

    // Toggle button
    const toggleBtnHtml = isUnread
      ? `<button type="button" class="notif-btn-toggle" onclick="toggleNotificationRead(${item.id}, true)" title="Mark as read">
           <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
           Mark as read
         </button>`
      : `<button type="button" class="notif-btn-toggle" onclick="toggleNotificationRead(${item.id}, false)" title="Mark as unread">
           <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/></svg>
           Mark as unread
         </button>`;

    return `
      <div class="notif-card ${isUnread ? 'unread' : 'read'}" id="notifCard-${item.id}">
        <div class="notif-icon-wrap type-${escHtml(type)}">
          ${typeIcon}
        </div>
        <div class="notif-body-wrap">
          <div class="notif-title-row">
            <div class="notif-item-title">
              ${isUnread ? '<span class="notif-dot-unread" title="Unread"></span>' : ''}
              ${escHtml(item.title)}
            </div>
            <div class="notif-item-time">${formatRelativeTime(item.created_at)}</div>
          </div>
          <div class="notif-item-msg">${escHtml(item.message)}</div>
          <div class="notif-actions-row">
            ${linkBtnHtml}
            ${toggleBtnHtml}
            <button type="button" class="notif-btn-del" onclick="deleteNotificationItem(${item.id})" title="Dismiss notification">&times;</button>
          </div>
        </div>
      </div>
    `;
  }).join('');

  listEl.innerHTML = html;
}

// 8. Human-friendly Link Labels
function getNotificationLinkLabel(link) {
  if (link === 'calendar') return 'Open Calendar';
  if (link === 'settings') return 'Open Settings';
  if (link === 'chat') return 'Open Chat';
  if (link === 'invoices') return 'View Invoices';
  if (link === 'dashboard') return 'View Dashboard';
  if (link === 'pipeline') return 'View Pipeline';
  return 'Open';
}

// 9. Jump to View from Notification
window.jumpToNotificationLink = function(link) {
  closeNotificationPanel();
  if (typeof closeModal === 'function') closeModal('notifModal');
  if (typeof showView === 'function') {
    showView(link);
  }
};

// 10. Toggle Read / Unread Status
window.toggleNotificationRead = async function(id, markAsRead) {
  const action = markAsRead ? 'read' : 'unread';
  try {
    const res = await JMOS_API.post(`/notifications/${id}/${action}`, {});
    if (res && res.status === 'success') {
      window.JMOS_NOTIFS.unreadCount = res.unread_count;
      
      // Update item in local array
      const item = window.JMOS_NOTIFS.items.find(i => i.id === id);
      if (item) {
        item.read_at = markAsRead ? new Date().toISOString() : null;
      }

      updateNotificationBellBadge();
      updateNotificationTabCounts();

      // If viewing unread tab and item is marked read, remove or refresh
      if (window.JMOS_NOTIFS.currentFilter === 'unread' && markAsRead) {
        window.JMOS_NOTIFS.items = window.JMOS_NOTIFS.items.filter(i => i.id !== id);
      } else if (window.JMOS_NOTIFS.currentFilter === 'read' && !markAsRead) {
        window.JMOS_NOTIFS.items = window.JMOS_NOTIFS.items.filter(i => i.id !== id);
      }

      renderNotificationItems();
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Error', err.message, true);
  }
};

// 11. Mark All Notifications as Read
window.markAllNotificationsRead = async function() {
  try {
    const res = await JMOS_API.post('/notifications/mark-all-read', {});
    if (res && res.status === 'success') {
      window.JMOS_NOTIFS.unreadCount = 0;
      
      window.JMOS_NOTIFS.items.forEach(i => {
        if (!i.read_at) i.read_at = new Date().toISOString();
      });

      updateNotificationBellBadge();
      updateNotificationTabCounts();

      if (window.JMOS_NOTIFS.currentFilter === 'unread') {
        window.JMOS_NOTIFS.items = [];
      }

      renderNotificationItems();
      if (typeof showToast === 'function') {
        showToast('All read', 'All notifications marked as read.');
      }
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Error', err.message, true);
  }
};

// 12. Delete Notification
window.deleteNotificationItem = async function(id) {
  try {
    const res = await JMOS_API.delete(`/notifications/${id}`);
    if (res && res.status === 'success') {
      window.JMOS_NOTIFS.unreadCount = res.unread_count;
      window.JMOS_NOTIFS.totalCount = Math.max(0, window.JMOS_NOTIFS.totalCount - 1);
      window.JMOS_NOTIFS.items = window.JMOS_NOTIFS.items.filter(i => i.id !== id);

      updateNotificationBellBadge();
      updateNotificationTabCounts();
      renderNotificationItems();
    }
  } catch (err) {
    if (typeof showToast === 'function') showToast('Error', err.message, true);
  }
};

/* ==========================================================================
   JMOS — Native Browser & Web Push Notifications Manager
   ========================================================================== */
window.JMOS_PUSH = {
  isSupported: ('Notification' in window),
  permission: ('Notification' in window) ? Notification.permission : 'unsupported',
  seenIds: new Set(),
  isInitialLoad: true,

  async requestPermission() {
    if (!this.isSupported) {
      if (typeof showToast === 'function') {
        showToast('Push Unsupported', 'Your browser does not support the Web Notifications API.', true);
      }
      return false;
    }

    try {
      const perm = await Notification.requestPermission();
      this.permission = perm;
      this.updateUiControls();

      if (perm === 'granted') {
        this.send({
          title: 'JMOS Notifications Activated 🔔',
          body: 'You will now receive desktop and mobile push alerts for shoots, tasks, chats, and projects.',
          url: '/'
        });

        if (typeof showToast === 'function') {
          showToast('Push Enabled', 'Browser push notifications are now active on this device!');
        }

        // Record audit log
        if (window.JMOS_API && window.JMOS_API.post) {
          window.JMOS_API.post('/audit-logs', {
            action: 'AUTH',
            description: 'User enabled browser push notifications on ' + (navigator.userAgent.includes('Mobile') ? 'Mobile device' : 'PC / Desktop'),
            entity_type: 'System'
          }).catch(() => {});
        }
        return true;
      } else {
        if (typeof showToast === 'function') {
          showToast('Notifications Blocked', 'Permission was denied. Please allow notifications in your browser address bar settings.', true);
        }
        return false;
      }
    } catch (err) {
      console.warn('Error requesting notification permission:', err);
      return false;
    }
  },

  send(options = {}) {
    if (!this.isSupported || Notification.permission !== 'granted') return;

    const title = options.title || 'JMOS Alert';
    const notifOptions = {
      body: options.body || '',
      icon: options.icon || '/assets/img/jeota-logo.png',
      badge: options.badge || '/assets/img/jeota-logo.png',
      tag: options.tag || ('jmos-' + Date.now()),
      data: { url: options.url || '/' },
      requireInteraction: options.requireInteraction || false
    };

    if ('serviceWorker' in navigator && navigator.serviceWorker.ready) {
      navigator.serviceWorker.ready.then(reg => {
        if (reg && reg.showNotification) {
          reg.showNotification(title, notifOptions);
        } else {
          this.fallbackNotification(title, notifOptions);
        }
      }).catch(() => {
        this.fallbackNotification(title, notifOptions);
      });
    } else {
      this.fallbackNotification(title, notifOptions);
    }
  },

  fallbackNotification(title, options) {
    try {
      const n = new Notification(title, options);
      n.onclick = function() {
        window.focus();
        if (options.data?.url && options.data.url !== '/') {
          if (typeof navigateToView === 'function') {
            navigateToView(options.data.url.replace('/', ''));
          }
        }
        n.close();
      };
    } catch (e) {
      console.warn('Fallback Notification failed:', e);
    }
  },

  updateUiControls() {
    const statusBadges = document.querySelectorAll('.push-status-badge');
    const enableBtns = document.querySelectorAll('.push-enable-btn');
    const isGranted = (this.permission === 'granted');

    statusBadges.forEach(b => {
      if (isGranted) {
        b.textContent = 'Active / Allowed';
        b.style.background = 'rgba(43,138,90,0.15)';
        b.style.color = 'var(--green)';
      } else if (this.permission === 'denied') {
        b.textContent = 'Blocked by Browser';
        b.style.background = 'rgba(197,37,35,0.15)';
        b.style.color = 'var(--red)';
      } else {
        b.textContent = 'Not Enabled';
        b.style.background = 'rgba(217,119,6,0.15)';
        b.style.color = 'var(--amber)';
      }
    });

    enableBtns.forEach(btn => {
      if (isGranted) {
        btn.textContent = 'Push Active ✓';
        btn.disabled = true;
        btn.style.opacity = '0.75';
      } else {
        btn.textContent = 'Enable Browser Notifications';
        btn.disabled = false;
        btn.style.opacity = '1';
      }
    });
  },

  scheduleDailyFirstVisitPrompt() {
    if (!this.isSupported) return;
    if (Notification.permission !== 'default') return;

    try {
      const todayStr = new Date().toISOString().split('T')[0];
      const lastPromptDate = localStorage.getItem('jmos_notif_prompt_date');

      // Only trigger on the day's first site visit
      if (lastPromptDate === todayStr) {
        return;
      }

      // Record today's visit so subsequent visits today won't re-prompt
      localStorage.setItem('jmos_notif_prompt_date', todayStr);

      // Trigger after 10 seconds of the day's first site visit
      setTimeout(() => {
        if (Notification.permission === 'default') {
          this.showPermissionBanner();
        }
      }, 10000);
    } catch (e) {
      console.warn('Error scheduling notification prompt:', e);
    }
  },

  showPermissionBanner() {
    if (document.getElementById('jmosPushPromptBanner')) return;

    const banner = document.createElement('div');
    banner.id = 'jmosPushPromptBanner';
    banner.style.cssText = 'position:fixed;top:20px;right:20px;max-width:390px;width:calc(100vw - 40px);background:var(--surface,#1e1715);border:1px solid var(--red,#C52523);box-shadow:0 10px 30px rgba(0,0,0,0.35);border-radius:12px;padding:16px 18px;z-index:99999;display:flex;flex-direction:column;gap:10px;animation:jmosSlideDown 0.35s ease-out;font-family:inherit;';

    banner.innerHTML = `
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:34px;height:34px;border-radius:8px;background:rgba(197,37,35,0.15);display:grid;place-items:center;color:var(--red,#C52523);font-size:16px">
            🔔
          </div>
          <div>
            <div style="font-weight:700;font-size:13.5px;color:var(--ink,#fff)">Enable JMOS Notifications</div>
            <div style="font-size:11.5px;color:var(--muted,#948885)">Real-time alerts for shoots, tasks &amp; projects</div>
          </div>
        </div>
        <button type="button" id="jmosClosePushBanner" style="background:none;border:none;color:var(--muted,#948885);font-size:18px;cursor:pointer;padding:0 4px;line-height:1">&times;</button>
      </div>
      <p style="font-size:12px;color:var(--ink,#fff);line-height:1.45;margin:0">
        Allow browser push notifications to stay informed on live shoots, client delivery deadlines, and system updates.
      </p>
      <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;margin-top:4px">
        <button type="button" id="jmosDismissPushBanner" class="btn" style="padding:5px 12px;font-size:11.5px">Later</button>
        <button type="button" id="jmosAllowPushBanner" class="btn primary" style="padding:5px 14px;font-size:11.5px;font-weight:600">Allow Notifications</button>
      </div>
    `;

    document.body.appendChild(banner);

    // Also attempt native browser prompt directly
    try {
      Notification.requestPermission().then(perm => {
        this.permission = perm;
        this.updateUiControls();
        if (perm === 'granted' || perm === 'denied') {
          banner.remove();
        }
      }).catch(() => {});
    } catch (_) {}

    const removeBanner = () => {
      banner.style.opacity = '0';
      banner.style.transform = 'translateY(-10px)';
      banner.style.transition = 'all 0.25s ease';
      setTimeout(() => banner.remove(), 250);
    };

    document.getElementById('jmosClosePushBanner')?.addEventListener('click', removeBanner);
    document.getElementById('jmosDismissPushBanner')?.addEventListener('click', removeBanner);
    document.getElementById('jmosAllowPushBanner')?.addEventListener('click', async () => {
      removeBanner();
      await this.requestPermission();
    });
  }
};

window.triggerTestPushNotification = function() {
  if (window.JMOS_PUSH.permission !== 'granted') {
    window.JMOS_PUSH.requestPermission().then(granted => {
      if (granted) {
        window.JMOS_PUSH.send({
          title: '🎬 Shoot Confirmed: Westlands Studio',
          body: 'Production shoot call sheet generated for Moyo Honey Commercial. Crew: Amos, Stephen.',
          url: 'calendar'
        });
      }
    });
  } else {
    window.JMOS_PUSH.send({
      title: '🎬 Production Shoot Alert',
      body: 'Production shoot call sheet synced with Google Meet for tomorrow at 10:00 AM.',
      url: 'calendar'
    });
    if (typeof showToast === 'function') {
      showToast('Push Sent', 'Test push notification dispatched to your browser.');
    }
  }
};

// 13. Initialize Background Polling
function initNotifications() {
  // Update Push UI state
  if (window.JMOS_PUSH) {
    window.JMOS_PUSH.updateUiControls();
    window.JMOS_PUSH.scheduleDailyFirstVisitPrompt();
  }

  // Fetch immediately
  fetchNotifications();

  // Periodically refresh notification badge every 30s
  if (!window.JMOS_NOTIFS.pollTimer) {
    window.JMOS_NOTIFS.pollTimer = setInterval(() => {
      if (JMOS_STATE.currentUser && JMOS_STATE.apiToken) {
        fetchNotifications();
      }
    }, 30000);
  }
}

// Auto-run when DOM loads
document.addEventListener('DOMContentLoaded', () => {
  initNotifications();
});

// Dismiss notification panel on outside click
document.addEventListener('click', (e) => {
  const panel = document.getElementById('notifPanel');
  const bell = document.getElementById('notifBellBtn');
  if (panel && panel.classList.contains('open')) {
    if (!panel.contains(e.target) && !bell?.contains(e.target) && !e.target.closest('#notifBellBtn') && !e.target.closest('#notifPanel')) {
      closeNotificationPanel();
    }
  }
});

// Dismiss notification panel on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeNotificationPanel();
  }
});
