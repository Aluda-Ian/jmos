/* ==========================================================================
   JMOS — Navigation & Access Control Router
   ========================================================================== */

function showView(view) {
  if (!view) view = 'dashboard';

  // Toggle active view container
  document.querySelectorAll('.view').forEach(v => {
    v.hidden = v.getAttribute('data-view') !== view;
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
  if (view === 'clients') ensureClients();
  if (view === 'projects') ensureProjects();
  if (view === 'calendar' && window.renderFullCalendar) window.renderFullCalendar();
  if (view === 'dashboard' && window.renderDashboardCalendar) window.renderDashboardCalendar();
  if (view === 'chat' && window.fetchChatData) window.fetchChatData();

  // Remember active view across refreshes
  try {
    localStorage.setItem('jmos_active_view', view);
  } catch (_) {}
}

function applyRole(role) {
  const banner = document.getElementById('roleBanner');
  const bannerT = document.getElementById('roleBannerText');
  const bannerText = {
    finance: 'Signed in as Matthew (Finance): money and delivery, not the Services setup.',
    sales: 'Signed in as Patrick (Sales): clients, pipeline, projects & tasks — no finances.',
    team: 'Signed in as a team member: only projects and tasks. Money and pipeline are hidden.'
  };

  // Show/hide elements according to data-perm attribute
  document.querySelectorAll('[data-perm]').forEach(el => {
    const perms = el.getAttribute('data-perm').split(' ');
    el.style.display = perms.indexOf(role) > -1 ? '' : 'none';
  });

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

  // Delegate click for all [data-view] triggers
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-view]');
    if (trigger) {
      e.preventDefault();
      showView(trigger.getAttribute('data-view'));
    }
  });

  // Mobile drawer hamburger toggle
  if (menuBtn && nav) {
    menuBtn.onclick = () => nav.classList.toggle('on');
  }
}
