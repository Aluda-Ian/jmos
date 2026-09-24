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
  <meta name="theme-color" content="#FAF7F6" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#120e0d" media="(prefers-color-scheme: dark)">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
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
  <link rel="stylesheet" href="{{ asset('css/ai-assistant.css') }}?v={{ time() }}">

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
      <div class="foot">JMOS · Jeota Media Operating System <span style="opacity:0.75;margin-left:6px;font-family:'IBM Plex Mono',monospace;font-size:11px">{{ config('app.version', 'v2.5.9') }}</span></div>
      <div class="bdrip" style="left:46px;height:60px"></div>
      <div class="bdrip" style="left:62px;height:96px"></div>
      <div class="bdrip" style="left:77px;height:44px"></div>
    </div>
    <div class="formside">
      <!-- 1. Sign In Card -->
      <div class="formcard" id="signinCard">
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
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <label for="loginPass" style="margin-bottom:0">Password</label>
            <a href="javascript:void(0)" id="toForgotBtn" style="font-size:11.5px;color:var(--red);text-decoration:none;font-weight:500">Forgot password?</a>
          </div>
          <input type="password" id="loginPass" placeholder="••••••••" autocomplete="current-password">
        </div>
        <button type="button" class="signin" id="signinBtn">Sign in</button>
      </div>

      <!-- 2. Request Password Reset OTP Card -->
      <div class="formcard" id="forgotCard" style="display:none">
        <div class="mlogo">
          <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota">
          <b>JEOTA MEDIA</b>
        </div>
        <h2>Reset password</h2>
        <p class="sub">Enter your account email to receive a 6-digit OTP verification code</p>
        <div class="notice" id="forgotNotice"></div>
        <div class="err" id="forgotErr"></div>
        <div class="field">
          <label for="forgotEmail">Account Email</label>
          <input type="email" id="forgotEmail" placeholder="you@jeotamedia.co.ke" autocomplete="email">
        </div>
        <button type="button" class="signin" id="sendOtpBtn">Send verification code</button>
        <div style="margin-top:16px;text-align:center">
          <a href="javascript:void(0)" id="backToSigninFromForgot" style="font-size:12.5px;color:var(--muted);text-decoration:none;font-weight:500">&larr; Back to sign in</a>
        </div>
      </div>

      <!-- 3. Verify OTP & Reset Password Card -->
      <div class="formcard" id="resetCard" style="display:none">
        <div class="mlogo">
          <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota">
          <b>JEOTA MEDIA</b>
        </div>
        <h2>Enter security code</h2>
        <p class="sub">A 6-digit verification code was sent to <strong id="resetTargetEmail" style="color:#2B2625"></strong></p>
        <div class="notice" id="resetNotice"></div>
        <div class="err" id="resetErr"></div>
        <div class="field">
          <label for="resetOtp">6-Digit Verification Code</label>
          <input type="text" id="resetOtp" maxlength="6" placeholder="000000" style="font-family:'IBM Plex Mono',monospace;font-size:20px;letter-spacing:6px;text-align:center;font-weight:700">
        </div>
        <div class="field">
          <label for="resetNewPass">New Password</label>
          <input type="password" id="resetNewPass" placeholder="At least 6 characters" autocomplete="new-password">
        </div>
        <div class="field">
          <label for="resetConfirmPass">Confirm New Password</label>
          <input type="password" id="resetConfirmPass" placeholder="Repeat new password" autocomplete="new-password">
        </div>
        <button type="button" class="signin" id="submitResetBtn">Reset password &amp; Sign in</button>
        <div style="margin-top:14px;font-size:12px;text-align:center;color:var(--muted)">
          Didn't get the code? <a href="javascript:void(0)" id="resendOtpBtn" style="color:var(--red);font-weight:600;text-decoration:none">Resend code</a>
          <span id="resendTimer" style="display:none;color:var(--muted)">(<span id="resendSecs">60</span>s)</span>
        </div>
        <div style="margin-top:12px;text-align:center">
          <a href="javascript:void(0)" id="backToSigninFromReset" style="font-size:12.5px;color:var(--muted);text-decoration:none;font-weight:500">&larr; Back to sign in</a>
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
        <div class="brand-text">
          <b>JEOTA MEDIA</b>
          <span>OPERATING SYSTEM</span>
        </div>
      </div>

      <div class="grp">Overview</div>
      <a class="item active" href="#" data-view="dashboard" title="Dashboard">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
        <span class="nav-text">Dashboard</span>
      </a>

      <div class="grp">Project Management</div>
      <a class="item" href="#" data-view="projects" title="Projects & Productions">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M10 4v16"/></svg>
        <span class="nav-text">Projects</span>
      </a>
      <a class="item" href="#" data-view="tasks" title="Tasks & Schedules">
        <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <span class="nav-text">Tasks</span>
      </a>
      <a class="item" href="#" data-view="calendar" title="Production Calendar">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <span class="nav-text">Calendar</span>
      </a>
      <a class="item" href="#" data-view="documents" title="Documents & Media Assets">
        <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        <span class="nav-text">Documents</span>
      </a>
      <a class="item" href="#" data-view="chat" title="Team Chat">
        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span class="nav-text">Team Chat</span>
        <span class="chat-nav-badge" id="sidebarChatBadge" style="display:none">0</span>
      </a>

      <div class="grp">Lead Generation</div>
      <a class="item" href="#" data-view="clients" title="Clients & Accounts">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span class="nav-text">Clients</span>
      </a>
      <a class="item" href="#" data-view="pipeline" title="Commercial Deals Pipeline">
        <svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7M22 20v-11"/></svg>
        <span class="nav-text">Pipeline</span>
      </a>
      <a class="item" href="#" data-view="fundraising" title="Grants & Open Calls">
        <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/><path d="M12 18v4M4.93 4.93l1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
        <span class="nav-text">Grants &amp; Open Calls</span>
      </a>
      <a class="item" href="#" data-view="partnerships" title="Partnership Exploration">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span class="nav-text">Partnership Exploration</span>
      </a>

      <div class="grp" data-perm="owner">Money</div>
      <a class="item" href="#" data-view="finance" data-perm="owner" title="Finance Overview & Cash Flow">
        <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/></svg>
        <span class="nav-text">Finance</span>
      </a>
      <a class="item" href="#" data-view="quotes" data-perm="owner" title="Commercial Quotations">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span class="nav-text">Quotations</span>
      </a>
      <a class="item" href="#" data-view="invoices" data-perm="owner" title="Invoices & Billing">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        <span class="nav-text">Invoices</span>
      </a>
      <a class="item" href="#" data-view="expenses" data-perm="owner" title="Expenses & ETR Tracking">
        <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <span class="nav-text">Expenses</span>
      </a>
      <a class="item" href="#" data-view="budget" data-perm="owner" title="Production Budget Calculator">
        <svg viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        <span class="nav-text">Production Budget</span>
      </a>
      <a class="item" href="#" data-view="statements" data-perm="owner" title="Financial Statements & Ledger">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
        <span class="nav-text">Financial Statements</span>
      </a>

      <div class="grp" data-perm="owner finance manager">Setup</div>
      <a class="item" href="#" data-view="people" data-perm="owner finance manager" title="People & Team Roles">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
        <span class="nav-text">People</span>
      </a>
      <a class="item" href="#" data-view="services" data-perm="owner manager" title="Service Recipes & Rate Cards">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span class="nav-text">Services</span>
      </a>
      <a class="item" href="#" data-view="settings" title="System Settings & Infrastructure">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span class="nav-text">Settings</span>
      </a>

      <div class="nav-spacer"></div>

      <!-- User Profile Chip (Clickable to Edit Profile & Avatar) -->
      <div class="userchip" id="userchip" onclick="window.openMyProfileModal()" style="cursor:pointer" title="Edit your profile, photo & account settings">
        <div class="av" id="userAv" style="background:var(--red)">BK</div>
        <div style="flex:1;min-width:0">
          <div class="nm" id="userNm" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Barny Kiome</div>
          <div class="rl" id="userRl">Owner · full access</div>
        </div>
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--muted);opacity:0.6;margin-left:auto"><path d="M9 18l6-6-6-6"/></svg>
      </div>
      <div class="nav-footer-version" style="padding:4px 16px 14px;font-size:10.5px;color:var(--muted);font-family:'IBM Plex Mono',monospace;opacity:0.7;display:flex;align-items:center;gap:6px">
        <span>JMOS</span>
        <span class="badge" style="font-size:9.5px;padding:1px 5px;background:var(--paper);border:1px solid var(--line);color:var(--muted)">{{ config('app.version', 'v2.5.9') }}</span>
      </div>
    </aside>

    <!-- Main View Content Area -->
    <main class="main">
      <!-- Top Bar -->
      <header class="topbar">
        <button type="button" class="menu-btn" id="menuBtn" aria-label="Toggle Sidebar Navigation" title="Collapse sidebar (icons only)">
          <!-- Desktop Expanded mode: << (collapse) -->
          <svg class="menu-icon-collapse" viewBox="0 0 24 24"><path d="M11 19l-7-7 7-7M19 19l-7-7 7-7"/></svg>
          <!-- Desktop Collapsed mode: >> (expand / return) -->
          <svg class="menu-icon-expand" viewBox="0 0 24 24"><path d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
          <!-- Mobile Drawer mode (<=760px) -->
          <svg class="menu-icon-mobile" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
          <input placeholder="Search clients, projects, invoices, tasks…">
        </div>
        <div class="spacer"></div>
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

        <!-- 8b. QUOTATIONS & COMMERCIAL PROPOSALS VIEW -->
        @include('pages.quotes')

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

        <!-- 15. DOCUMENTS & ASSETS VIEW -->
        @include('pages.documents')

        <!-- 16. FUNDRAISER & IMPACT PROJECTS VIEW -->
        @include('pages.fundraising')

        <!-- 16b. PARTNERSHIP EXPLORATION VIEW (SEPARATE INSTITUTIONAL MASTER) -->
        @include('pages.partnerships')

        <!-- 17. FINANCIAL STATEMENTS & CLIENT LEDGER VIEW -->
        @include('pages.statements')

      </div>
    </main>
  </div>

  <!-- ==========================================================================
       MODALS & OVERLAYS
       ========================================================================== -->
  @include('partials.modals')

  <!-- ==========================================================================
       J- AI OPERATING ASSISTANT & INTELLIGENCE DRAWER
       ========================================================================== -->
  <button type="button" class="ai-assistant-fab" id="aiAssistantFab" title="Open J- ai Intelligence Assistant">
    <div class="sparkle-icon">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
    </div>
    <span>J- ai</span>
    <span class="badge-pulse"></span>
  </button>

  <div class="ai-drawer-overlay" id="aiDrawerOverlay"></div>
  <aside class="ai-drawer" id="aiAssistantDrawer">
    <div class="ai-drawer-header">
      <div class="ai-drawer-title">
        <div class="sparkle-icon" style="width:24px;height:24px;border-radius:6px;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>
        </div>
        <div>
          <h3>J- ai Assistant</h3>
        </div>
        <span class="badge">J- ai</span>
      </div>
      <div class="ai-drawer-actions">
        <button type="button" class="ai-icon-btn" id="aiDrawerClear" title="Clear Conversation">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        </button>
        <button type="button" class="ai-icon-btn" id="aiDrawerClose" title="Close Drawer">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <!-- Mode Selector Tabs -->
    <div class="ai-mode-bar">
      <button type="button" class="ai-mode-btn active" data-mode="general_help">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        General Help
      </button>
      <button type="button" class="ai-mode-btn" data-mode="lead_gen">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7.5" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Lead Qualification
      </button>
      <button type="button" class="ai-mode-btn" data-mode="fundraising_partner">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Grants &amp; Funding
      </button>
    </div>

    <!-- Messages Body -->
    <div class="ai-messages-container" id="aiMessagesContainer"></div>

    <!-- Quick Prompts Chips -->
    <div class="ai-prompt-chips">
      <button type="button" class="ai-chip">How do I create a production quote?</button>
      <button type="button" class="ai-chip">Find grant opportunities</button>
      <button type="button" class="ai-chip">Where are unpaid invoices?</button>
    </div>

    <!-- Input Footer -->
    <div class="ai-drawer-input-container">
      <div class="ai-input-wrapper">
        <input type="text" id="aiInputField" class="ai-input-field" placeholder="Ask J- ai anything about projects, leads, or workflows…" autocomplete="off">
        <button type="button" id="aiSendBtn" class="ai-send-btn" title="Send message">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </div>
    </div>
  </aside>

  <!-- ==========================================================================
       JAVASCRIPT MODULES
       ========================================================================== -->
  <script src="{{ asset('js/data.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/auth.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/navigation.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/modals.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/finance.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/clients-api.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/people.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/calendar.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/chat.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/notifications.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/leads-pipeline.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/quotes.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/documents.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/fundraising.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/settings.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/ai-assistant.js') }}?v={{ time() }}"></script>
  <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
</body>
</html>

