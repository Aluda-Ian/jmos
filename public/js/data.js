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

function fmt(n) {
  return 'KES ' + (Number(n) || 0).toLocaleString('en-US');
}

function fmtK(n) {
  n = Number(n) || 0;
  if (n >= 1e6) return 'KES ' + (n / 1e6).toFixed(2) + 'M';
  if (n >= 1e3) return 'KES ' + Math.round(n / 1e3) + 'K';
  return 'KES ' + n.toLocaleString();
}

function escHtml(str) {
  return String(str == null ? '' : str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

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

      return true;
    } catch (err) {
      console.error('Failed to sync state from database:', err);
      return false;
    }
  }
};
