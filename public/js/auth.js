/* ==========================================================================
   JMOS — Authentication, Session Persistence & 15-Minute Idle Logout
   ========================================================================== */

const IDLE_TIMEOUT_MS = 15 * 60 * 1000; // 15 minutes in milliseconds
let idleCheckInterval = null;
let activityThrottleTimer = null;
let isIdleTrackingActive = false;

// 1. Idle Activity Monitor
function recordUserActivity() {
  if (!JMOS_STATE.currentUser) return;

  // Throttle updates to localStorage to once every 15 seconds
  if (activityThrottleTimer) return;
  activityThrottleTimer = setTimeout(() => {
    activityThrottleTimer = null;
  }, 15000);

  const now = Date.now();
  try {
    localStorage.setItem('jmos_last_activity', now.toString());
  } catch (_) {}
}

function checkIdleStatus() {
  if (!JMOS_STATE.currentUser) return;

  const lastActiveStr = localStorage.getItem('jmos_last_activity');
  const lastActive = parseInt(lastActiveStr || '0', 10);
  const now = Date.now();

  if (lastActive > 0 && (now - lastActive >= IDLE_TIMEOUT_MS)) {
    performLogout('idle');
  }
}

function startIdleTracker() {
  if (isIdleTrackingActive) return;
  isIdleTrackingActive = true;

  // Record current time as starting activity point
  try {
    localStorage.setItem('jmos_last_activity', Date.now().toString());
  } catch (_) {}

  // Activity listeners across window and document
  const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
  activityEvents.forEach(evt => {
    window.addEventListener(evt, recordUserActivity, { passive: true });
  });

  // Regular heartbeat check every 10 seconds
  if (idleCheckInterval) clearInterval(idleCheckInterval);
  idleCheckInterval = setInterval(checkIdleStatus, 10000);

  // Check immediately on tab focus or visibility change (e.g. laptop wake or returning to tab)
  document.addEventListener('visibilitychange', checkIdleStatus);
  window.addEventListener('focus', checkIdleStatus);

  // Synchronize across multiple open browser tabs
  window.addEventListener('storage', handleMultiTabSync);
}

function stopIdleTracker() {
  isIdleTrackingActive = false;

  if (idleCheckInterval) {
    clearInterval(idleCheckInterval);
    idleCheckInterval = null;
  }
  if (activityThrottleTimer) {
    clearTimeout(activityThrottleTimer);
    activityThrottleTimer = null;
  }

  const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
  activityEvents.forEach(evt => {
    window.removeEventListener(evt, recordUserActivity);
  });

  document.removeEventListener('visibilitychange', checkIdleStatus);
  window.removeEventListener('focus', checkIdleStatus);
  window.removeEventListener('storage', handleMultiTabSync);
}

function handleMultiTabSync(e) {
  if (e.key === 'jmos_logout_event') {
    performLogout('sync');
  }
}

// 2. Core Logout Handler
async function performLogout(reason = 'user') {
  stopIdleTracker();

  const token = JMOS_STATE.apiToken || localStorage.getItem('jmos_api_token');
  if (token && reason !== 'sync') {
    try {
      await JMOS_API.post('/auth/logout', {});
    } catch (_) {}
  }

  // Clear in-memory and persisted session
  JMOS_STATE.currentUser = null;
  JMOS_STATE.apiToken = null;

  try {
    localStorage.removeItem('jmos_api_token');
    localStorage.removeItem('jmos_user');
    localStorage.removeItem('jmos_last_activity');
    localStorage.removeItem('jmos_active_view');
    if (reason !== 'sync') {
      localStorage.setItem('jmos_logout_event', Date.now().toString());
    }
  } catch (_) {}

  // Update DOM state
  document.documentElement.classList.remove('jmos-authenticated');

  const loginScreen = document.getElementById('loginScreen');
  const appRoot = document.getElementById('appRoot');
  const loginNotice = document.getElementById('loginNotice');
  const loginErr = document.getElementById('loginErr');
  const loginEmail = document.getElementById('loginEmail');
  const loginPass = document.getElementById('loginPass');

  if (appRoot) appRoot.style.display = 'none';
  if (loginScreen) loginScreen.classList.remove('hidden');
  if (loginErr) loginErr.classList.remove('show');

  if (loginNotice) {
    if (reason === 'idle') {
      loginNotice.textContent = 'You have been signed out due to 15 minutes of inactivity. Please sign in again.';
      loginNotice.classList.add('show');
    } else if (reason === 'server_expired') {
      loginNotice.textContent = 'Your session has expired. Please sign in again.';
      loginNotice.classList.add('show');
    } else {
      loginNotice.classList.remove('show');
    }
  }

  if (reason === 'user') {
    if (loginEmail) loginEmail.value = '';
    if (loginPass) loginPass.value = '';
  }
}

