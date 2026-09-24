/* ==========================================================================
   JMOS — Core Data & Unified API Client
   ========================================================================== */

const JMOS_COLORS = [
  '#C52523', '#2B6E8A', '#8A5A2B', '#5A7A2B',
  '#6E2B8A', '#2B8A5A', '#B4780F', '#7A2B5A'
];

function getInitials(name) {
  return (name || '').trim().split(/\s+/).map(w => w[0] || '').slice(0, 2).join('').toUpperCase();
}
window.getInitials = getInitials;

function fmt(n) {
  return 'KES ' + (Number(n) || 0).toLocaleString('en-US');
}
window.fmt = fmt;

function fmtK(n) {
  n = Number(n) || 0;
  if (n >= 1e6) return 'KES ' + (n / 1e6).toFixed(2) + 'M';
  if (n >= 1e3) return 'KES ' + Math.round(n / 1e3) + 'K';
  return 'KES ' + n.toLocaleString();
}
window.fmtK = fmtK;

function escHtml(str) {
  return String(str == null ? '' : str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
window.escHtml = escHtml;

function fmtDate(dateStr) {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return String(dateStr);
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch (_) {
    return String(dateStr);
  }
}
window.fmtDate = fmtDate;

function fmtRelativeTime(isoStr) {
  if (!isoStr) return '';
  try {
    const d = new Date(isoStr);
    if (isNaN(d.getTime())) return '';
    const now = new Date();
    const diffSec = Math.floor((now - d) / 1000);
    if (diffSec < 60) return 'Just now';
    const diffMin = Math.floor(diffSec / 60);
    if (diffMin < 60) return `${diffMin}m ago`;
    const diffHour = Math.floor(diffMin / 60);
    if (diffHour < 24) return `${diffHour}h ago`;
    const diffDays = Math.floor(diffHour / 24);
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  } catch (_) {
    return '';
  }
}
window.fmtRelativeTime = fmtRelativeTime;

window.showToast = function(title, subtitle, isRed = false) {
  const toastsContainer = document.getElementById('toasts');
  if (!toastsContainer) {
    console.log(`[Toast] ${title}: ${subtitle || ''}`);
    return;
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <div class="tk ${isRed ? 'red' : ''}">
      <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div>
      <b>${escHtml(title)}</b>
      <small>${escHtml(subtitle || '')}</small>
    </div>
  `;

  toastsContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'opacity .4s, transform .4s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    setTimeout(() => toast.remove(), 400);
  }, 3400);
};

window.openModal = function(id) {
  const m = typeof id === 'string' ? document.getElementById(id) : id;
  if (!m) return;
  m.classList.add('on');
  m.style.display = 'flex';
  document.body.style.overflow = 'hidden';
};

window.closeModal = function(id) {
  if (!id) {
    const openModals = Array.from(document.querySelectorAll('.modal.on, .cascade.on'));
    openModals.forEach(m => window.closeModal(m));
    return;
  }
  const m = typeof id === 'string' ? document.getElementById(id) : id;
  if (m) {
    m.classList.remove('on');
    m.style.display = 'none';
    m.style.removeProperty('display');
    m.style.removeProperty('z-index');
  }
  if (!document.querySelector('.modal.on') && !document.querySelector('.cascade.on')) {
    document.body.style.overflow = '';
  }
};

// Universal Modal Event Delegation (Active immediately from script load)
document.addEventListener('click', (e) => {
  // 1. Close triggers: [data-close], [data-modal-close], .mclose
  const closeTrigger = e.target.closest('[data-close], [data-modal-close], .mclose');
  if (closeTrigger) {
    e.preventDefault();
    const targetId = closeTrigger.getAttribute('data-close') || closeTrigger.getAttribute('data-modal-close');
    if (targetId) {
      window.closeModal(targetId);
    } else {
      const parentModal = closeTrigger.closest('.modal, .cascade');
      if (parentModal) window.closeModal(parentModal);
    }
    return;
  }

  // 2. Backdrop click
  if (e.target.classList.contains('mbg') || e.target.classList.contains('cbg')) {
    const parentModal = e.target.closest('.modal, .cascade') || e.target.parentElement;
    if (parentModal) window.closeModal(parentModal);
    return;
  }

  // 3. Cancel / Close buttons inside modal footers
  const btn = e.target.closest('button, .btn');
  if (btn) {
    const txt = btn.textContent.trim().toLowerCase();
    if (txt === 'cancel' || txt === 'close' || btn.classList.contains('cancel') || btn.classList.contains('btn-cancel')) {
      if (!btn.id?.includes('save') && !btn.id?.includes('submit') && !btn.getAttribute('onclick')?.includes('open') && !btn.closest('.tab-content')) {
        const parentModal = btn.closest('.modal, .cascade');
        if (parentModal) {
          e.preventDefault();
          window.closeModal(parentModal);
          return;
        }
      }
    }
  }

  // 4. Modal Openers: [data-modal-open]
  const openTrigger = e.target.closest('[data-modal-open]');
  if (openTrigger) {
    e.preventDefault();
    const modalId = openTrigger.getAttribute('data-modal-open');
    if (modalId) window.openModal(modalId);
    return;
  }

  // 5. Task Detail Openers: [data-view-task] and .tcard[data-task-id]
  const viewTaskBtn = e.target.closest('[data-view-task]');
  if (viewTaskBtn) {
    e.preventDefault();
    const taskId = viewTaskBtn.getAttribute('data-view-task');
    if (typeof window.openTaskDetailModal === 'function') {
      window.openTaskDetailModal(taskId);
    }
    return;
  }

  const taskCard = e.target.closest('.tcard[data-task-id]');
  if (taskCard && !e.target.closest('button') && !e.target.closest('a') && !e.target.closest('[data-close]')) {
    const taskId = taskCard.getAttribute('data-task-id');
    if (typeof window.openTaskDetailModal === 'function') {
      window.openTaskDetailModal(taskId);
    }
    return;
  }
});

// Universal Escape Key Listener
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' || e.keyCode === 27) {
    const openModals = Array.from(document.querySelectorAll('.modal.on, .cascade.on'));
    if (openModals.length > 0) {
      window.closeModal(openModals[openModals.length - 1]);
    }
  }
});

// App State
const JMOS_STATE = {
  currentUser: null,
  apiToken: localStorage.getItem('jmos_api_token') || null,
  users: [],
  clients: [],
  leads: [],
  contacts: [],
  projects: [],
  pipeline: [],
  tasks: [],
  quotes: [],
  invoices: [],
  expenses: [],
  services: [],
  finance: {
    brought_forward: 0,
    money_in: 0,
    money_out: 0,
    current_balance: 0,
    profit: 0,
    unpaid_total: 0,
    overdue_count: 0,
    ledger: []
  },
  roleLabel: {
    owner: 'Owner · full access',
    finance: 'Finance access',
    sales: 'Sales access',
    team: 'Team member'
  },
  accessPill: {
    owner: 'tint-red',
    finance: 'tint-amber',
    sales: 'tint-green',
    team: ''
  }
};
if (typeof window !== 'undefined') {
  window.JMOS_STATE = JMOS_STATE;
}

// Unified Central API Client
const JMOS_API = {
  baseUrl: '/api',

  getHeaders() {
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    if (JMOS_STATE.apiToken) {
      headers['Authorization'] = 'Bearer ' + JMOS_STATE.apiToken;
    }
    if (JMOS_STATE.currentUser && JMOS_STATE.currentUser.id) {
      headers['X-User-Id'] = String(JMOS_STATE.currentUser.id);
    }
    return headers;
  },

  async req(endpoint, options = {}) {
    const url = endpoint.startsWith('http') ? endpoint : (this.baseUrl + (endpoint.startsWith('/') ? '' : '/') + endpoint);
    const config = {
      headers: this.getHeaders(),
      ...options
    };
    const res = await fetch(url, config);
    if (!res.ok) {
      if (res.status === 401 && !endpoint.includes('auth/login')) {
        if (typeof performLogout === 'function') {
          performLogout('server_expired');
        }
      }
      let errMsg = 'API error (' + res.status + ')';
      try {
        const errJson = await res.json();
        if (errJson.message) errMsg = errJson.message;
      } catch (_) {}
      throw new Error(errMsg);
    }
    return res.json();
  },

  get(endpoint) {
    return this.req(endpoint, { method: 'GET' });
  },

  post(endpoint, data) {
    return this.req(endpoint, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  put(endpoint, data) {
    return this.req(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data)
    });
  },

  delete(endpoint) {
    return this.req(endpoint, { method: 'DELETE' });
  },

  upload(endpoint, formData) {
    const headers = { 'Accept': 'application/json' };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
    if (JMOS_STATE.apiToken) headers['Authorization'] = 'Bearer ' + JMOS_STATE.apiToken;

    const url = endpoint.startsWith('http') ? endpoint : (this.baseUrl + (endpoint.startsWith('/') ? '' : '/') + endpoint);
    return fetch(url, {
      method: 'POST',
      headers,
      body: formData
    }).then(async (res) => {
      let json = null;
      try {
        json = await res.json();
      } catch (_) {}
      if (!res.ok) {
        let errMsg = (json && json.message) || ('Upload failed (' + res.status + ')');
        throw new Error(errMsg);
      }
      return json;
    });
  },

  // Pull all database records in parallel
  async fetchAll() {
    try {
      const [users, clients, projects, pipeline, tasks, invoices, expenses, finance, services, quotes] = await Promise.all([
        this.get('/users').catch(() => []),
        this.get('/clients').catch(() => []),
        this.get('/projects').catch(() => []),
        this.get('/pipeline').catch(() => []),
        this.get('/tasks').catch(() => []),
        this.get('/invoices').catch(() => []),
        this.get('/expenses').catch(() => []),
        this.get('/finance/overview').catch(() => null),
        this.get('/services').catch(() => []),
        this.get('/quotes').catch(() => [])
      ]);

      function unwrapList(res) {
        if (Array.isArray(res)) return res;
        if (res && Array.isArray(res.data)) return res.data;
        if (res && typeof res === 'object') {
          for (const k of ['projects', 'clients', 'tasks', 'invoices', 'expenses', 'services', 'deals', 'users', 'quotes']) {
            if (Array.isArray(res[k])) return res[k];
          }
        }
        return null;
      }

      const uList = unwrapList(users);
      if (uList && uList.length) {
        JMOS_STATE.users = uList.map((u, i) => ({
          id: u.id,
          name: u.name,
          title: u.title || 'Team',
          department: u.department || '',
          email: u.email,
          phone: u.phone || '',
          secondary_email: u.secondary_email || null,
          avatar_url: u.avatar_url || null,
          bio: u.bio || '',
          role: u.role || 'team',
          type: u.type || 'Full-time',
          pay: u.pay || '—',
          color: u.color || JMOS_COLORS[i % JMOS_COLORS.length],
          ini: u.initials || getInitials(u.name)
        }));
      }

      const cList = unwrapList(clients);
      if (cList) JMOS_STATE.clients = cList;

      const pList = unwrapList(projects);
      if (pList) JMOS_STATE.projects = pList;

      const pipeList = unwrapList(pipeline);
      if (pipeList) JMOS_STATE.pipeline = pipeList;

      const tList = unwrapList(tasks);
      if (tList) JMOS_STATE.tasks = tList;

      const iList = unwrapList(invoices);
      if (iList) JMOS_STATE.invoices = iList;

      const eList = unwrapList(expenses);
      if (eList) JMOS_STATE.expenses = eList;

      const sList = unwrapList(services);
      if (sList) JMOS_STATE.services = sList;

      const qList = unwrapList(quotes);
      if (qList) {
        JMOS_STATE.quotes = qList;
        if (typeof FINANCE_STATE !== 'undefined') FINANCE_STATE.quotes = qList;
        if (typeof JMOS_QUOTES !== 'undefined') JMOS_QUOTES.quotes = qList;
      }
      if (finance && finance.status === 'success') {
        JMOS_STATE.finance = finance;
        JMOS_STATE.broughtForward = finance.brought_forward;
      }

      if (typeof fetchNotifications === 'function') {
        fetchNotifications();
      }

      if (typeof fetchCalendarEvents === 'function') {
        await fetchCalendarEvents();
      }

      if (typeof populateAllUserSelects === 'function') {
        populateAllUserSelects();
      } else if (typeof window !== 'undefined' && typeof window.populateAllUserSelects === 'function') {
        window.populateAllUserSelects();
      }

      return true;
    } catch (err) {
      console.error('Failed to sync state from database:', err);
      return false;
    }
  }
};
if (typeof window !== 'undefined') {
  window.JMOS_API = JMOS_API;
}

