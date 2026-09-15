<!-- ==========================================================================
     JMOS — View: Settings (Appearance for all · Admin config for owner/manager)
     ========================================================================== -->
<section class="view" data-view="settings" hidden>
  <div class="page-head">
    <div>
      <h1 class="pt">Settings</h1>
      <p>Appearance preferences and system configuration.</p>
    </div>
    <div class="head-actions" data-perm="owner manager">
      <button type="button" class="btn primary" id="saveAllSettingsBtn">
        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save all settings
      </button>
    </div>
  </div>

  <!-- My Profile & Account (All Users) -->
  <div class="card" style="padding:24px;margin-bottom:20px" id="myProfileCard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:8px;background:var(--red-soft);color:var(--red);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
          <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">My Profile &amp; Account</h3>
          <p style="font-size:12px;color:var(--muted)">Manage your personal details, profile picture, and account credentials.</p>
        </div>
      </div>
      <button type="button" class="btn primary" id="saveProfileBtn" onclick="saveMyProfile()">
        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save Profile
      </button>
    </div>

    <!-- Avatar Upload Banner -->
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:22px;padding:16px;background:var(--paper);border:1px solid var(--line);border-radius:12px;flex-wrap:wrap">
      <div id="profileAvatarCircle" style="width:72px;height:72px;border-radius:50%;background:var(--red);color:#fff;display:grid;place-items:center;font-size:24px;font-weight:700;overflow:hidden;border:3px solid var(--card);box-shadow:0 2px 8px rgba(0,0,0,0.08);flex-shrink:0">
        <span id="profileAvatarInitials">--</span>
      </div>
      <div style="flex:1;min-width:200px">
        <div style="font-size:14px;font-weight:600;margin-bottom:4px">Profile Picture</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:10px">Upload a high-resolution photo (JPG, PNG, WebP or GIF, up to 10MB). Square aspect ratio looks best.</p>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <input type="file" id="profileAvatarInput" accept="image/*" style="display:none" onchange="uploadMyAvatar(this.files[0])">
          <button type="button" class="btn sm" onclick="document.getElementById('profileAvatarInput').click()">
            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>Change Photo
          </button>
          <button type="button" class="btn sm" id="profileAvatarRemoveBtn" onclick="removeMyAvatar()" style="color:var(--red);border-color:rgba(239,68,68,0.3)">
            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>Remove Photo
          </button>
          <span id="profileAvatarStatus" style="font-size:11px;color:var(--muted)"></span>
        </div>
      </div>
    </div>

    <!-- Personal & Work Details Grid -->
    <div class="grid2" style="margin-bottom:16px">
      <div class="field">
        <label for="profileName">Full Name *</label>
        <input id="profileName" placeholder="e.g. Grace Wanjiru" required autocomplete="name">
      </div>
      <div class="field">
        <label for="profileTitle">Professional Title / Role</label>
        <input id="profileTitle" placeholder="e.g. Senior Cinematographer" autocomplete="organization-title">
      </div>
    </div>

    <div class="grid2" style="margin-bottom:16px">
      <div class="field">
        <label for="profileDepartment">Department</label>
        <input id="profileDepartment" list="profileDeptSuggestions" placeholder="e.g. Production, Creative, Video" autocomplete="off">
        <datalist id="profileDeptSuggestions">
          <option value="Production">
          <option value="Creative">
          <option value="Video &amp; Cinematography">
          <option value="Photography">
          <option value="Audio &amp; Sound">
          <option value="Post-Production &amp; 3D">
          <option value="Sales &amp; Marketing">
          <option value="Finance &amp; Operations">
          <option value="Executive &amp; Management">
        </datalist>
      </div>
      <div class="field">
        <label for="profilePhone">Phone / WhatsApp Number</label>
        <input id="profilePhone" placeholder="e.g. +254 712 345 678" autocomplete="tel">
      </div>
    </div>

    <div class="grid2" style="margin-bottom:16px">
      <div class="field">
        <label for="profileEmail">Primary Login Email (Contact Admin to change)</label>
        <input id="profileEmail" type="email" readonly disabled style="opacity:0.75;cursor:not-allowed;background:var(--paper)">
      </div>
      <div class="field">
        <label for="profileBio">Bio / Short Note</label>
        <input id="profileBio" placeholder="e.g. Specializing in anamorphic lenses &amp; color grading" autocomplete="off">
      </div>
    </div>

    <!-- Password Change (Optional) Accordion / Block -->
    <div style="margin-top:16px;padding-top:16px;border-top:1px dashed var(--line)">
      <div style="font-size:13px;font-weight:600;margin-bottom:4px;display:flex;align-items:center;gap:6px">
        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Change Account Password <span style="font-weight:400;font-size:11px;color:var(--muted)">(Leave blank if you don't want to change password)</span>
      </div>
      <div class="grid3" style="margin-top:10px">
        <div class="field">
          <label for="profileCurrentPass">Current Password</label>
          <input id="profileCurrentPass" type="password" placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="field">
          <label for="profileNewPass">New Password</label>
          <input id="profileNewPass" type="password" placeholder="Min 6 characters" autocomplete="new-password">
        </div>
        <div class="field">
          <label for="profileConfirmPass">Confirm New Password</label>
          <input id="profileConfirmPass" type="password" placeholder="Re-enter new password" autocomplete="new-password">
        </div>
      </div>
    </div>
    <div id="profileSaveResult" style="margin-top:12px;font-size:12px;display:none;padding:10px 14px;border-radius:8px"></div>
  </div>

  <!-- Appearance Settings (All Users) -->
  <div class="card" style="padding:24px;margin-bottom:20px" id="appearanceCard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:8px;background:var(--amber-soft);color:var(--amber);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        </div>
        <div>
          <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Appearance</h3>
          <p style="font-size:12px;color:var(--muted)">Customize the look and feel of your workspace.</p>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start">
      <!-- Theme Selector -->
      <div class="field">
        <label for="cfg_appearance_theme">Theme</label>
        <select id="cfg_appearance_theme" onchange="applyThemeFromSettings(this.value)">
          <option value="light">Light</option>
          <option value="dark">Dark</option>
        </select>
      </div>

      <!-- Live Preview Tiles -->
      <div style="display:flex;gap:10px;padding-top:22px">
        <button type="button" class="theme-preview-tile" data-set-theme="light" onclick="applyThemeFromSettings('light')" style="flex:1;border:2px solid var(--line);border-radius:10px;padding:12px;cursor:pointer;background:#FAF7F6;transition:border-color .2s">
          <div style="display:flex;gap:6px;margin-bottom:8px">
            <div style="width:18px;height:18px;border-radius:4px;background:#FFFFFF;border:1px solid #ECE6E4"></div>
            <div style="width:18px;height:18px;border-radius:4px;background:#ECE6E4"></div>
            <div style="width:18px;height:18px;border-radius:4px;background:#C52523"></div>
          </div>
          <div style="font-size:11px;font-weight:600;color:#1C1614">Light</div>
        </button>
        <button type="button" class="theme-preview-tile" data-set-theme="dark" onclick="applyThemeFromSettings('dark')" style="flex:1;border:2px solid var(--line);border-radius:10px;padding:12px;cursor:pointer;background:#000000;transition:border-color .2s">
          <div style="display:flex;gap:6px;margin-bottom:8px">
            <div style="width:18px;height:18px;border-radius:4px;background:#0A0A0A;border:1px solid #1E1E1E"></div>
            <div style="width:18px;height:18px;border-radius:4px;background:#1E1E1E"></div>
            <div style="width:18px;height:18px;border-radius:4px;background:#C52523"></div>
          </div>
          <div style="font-size:11px;font-weight:600;color:#F3F4F6">Dark</div>
        </button>
      </div>
    </div>
  </div>

  <!-- Notification Preferences (All Users) -->
  <div class="card" style="padding:24px;margin-bottom:20px" id="notifPrefsCard">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:8px;background:var(--green-soft);color:var(--green);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
        </div>
        <div>
          <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Notification Email</h3>
          <p style="font-size:12px;color:var(--muted)">Add a secondary email to receive copies of all system notifications.</p>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:10px;align-items:flex-end">
      <div class="field" style="flex:1;margin:0">
        <label for="cfg_secondary_email">Secondary Email Address</label>
        <input id="cfg_secondary_email" type="email" placeholder="e.g. personal@gmail.com" autocomplete="off">
      </div>
      <button type="button" class="btn primary" id="saveSecondaryEmailBtn" style="height:40px" onclick="saveSecondaryEmail()">
        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save
      </button>
    </div>
    <div id="secondaryEmailResult" style="margin-top:10px;font-size:12px;display:none;padding:8px 12px;border-radius:8px"></div>
  </div>

  <!-- App Installation & Browser Push Notifications Card -->
  <div class="card" style="padding:24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid var(--line);padding-bottom:14px;flex-wrap:wrap;gap:10px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:8px;background:rgba(2,132,199,0.12);color:var(--blue);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div>
          <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">App Download &amp; Push Notifications</h3>
          <p style="font-size:12px;color:var(--muted)">Run JMOS as a native standalone application and receive real-time desktop &amp; mobile alerts.</p>
        </div>
      </div>
    </div>

    <div class="grid2">
      <!-- 1. Native Push Notifications -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:16px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-weight:600;font-size:13.5px;color:var(--ink);display:flex;align-items:center;gap:8px">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--red)"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              Browser Push Notifications
            </div>
            <span class="badge push-status-badge" style="font-size:10.5px;padding:2px 8px;border-radius:6px;background:rgba(217,119,6,0.15);color:var(--amber);font-weight:600">Checking…</span>
          </div>
          <p style="font-size:12px;color:var(--muted);line-height:1.55;margin-bottom:12px">
            Receive native alerts for production shoots, task assignments, chat messages, and client payments even when JMOS is minimized or running in the background.
          </p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <button type="button" class="btn primary push-enable-btn" onclick="window.JMOS_PUSH.requestPermission()" style="font-size:12px;padding:7px 14px">
            Enable Browser Notifications
          </button>
          <button type="button" class="btn" onclick="triggerTestPushNotification()" style="font-size:12px;padding:7px 12px" title="Send a test notification to verify delivery">
            Send Test Alert
          </button>
        </div>
      </div>

      <!-- 2. Download / Install as App (PC & Mobile) -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:16px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="font-weight:600;font-size:13.5px;color:var(--ink);display:flex;align-items:center;gap:8px">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--blue)"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              Download App for PC &amp; Mobile
            </div>
            <span class="badge pwa-status-badge" style="font-size:10.5px;padding:2px 8px;border-radius:6px;background:rgba(2,132,199,0.15);color:var(--blue);font-weight:600">Ready to Install</span>
          </div>
          <p style="font-size:12px;color:var(--muted);line-height:1.55;margin-bottom:12px">
            Install JMOS directly to your Windows PC, Mac, Android, or iOS device. Launches instantly in full-screen window with offline support and zero browser toolbars.
          </p>
        </div>
        <div>
          <button type="button" class="btn primary pwa-install-btn" onclick="triggerDownloadApp()" style="width:100%;font-size:12px;padding:8px 14px;display:flex;align-items:center;justify-content:center;gap:6px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Download &amp; Install JMOS App
          </button>
        </div>
      </div>
    </div>
  </div>

  <div data-perm="owner manager">
  <div class="settings-grid">
    <!-- 1. SMTP Email Gateway Configuration -->
    <div class="card" style="padding:24px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:8px;background:var(--red-soft);color:var(--red);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
          </div>
          <div>
            <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">SMTP Email Gateway</h3>
            <p style="font-size:12px;color:var(--muted)">Outgoing notifications, invoices, and cascade alert emails.</p>
          </div>
        </div>
        <span class="badge" style="background:var(--green-soft);color:var(--green)">Active Gateway</span>
      </div>

      <div class="grid2">
        <div class="field">
          <label for="cfg_mail_host">SMTP Host *</label>
          <input id="cfg_mail_host" placeholder="e.g. mail.jeotamedia.co.ke" value="mail.jeotamedia.co.ke" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_mail_port">SMTP Port *</label>
          <input id="cfg_mail_port" type="number" placeholder="587" value="587" autocomplete="off">
        </div>
      </div>

      <div class="grid2">
        <div class="field">
          <label for="cfg_mail_username">SMTP Username / Email *</label>
          <input id="cfg_mail_username" placeholder="jmos@jeotamedia.co.ke" value="jmos@jeotamedia.co.ke" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_mail_password">SMTP Password *</label>
          <input id="cfg_mail_password" type="password" placeholder="••••••••" value="••••••••" autocomplete="new-password">
        </div>
      </div>

      <div class="grid2">
        <div class="field">
          <label for="cfg_mail_encryption">Encryption</label>
          <select id="cfg_mail_encryption">
            <option value="tls" selected>TLS (Port 587 - Recommended)</option>
            <option value="ssl">SSL (Port 465)</option>
            <option value="none">None (Port 25)</option>
          </select>
        </div>
        <div class="field">
          <label for="cfg_mail_from_address">From Email Address</label>
          <input id="cfg_mail_from_address" placeholder="jmos@jeotamedia.co.ke" value="jmos@jeotamedia.co.ke" autocomplete="off">
        </div>
      </div>

      <div class="field">
        <label for="cfg_mail_from_name">Sender Display Name</label>
        <input id="cfg_mail_from_name" placeholder="JMOS — Jeota Media" value="JMOS — Jeota Media" autocomplete="off">
      </div>

      <!-- Live Test SMTP Connection Box -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px;margin-top:16px">
        <b style="font-size:13px;display:block;margin-bottom:6px">Test SMTP Connectivity</b>
        <p style="font-size:12px;color:var(--muted);margin-bottom:10px">Send a live test verification email to confirm server delivery using the credentials above.</p>
        <div style="display:flex;gap:8px">
          <input id="testEmailRecipient" placeholder="Recipient (e.g. jmos@jeotamedia.co.ke)" value="jmos@jeotamedia.co.ke" style="flex:1;padding:8px 12px;border:1px solid var(--line-strong);border-radius:8px;font-size:13px;background:var(--surface)">
          <button type="button" class="btn" id="testSmtpBtn" style="white-space:nowrap">
            <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Test Email
          </button>
        </div>
        <div id="smtpTestResult" style="margin-top:10px;font-size:12px;display:none;padding:10px 14px;border-radius:8px;line-height:1.4"></div>
      </div>
    </div>

    <!-- 2. Google Calendar & Meeting Notifications API -->
    <div class="card" style="padding:24px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:8px;background:var(--amber-soft);color:var(--amber);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </div>
          <div>
            <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Google Calendar &amp; Task API</h3>
            <p style="font-size:12px;color:var(--muted)">Sync shoots, client meetings, task milestones &amp; project deliveries.</p>
          </div>
        </div>
        <span class="badge" style="background:var(--amber-soft);color:var(--amber)">Connected API</span>
      </div>

      <div class="field">
        <label for="cfg_google_calendar_id">Target Google Calendar ID *</label>
        <input id="cfg_google_calendar_id" placeholder="primary or your-calendar-id@group.calendar.google.com" value="primary" autocomplete="off">
      </div>

      <div class="field">
        <label for="cfg_google_client_id">OAuth Client ID</label>
        <input id="cfg_google_client_id" placeholder="e.g. 123456789-abc.apps.googleusercontent.com" autocomplete="off">
      </div>

      <div class="field">
        <label for="cfg_google_client_secret">Client Secret</label>
        <input id="cfg_google_client_secret" type="password" placeholder="••••••••" value="••••••••" autocomplete="new-password">
      </div>

      <div class="field">
        <label for="cfg_google_api_key">Google API Key / Service Account Key</label>
        <input id="cfg_google_api_key" type="password" placeholder="AIzaSy••••••••••••••••••••••••••••" value="••••••••" autocomplete="off">
      </div>

      <!-- Google Calendar Sync Trigger Box -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px;margin-top:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <b style="font-size:13px">Bi-directional Calendar Sync</b>
          <button type="button" class="btn" id="syncGoogleCalendarBtn" style="font-size:12px;padding:6px 12px">
            <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Sync Now
          </button>
        </div>
        <p style="font-size:12px;color:var(--muted)">Automatically pushes new meetings, production shoots, and project deadlines directly to Google Calendar and notifies attendees.</p>
        <div id="calendarSyncResult" style="margin-top:10px;font-size:12px;display:none"></div>
      </div>
    </div>

    <!-- 3. Branded Email Templates & Live Notification Dispatch -->
    <div class="card" style="padding:24px;grid-column:span 2">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:8px;background:var(--red-soft);color:var(--red);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
          </div>
          <div>
            <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Branded Email Notifications &amp; Reminders</h3>
            <p style="font-size:12px;color:var(--muted)">Preview and test outgoing branded HTML emails for team assignments, calendar reminders, invoices, and closed deals.</p>
          </div>
        </div>
        <span class="badge" style="background:var(--green-soft);color:var(--green)">4 Templates Ready</span>
      </div>

      <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:12px;margin-bottom:16px">
        <div style="border:1px solid var(--line);border-radius:10px;padding:14px;background:var(--paper)">
          <b style="font-size:13px;display:block;margin-bottom:4px">1. Task Assignment</b>
          <p style="font-size:11.5px;color:var(--muted);margin-bottom:10px">Notifies team members when assigned a new production task.</p>
          <button type="button" class="btn" style="width:100%;font-size:12px;justify-content:center" data-send-sample="task">
            Send Sample
          </button>
        </div>

        <div style="border:1px solid var(--line);border-radius:10px;padding:14px;background:var(--paper)">
          <b style="font-size:13px;display:block;margin-bottom:4px">2. Calendar Reminder</b>
          <p style="font-size:11.5px;color:var(--muted);margin-bottom:10px">Shoots, meetings, and calendar agenda alerts with Google Calendar sync.</p>
          <button type="button" class="btn" style="width:100%;font-size:12px;justify-content:center" data-send-sample="meeting">
            Send Sample
          </button>
        </div>

        <div style="border:1px solid var(--line);border-radius:10px;padding:14px;background:var(--paper)">
          <b style="font-size:13px;display:block;margin-bottom:4px">3. Invoice Statement</b>
          <p style="font-size:11.5px;color:var(--muted);margin-bottom:10px">Client billing reminder with NCBA, M-Pesa paybill, and eTIMS badge.</p>
          <button type="button" class="btn" style="width:100%;font-size:12px;justify-content:center" data-send-sample="invoice">
            Send Sample
          </button>
        </div>

        <div style="border:1px solid var(--line);border-radius:10px;padding:14px;background:var(--paper)">
          <b style="font-size:13px;display:block;margin-bottom:4px">4. Deal-Won Alert</b>
          <p style="font-size:11.5px;color:var(--muted);margin-bottom:10px">Broadcasts new closed deal to team and starts production cascade.</p>
          <button type="button" class="btn" style="width:100%;font-size:12px;justify-content:center" data-send-sample="deal">
            Send Sample
          </button>
        </div>
      </div>
      <div id="sampleEmailResult" style="font-size:12.5px;display:none;padding:10px 14px;border-radius:8px;margin-top:4px"></div>
    </div>

    <!-- 4. General Operations & System Branding -->
    <div class="card" style="padding:24px;grid-column:span 2">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px">
        <div style="width:34px;height:34px;border-radius:8px;background:var(--paper);color:var(--ink);border:1px solid var(--line);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <div>
          <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Company &amp; Operations Defaults</h3>
          <p style="font-size:12px;color:var(--muted)">System timezone, business currency, and organization metadata.</p>
        </div>
      </div>

      <div class="grid3" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px">
        <div class="field">
          <label for="cfg_company_name">Company Name</label>
          <input id="cfg_company_name" placeholder="Jeota Media Ltd" value="Jeota Media Ltd" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_currency">Default Currency</label>
          <input id="cfg_currency" placeholder="KES" value="KES" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_timezone">System Timezone</label>
          <select id="cfg_timezone">
            <option value="Africa/Nairobi" selected>Africa/Nairobi (EAT, UTC+3)</option>
            <option value="UTC">UTC (Coordinated Universal Time)</option>
            <option value="Europe/London">Europe/London (GMT/BST)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 4b. Connect Gava (KRA / eTIMS & iTax Integration) -->
    <div class="card" style="padding:24px;grid-column:span 2" id="kraIntegrationCard">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;border-bottom:1px solid var(--line);padding-bottom:12px;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:10px">
          <div style="width:36px;height:36px;border-radius:8px;background:rgba(197,37,35,0.1);color:var(--red);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 10v11M9 10v11M15 10v11M19 10v11M12 2L2 7h20L12 2z"/></svg>
          </div>
          <div>
            <div style="display:flex;align-items:center;gap:8px">
              <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Connect Gava (KRA / eTIMS &amp; iTax Gateway)</h3>
              <span class="badge" style="background:var(--green-soft);color:var(--green)">eTIMS Active</span>
            </div>
            <p style="font-size:12px;color:var(--muted)">Government of Kenya tax synchronization for direct expenses, claimable VAT input, and real-time invoice income tracking.</p>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <button type="button" class="btn" id="syncKraGavaBtn" style="font-size:12px;padding:6px 12px">
            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
            Sync with Gava
          </button>
        </div>
      </div>

      <div class="grid3" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;margin-bottom:14px">
        <div class="field">
          <label for="cfg_kra_pin">KRA PIN *</label>
          <input id="cfg_kra_pin" placeholder="e.g. P051782390X" value="P051782390X" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_kra_taxpayer_name">Taxpayer Name *</label>
          <input id="cfg_kra_taxpayer_name" placeholder="Jeota Media Ltd" value="Jeota Media Ltd" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_kra_etims_branch">eTIMS Branch / Device ID</label>
          <input id="cfg_kra_etims_branch" placeholder="00" value="00" autocomplete="off">
        </div>
      </div>

      <div class="grid3" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px">
        <div class="field">
          <label for="cfg_kra_vat_rate">Standard VAT Rate (%)</label>
          <input id="cfg_kra_vat_rate" type="number" placeholder="16" value="16" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_kra_wht_rate">Withholding Tax (WHT %)</label>
          <input id="cfg_kra_wht_rate" type="number" placeholder="5" value="5" autocomplete="off">
        </div>
        <div class="field">
          <label for="cfg_kra_status">Gava Connection Status</label>
          <select id="cfg_kra_status">
            <option value="connected" selected>Connected &amp; Live (iTax / eTIMS)</option>
            <option value="audit_mode">Audit &amp; Compliance Mode</option>
            <option value="offline">Offline Staging</option>
          </select>
        </div>
      </div>

      <div id="kraSyncNotice" style="margin-top:12px;padding:10px 14px;border-radius:8px;font-size:12px;background:var(--panel-2);border:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
          <span style="color:var(--green);font-weight:700">✓</span>
          <span style="color:var(--ink)"><b>eTIMS &amp; iTax Synced:</b> All direct project expenses with ETR are tax-deductible; issued invoices track 16% output VAT.</span>
        </div>
        <span class="mono" style="font-size:11px;color:var(--muted)">Verified KRA PIN</span>
      </div>
    </div>

    <!-- 5. System Software Upgrade & Maintenance (IT Manager Exclusive) -->
    <div class="card" style="padding:24px;grid-column:span 2" id="systemUpgradeCard">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:14px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:38px;height:38px;border-radius:10px;background:var(--blue-soft, rgba(37,99,235,0.1));color:var(--blue, #2563eb);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
          </div>
          <div>
            <div style="display:flex;align-items:center;gap:8px">
              <h3 style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:600">Software Upgrade &amp; System Maintenance</h3>
              <span class="badge" style="background:var(--blue-soft, #e0f2fe);color:var(--blue, #0284c7);font-size:11px;font-weight:600">IT Manager</span>
            </div>
            <p style="font-size:12px;color:var(--muted)">Deploy updated codebase archives, execute safe database migrations, and generate zero-data-loss system backups.</p>
          </div>
        </div>

        <!-- Quick Environment Badges -->
        <div style="display:flex;align-items:center;gap:8px;font-size:12px" id="sysEnvBadges">
          <span class="badge" id="sysLaravelVer" style="background:var(--paper);border:1px solid var(--line)">Laravel ...</span>
          <span class="badge" id="sysPhpVer" style="background:var(--paper);border:1px solid var(--line)">PHP ...</span>
          <span class="badge" id="sysDbStatus" style="background:var(--green-soft);color:var(--green)">Database Online</span>
          <span class="badge" id="sysPendingMigrationsBadge" style="background:var(--paper);border:1px solid var(--line)">Checking migrations…</span>
        </div>
      </div>

      <!-- Zero-Data-Loss Safety Guarantees Notice -->
      <div style="background:rgba(16, 185, 129, 0.08);border:1px solid rgba(16, 185, 129, 0.25);border-radius:10px;padding:14px;margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:8px;color:var(--green);font-weight:600;font-size:13px;margin-bottom:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Zero Data Loss Protection Active
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:10px;font-size:12px;color:var(--ink)">
          <div style="display:flex;gap:6px;align-items:flex-start">
            <span style="color:var(--green);font-weight:700">✓</span>
            <span><b>Automated DB Backup:</b> Snapshot created prior to applying changes.</span>
          </div>
          <div style="display:flex;gap:6px;align-items:flex-start">
            <span style="color:var(--green);font-weight:700">✓</span>
            <span><b>Protected Secrets:</b> Existing <code>.env</code> file is never overwritten.</span>
          </div>
          <div style="display:flex;gap:6px;align-items:flex-start">
            <span style="color:var(--green);font-weight:700">✓</span>
            <span><b>Storage Shielded:</b> All client media &amp; uploads in <code>storage/</code> remain untouched.</span>
          </div>
          <div style="display:flex;gap:6px;align-items:flex-start">
            <span style="color:var(--green);font-weight:700">✓</span>
            <span><b>Safe Migrations:</b> Runs strictly <code>migrate --force</code> without data drops.</span>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns: 1.2fr 1fr;gap:20px;margin-bottom:18px">
        <!-- Upgrade Form Dropzone Area -->
        <div style="border:1px solid var(--line);border-radius:12px;padding:16px;background:var(--paper)">
          <b style="font-size:13px;display:block;margin-bottom:6px">Deploy Code Update (.zip)</b>
          <p style="font-size:12px;color:var(--muted);margin-bottom:12px">Upload the update zip package provided by your engineering team containing new features or bug fixes.</p>

          <div id="upgradeDropzone" style="border:2px dashed var(--line-strong);border-radius:10px;padding:24px 16px;text-align:center;background:var(--surface);cursor:pointer;transition:border-color .2s, background .2s">
            <input type="file" id="upgradeZipInput" accept=".zip" style="display:none">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--paper);border:1px solid var(--line);margin:0 auto 10px;display:grid;place-items:center;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
            </div>
            <div style="font-size:13px;font-weight:600;margin-bottom:2px" id="dropzoneMainText">Click or drag &amp; drop updated code (.zip)</div>
            <div style="font-size:11.5px;color:var(--muted)" id="dropzoneSubText">Maximum archive size: 150MB</div>
            <div id="selectedFileInfo" style="display:none;margin-top:10px;padding:6px 12px;background:var(--paper);border-radius:6px;font-size:12px;display:inline-flex;align-items:center;gap:8px">
              <span id="selectedFileName" style="font-weight:600">file.zip</span>
              <span id="selectedFileSize" style="color:var(--muted)">0 MB</span>
              <button type="button" id="clearSelectedFileBtn" style="background:none;border:none;color:var(--red);cursor:pointer;padding:0 4px;font-weight:bold">&times;</button>
            </div>
          </div>

          <!-- Options Checklist -->
          <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px;font-size:12px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" id="chkRunMigrations" checked style="accent-color:var(--red)">
              <span>Run pending database migrations automatically (<code>migrate --force</code>)</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" id="chkCreateBackup" checked style="accent-color:var(--red)">
              <span>Create automated safety database backup before extracting</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" id="chkClearCaches" checked style="accent-color:var(--red)">
              <span>Clear and refresh application caches (<code>optimize:clear</code>)</span>
            </label>
          </div>

          <!-- Upload Progress Bar -->
          <div id="upgradeProgressContainer" style="display:none;margin-top:14px">
            <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:4px">
              <span id="upgradeProgressLabel">Uploading upgrade package…</span>
              <span id="upgradeProgressPercent">0%</span>
            </div>
            <div style="height:6px;background:var(--line);border-radius:3px;overflow:hidden">
              <div id="upgradeProgressBar" style="width:0%;height:100%;background:var(--red);transition:width .2s"></div>
            </div>
          </div>

          <div style="margin-top:16px;display:flex;gap:10px">
            <button type="button" class="btn primary" id="applyUpgradeBtn" style="flex:1;justify-content:center" disabled>
              <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
              Upload &amp; Install Upgrade
            </button>
          </div>
        </div>

        <!-- Quick Maintenance & Backup Actions -->
        <div style="border:1px solid var(--line);border-radius:12px;padding:16px;background:var(--paper);display:flex;flex-direction:column;justify-content:space-between">
          <div>
            <b style="font-size:13px;display:block;margin-bottom:6px">Quick System Actions</b>
            <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Execute operational maintenance tasks without re-uploading code archives.</p>

            <div style="display:flex;flex-direction:column;gap:10px">
              <!-- Run Migrations Standalone -->
              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface);border:1px solid var(--line);border-radius:8px">
                <div>
                  <b style="font-size:12.5px;display:block">Database Migrations</b>
                  <span style="font-size:11.5px;color:var(--muted)" id="migrationActionDesc">Check &amp; apply pending schema migrations</span>
                </div>
                <button type="button" class="btn" id="runMigrationsBtn" style="font-size:12px;padding:6px 12px;white-space:nowrap">
                  <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                  Run Migrations
                </button>
              </div>

              <!-- Database Backup On-Demand -->
              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface);border:1px solid var(--line);border-radius:8px">
                <div>
                  <b style="font-size:12.5px;display:block">Instant Database Backup</b>
                  <span style="font-size:11.5px;color:var(--muted)">Generate a live SQL dump of all 23+ tables</span>
                </div>
                <button type="button" class="btn" id="createDbBackupBtn" style="font-size:12px;padding:6px 12px;white-space:nowrap">
                  <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                  Backup Now
                </button>
              </div>

              <!-- Flush Caches -->
              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface);border:1px solid var(--line);border-radius:8px">
                <div>
                  <b style="font-size:12.5px;display:block">Clear Application Caches</b>
                  <span style="font-size:11.5px;color:var(--muted)">Purge view, route, config, and framework caches</span>
                </div>
                <button type="button" class="btn" id="clearSystemCacheBtn" style="font-size:12px;padding:6px 12px;white-space:nowrap">
                  <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                  Clear Caches
                </button>
              </div>
            </div>
          </div>

          <!-- Existing Backups Summary -->
          <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--line)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
              <b style="font-size:12px">Recent Database Backups</b>
              <span style="font-size:11px;color:var(--muted)" id="backupCountLabel">0 stored</span>
            </div>
            <div id="backupListContainer" style="max-height:110px;overflow-y:auto;display:flex;flex-direction:column;gap:6px">
              <span style="font-size:11.5px;color:var(--muted)">No backups generated yet.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Execution Log Terminal -->
      <div style="margin-top:10px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <b style="font-size:12.5px;display:flex;align-items:center;gap:6px">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block"></span>
            Execution Log &amp; Terminal Output
          </b>
          <button type="button" id="clearUpgradeLogBtn" style="background:none;border:none;font-size:11.5px;color:var(--muted);cursor:pointer">Clear console</button>
        </div>
        <div id="upgradeConsole" style="background:#0f172a;color:#f8fafc;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:12px;border-radius:10px;padding:14px;min-height:120px;max-height:240px;overflow-y:auto;line-height:1.5;box-shadow:inset 0 2px 4px rgba(0,0,0,0.3)">
          <div style="color:#94a3b8">[System Ready] JMOS System Software Upgrade &amp; Maintenance console initialized. Waiting for action...</div>
        </div>
      </div>
    </div>
  </div>
  </div><!-- end data-perm="owner manager" wrapper -->
</section>
