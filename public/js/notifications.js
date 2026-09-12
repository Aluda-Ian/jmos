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

window.openNotificationPanel = function() {
  const panel = document.getElementById('notifPanel');
  const bell = document.getElementById('notifBellBtn');
  if (!panel) return;

  panel.classList.add('open');
  if (bell) bell.classList.add('active');
  fetchNotifications(window.JMOS_NOTIFS.currentFilter || 'all');
};

window.closeNotificationPanel = function() {
  const panel = document.getElementById('notifPanel');
  const bell = document.getElementById('notifBellBtn');
  if (panel) panel.classList.remove('open');
  if (bell) bell.classList.remove('active');
};

window.openNotificationModal = function() {
  window.toggleNotificationPanel();
};

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

// 13. Initialize Background Polling
function initNotifications() {
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
    if (!panel.contains(e.target) && !bell?.contains(e.target)) {
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
