<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>JMOS — Jeota Media Operating System</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('assets/img/jeota-logo.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/jeota-logo.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/img/jeota-logo.png') }}">

  <!-- Progressive Web App (PWA) Manifest & Mobile Config -->
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <meta name="theme-color" content="#C52523">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="JMOS">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Modular Stylesheets -->
  <link rel="stylesheet" href="{{ asset('css/main.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/views.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}?v={{ time() }}">

  <!-- Fast session restore pre-check to eliminate flicker on page refresh -->
  <script>
    (function() {
      try {
        // Restore theme preference before paint to prevent flash
        var theme = localStorage.getItem('jmos_theme') || 'light';
        if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');

        var token = localStorage.getItem('jmos_api_token');
        var user = localStorage.getItem('jmos_user');
        var lastAct = parseInt(localStorage.getItem('jmos_last_activity') || '0', 10);
        var IDLE_TIMEOUT = 15 * 60 * 1000; // 15 minutes
        if (token && user && lastAct > 0 && (Date.now() - lastAct < IDLE_TIMEOUT)) {
          document.documentElement.classList.add('jmos-authenticated');
        }
      } catch (e) {}
    })();
  </script>
</head>
<body>

  <!-- ==========================================================================
       LOGIN SCREEN
       ========================================================================== -->
  <div class="login" id="loginScreen">
    <div class="brandside">
      <div class="lg">
        <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota Media">
        <b>JEOTA MEDIA</b>
      </div>
      <div class="mid">
        <h1>Run all of Jeota from one place.</h1>
        <p>Clients, projects, tasks and finances — your whole operation, signed in and ready.</p>
      </div>
      <div class="foot">JMOS · Jeota Media Operating System <span style="opacity:0.75;margin-left:6px;font-family:'IBM Plex Mono',monospace;font-size:11px">{{ config('app.version', 'v2.4.6') }}</span></div>
      <div class="bdrip" style="left:46px;height:60px"></div>
      <div class="bdrip" style="left:62px;height:96px"></div>
      <div class="bdrip" style="left:77px;height:44px"></div>
    </div>
    <div class="formside">
      <div class="formcard">
        <div class="mlogo">
          <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota">
          <b>JEOTA MEDIA</b>
        </div>
        <h2>Welcome back</h2>
        <p class="sub">Sign in to your JMOS workspace</p>
        <div class="notice" id="loginNotice"></div>
        <div class="err" id="loginErr">Email or password didn't match.</div>
        <div class="field">
          <label for="loginEmail">Email address</label>
          <input type="email" id="loginEmail" placeholder="you@jeotamedia.co.ke" autocomplete="username">
        </div>
        <div class="field">
          <label for="loginPass">Password</label>
          <input type="password" id="loginPass" placeholder="••••••••" autocomplete="current-password">
        </div>
        <button type="button" class="signin" id="signinBtn">Sign in</button>
        <div class="demohint">
          <b>Try a role:</b> click any name below to fill their login (all use password <span class="mono">jeota2024</span>):
          <div class="accts" id="demoAccts"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MAIN APP SHELL
       ========================================================================== -->
  <div class="app" id="appRoot" style="display:none">
    
    <!-- Sidebar Navigation -->
    <aside class="nav" id="nav">
      <div class="brand">
        <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota">
        <div>
          <b>JEOTA MEDIA</b>
          <span>OPERATING SYSTEM</span>
        </div>
      </div>

      <div class="grp">Overview</div>
      <a class="item active" href="#" data-view="dashboard">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
        Dashboard
      </a>

      <div class="grp">Live Data</div>
      <a class="item" href="#" data-view="clients" data-perm="owner finance sales manager">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Clients
      </a>
      <a class="item" href="#" data-view="calendar">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Calendar
      </a>
      <a class="item" href="#" data-view="chat">
        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Team Chat
        <span class="chat-nav-badge" id="sidebarChatBadge" style="display:none">0</span>
      </a>
      <a class="item" href="#" data-view="pipeline" data-perm="owner finance sales manager">
        <svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20v-11"/></svg>
        Pipeline
      </a>
      <a class="item" href="#" data-view="projects">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M10 4v16"/></svg>
        Projects
      </a>
      <a class="item" href="#" data-view="tasks">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tasks
      </a>

      <div class="grp" data-perm="owner finance">Money</div>
      <a class="item" href="#" data-view="finance" data-perm="owner finance">
        <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/></svg>
        Finance
      </a>
      <a class="item" href="#" data-view="invoices" data-perm="owner finance">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        Invoices
      </a>
      <a class="item" href="#" data-view="expenses" data-perm="owner finance">
        <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Expenses
      </a>
      <a class="item" href="#" data-view="budget" data-perm="owner finance">
        <svg viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        Production Budget
      </a>

      <div class="grp" data-perm="owner finance manager">Setup</div>
      <a class="item" href="#" data-view="people" data-perm="owner finance manager">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
        People
      </a>
      <a class="item" href="#" data-view="services" data-perm="owner manager">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Services
      </a>
      <a class="item" href="#" data-view="settings">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Settings
      </a>

      <div class="nav-spacer"></div>

      <!-- User Profile Chip -->
      <div class="userchip">
        <div class="av" id="userAv" style="background:var(--red)">BK</div>
        <div>
          <div class="nm" id="userNm">Barny Kiome</div>
          <div class="rl" id="userRl">Owner · full access</div>
        </div>
      </div>
      <div style="padding:4px 16px 14px;font-size:10.5px;color:var(--muted);font-family:'IBM Plex Mono',monospace;opacity:0.7;display:flex;align-items:center;gap:6px">
        <span>JMOS</span>
        <span class="badge" style="font-size:9.5px;padding:1px 5px;background:var(--paper);border:1px solid var(--line);color:var(--muted)">{{ config('app.version', 'v2.4.6') }}</span>
      </div>
    </aside>

    <!-- Main View Content Area -->
    <main class="main">
      <!-- Top Bar -->
      <header class="topbar">
        <button type="button" class="menu-btn" id="menuBtn" aria-label="Toggle Navigation">
          <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input placeholder="Search clients, projects, invoices, tasks…">
        </div>
        <div class="spacer"></div>
        <!-- Download / Install App Button -->
        <button type="button" class="btn pwa-install-btn" id="headerInstallAppBtn" onclick="triggerDownloadApp()" title="Download &amp; install JMOS application for PC &amp; mobile" style="padding:5px 11px;font-size:11.5px;display:flex;align-items:center;gap:6px;background:var(--paper);border:1px solid var(--line);border-radius:8px;font-weight:600;color:var(--ink)">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
          <span class="pwa-btn-text">Download App</span>
        </button>

        <button type="button" class="icon-btn" id="themeToggleBtn" title="Toggle dark mode" aria-label="Toggle dark mode" onclick="toggleTheme()">
          <!-- Sun icon (shown in dark mode) -->
          <svg class="theme-icon-light" viewBox="0 0 24 24" style="display:none"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          <!-- Moon icon (shown in light mode) -->
          <svg class="theme-icon-dark" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
        <div class="notif-dropdown-wrapper" id="notifDropdownWrapper">
          <button type="button" class="icon-btn notif-bell-btn" id="notifBellBtn" title="Notifications" onclick="toggleNotificationPanel(event)" aria-label="Toggle Notifications">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="ping" id="notifPing" style="display:none"></span>
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
          </button>

          <!-- Notification Popover Panel anchored right at the bell -->
          <div class="notif-popover-panel" id="notifPanel" role="region" aria-label="Notifications Panel">
            <!-- Header -->
            <div class="notif-panel-head">
              <div class="notif-head-title">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <h3>Notifications</h3>
                <span class="notif-unread-pill" id="notifModalUnreadPill">0 unread</span>
              </div>
              <button type="button" class="notif-head-close-btn" onclick="closeNotificationPanel()" title="Close notifications" aria-label="Close notifications">&times;</button>
            </div>

            <!-- Filter Tabs & Bulk Actions -->
            <div class="notif-tabs-bar">
              <div class="notif-segmented" role="tablist">
                <button type="button" class="notif-tab-btn active" id="notifTabAll" onclick="filterNotifications('all')">
                  All <span class="notif-tab-badge" id="notifBadgeAll">0</span>
                </button>
                <button type="button" class="notif-tab-btn" id="notifTabUnread" onclick="filterNotifications('unread')">
                  Unread <span class="notif-tab-badge" id="notifBadgeUnread">0</span>
                </button>
                <button type="button" class="notif-tab-btn" id="notifTabRead" onclick="filterNotifications('read')">
                  Read <span class="notif-tab-badge" id="notifBadgeRead">0</span>
                </button>
              </div>

              <button type="button" class="notif-mark-all-btn" id="notifMarkAllReadBtn" onclick="markAllNotificationsRead()" title="Mark all notifications as read">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Mark all read
              </button>
            </div>

            <!-- Notifications List -->
            <div class="notif-list-container" id="notifItemsList">
              <!-- Injected dynamically by JS -->
            </div>

            <!-- Footer with Browser Push Enable / Status -->
            <div class="notif-panel-foot" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--paper);border-top:1px solid var(--line);font-size:11px">
              <div style="color:var(--muted);display:flex;align-items:center;gap:6px">
                <span style="width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block"></span>
                <span>Push: <b class="push-status-badge" style="font-weight:600;color:var(--ink)">Checking…</b></span>
              </div>
              <div style="display:flex;align-items:center;gap:8px">
                <button type="button" class="linkbtn" onclick="triggerTestPushNotification()" style="font-size:11px;font-weight:600" title="Send a test notification">Test Alert</button>
                <button type="button" class="linkbtn push-enable-btn" onclick="window.JMOS_PUSH.requestPermission()" style="font-size:11px;font-weight:600;color:var(--red)">Enable</button>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="btn signout" id="signoutBtn">
          <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
          Sign out
        </button>
      </header>

      <!-- Scrollable Views Container -->
      <div class="scroll">
        <!-- Role Alert Banner -->
        <div class="rolebanner" id="roleBanner">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
          <span id="roleBannerText"></span>
        </div>

        <!-- 1. DASHBOARD VIEW -->
        @include('pages.dashboard')

        <!-- 2. CLIENTS VIEW -->
        @include('pages.clients')

        <!-- 3. OPERATIONS CALENDAR VIEW -->
        @include('pages.calendar')

        <!-- 4. TEAM CHAT & DIRECT MESSAGING VIEW -->
        @include('pages.chat')

        <!-- 5. PIPELINE VIEW -->
        @include('pages.pipeline')

        <!-- 6. PROJECTS VIEW -->
        @include('pages.projects')

        <!-- 7. TASKS VIEW -->
        @include('pages.tasks')

        <!-- 8. FINANCE VIEW -->
        @include('pages.finance')

        <!-- 9. INVOICES VIEW -->
        @include('pages.invoices')

        <!-- 10. EXPENSES VIEW -->
        @include('pages.expenses')

        <!-- 11. PRODUCTION BUDGET VIEW -->
        @include('pages.budget')

        <!-- 12. PEOPLE VIEW -->
        @include('pages.people')

        <!-- 13. SERVICES VIEW -->
        @include('pages.services')

        <!-- 14. SETTINGS VIEW (Super Admin & IT) -->
        @include('pages.settings')

      </div>
    </main>
  </div>

  <!-- ==========================================================================
       MODALS & OVERLAYS
       ========================================================================== -->
  @include('partials.modals')

  <!-- ==========================================================================
       JAVASCRIPT MODULES
       ========================================================================== -->
  <script src="{{ asset('js/data.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/auth.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/navigation.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/finance.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/clients-api.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/people.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/calendar.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/chat.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/notifications.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/settings.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/modals.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
</body>
</html>

