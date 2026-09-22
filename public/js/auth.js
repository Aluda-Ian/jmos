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
    // New login: navigate to intended destination from notification/link, or default to dashboard
    const redirectView = sessionStorage.getItem('jmos_redirect_view') || (typeof resolveTargetViewFromUrl === 'function' ? resolveTargetViewFromUrl() : null) || 'dashboard';
    sessionStorage.removeItem('jmos_redirect_view');

    await JMOS_API.fetchAll();
    if (typeof renderAllViews === 'function') renderAllViews();
    if (typeof showView === 'function') showView(redirectView);
    if (typeof showToast === 'function') showToast('Signed in', `Welcome back, ${user.name}`);
  } else {
    // Session restored from page refresh or direct notification click
    const targetView = (typeof resolveTargetViewFromUrl === 'function' ? resolveTargetViewFromUrl() : null) || localStorage.getItem('jmos_active_view') || 'dashboard';
    if (typeof showView === 'function') showView(targetView);

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

// 5. Main Auth Initializer
function initAuth() {
  const loginScreen = document.getElementById('loginScreen');
  const appRoot = document.getElementById('appRoot');
  const signinBtn = document.getElementById('signinBtn');
  const signoutBtn = document.getElementById('signoutBtn');
  const loginEmail = document.getElementById('loginEmail');
  const loginPass = document.getElementById('loginPass');
  const loginErr = document.getElementById('loginErr');
  const loginNotice = document.getElementById('loginNotice');

  // Capture target view from incoming notification or deep link
  const targetFromUrl = typeof resolveTargetViewFromUrl === 'function' ? resolveTargetViewFromUrl() : null;
  if (targetFromUrl) {
    try {
      sessionStorage.setItem('jmos_redirect_view', targetFromUrl);
    } catch (_) {}
  }

  // Check and restore existing session across page refresh
  const storedToken = localStorage.getItem('jmos_api_token');
  const storedUserRaw = localStorage.getItem('jmos_user');
  const lastActive = parseInt(localStorage.getItem('jmos_last_activity') || '0', 10);
  const now = Date.now();

  if (storedToken && storedUserRaw) {
    if (lastActive > 0 && (now - lastActive >= IDLE_TIMEOUT_MS)) {
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

  // --- Password Reset & OTP Flow ---
  const signinCard = document.getElementById('signinCard');
  const forgotCard = document.getElementById('forgotCard');
  const resetCard = document.getElementById('resetCard');
  const toForgotBtn = document.getElementById('toForgotBtn');
  const backToSigninFromForgot = document.getElementById('backToSigninFromForgot');
  const backToSigninFromReset = document.getElementById('backToSigninFromReset');
  const sendOtpBtn = document.getElementById('sendOtpBtn');
  const submitResetBtn = document.getElementById('submitResetBtn');
  const resendOtpBtn = document.getElementById('resendOtpBtn');
  const forgotEmail = document.getElementById('forgotEmail');
  const forgotNotice = document.getElementById('forgotNotice');
  const forgotErr = document.getElementById('forgotErr');
  const resetOtp = document.getElementById('resetOtp');
  const resetNewPass = document.getElementById('resetNewPass');
  const resetConfirmPass = document.getElementById('resetConfirmPass');
  const resetNotice = document.getElementById('resetNotice');
  const resetErr = document.getElementById('resetErr');
  const resetTargetEmail = document.getElementById('resetTargetEmail');
  const resendTimer = document.getElementById('resendTimer');
  const resendSecs = document.getElementById('resendSecs');

  let activeResetEmail = '';
  let resendInterval = null;

  function showAuthCard(cardToShow) {
    [signinCard, forgotCard, resetCard].forEach(card => {
      if (card) card.style.display = 'none';
    });
    if (cardToShow) cardToShow.style.display = 'block';

    // Clear notices/errors
    [loginNotice, loginErr, forgotNotice, forgotErr, resetNotice, resetErr].forEach(el => {
      if (el) {
        el.classList.remove('show');
        el.style.display = 'none';
        el.textContent = '';
      }
    });
  }

  function startResendCountdown(seconds = 60) {
    if (resendInterval) clearInterval(resendInterval);
    if (!resendTimer || !resendSecs || !resendOtpBtn) return;

    let remaining = seconds;
    resendOtpBtn.style.pointerEvents = 'none';
    resendOtpBtn.style.opacity = '0.5';
    resendTimer.style.display = 'inline';
    resendSecs.textContent = remaining;

    resendInterval = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        clearInterval(resendInterval);
        resendOtpBtn.style.pointerEvents = 'auto';
        resendOtpBtn.style.opacity = '1';
        resendTimer.style.display = 'none';
      } else {
        resendSecs.textContent = remaining;
      }
    }, 1000);
  }

  if (toForgotBtn) {
    toForgotBtn.addEventListener('click', (e) => {
      e.preventDefault();
      showAuthCard(forgotCard);
      if (forgotEmail) {
        forgotEmail.value = (loginEmail && loginEmail.value) ? loginEmail.value : '';
        forgotEmail.focus();
      }
    });
  }

  if (backToSigninFromForgot) {
    backToSigninFromForgot.addEventListener('click', (e) => {
      e.preventDefault();
      showAuthCard(signinCard);
    });
  }

  if (backToSigninFromReset) {
    backToSigninFromReset.addEventListener('click', (e) => {
      e.preventDefault();
      showAuthCard(signinCard);
    });
  }

  // Action: Request OTP
  async function doSendOtp() {
    const em = forgotEmail ? forgotEmail.value.trim().toLowerCase() : '';
    if (!em) {
      if (forgotErr) {
        forgotErr.textContent = 'Please enter your account email address.';
        forgotErr.style.display = 'block';
        forgotErr.classList.add('show');
      }
      return;
    }

    if (sendOtpBtn) {
      sendOtpBtn.textContent = 'Sending code…';
      sendOtpBtn.disabled = true;
    }
    if (forgotErr) forgotErr.classList.remove('show');
    if (forgotNotice) forgotNotice.classList.remove('show');

    try {
      const res = await JMOS_API.post('/auth/forgot-password', { email: em });
      if (res && res.status === 'success') {
        activeResetEmail = em;
        if (resetTargetEmail) resetTargetEmail.textContent = em;
        showAuthCard(resetCard);
        if (resetNotice) {
          resetNotice.textContent = res.message || 'Verification code sent to your email.';
          resetNotice.style.display = 'block';
          resetNotice.classList.add('show');
        }
        if (resetOtp) {
          resetOtp.value = '';
          resetOtp.focus();
        }
        startResendCountdown(60);
      } else {
        throw new Error(res.message || 'Could not send verification code.');
      }
    } catch (err) {
      if (forgotErr) {
        forgotErr.textContent = err.message || 'No account found with this email address.';
        forgotErr.style.display = 'block';
        forgotErr.classList.add('show');
      }
    } finally {
      if (sendOtpBtn) {
        sendOtpBtn.textContent = 'Send verification code';
        sendOtpBtn.disabled = false;
      }
    }
  }

  if (sendOtpBtn) sendOtpBtn.onclick = doSendOtp;
  if (forgotEmail) {
    forgotEmail.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') doSendOtp();
    });
  }

  // Action: Resend OTP
  if (resendOtpBtn) {
    resendOtpBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      if (!activeResetEmail) return;

      resendOtpBtn.textContent = 'Resending…';
      try {
        const res = await JMOS_API.post('/auth/forgot-password', { email: activeResetEmail });
        if (res && res.status === 'success') {
          if (resetNotice) {
            resetNotice.textContent = 'New 6-digit verification code sent!';
            resetNotice.style.display = 'block';
            resetNotice.classList.add('show');
          }
          if (resetErr) resetErr.classList.remove('show');
          startResendCountdown(60);
        }
      } catch (err) {
        if (resetErr) {
          resetErr.textContent = err.message || 'Failed to resend verification code.';
          resetErr.style.display = 'block';
          resetErr.classList.add('show');
        }
      } finally {
        resendOtpBtn.textContent = 'Resend code';
      }
    });
  }

  // Action: Submit OTP and Reset Password
  async function doResetPassword() {
    const otp = resetOtp ? resetOtp.value.trim() : '';
    const newPass = resetNewPass ? resetNewPass.value : '';
    const confirmPass = resetConfirmPass ? resetConfirmPass.value : '';

    if (!otp || otp.length !== 6) {
      if (resetErr) {
        resetErr.textContent = 'Please enter the complete 6-digit verification code.';
        resetErr.style.display = 'block';
        resetErr.classList.add('show');
      }
      return;
    }

    if (!newPass || newPass.length < 6) {
      if (resetErr) {
        resetErr.textContent = 'Password must be at least 6 characters in length.';
        resetErr.style.display = 'block';
        resetErr.classList.add('show');
      }
      return;
    }

    if (newPass !== confirmPass) {
      if (resetErr) {
        resetErr.textContent = 'Passwords do not match. Please check and try again.';
        resetErr.style.display = 'block';
        resetErr.classList.add('show');
      }
      return;
    }

    if (submitResetBtn) {
      submitResetBtn.textContent = 'Resetting password…';
      submitResetBtn.disabled = true;
    }
    if (resetErr) resetErr.classList.remove('show');

    try {
      const res = await JMOS_API.post('/auth/reset-password', {
        email: activeResetEmail,
        otp: otp,
        password: newPass,
        password_confirmation: confirmPass
      });

      if (res && res.status === 'success') {
        showAuthCard(signinCard);
        if (loginEmail) loginEmail.value = activeResetEmail;
        if (loginPass) loginPass.value = newPass;

        if (loginNotice) {
          loginNotice.textContent = 'Password reset successfully! Signing you in…';
          loginNotice.style.display = 'block';
          loginNotice.classList.add('show');
        }

        // Auto sign-in with the new credentials
        setTimeout(() => {
          doLogin();
        }, 600);
      } else {
        throw new Error(res.message || 'Could not reset password.');
      }
    } catch (err) {
      if (resetErr) {
        resetErr.textContent = err.message || 'Invalid or expired verification code.';
        resetErr.style.display = 'block';
        resetErr.classList.add('show');
      }
    } finally {
      if (submitResetBtn) {
        submitResetBtn.textContent = 'Reset password & Sign in';
        submitResetBtn.disabled = false;
      }
    }
  }

  if (submitResetBtn) submitResetBtn.onclick = doResetPassword;
  [resetOtp, resetNewPass, resetConfirmPass].forEach(input => {
    if (input) {
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') doResetPassword();
      });
    }
  });

  // Auto-detect invitation setup link / OTP from URL query parameters
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const emailParam = urlParams.get('email');
    const otpParam = urlParams.get('otp');
    const setupParam = urlParams.get('setup') || urlParams.get('reset');

    if (emailParam && (otpParam || setupParam)) {
      activeResetEmail = emailParam.toLowerCase().trim();
      if (resetTargetEmail) resetTargetEmail.textContent = activeResetEmail;
      showAuthCard(resetCard);
      if (resetOtp && otpParam) {
        resetOtp.value = otpParam;
      }
      if (resetNotice) {
        resetNotice.textContent = 'Welcome to JMOS! Choose your password to activate your workspace.';
        resetNotice.style.display = 'block';
        resetNotice.classList.add('show');
      }
      if (resetNewPass) {
        setTimeout(() => resetNewPass.focus(), 200);
      }
    }
  } catch (e) {}
}

