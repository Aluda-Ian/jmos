<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>JMOS — Jeota Media Operating System</title>

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
      <div class="foot">JMOS · Jeota Media Operating System</div>
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
      <a class="item" href="#" data-view="clients" data-perm="owner finance sales">
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
      <a class="item" href="#" data-view="pipeline" data-perm="owner finance sales">
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

      <div class="grp" data-perm="owner finance">Setup</div>
      <a class="item" href="#" data-view="people" data-perm="owner finance">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
        People
      </a>
      <a class="item" href="#" data-view="services" data-perm="owner">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        Services
      </a>
      <a class="item" href="#" data-view="settings" data-perm="owner">
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
        <button type="button" class="icon-btn" title="Notifications">
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="ping"></span>
        </button>
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
  <script src="{{ asset('js/settings.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/modals.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
</body>
</html>

