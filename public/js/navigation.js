/* ==========================================================================
   JMOS — Navigation & Access Control Router
   ========================================================================== */

function showView(view) {
  if (!view) view = 'dashboard';

  // Toggle active view container
  document.querySelectorAll('.view').forEach(v => {
    const isTarget = v.getAttribute('data-view') === view;
    v.hidden = !isTarget;
    if (isTarget) {
      v.style.display = '';
    }
  });

  // Toggle active sidebar tab
  document.querySelectorAll('.nav a.item').forEach(a => {
    a.classList.toggle('active', a.getAttribute('data-view') === view);
  });

  // Reset scroll
  const scrollArea = document.querySelector('.scroll');
  if (scrollArea) scrollArea.scrollTop = 0;

  // Close mobile navigation drawer if open
  const nav = document.getElementById('nav');
  if (nav) nav.classList.remove('on');

  // Trigger on-demand API fetches / iframe loaders
  if (view === 'budget') {
    const frame = document.getElementById('budgetFrame');
    if (frame && (!frame.src || frame.src === 'about:blank' || frame.src === '')) {
      frame.src = '/budget-calculator';
    }
  }
  if (view === 'clients') ensureClients();
  if (view === 'projects') ensureProjects();
  if (view === 'tasks') {
    if (typeof ensureProjects === 'function') ensureProjects();
    if (typeof renderTasks === 'function') renderTasks();
  }
  if (view === 'calendar') {
    if (window.renderFullCalendar) window.renderFullCalendar();
    if (typeof window.loadCalendarSyncStatus === 'function') window.loadCalendarSyncStatus();
  }
  if (view === 'dashboard' && window.renderDashboardCalendar) window.renderDashboardCalendar();
  if (view === 'pipeline') {
    if (typeof refreshCrmData === 'function') refreshCrmData();
    if (typeof renderPipeline === 'function') renderPipeline();
  }
  if (view === 'documents' && typeof window.refreshDocuments === 'function') {
    window.refreshDocuments();
  }
  if (view === 'fundraising' && typeof window.refreshFundraising === 'function') {
    window.refreshFundraising();
  }
  if (view === 'partnerships' && typeof window.refreshPartnerships === 'function') {
    window.refreshPartnerships();
  }
  if (view === 'finance' && typeof window.refreshFinanceData === 'function') {
    window.refreshFinanceData();
  }
  if (view === 'statements' && typeof window.refreshFinanceData === 'function') {
    window.refreshFinanceData();
  }
  if (view === 'invoices' && typeof window.renderInvoices === 'function') {
    window.renderInvoices();
  }
  if (view === 'quotes' && window.JMOS_QUOTES && typeof window.JMOS_QUOTES.loadQuotes === 'function') {
    window.JMOS_QUOTES.loadQuotes();
  }
  if (view === 'people') {
    if (typeof ensurePeople === 'function') {
      ensurePeople();
    } else if (typeof renderPeople === 'function') {
      renderPeople();
    }
  }
  if (view === 'settings' && typeof loadSettings === 'function') loadSettings();

  // Remember active view across refreshes
  try {
    localStorage.setItem('jmos_active_view', view);
  } catch (_) {}
}