// Time-bound greeting helpers
function getTimeBoundGreeting(date = new Date()) {
  const hour = date.getHours();
  if (hour >= 4 && hour < 12) return 'Good morning';
  if (hour >= 12 && hour < 17) return 'Good afternoon';
  return 'Good evening';
}

function getTimeBoundBriefTag(date = new Date()) {
  const hour = date.getHours();
  if (hour >= 4 && hour < 12) return 'Your morning brief';
  if (hour >= 12 && hour < 17) return 'Your afternoon brief';
  if (hour >= 17 && hour < 22) return 'Your evening brief';
  return 'Your late-night brief';
}

window.getTimeBoundGreeting = getTimeBoundGreeting;
window.getTimeBoundBriefTag = getTimeBoundBriefTag;

// 3. User Interface Binding for Authenticated Session
function applyAuthenticatedUI(user) {
  const loginScreen = document.getElementById('loginScreen');
  const appRoot = document.getElementById('appRoot');
  const loginNotice = document.getElementById('loginNotice');
  const loginErr = document.getElementById('loginErr');

  if (loginNotice) loginNotice.classList.remove('show');
  if (loginErr) loginErr.classList.remove('show');

  document.documentElement.classList.add('jmos-authenticated');
  if (loginScreen) loginScreen.classList.add('hidden');
  if (appRoot) appRoot.style.display = 'grid';

  // Update Topbar / Sidebar Profile Chip
  const userAv = document.getElementById('userAv');
  const userNm = document.getElementById('userNm');
  const userRl = document.getElementById('userRl');
  const greetName = document.getElementById('greetName');

  if (userAv) {
    if (user.avatar_url) {
      userAv.innerHTML = `<img src="${user.avatar_url}" alt="${escHtml(user.name || 'User')}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block">`;
      userAv.style.background = 'transparent';
      userAv.style.overflow = 'hidden';
    } else {
      userAv.textContent = user.ini || getInitials(user.name);
      userAv.style.background = user.color || '#C52523';
      userAv.style.overflow = '';
    }
  }
  if (userNm) userNm.textContent = user.name;
  if (userRl) userRl.textContent = (JMOS_STATE.roleLabel && JMOS_STATE.roleLabel[user.role]) || user.role;
  if (greetName) {
    const greeting = getTimeBoundGreeting();
    greetName.textContent = greeting + ', ' + (user.name ? user.name.split(' ')[0] : 'there') + '.';
  }

  // Also refresh the brief tag if on dashboard
  const briefTagText = document.getElementById('briefTagText');
  if (briefTagText) {
    briefTagText.textContent = getTimeBoundBriefTag();
  }

  if (typeof applyRole === 'function') {
    applyRole(user.role);
  }
}