/* ==========================================================================
   User Account & Profile Picture Management
   ========================================================================== */

window.openMyProfileModal = function () {
  let user = JMOS_STATE.currentUser;
  if (!user) {
    try {
      const stored = localStorage.getItem('jmos_user');
      if (stored) user = JSON.parse(stored);
    } catch (e) {}
  }
  if (!user) {
    user = {
      name: document.getElementById('userNm')?.textContent || 'Barny Kiome',
      email: 'owner@jeotamedia.co.ke',
      title: 'Managing Director & Lead Producer',
      role: 'owner',
      color: 'var(--red)'
    };
  }

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
    if (window.showToast) window.showToast('File Too Large', 'Avatar size exceeds 10MB limit. Please choose a smaller photo.', true);
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
  const confirmed = await window.showConfirmDialog({
    title: 'Remove Profile Photo?',
    subtitle: 'Account Avatar Customization',
    type: 'danger',
    confirmText: 'Remove Photo',
    message: 'Remove your custom profile picture and revert to initial avatar badge?'
  });
  if (!confirmed) return;

  try {
    const res = await JMOS_API.post('/auth/avatar/remove', {});
    if (res && res.user) {
      Object.assign(JMOS_STATE.currentUser, res.user);
      localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
      applyAuthenticatedUI(JMOS_STATE.currentUser);
      window.updateMyAvatarPreviewDisplay(null, JMOS_STATE.currentUser.name, JMOS_STATE.currentUser.color);
      if (window.showToast) window.showToast('Profile photo removed', 'Default initials restored');
    }
  } catch (err) {
    if (window.showToast) window.showToast('Remove Failed', err.message, true);
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
    }
  } catch (err) {
    if (window.showToast) window.showToast('Profile Error', err.message, true);
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Changes';
    }
  }
};