function resolveTargetViewFromUrl() {
  const path = window.location.pathname.replace(/^\/+|\/+$/g, '');
  const hash = window.location.hash.replace(/^#\/?/, '');
  const validViews = ['dashboard', 'pipeline', 'leads', 'clients', 'projects', 'tasks', 'calendar', 'quotes', 'finance', 'statements', 'invoices', 'documents', 'fundraising', 'partnerships', 'people', 'settings', 'chat'];

  if (validViews.includes(path)) return path;
  if (validViews.includes(hash)) return hash;

  const urlParams = new URLSearchParams(window.location.search);
  const paramView = urlParams.get('view');
  if (paramView && validViews.includes(paramView)) return paramView;

  return null;
}

function applyRole(role) {
  const banner = document.getElementById('roleBanner');
  const bannerT = document.getElementById('roleBannerText');
  const bannerText = {
    finance: 'Signed in as Matthew (Finance): money and delivery, not the Services setup.',
    sales: 'Signed in as Patrick (Sales): clients, pipeline, projects & tasks — no finances.',
    manager: 'Signed in as Ian (IT Specialist & System Manager): platform settings, infrastructure & operations.'
  };

  // Show/hide elements according to data-perm attribute
  document.querySelectorAll('[data-perm]').forEach(el => {
    const perms = el.getAttribute('data-perm').split(' ');
    el.style.display = perms.indexOf(role) > -1 ? '' : 'none';
  });

  // Reset calendar filter if non-finance role tries to filter invoices
  if (role !== 'owner' && role !== 'finance') {
    if (typeof CALENDAR_STATE !== 'undefined' && CALENDAR_STATE.activeFilter === 'invoice') {
      CALENDAR_STATE.activeFilter = 'all';
      const allPill = document.querySelector('[data-cal-filter="all"]');
      if (allPill) {
        document.querySelectorAll('[data-cal-filter]').forEach(p => p.classList.remove('active'));
        allPill.classList.add('active');
      }
    }
  }

  // Update role banner
  if (banner && bannerT) {
    if (bannerText[role]) {
      banner.style.display = 'flex';
      bannerT.textContent = bannerText[role];
    } else {
      banner.style.display = 'none';
    }
  }

  // Safety fallback if active view is forbidden for the role
  const activeNav = document.querySelector('.nav a.item.active');
  if (activeNav && activeNav.getAttribute('data-perm')) {
    const perms = activeNav.getAttribute('data-perm').split(' ');
    if (perms.indexOf(role) < 0) showView('dashboard');
  }

  const currentView = document.querySelector('.view:not([hidden])');
  if (currentView && currentView.getAttribute('data-perm')) {
    const perms = currentView.getAttribute('data-perm').split(' ');
    if (perms.indexOf(role) < 0) showView('dashboard');
  }
}

function initNavigation() {
  const nav = document.getElementById('nav');
  const menuBtn = document.getElementById('menuBtn');

  // Delegate click for navigation triggers (excluding .view section containers)
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('a[data-view], button[data-view], .nav [data-view], [data-nav-view]');
    if (trigger && !trigger.classList.contains('view')) {
      e.preventDefault();
      const targetView = trigger.getAttribute('data-nav-view') || trigger.getAttribute('data-view');
      if (targetView) {
        showView(targetView);
      }
    }
  });

  // Sidebar Toggle & Collapse
  const appRoot = document.getElementById('appRoot');

  window.toggleSidebar = function () {
    if (window.innerWidth <= 760) {
      if (nav) nav.classList.toggle('on');
    } else {
      if (appRoot) {
        appRoot.classList.toggle('collapsed-nav');
        const isCollapsed = appRoot.classList.contains('collapsed-nav');
        if (nav) nav.classList.toggle('collapsed', isCollapsed);
        if (menuBtn) {
          menuBtn.setAttribute('title', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar (icons only)');
        }
        try {
          localStorage.setItem('jmos_sidebar_collapsed', isCollapsed ? '1' : '0');
        } catch (_) {}
      }
    }
  };

  if (menuBtn) {
    menuBtn.onclick = window.toggleSidebar;
  }

  // Restore saved sidebar collapsed state on desktop
  if (window.innerWidth > 760) {
    try {
      const savedCollapsed = localStorage.getItem('jmos_sidebar_collapsed');
      if (savedCollapsed === '1' && appRoot) {
        appRoot.classList.add('collapsed-nav');
        if (nav) nav.classList.add('collapsed');
        if (menuBtn) {
          menuBtn.setAttribute('title', 'Expand sidebar');
        }
      }
    } catch (_) {}
  }

  // Keyboard shortcut: Ctrl + \ or Cmd + \ to toggle sidebar
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === '\\') {
      e.preventDefault();
      window.toggleSidebar();
    }
  });

  // Sync theme toggle icon state on load
  syncThemeIcons();
}

/* ==========================================================================
   Theme Toggle (Light / Dark Mode)
   ========================================================================== */

function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.getAttribute('data-theme') === 'dark';
  const newTheme = isDark ? 'light' : 'dark';

  html.setAttribute('data-theme', newTheme);

  try {
    localStorage.setItem('jmos_theme', newTheme);
  } catch (_) {}

  syncThemeIcons();

  // Sync the settings appearance dropdown if present
  const sel = document.getElementById('cfg_appearance_theme');
  if (sel) sel.value = newTheme;
}

function syncThemeIcons() {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const sunIcon = document.querySelector('.theme-icon-light');
  const moonIcon = document.querySelector('.theme-icon-dark');

  if (sunIcon) sunIcon.style.display = isDark ? '' : 'none';
  if (moonIcon) moonIcon.style.display = isDark ? 'none' : '';

  // Sync appearance settings dropdown if visible
  const sel = document.getElementById('cfg_appearance_theme');
  if (sel) sel.value = isDark ? 'dark' : 'light';

  // Highlight active preview tile
  document.querySelectorAll('.theme-preview-tile').forEach(tile => {
    const t = tile.getAttribute('data-set-theme');
    tile.style.borderColor = (t === (isDark ? 'dark' : 'light')) ? 'var(--red)' : 'var(--line)';
  });
}

function applyThemeFromSettings(theme) {
  document.documentElement.setAttribute('data-theme', theme);

  try {
    localStorage.setItem('jmos_theme', theme);
  } catch (_) {}

  syncThemeIcons();
}