// 4. Session Set and Restore
async function setAuthenticatedSession(user, token, isRestore = false) {
  JMOS_STATE.currentUser = user;
  JMOS_STATE.apiToken = token;

  try {
    localStorage.setItem('jmos_api_token', token);
    localStorage.setItem('jmos_user', JSON.stringify(user));
    localStorage.setItem('jmos_last_activity', Date.now().toString());
  } catch (_) {}

  applyAuthenticatedUI(user);
  startIdleTracker();

  if (!isRestore) {
    // New login: pull fresh database state, render, and go to dashboard
    await JMOS_API.fetchAll();
    if (typeof renderAllViews === 'function') renderAllViews();
    if (typeof showView === 'function') showView('dashboard');
    if (typeof showToast === 'function') showToast('Signed in', `Welcome back, ${user.name}`);
  } else {
    // Session restored from page refresh: preserve user view
    const savedView = localStorage.getItem('jmos_active_view') || 'dashboard';
    if (typeof showView === 'function') showView(savedView);

    // Fetch fresh database state in background and re-render views
    JMOS_API.fetchAll().then(() => {
      if (typeof renderAllViews === 'function') renderAllViews();
      if (typeof window.populateTaskProjectOptions === 'function') {
        window.populateTaskProjectOptions();
      }
      if (typeof window.populateExpenseProjectOptions === 'function') {
        window.populateExpenseProjectOptions();
      }
    }).catch(console.warn);

    // Verify token with backend in background and synchronize fresh user profile
    if (token && token !== 'demo_token') {
      JMOS_API.get('/auth/me').then(res => {
        if (res.status === 'success' && res.user) {
          const freshUser = {
            ...JMOS_STATE.currentUser,
            ...res.user,
            color: res.user.color || JMOS_STATE.currentUser?.color || '#C52523',
            ini: res.user.initials || res.user.ini || getInitials(res.user.name)
          };
          JMOS_STATE.currentUser = freshUser;
          try {
            localStorage.setItem('jmos_user', JSON.stringify(freshUser));
          } catch (_) {}

          // Refresh secondary email field if settings view is open
          const secEmailInput = document.getElementById('cfg_secondary_email');
          if (secEmailInput && !secEmailInput.matches(':focus')) {
            secEmailInput.value = freshUser.secondary_email || '';
          }
        }
      }).catch(err => {
        if (err.message && err.message.includes('401')) {
          console.warn('Session expired on backend:', err.message);
          performLogout('server_expired');
        }
      });
    }
  }
}

// 5. Demo Accounts Display
function renderDemoAccounts() {
  const demoContainer = document.getElementById('demoAccts');
  if (!demoContainer) return;

  const users = JMOS_STATE.users.length ? JMOS_STATE.users : [
    { name: 'Barny Kiome', role: 'owner', email: 'barny@jeotamedia.co.ke' },
    { name: 'Matthew Muange', role: 'finance', email: 'matthew@jeotamedia.co.ke' },
    { name: 'Patrick Mwendwa', role: 'sales', email: 'patrick@jeotamedia.co.ke' },
    { name: 'Stephen Otieno', role: 'team', email: 'stephen@jeotamedia.co.ke' },
    { name: 'Ian Aluda', role: 'team', email: 'ian@jeotamedia.co.ke' }
  ];

  demoContainer.innerHTML = users.map(u => 
    `<span class="acct" data-fill="${u.email}">${u.name.split(' ')[0]} · ${u.role}</span>`
  ).join('');
}

// 6. Main Auth Initializer
function initAuth() {
  const loginScreen = document.getElementById('loginScreen');
  const appRoot = document.getElementById('appRoot');
  const signinBtn = document.getElementById('signinBtn');
  const signoutBtn = document.getElementById('signoutBtn');
  const loginEmail = document.getElementById('loginEmail');
  const loginPass = document.getElementById('loginPass');
  const loginErr = document.getElementById('loginErr');
  const loginNotice = document.getElementById('loginNotice');
  const demoContainer = document.getElementById('demoAccts');

  // Check and restore existing session across page refresh
  const storedToken = localStorage.getItem('jmos_api_token');
  const storedUserRaw = localStorage.getItem('jmos_user');
  const lastActive = parseInt(localStorage.getItem('jmos_last_activity') || '0', 10);
  const now = Date.now();

  if (storedToken && storedUserRaw) {
    if (lastActive > 0 && (now - lastActive >= IDLE_TIMEOUT_MS)) {
      // Idle for 15+ minutes while away/refreshed
      performLogout('idle');
    } else {
      try {
        const storedUser = JSON.parse(storedUserRaw);
        setAuthenticatedSession(storedUser, storedToken, true);
      } catch (_) {
        performLogout('corrupt');
      }
    }
  }

  // Load team members for demo pills from database
  JMOS_API.get('/users')
    .then(users => {
      if (Array.isArray(users) && users.length) {
        JMOS_STATE.users = users.map((u, i) => ({
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
        renderDemoAccounts();
      }
    })
    .catch(() => renderDemoAccounts());

  // Quick-fill from demo pills
  if (demoContainer) {
    demoContainer.addEventListener('click', (e) => {
      const pill = e.target.closest('[data-fill]');
      if (pill) {
        loginEmail.value = pill.getAttribute('data-fill');
        loginPass.value = 'jeota2024';
        if (loginErr) loginErr.classList.remove('show');
        if (loginNotice) loginNotice.classList.remove('show');
      }
    });
  }

  // Sign In Action
  async function doLogin() {
    const em = loginEmail.value.trim().toLowerCase();
    const pw = loginPass.value;
    if (signinBtn) {
      signinBtn.textContent = 'Signing in…';
      signinBtn.disabled = true;
    }

    let user = null;
    let token = null;

    // 1. Authenticate with Laravel Sanctum API
    try {
      const res = await JMOS_API.post('/auth/login', { email: em, password: pw });
      if (res.status === 'success' && res.token && res.user) {
        token = res.token;
        user = {
          id: res.user.id,
          name: res.user.name,
          title: res.user.title,
          email: res.user.email,
          secondary_email: res.user.secondary_email || null,
          google_calendar_email: res.user.google_calendar_email || null,
          role: res.user.role,
          type: res.user.type,
          pay: res.user.pay,
          color: res.user.color || '#C52523',
          ini: res.user.initials || getInitials(res.user.name)
        };
      }
    } catch (err) {
      console.warn('API login failed, checking fallback:', err.message);
    }

    // 2. Fallback check for local demo accounts
    if (!user) {
      const matched = JMOS_STATE.users.find(x => x.email.toLowerCase() === em && (x.pass === pw || pw === 'jeota2024'));
      if (matched) {
        user = matched;
        token = 'demo_token';
      }
    }

    if (signinBtn) {
      signinBtn.textContent = 'Sign in';
      signinBtn.disabled = false;
    }

    if (!user || !token) {
      if (loginNotice) loginNotice.classList.remove('show');
      if (loginErr) loginErr.classList.add('show');
      return;
    }

    // Establish full active session
    await setAuthenticatedSession(user, token, false);
  }

  if (signinBtn) signinBtn.onclick = doLogin;

  if (loginPass) {
    loginPass.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') doLogin();
    });
  }

  if (loginEmail) {
    loginEmail.addEventListener('input', () => {
      if (loginErr) loginErr.classList.remove('show');
      if (loginNotice) loginNotice.classList.remove('show');
    });
  }

  if (loginPass) {
    loginPass.addEventListener('input', () => {
      if (loginErr) loginErr.classList.remove('show');
      if (loginNotice) loginNotice.classList.remove('show');
    });
  }

  if (signoutBtn) {
    signoutBtn.onclick = () => performLogout('user');
  }

  // Pre-fill default account if empty and not logged in
  if (!JMOS_STATE.currentUser) {
    if (loginEmail && !loginEmail.value) loginEmail.value = 'barny@jeotamedia.co.ke';
    if (loginPass && !loginPass.value) loginPass.value = 'jeota2024';
  }
}

/* ==========================================================================
   User Account & Profile Picture Management
   ========================================================================== */

window.openMyProfileModal = function () {
  const user = JMOS_STATE.currentUser;
  if (!user) return;

  const modal = document.getElementById('myProfileModal');
  if (!modal) return;

  // Populate form fields
  if (document.getElementById('mpName')) document.getElementById('mpName').value = user.name || '';
  if (document.getElementById('mpTitle')) document.getElementById('mpTitle').value = user.title || '';
  if (document.getElementById('mpDepartment')) document.getElementById('mpDepartment').value = user.department || '';
  if (document.getElementById('mpPhone')) document.getElementById('mpPhone').value = user.phone || '';
  if (document.getElementById('mpEmail')) document.getElementById('mpEmail').value = user.email || '';
  if (document.getElementById('mpSecondaryEmail')) document.getElementById('mpSecondaryEmail').value = user.secondary_email || '';
  if (document.getElementById('mpBio')) document.getElementById('mpBio').value = user.bio || '';

  // Reset password inputs
  if (document.getElementById('mpCurrentPass')) document.getElementById('mpCurrentPass').value = '';
  if (document.getElementById('mpNewPass')) document.getElementById('mpNewPass').value = '';
  if (document.getElementById('mpConfirmPass')) document.getElementById('mpConfirmPass').value = '';

  // Reset file input
  const fileInput = document.getElementById('mpAvatarFileInput');
  if (fileInput) fileInput.value = '';

  // Render current avatar in preview box
  window.updateMyAvatarPreviewDisplay(user.avatar_url, user.name, user.color);

  openModal('myProfileModal');
};

window.updateMyAvatarPreviewDisplay = function (avatarUrl, name, color) {
  const box = document.getElementById('mpAvatarPreviewBox');
  const removeBtn = document.getElementById('mpRemoveAvatarBtn');
  if (!box) return;

  if (avatarUrl) {
    box.innerHTML = `<img src="${avatarUrl}" alt="${escHtml(name || 'User')}" style="width:100%;height:100%;object-fit:cover;display:block">`;
    box.style.background = 'transparent';
    if (removeBtn) removeBtn.style.display = 'inline-flex';
  } else {
    const initials = getInitials(name || 'User');
    box.innerHTML = `<span id="mpAvatarInitials">${escHtml(initials)}</span>`;
    box.style.background = color || 'var(--red)';
    if (removeBtn) removeBtn.style.display = 'none';
  }
};

window.onMyAvatarFileSelected = function (input) {
  const file = input && input.files && input.files[0];
  if (!file) return;

  const box = document.getElementById('mpAvatarPreviewBox');
  const removeBtn = document.getElementById('mpRemoveAvatarBtn');

  if (file.size > 10 * 1024 * 1024) {
    alert('File size exceeds 10MB limit. Please choose a smaller photo.');
    input.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    if (box) {
      box.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;display:block">`;
      box.style.background = 'transparent';
    }
    if (removeBtn) removeBtn.style.display = 'inline-flex';
  };
  reader.readAsDataURL(file);
};

window.removeMyAvatar = async function () {
  if (!confirm('Remove your custom profile picture and use initials avatar?')) return;

  try {
    const res = await JMOS_API.post('/auth/avatar/remove', {});
    if (res && res.user) {
      Object.assign(JMOS_STATE.currentUser, res.user);
      localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
      applyAuthenticatedUI(JMOS_STATE.currentUser);
      window.updateMyAvatarPreviewDisplay(null, JMOS_STATE.currentUser.name, JMOS_STATE.currentUser.color);
      if (window.showToast) window.showToast('Profile picture removed', 'Default initials restored');
    }
  } catch (err) {
    alert('Failed to remove photo: ' + err.message);
  }
};

window.saveMyProfile = async function (e) {
  if (e) e.preventDefault();
  const saveBtn = document.getElementById('mpSaveBtn');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }

  try {
    const name = document.getElementById('mpName')?.value.trim();
    const title = document.getElementById('mpTitle')?.value.trim();
    const department = document.getElementById('mpDepartment')?.value.trim();
    const phone = document.getElementById('mpPhone')?.value.trim();
    const secondaryEmail = document.getElementById('mpSecondaryEmail')?.value.trim();
    const bio = document.getElementById('mpBio')?.value.trim();

    const fileInput = document.getElementById('mpAvatarFileInput');
    const avatarFile = fileInput && fileInput.files && fileInput.files[0];

    // 1. Upload Avatar if a new file was chosen
    if (avatarFile) {
      const formData = new FormData();
      formData.append('avatar', avatarFile);
      const avRes = await JMOS_API.upload('/auth/avatar', formData);
      if (avRes && avRes.user) {
        Object.assign(JMOS_STATE.currentUser, avRes.user);
      }
    }

    // 2. Update Profile Information
    const profRes = await JMOS_API.post('/auth/profile', {
      name: name,
      title: title,
      department: department,
      phone: phone,
      secondary_email: secondaryEmail,
      bio: bio,
    });

    if (profRes && profRes.user) {
      Object.assign(JMOS_STATE.currentUser, profRes.user);
    }

    // 3. Update Password if specified
    const currentPass = document.getElementById('mpCurrentPass')?.value;
    const newPass = document.getElementById('mpNewPass')?.value;
    const confirmPass = document.getElementById('mpConfirmPass')?.value;

    if (newPass) {
      if (!currentPass) {
        throw new Error('Please enter your current password to set a new password.');
      }
      if (newPass !== confirmPass) {
        throw new Error('New password and confirmation do not match.');
      }
      await JMOS_API.post('/auth/password', {
        current_password: currentPass,
        password: newPass,
        password_confirmation: confirmPass
      });
    }

    // Save state and update UI
    localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
    applyAuthenticatedUI(JMOS_STATE.currentUser);

    // Refresh people directory in background
    if (typeof ensurePeople === 'function') ensurePeople();

    closeModal('myProfileModal');
    if (window.showToast) {
      window.showToast('Profile updated', 'Your personal details and avatar have been saved successfully');
    } else {
      alert('Profile updated successfully');
    }
  } catch (err) {
    alert('Error updating profile: ' + err.message);
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Changes';
    }
  }
};

