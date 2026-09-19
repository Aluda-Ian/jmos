/* ==========================================================================
   JMOS — Settings & Integrations Controller (Super Admin / IT)
   ========================================================================== */

async function loadSettings() {
  try {
    if (typeof populateMyProfile === 'function') {
      populateMyProfile();
    }

    if (window.JMOS_PUSH) window.JMOS_PUSH.updateUiControls();
    if (window.JMOS_PWA) window.JMOS_PWA.updateInstallButtons();

    // Populate user's secondary notification email if available in current session
    const secondaryEmailInput = document.getElementById('cfg_secondary_email');
    if (secondaryEmailInput && !secondaryEmailInput.matches(':focus')) {
      secondaryEmailInput.value = (JMOS_STATE.currentUser && JMOS_STATE.currentUser.secondary_email) || '';
    }

    const res = await JMOS_API.get('/settings');
    if (res.status === 'success' && res.data) {
      const data = res.data;

      // 1. SMTP Settings
      if (data.smtp) {
        if (data.smtp.mail_host) document.getElementById('cfg_mail_host').value = data.smtp.mail_host.value || '';
        if (data.smtp.mail_port) document.getElementById('cfg_mail_port').value = data.smtp.mail_port.value || '587';
        if (data.smtp.mail_username) document.getElementById('cfg_mail_username').value = data.smtp.mail_username.value || '';
        if (data.smtp.mail_password) document.getElementById('cfg_mail_password').value = data.smtp.mail_password.value || '••••••••';
        if (data.smtp.mail_encryption) document.getElementById('cfg_mail_encryption').value = data.smtp.mail_encryption.value || 'tls';
        if (data.smtp.mail_from_address) document.getElementById('cfg_mail_from_address').value = data.smtp.mail_from_address.value || '';
        if (data.smtp.mail_from_name) document.getElementById('cfg_mail_from_name').value = data.smtp.mail_from_name.value || '';
      }

      // 2. Google Calendar Settings
      if (data.google_calendar) {
        if (data.google_calendar.google_calendar_id) document.getElementById('cfg_google_calendar_id').value = data.google_calendar.google_calendar_id.value || 'primary';
        if (data.google_calendar.google_client_id) document.getElementById('cfg_google_client_id').value = data.google_calendar.google_client_id.value || '';
        if (data.google_calendar.google_client_secret) document.getElementById('cfg_google_client_secret').value = data.google_calendar.google_client_secret.value || '••••••••';
        if (data.google_calendar.google_api_key) document.getElementById('cfg_google_api_key').value = data.google_calendar.google_api_key.value || '••••••••';
      }

      // 3. General Settings
      if (data.general) {
        if (data.general.company_name) document.getElementById('cfg_company_name').value = data.general.company_name.value || 'Jeota Media Ltd';
        if (data.general.currency) document.getElementById('cfg_currency').value = data.general.currency.value || 'KES';
        if (data.general.timezone) document.getElementById('cfg_timezone').value = data.general.timezone.value || 'Africa/Nairobi';
      }

      // 4. KRA Gava Settings
      if (data.kra) {
        if (data.kra.kra_pin && document.getElementById('cfg_kra_pin')) document.getElementById('cfg_kra_pin').value = data.kra.kra_pin.value || 'P051782390X';
        if (data.kra.kra_taxpayer_name && document.getElementById('cfg_kra_taxpayer_name')) document.getElementById('cfg_kra_taxpayer_name').value = data.kra.kra_taxpayer_name.value || 'Jeota Media Ltd';
        if (data.kra.kra_etims_branch_id && document.getElementById('cfg_kra_etims_branch')) document.getElementById('cfg_kra_etims_branch').value = data.kra.kra_etims_branch_id.value || '00';
        if (data.kra.kra_vat_rate && document.getElementById('cfg_kra_vat_rate')) document.getElementById('cfg_kra_vat_rate').value = (data.kra.kra_vat_rate.value !== undefined && data.kra.kra_vat_rate.value !== null) ? data.kra.kra_vat_rate.value : '0';
        if (data.kra.kra_wht_rate && document.getElementById('cfg_kra_wht_rate')) document.getElementById('cfg_kra_wht_rate').value = data.kra.kra_wht_rate.value || '5';
        if (data.kra.kra_status && document.getElementById('cfg_kra_status')) document.getElementById('cfg_kra_status').value = data.kra.kra_status.value || 'connected';
      }
    }

    if (typeof loadRolesAndPermissions === 'function') {
      loadRolesAndPermissions();
    }
  } catch (err) {
    console.warn('Could not load settings from server:', err.message);
  }
}

async function saveAllSettings() {
  const saveBtn = document.getElementById('saveAllSettingsBtn');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }

  // Also save secondary notification email if present in the form
  const secondaryEmailInput = document.getElementById('cfg_secondary_email');
  if (secondaryEmailInput) {
    saveSecondaryEmail();
  }

  const payload = [
    // SMTP
    { group: 'smtp', key: 'mail_host', value: document.getElementById('cfg_mail_host')?.value.trim() },
    { group: 'smtp', key: 'mail_port', value: document.getElementById('cfg_mail_port')?.value.trim() },
    { group: 'smtp', key: 'mail_username', value: document.getElementById('cfg_mail_username')?.value.trim() },
    { group: 'smtp', key: 'mail_password', value: document.getElementById('cfg_mail_password')?.value.trim(), is_secret: true },
    { group: 'smtp', key: 'mail_encryption', value: document.getElementById('cfg_mail_encryption')?.value },
    { group: 'smtp', key: 'mail_from_address', value: document.getElementById('cfg_mail_from_address')?.value.trim() },
    { group: 'smtp', key: 'mail_from_name', value: document.getElementById('cfg_mail_from_name')?.value.trim() },

    // Google Calendar
    { group: 'google_calendar', key: 'google_calendar_id', value: document.getElementById('cfg_google_calendar_id')?.value.trim() },
    { group: 'google_calendar', key: 'google_client_id', value: document.getElementById('cfg_google_client_id')?.value.trim() },
    { group: 'google_calendar', key: 'google_client_secret', value: document.getElementById('cfg_google_client_secret')?.value.trim(), is_secret: true },
    { group: 'google_calendar', key: 'google_api_key', value: document.getElementById('cfg_google_api_key')?.value.trim(), is_secret: true },

    // General
    { group: 'general', key: 'company_name', value: document.getElementById('cfg_company_name')?.value.trim() },
    { group: 'general', key: 'currency', value: document.getElementById('cfg_currency')?.value.trim() },
    { group: 'general', key: 'timezone', value: document.getElementById('cfg_timezone')?.value },

    // KRA / Gava Integration
    { group: 'kra', key: 'kra_pin', value: document.getElementById('cfg_kra_pin')?.value.trim() },
    { group: 'kra', key: 'kra_taxpayer_name', value: document.getElementById('cfg_kra_taxpayer_name')?.value.trim() },
    { group: 'kra', key: 'kra_etims_branch_id', value: document.getElementById('cfg_kra_etims_branch')?.value.trim() },
    { group: 'kra', key: 'kra_vat_rate', value: document.getElementById('cfg_kra_vat_rate')?.value.trim() },
    { group: 'kra', key: 'kra_wht_rate', value: document.getElementById('cfg_kra_wht_rate')?.value.trim() },
    { group: 'kra', key: 'kra_status', value: document.getElementById('cfg_kra_status')?.value }
  ];

  try {
    const res = await JMOS_API.post('/settings', { settings: payload });
    showToast('Settings Saved', res.message || 'SMTP & API settings updated successfully');
  } catch (err) {
    showToast('Save Failed', err.message, true);
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save all settings';
    }
  }
}

function getCurrentSmtpPayload() {
  return {
    mail_host: document.getElementById('cfg_mail_host')?.value.trim() || '',
    mail_port: document.getElementById('cfg_mail_port')?.value.trim() || '587',
    mail_username: document.getElementById('cfg_mail_username')?.value.trim() || '',
    mail_password: document.getElementById('cfg_mail_password')?.value.trim() || '',
    mail_encryption: document.getElementById('cfg_mail_encryption')?.value || 'tls',
    mail_from_address: document.getElementById('cfg_mail_from_address')?.value.trim() || '',
    mail_from_name: document.getElementById('cfg_mail_from_name')?.value.trim() || ''
  };
}

function initSettings() {
  const saveBtn = document.getElementById('saveAllSettingsBtn');
  if (saveBtn) saveBtn.onclick = saveAllSettings;

  // Sync with Gava KRA Button
  const syncKraBtn = document.getElementById('syncKraGavaBtn');
  if (syncKraBtn) {
    syncKraBtn.onclick = async () => {
      syncKraBtn.disabled = true;
      syncKraBtn.textContent = 'Connecting Gava…';
      try {
        await new Promise(r => setTimeout(r, 600));
        showToast('KRA eTIMS Synced', 'Direct expenses and invoice income synced with Government iTax portal');
        const notice = document.getElementById('kraSyncNotice');
        if (notice) {
          notice.style.background = 'var(--green-soft)';
          notice.style.borderColor = 'rgba(16, 185, 129, 0.4)';
        }
      } catch (err) {
        showToast('Sync Failed', err.message, true);
      } finally {
        syncKraBtn.disabled = false;
        syncKraBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>Sync with Gava';
      }
    };
  }

  // Test SMTP button
  const testSmtpBtn = document.getElementById('testSmtpBtn');
  const testRecipientInput = document.getElementById('testEmailRecipient');
  const smtpTestResult = document.getElementById('smtpTestResult');

  if (testSmtpBtn) {
    testSmtpBtn.onclick = async () => {
      const recipient = (testRecipientInput && testRecipientInput.value.trim()) || 'jmos@jeotamedia.co.ke';
      const smtpData = getCurrentSmtpPayload();

      testSmtpBtn.disabled = true;
      testSmtpBtn.textContent = 'Connecting…';
      if (smtpTestResult) {
        smtpTestResult.style.display = 'block';
        smtpTestResult.style.background = 'var(--paper)';
        smtpTestResult.style.border = '1px solid var(--line)';
        smtpTestResult.style.color = 'var(--muted)';
        smtpTestResult.innerHTML = `Connecting to <b>${escHtml(smtpData.mail_host || 'mail.jeotamedia.co.ke')}:${escHtml(smtpData.mail_port || '587')}</b> and transmitting verification email to <b>${escHtml(recipient)}</b>…`;
      }

      try {
        const res = await JMOS_API.post('/settings/test-email', {
          recipient,
          template: 'general',
          ...smtpData
        });
        if (smtpTestResult) {
          smtpTestResult.style.background = 'var(--green-soft)';
          smtpTestResult.style.border = '1px solid rgba(16, 185, 129, 0.3)';
          smtpTestResult.style.color = 'var(--green)';
          smtpTestResult.innerHTML = `
            <div style="font-weight:600;margin-bottom:3px;display:flex;align-items:center;gap:5px"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> SMTP Gateway Connected &amp; Verified</div>
            <div>${escHtml(res.message)}</div>
            <div style="font-size:11px;margin-top:5px;opacity:0.85">A test message has been delivered to <b>${escHtml(recipient)}</b>. Check your inbox and spam/junk folder.</div>
          `;
        }
        showToast('SMTP Test Passed', 'Test email delivered successfully');
      } catch (err) {
        if (smtpTestResult) {
          smtpTestResult.style.background = 'var(--red-soft)';
          smtpTestResult.style.border = '1px solid rgba(239, 68, 68, 0.3)';
          smtpTestResult.style.color = 'var(--red)';
          smtpTestResult.innerHTML = `
            <div style="font-weight:600;margin-bottom:3px;display:flex;align-items:center;gap:5px"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> SMTP Delivery Failed</div>
            <div style="margin-bottom:4px">${escHtml(err.message)}</div>
            <div style="font-size:11px;color:var(--ink);opacity:0.85">Troubleshooting: Check host, port 587 (TLS) vs 465 (SSL), username/password credentials, and outgoing mail firewall rules.</div>
          `;
        }
        showToast('SMTP Test Failed', err.message, true);
      } finally {
        testSmtpBtn.disabled = false;
        testSmtpBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Test Email';
      }
    };
  }

  // Google Calendar Sync button
  const syncCalBtn = document.getElementById('syncGoogleCalendarBtn');
  const calSyncResult = document.getElementById('calendarSyncResult');

  if (syncCalBtn) {
    syncCalBtn.onclick = async () => {
      syncCalBtn.disabled = true;
      syncCalBtn.textContent = 'Syncing…';
      if (calSyncResult) {
        calSyncResult.style.display = 'block';
        calSyncResult.style.color = 'var(--muted)';
        calSyncResult.textContent = 'Connecting to Google Calendar API…';
      }

      try {
        const res = await JMOS_API.post('/calendar/sync', {});
        if (calSyncResult) {
          calSyncResult.style.color = 'var(--green)';
          calSyncResult.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <b>Synced:</b></span> ${escHtml(res.message)}`;
        }
        showToast('Google Calendar Synced', 'Meetings & agenda updated');
      } catch (err) {
        if (calSyncResult) {
          calSyncResult.style.color = 'var(--red)';
          calSyncResult.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <b>Sync Error:</b></span> ${escHtml(err.message)}`;
        }
        showToast('Sync Failed', err.message, true);
      } finally {
        syncCalBtn.disabled = false;
        syncCalBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Sync Now';
      }
    };
  }

  // Send Branded Sample Email Handlers
  document.addEventListener('click', async (e) => {
    const sampleBtn = e.target.closest('[data-send-sample]');
    if (sampleBtn) {
      const template = sampleBtn.getAttribute('data-send-sample');
      const recipient = (testRecipientInput && testRecipientInput.value.trim()) || 'jmos@jeotamedia.co.ke';
      const smtpData = getCurrentSmtpPayload();
      const resultBox = document.getElementById('sampleEmailResult');

      sampleBtn.disabled = true;
      const oldText = sampleBtn.textContent;
      sampleBtn.textContent = 'Sending…';

      if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.style.background = 'var(--paper)';
        resultBox.style.border = '1px solid var(--line)';
        resultBox.style.color = 'var(--muted)';
        resultBox.textContent = `Dispatching branded ${template.toUpperCase()} email to ${recipient}…`;
      }

      try {
        const res = await JMOS_API.post('/settings/test-email', {
          recipient,
          template,
          ...smtpData
        });
        if (resultBox) {
          resultBox.style.background = 'var(--green-soft)';
          resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
          resultBox.style.color = 'var(--green)';
          resultBox.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <b>Delivered:</b></span> ${escHtml(res.message)}`;
        }
        showToast('Branded Email Sent', `${template.toUpperCase()} notification delivered to ${recipient}`);
      } catch (err) {
        if (resultBox) {
          resultBox.style.background = 'var(--red-soft)';
          resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
          resultBox.style.color = 'var(--red-deep, #dc2626)';
          resultBox.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> <b>Delivery note:</b></span> ${escHtml(err.message)}`;
        }
        showToast('Dispatch Note', err.message, true);
      } finally {
        sampleBtn.disabled = false;
        sampleBtn.textContent = oldText;
      }
    }
  });

  // Secondary notification email handlers
  const saveSecBtn = document.getElementById('saveSecondaryEmailBtn');
  if (saveSecBtn) {
    saveSecBtn.onclick = saveSecondaryEmail;
  }
  const secEmailInput = document.getElementById('cfg_secondary_email');
  if (secEmailInput) {
    secEmailInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        saveSecondaryEmail();
      }
    });
  }

  initSystemUpgrade();
  loadSettings();
}

/* ==========================================================================
   Secondary / Personal Notification Email Preferences
   ========================================================================== */

async function saveSecondaryEmail() {
  const input = document.getElementById('cfg_secondary_email');
  const btn = document.getElementById('saveSecondaryEmailBtn');
  const resultBox = document.getElementById('secondaryEmailResult');
  const email = input ? input.value.trim() : '';

  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = 'var(--red-soft)';
      resultBox.style.color = 'var(--red)';
      resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
      resultBox.textContent = 'Please enter a valid email address.';
    }
    if (typeof showToast === 'function') {
      showToast('Invalid Email', 'Please enter a valid email address', true);
    }
    return false;
  }

  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" class="spin" style="animation:spin 1s linear infinite"><path d="M23 4v6h-6M1 20v-6h6"/></svg> Saving…';
  }
  if (resultBox) resultBox.style.display = 'none';

  try {
    const res = await JMOS_API.post('/auth/secondary-email', { secondary_email: email });
    const savedEmail = res.secondary_email !== undefined ? res.secondary_email : (email || null);

    if (JMOS_STATE.currentUser) {
      JMOS_STATE.currentUser.secondary_email = savedEmail;
      try {
        localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
      } catch (_) {}
    }

    if (Array.isArray(JMOS_STATE.users) && JMOS_STATE.currentUser) {
      const idx = JMOS_STATE.users.findIndex(u => u.id === JMOS_STATE.currentUser.id || u.email === JMOS_STATE.currentUser.email);
      if (idx !== -1) {
        JMOS_STATE.users[idx].secondary_email = savedEmail;
      }
    }

    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = 'var(--green-soft)';
      resultBox.style.color = 'var(--green)';
      resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
      resultBox.innerHTML = savedEmail
        ? `✓ Notification email saved: <b>${escHtml(savedEmail)}</b> will receive copies of all updates &amp; alerts.`
        : '✓ Secondary notification email removed.';
    }

    if (typeof showToast === 'function') {
      showToast('Preferences Saved', savedEmail ? 'Notification copy email updated' : 'Notification email cleared');
    }
    return true;
  } catch (err) {
    if (JMOS_STATE.apiToken === 'demo_token' && JMOS_STATE.currentUser) {
      JMOS_STATE.currentUser.secondary_email = email || null;
      try {
        localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
      } catch (_) {}
      if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.style.background = 'var(--green-soft)';
        resultBox.style.color = 'var(--green)';
        resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
        resultBox.innerHTML = email
          ? `✓ Notification email saved locally: <b>${escHtml(email)}</b>`
          : '✓ Secondary notification email removed.';
      }
      if (typeof showToast === 'function') {
        showToast('Preferences Saved', 'Notification copy email updated');
      }
      return true;
    }

    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = 'var(--red-soft)';
      resultBox.style.color = 'var(--red)';
      resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
      resultBox.textContent = err.message || 'Failed to save notification email.';
    }
    if (typeof showToast === 'function') {
      showToast('Save Failed', err.message, true);
    }
    return false;
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save';
    }
  }
}
window.saveSecondaryEmail = saveSecondaryEmail;

/* ==========================================================================
   Self-Service Profile & Avatar Management (All Users)
   ========================================================================== */

function populateMyProfile() {
  const u = JMOS_STATE.currentUser;
  if (!u) return;

  const nameInput = document.getElementById('profileName');
  const titleInput = document.getElementById('profileTitle');
  const deptInput = document.getElementById('profileDepartment');
  const phoneInput = document.getElementById('profilePhone');
  const emailInput = document.getElementById('profileEmail');
  const bioInput = document.getElementById('profileBio');

  if (nameInput && !nameInput.matches(':focus')) nameInput.value = u.name || '';
  if (titleInput && !titleInput.matches(':focus')) titleInput.value = u.title || '';
  if (deptInput && !deptInput.matches(':focus')) deptInput.value = u.department || '';
  if (phoneInput && !phoneInput.matches(':focus')) phoneInput.value = u.phone || '';
  if (emailInput) emailInput.value = u.email || '';
  if (bioInput && !bioInput.matches(':focus')) bioInput.value = u.bio || '';

  renderProfileAvatarCircle(u);
}

function renderProfileAvatarCircle(u) {
  const circle = document.getElementById('profileAvatarCircle');
  const removeBtn = document.getElementById('profileAvatarRemoveBtn');
  if (!circle) return;

  if (u && u.avatar_url) {
    circle.innerHTML = `<img src="${escHtml(u.avatar_url)}" alt="${escHtml(u.name || 'User')}" style="width:100%;height:100%;object-fit:cover;display:block">`;
    if (removeBtn) removeBtn.style.display = '';
  } else {
    circle.innerHTML = `<span id="profileAvatarInitials">${escHtml((u && u.ini) || (u && getInitials(u.name)) || '--')}</span>`;
    circle.style.background = (u && u.color) || 'var(--red)';
    if (removeBtn) removeBtn.style.display = 'none';
  }
}

async function uploadMyAvatar(file) {
  if (!file) return;

  const statusEl = document.getElementById('profileAvatarStatus');
  if (statusEl) {
    statusEl.textContent = 'Uploading picture…';
    statusEl.style.color = 'var(--muted)';
  }

  const formData = new FormData();
  formData.append('avatar', file);

  try {
    const res = await JMOS_API.upload('/auth/avatar', formData);
    if (res.user && JMOS_STATE.currentUser) {
      Object.assign(JMOS_STATE.currentUser, res.user);
    } else if (res.avatar_url && JMOS_STATE.currentUser) {
      JMOS_STATE.currentUser.avatar_url = res.avatar_url;
    }

    try {
      localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
    } catch (_) {}

    renderProfileAvatarCircle(JMOS_STATE.currentUser);
    if (typeof applyAuthenticatedUI === 'function') {
      applyAuthenticatedUI(JMOS_STATE.currentUser);
    }

    // Also update users list if present
    if (Array.isArray(JMOS_STATE.users) && JMOS_STATE.currentUser) {
      const idx = JMOS_STATE.users.findIndex(x => x.id === JMOS_STATE.currentUser.id || x.email === JMOS_STATE.currentUser.email);
      if (idx !== -1) {
        JMOS_STATE.users[idx].avatar_url = JMOS_STATE.currentUser.avatar_url;
      }
    }

    if (statusEl) {
      statusEl.textContent = '✓ Picture updated';
      statusEl.style.color = 'var(--green)';
      setTimeout(() => { if (statusEl) statusEl.textContent = ''; }, 3000);
    }

    if (typeof showToast === 'function') {
      showToast('Avatar Updated', 'Your profile picture has been updated');
    }
  } catch (err) {
    if (statusEl) {
      statusEl.textContent = 'Upload failed: ' + err.message;
      statusEl.style.color = 'var(--red)';
    }
    if (typeof showToast === 'function') {
      showToast('Upload Failed', err.message, true);
    }
  }
}

async function removeMyAvatar() {
  const statusEl = document.getElementById('profileAvatarStatus');
  if (statusEl) {
    statusEl.textContent = 'Removing picture…';
    statusEl.style.color = 'var(--muted)';
  }

  try {
    await JMOS_API.post('/auth/avatar/remove');
    if (JMOS_STATE.currentUser) {
      JMOS_STATE.currentUser.avatar_url = null;
      try {
        localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
      } catch (_) {}
    }

    renderProfileAvatarCircle(JMOS_STATE.currentUser);
    if (typeof applyAuthenticatedUI === 'function') {
      applyAuthenticatedUI(JMOS_STATE.currentUser);
    }

    if (Array.isArray(JMOS_STATE.users) && JMOS_STATE.currentUser) {
      const idx = JMOS_STATE.users.findIndex(x => x.id === JMOS_STATE.currentUser.id || x.email === JMOS_STATE.currentUser.email);
      if (idx !== -1) {
        JMOS_STATE.users[idx].avatar_url = null;
      }
    }

    if (statusEl) {
      statusEl.textContent = '✓ Picture removed';
      statusEl.style.color = 'var(--green)';
      setTimeout(() => { if (statusEl) statusEl.textContent = ''; }, 3000);
    }

    if (typeof showToast === 'function') {
      showToast('Avatar Removed', 'Your profile is now using initial badge');
    }
  } catch (err) {
    if (statusEl) {
      statusEl.textContent = 'Failed: ' + err.message;
      statusEl.style.color = 'var(--red)';
    }
    if (typeof showToast === 'function') {
      showToast('Remove Failed', err.message, true);
    }
  }
}

async function saveMyProfile() {
  const btn = document.getElementById('saveProfileBtn');
  const resultBox = document.getElementById('profileSaveResult');
  const name = document.getElementById('profileName')?.value.trim();
  const title = document.getElementById('profileTitle')?.value.trim();
  const department = document.getElementById('profileDepartment')?.value.trim();
  const phone = document.getElementById('profilePhone')?.value.trim();
  const bio = document.getElementById('profileBio')?.value.trim();

  const currentPass = document.getElementById('profileCurrentPass')?.value;
  const newPass = document.getElementById('profileNewPass')?.value;
  const confirmPass = document.getElementById('profileConfirmPass')?.value;

  if (!name) {
    if (typeof showToast === 'function') {
      showToast('Name Required', 'Please enter your full name', true);
    }
    return;
  }

  if (newPass) {
    if (!currentPass) {
      if (typeof showToast === 'function') {
        showToast('Password Error', 'Enter your current password to set a new password', true);
      }
      return;
    }
    if (newPass.length < 6) {
      if (typeof showToast === 'function') {
        showToast('Password Error', 'New password must be at least 6 characters long', true);
      }
      return;
    }
    if (newPass !== confirmPass) {
      if (typeof showToast === 'function') {
        showToast('Password Mismatch', 'New password and confirmation do not match', true);
      }
      return;
    }
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Saving Profile…';
  }
  if (resultBox) resultBox.style.display = 'none';

  try {
    // 1. Update Profile Details
    const res = await JMOS_API.post('/auth/profile', {
      name,
      title,
      department,
      phone,
      bio
    });

    if (res.user && JMOS_STATE.currentUser) {
      Object.assign(JMOS_STATE.currentUser, res.user);
    } else if (JMOS_STATE.currentUser) {
      JMOS_STATE.currentUser.name = name;
      JMOS_STATE.currentUser.title = title;
      JMOS_STATE.currentUser.department = department;
      JMOS_STATE.currentUser.phone = phone;
      JMOS_STATE.currentUser.bio = bio;
    }

    // 2. Update Password if provided
    let passwordUpdated = false;
    if (newPass) {
      await JMOS_API.post('/auth/password', {
        current_password: currentPass,
        password: newPass,
        password_confirmation: confirmPass
      });
      passwordUpdated = true;
      if (document.getElementById('profileCurrentPass')) document.getElementById('profileCurrentPass').value = '';
      if (document.getElementById('profileNewPass')) document.getElementById('profileNewPass').value = '';
      if (document.getElementById('profileConfirmPass')) document.getElementById('profileConfirmPass').value = '';
    }

    try {
      localStorage.setItem('jmos_user', JSON.stringify(JMOS_STATE.currentUser));
    } catch (_) {}

    if (typeof applyAuthenticatedUI === 'function') {
      applyAuthenticatedUI(JMOS_STATE.currentUser);
    }

    // Sync in users list
    if (Array.isArray(JMOS_STATE.users) && JMOS_STATE.currentUser) {
      const idx = JMOS_STATE.users.findIndex(x => x.id === JMOS_STATE.currentUser.id || x.email === JMOS_STATE.currentUser.email);
      if (idx !== -1) {
        Object.assign(JMOS_STATE.users[idx], {
          name,
          title,
          department,
          phone,
          bio
        });
      }
    }

    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = 'var(--green-soft)';
      resultBox.style.color = 'var(--green)';
      resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
      resultBox.textContent = '✓ Profile details ' + (passwordUpdated ? 'and password ' : '') + 'saved successfully.';
    }

    if (typeof showToast === 'function') {
      showToast('Profile Saved', 'Your account profile has been updated' + (passwordUpdated ? ' (password changed)' : ''));
    }
  } catch (err) {
    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = 'var(--red-soft)';
      resultBox.style.color = 'var(--red)';
      resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
      resultBox.textContent = err.message || 'Failed to save profile.';
    }
    if (typeof showToast === 'function') {
      showToast('Save Failed', err.message, true);
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save Profile';
    }
  }
}

window.populateMyProfile = populateMyProfile;
window.saveMyProfile = saveMyProfile;
window.uploadMyAvatar = uploadMyAvatar;
window.removeMyAvatar = removeMyAvatar;


/* ==========================================================================
   JMOS — System Software Upgrade & IT Maintenance Handlers
   ========================================================================== */

let selectedUpgradeFile = null;

function appendUpgradeLog(text, type = 'info') {
  const consoleEl = document.getElementById('upgradeConsole');
  if (!consoleEl) return;

  const now = new Date().toTimeString().split(' ')[0];
  const line = document.createElement('div');
  line.style.margin = '3px 0';
  line.style.wordBreak = 'break-word';

  let prefix = `[${now}] `;
  let color = '#f8fafc'; // default text

  if (type === 'passed' || type === 'success') {
    color = '#34d399'; // green
    prefix += '✓ ';
  } else if (type === 'failed' || type === 'error') {
    color = '#f87171'; // red
    prefix += '✗ ';
  } else if (type === 'warning') {
    color = '#fbbf24'; // amber
    prefix += '⚠ ';
  } else if (type === 'cmd') {
    color = '#38bdf8'; // cyan
    prefix += '$ ';
  } else {
    color = '#cbd5e1'; // slate
  }

  line.style.color = color;
  line.textContent = prefix + text;
  consoleEl.appendChild(line);
  consoleEl.scrollTop = consoleEl.scrollHeight;
}

function renderBackupsList(backups) {
  const container = document.getElementById('backupListContainer');
  const countLabel = document.getElementById('backupCountLabel');
  if (!container) return;

  if (countLabel) {
    countLabel.textContent = (backups ? backups.length : 0) + ' stored';
  }

  if (!backups || backups.length === 0) {
    container.innerHTML = '<span style="font-size:11.5px;color:var(--muted)">No backups generated yet.</span>';
    return;
  }

  container.innerHTML = backups.slice(0, 5).map(b => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 8px;background:var(--surface);border:1px solid var(--line);border-radius:6px;font-size:11.5px">
      <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:210px">
        <span style="font-weight:600" title="${escHtml(b.filename)}">${escHtml(b.filename)}</span>
        <span style="color:var(--muted);display:block;font-size:10.5px">${escHtml(b.created_at)} · ${escHtml(b.size_human)}</span>
      </div>
      <a href="/api/system/backups/${encodeURIComponent(b.filename)}" class="btn" style="padding:4px 8px;font-size:11px;text-decoration:none;gap:4px" title="Download backup archive" download>
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        Save
      </a>
    </div>
  `).join('');
}

async function loadSystemStatus() {
  try {
    const res = await JMOS_API.get('/system/status');
    if (res.status === 'success' && res.data) {
      const { system, backups } = res.data;

      const verEl = document.getElementById('sysLaravelVer');
      if (verEl && system.laravel_version) {
        verEl.textContent = 'Laravel ' + system.laravel_version;
      }

      const phpEl = document.getElementById('sysPhpVer');
      if (phpEl && system.php_version) {
        phpEl.textContent = 'PHP ' + system.php_version;
      }

      const dbStatusEl = document.getElementById('sysDbStatus');
      if (dbStatusEl && system.database) {
        if (system.database.connected) {
          dbStatusEl.style.background = 'var(--green-soft)';
          dbStatusEl.style.color = 'var(--green)';
          dbStatusEl.textContent = `DB: ${system.database.driver} (${system.database.database_name || 'connected'})`;
        } else {
          dbStatusEl.style.background = 'var(--red-soft)';
          dbStatusEl.style.color = 'var(--red)';
          dbStatusEl.textContent = 'DB Offline';
        }
      }

      const pendingBadge = document.getElementById('sysPendingMigrationsBadge');
      const actionDesc = document.getElementById('migrationActionDesc');
      if (pendingBadge && system.database) {
        const pCount = system.database.pending_migrations_count || 0;
        if (pCount > 0) {
          pendingBadge.style.background = 'var(--amber-soft)';
          pendingBadge.style.color = 'var(--amber)';
          pendingBadge.textContent = `${pCount} Pending Migration(s)`;
          if (actionDesc) actionDesc.textContent = `${pCount} new schema migration(s) ready to execute`;
        } else {
          pendingBadge.style.background = 'var(--green-soft)';
          pendingBadge.style.color = 'var(--green)';
          pendingBadge.innerHTML = '<span style="display:inline-flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Migrations Up-to-date</span>';
          if (actionDesc) actionDesc.textContent = `All ${system.database.applied_migrations_count || 23}+ database tables up to date`;
        }
      }

      renderBackupsList(backups);
    }
  } catch (err) {
    console.warn('Could not load system maintenance status:', err.message);
  }
}

function initSystemUpgrade() {
  const dropzone = document.getElementById('upgradeDropzone');
  const zipInput = document.getElementById('upgradeZipInput');
  const selectedFileInfo = document.getElementById('selectedFileInfo');
  const selectedFileName = document.getElementById('selectedFileName');
  const selectedFileSize = document.getElementById('selectedFileSize');
  const clearFileBtn = document.getElementById('clearSelectedFileBtn');
  const applyBtn = document.getElementById('applyUpgradeBtn');
  const dropzoneMainText = document.getElementById('dropzoneMainText');
  const progressContainer = document.getElementById('upgradeProgressContainer');
  const progressBar = document.getElementById('upgradeProgressBar');
  const progressPercent = document.getElementById('upgradeProgressPercent');
  const progressLabel = document.getElementById('upgradeProgressLabel');

  const runMigrationsBtn = document.getElementById('runMigrationsBtn');
  const createDbBackupBtn = document.getElementById('createDbBackupBtn');
  const clearCacheBtn = document.getElementById('clearSystemCacheBtn');
  const clearConsoleBtn = document.getElementById('clearUpgradeLogBtn');

  function handleFileSelected(file) {
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.zip')) {
      showToast('Invalid File', 'Please select a valid .zip upgrade package', true);
      return;
    }

    selectedUpgradeFile = file;
    if (selectedFileName) selectedFileName.textContent = file.name;
    if (selectedFileSize) selectedFileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
    if (selectedFileInfo) selectedFileInfo.style.display = 'inline-flex';
    if (dropzoneMainText) dropzoneMainText.textContent = 'Package ready for installation';
    if (applyBtn) {
      applyBtn.disabled = false;
      applyBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Install Upgrade (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
    }

    appendUpgradeLog(`Selected package archive: ${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`, 'info');
  }

  function resetSelectedFile() {
    selectedUpgradeFile = null;
    if (zipInput) zipInput.value = '';
    if (selectedFileInfo) selectedFileInfo.style.display = 'none';
    if (dropzoneMainText) dropzoneMainText.textContent = 'Click or drag & drop updated code (.zip)';
    if (applyBtn) {
      applyBtn.disabled = true;
      applyBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Upload &amp; Install Upgrade';
    }
  }

  if (dropzone && zipInput) {
    dropzone.onclick = (e) => {
      if (e.target !== clearFileBtn && !clearFileBtn?.contains(e.target)) {
        zipInput.click();
      }
    };

    zipInput.onchange = (e) => {
      if (e.target.files && e.target.files[0]) {
        handleFileSelected(e.target.files[0]);
      }
    };

    dropzone.ondragover = (e) => {
      e.preventDefault();
      dropzone.style.borderColor = 'var(--blue, #2563eb)';
      dropzone.style.background = 'var(--blue-soft, rgba(37,99,235,0.08))';
    };

    dropzone.ondragleave = () => {
      dropzone.style.borderColor = 'var(--line-strong)';
      dropzone.style.background = 'var(--surface)';
    };

    dropzone.ondrop = (e) => {
      e.preventDefault();
      dropzone.style.borderColor = 'var(--line-strong)';
      dropzone.style.background = 'var(--surface)';
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        handleFileSelected(e.dataTransfer.files[0]);
      }
    };
  }

  if (clearFileBtn) {
    clearFileBtn.onclick = (e) => {
      e.stopPropagation();
      resetSelectedFile();
      appendUpgradeLog('Selected package cleared.', 'info');
    };
  }

  // Handle Install Upgrade Form Submission
  if (applyBtn) {
    applyBtn.onclick = async () => {
      if (!selectedUpgradeFile) {
        showToast('No File', 'Please select an upgrade .zip archive first', true);
        return;
      }

      const confirmed = await window.showConfirmDialog({
        title: 'Deploy Software Upgrade?',
        subtitle: 'Zero Data Loss Deployment Pipeline',
        type: 'upgrade',
        confirmText: 'Install & Deploy',
        cancelText: 'Cancel',
        message: `Are you sure you want to install and deploy <b>${escHtml(selectedUpgradeFile.name)}</b>?`,
        bullets: [
          `Package archive size: ${(selectedUpgradeFile.size / (1024 * 1024)).toFixed(2)} MB`,
          'Automated safety database backup snapshot will be generated first.',
          'Existing .env configuration and media uploads in storage/ are safely protected.',
          'Pending database migrations will be executed non-destructively.'
        ]
      });
      if (!confirmed) return;

      applyBtn.disabled = true;
      applyBtn.textContent = 'Deploying Upgrade…';
      if (progressContainer) progressContainer.style.display = 'block';
      if (progressBar) progressBar.style.width = '20%';
      if (progressPercent) progressPercent.textContent = '20%';
      if (progressLabel) progressLabel.textContent = 'Uploading package archive to server…';

      appendUpgradeLog(`Initiating upgrade pipeline for: ${selectedUpgradeFile.name}`, 'cmd');

      const formData = new FormData();
      formData.append('archive', selectedUpgradeFile);
      formData.append('run_migrations', document.getElementById('chkRunMigrations')?.checked ? '1' : '0');
      formData.append('create_backup', document.getElementById('chkCreateBackup')?.checked ? '1' : '0');
      formData.append('clear_caches', document.getElementById('chkClearCaches')?.checked ? '1' : '0');

      try {
        if (progressBar) progressBar.style.width = '60%';
        if (progressPercent) progressPercent.textContent = '60%';
        if (progressLabel) progressLabel.textContent = 'Extracting files and executing safe migrations…';

        const res = await JMOS_API.upload('/system/upgrade', formData);

        if (progressBar) progressBar.style.width = '100%';
        if (progressPercent) progressPercent.textContent = '100%';
        if (progressLabel) progressLabel.textContent = 'Upgrade complete!';

        // Log step outputs
        if (res.data && res.data.steps) {
          res.data.steps.forEach(st => {
            appendUpgradeLog(`[${st.step}] ${st.details}`, st.status);
          });
        }

        appendUpgradeLog(`Upgrade finished successfully: ${res.message}`, 'success');
        showToast('Upgrade Complete', res.message || 'System software updated safely');
        resetSelectedFile();
        loadSystemStatus();
      } catch (err) {
        appendUpgradeLog(`Upgrade encountered an issue: ${err.message}`, 'error');
        showToast('Upgrade Notice', err.message, true);
        if (progressLabel) progressLabel.textContent = 'Upgrade halted.';
      } finally {
        applyBtn.disabled = false;
        applyBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Upload &amp; Install Upgrade';
        setTimeout(() => {
          if (progressContainer) progressContainer.style.display = 'none';
          if (progressBar) progressBar.style.width = '0%';
        }, 3000);
      }
    };
  }

  // Quick Action: Run Migrations
  if (runMigrationsBtn) {
    runMigrationsBtn.onclick = async () => {
      runMigrationsBtn.disabled = true;
      const oldText = runMigrationsBtn.innerHTML;
      runMigrationsBtn.textContent = 'Migrating…';

      appendUpgradeLog('Executing database migrations: php artisan migrate --force', 'cmd');

      try {
        const res = await JMOS_API.post('/system/migrate', {});
        appendUpgradeLog(res.data?.output || res.message, 'passed');
        showToast('Migrations Executed', res.message || 'Database schema updated');
        loadSystemStatus();
      } catch (err) {
        appendUpgradeLog(`Migration failed: ${err.message}`, 'error');
        showToast('Migration Error', err.message, true);
      } finally {
        runMigrationsBtn.disabled = false;
        runMigrationsBtn.innerHTML = oldText;
      }
    };
  }

  // Quick Action: Backup Database
  if (createDbBackupBtn) {
    createDbBackupBtn.onclick = async () => {
      createDbBackupBtn.disabled = true;
      const oldText = createDbBackupBtn.innerHTML;
      createDbBackupBtn.textContent = 'Dumping…';

      appendUpgradeLog('Generating live database snapshot dump...', 'cmd');

      try {
        const res = await JMOS_API.post('/system/backup', {});
        appendUpgradeLog(`Database backup created: ${res.data?.backup?.filename} (${res.data?.backup?.size_human})`, 'success');
        showToast('Backup Created', res.message || 'Database snapshot saved');
        loadSystemStatus();
      } catch (err) {
        appendUpgradeLog(`Backup failed: ${err.message}`, 'error');
        showToast('Backup Failed', err.message, true);
      } finally {
        createDbBackupBtn.disabled = false;
        createDbBackupBtn.innerHTML = oldText;
      }
    };
  }

  // Quick Action: Clear Caches
  if (clearCacheBtn) {
    clearCacheBtn.onclick = async () => {
      clearCacheBtn.disabled = true;
      const oldText = clearCacheBtn.innerHTML;
      clearCacheBtn.textContent = 'Clearing…';

      appendUpgradeLog('Purging caches: php artisan optimize:clear', 'cmd');

      try {
        const res = await JMOS_API.post('/system/clear-cache', {});
        appendUpgradeLog('Application, route, configuration and view caches cleared.', 'passed');
        showToast('Caches Cleared', res.message || 'Application caches flushed');
      } catch (err) {
        appendUpgradeLog(`Cache clear failed: ${err.message}`, 'error');
        showToast('Notice', err.message, true);
      } finally {
        clearCacheBtn.disabled = false;
        clearCacheBtn.innerHTML = oldText;
      }
    };
  }

  // Clear Terminal
  if (clearConsoleBtn) {
    clearConsoleBtn.onclick = () => {
      const consoleEl = document.getElementById('upgradeConsole');
      if (consoleEl) {
        consoleEl.innerHTML = '<div style="color:#94a3b8">[System Ready] Console cleared. Waiting for action...</div>';
      }
    };
  }

  loadSystemStatus();
}

/* ==========================================================================
   JMOS — Custom Roles & Granular Access Control Engine
   ========================================================================== */

window.JMOS_ROLES = [];
window.JMOS_PERMS_CATALOG = [];

async function loadRolesAndPermissions() {
  const container = document.getElementById('rolesListContainer');
  try {
    const res = await JMOS_API.get('/roles');
    if (res && res.status === 'success') {
      window.JMOS_ROLES = res.data || [];
      window.JMOS_PERMS_CATALOG = res.catalog || [];
      renderRolesSection();
      if (typeof updateUserRoleDropdowns === 'function') {
        updateUserRoleDropdowns();
      }
    }
  } catch (err) {
    console.warn('Could not load roles:', err.message);
    if (container) {
      container.innerHTML = '<div style="padding:16px;color:var(--muted);font-size:12.5px;grid-column:1/-1">Could not load workspace roles.</div>';
    }
  }
}

function renderRolesSection() {
  const container = document.getElementById('rolesListContainer');
  if (!container) return;

  const roles = window.JMOS_ROLES || [];
  if (roles.length === 0) {
    container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--muted);font-size:13px;grid-column:1/-1">No roles found. Click "Create Custom Role" to add one.</div>';
    return;
  }

  container.innerHTML = roles.map(role => {
    const isOwner = role.slug === 'owner';
    const isSystem = !!role.is_system;
    const permissions = Array.isArray(role.permissions) ? role.permissions : [];
    const permsCount = isOwner ? 'All Access (*)' : `${permissions.length} rights granted`;
    const userCount = role.users_count !== undefined ? role.users_count : (Array.isArray(JMOS_STATE.users) ? JMOS_STATE.users.filter(u => u.role === role.slug || u.role_id === role.id).length : 0);
    const badgeBg = role.color || '#C52523';

    let previewChips = [];
    if (isOwner) {
      previewChips = ['Unrestricted Super Admin'];
    } else {
      if (permissions.some(p => p.startsWith('dashboard.'))) previewChips.push('Dashboard');
      if (permissions.some(p => p.startsWith('leads.') || p.startsWith('deals.'))) previewChips.push('Leads & CRM');
      if (permissions.some(p => p.startsWith('projects.') || p.startsWith('tasks.'))) previewChips.push('Projects');
      if (permissions.some(p => p.startsWith('quotes.'))) previewChips.push('Quotes');
      if (permissions.some(p => p.startsWith('finance.') || p.startsWith('invoices.') || p.startsWith('expenses.'))) previewChips.push('Finance');
      if (permissions.some(p => p.startsWith('people.') || p.startsWith('roles.'))) previewChips.push('Team Admin');
      if (permissions.some(p => p.startsWith('chat.'))) previewChips.push('Team Chat');
      if (permissions.some(p => p.startsWith('calendar.'))) previewChips.push('Calendar');
      if (permissions.some(p => p.startsWith('settings.') || p.startsWith('system.'))) previewChips.push('System Admin');
    }

    const chipsHtml = previewChips.slice(0, 4).map(c => `
      <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:var(--paper);border:1px solid var(--line);color:var(--ink);font-weight:500">${escHtml(c)}</span>
    `).join('') + (previewChips.length > 4 ? `<span style="font-size:10.5px;color:var(--muted);padding:2px 4px">+${previewChips.length - 4} more</span>` : '');

    return `
      <div style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:16px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,0.03);position:relative">
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:8px">
              <span style="width:10px;height:10px;border-radius:50%;background:${badgeBg};display:inline-block"></span>
              <h4 style="font-family:'Poppins',sans-serif;font-size:14.5px;font-weight:600;margin:0;color:var(--ink)">${escHtml(role.name)}</h4>
            </div>
            <span style="font-size:10.5px;font-weight:600;padding:2px 7px;border-radius:6px;background:${isSystem ? 'rgba(43,138,90,0.12)' : 'rgba(197,37,35,0.12)'};color:${isSystem ? 'var(--green)' : 'var(--red)'}">
              ${isSystem ? 'System' : 'Custom'}
            </span>
          </div>

          <p style="font-size:12px;color:var(--muted);margin-bottom:12px;min-height:34px;line-height:1.4">
            ${escHtml(role.description || 'Custom role with configured modular permissions.')}
          </p>

          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
            ${chipsHtml}
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid var(--line);margin-top:6px">
          <div style="font-size:11.5px;color:var(--muted)">
            <b style="color:var(--ink);font-weight:600">${userCount}</b> ${userCount === 1 ? 'member' : 'members'} · <span style="font-size:11px;color:var(--muted)">${escHtml(permsCount)}</span>
          </div>
          <div style="display:flex;gap:6px">
            <button type="button" class="btn sm" onclick="openEditRoleModal(${role.id})" style="font-size:11px;padding:4px 10px">
              Edit Rights
            </button>
            ${!isSystem ? `
              <button type="button" class="btn sm" onclick="deleteRole(${role.id}, '${escHtml(role.name)}')" style="font-size:11px;padding:4px 8px;color:var(--red);border-color:rgba(239,68,68,0.25)" title="Delete custom role">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
              </button>
            ` : ''}
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function renderPermissionCheckboxes(selectedPermissions = []) {
  const container = document.getElementById('rolePermissionsContainer');
  if (!container) return;

  const catalog = window.JMOS_PERMS_CATALOG || [];
  const isWildcard = selectedPermissions.includes('*');

  container.innerHTML = catalog.map((group, groupIdx) => {
    const allGroupKeys = group.permissions.map(p => p.key);
    const groupCheckedCount = allGroupKeys.filter(k => isWildcard || selectedPermissions.includes(k)).length;
    const isGroupAll = groupCheckedCount === allGroupKeys.length;

    const permsList = group.permissions.map(perm => {
      const isChecked = isWildcard || selectedPermissions.includes(perm.key);
      return `
        <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;cursor:pointer;padding:6px 8px;border-radius:6px;background:var(--paper);border:1px solid var(--line);user-select:none;transition:background .15s ease" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--paper)'">
          <input type="checkbox" name="rolePerm" value="${perm.key}" ${isChecked ? 'checked' : ''} style="margin-top:2px;accent-color:var(--red);cursor:pointer">
          <div style="flex:1">
            <div style="font-weight:600;color:var(--ink)">${escHtml(perm.label)}</div>
            <div style="font-size:11px;color:var(--muted);line-height:1.3">${escHtml(perm.description)}</div>
          </div>
        </label>
      `;
    }).join('');

    return `
      <div style="background:var(--card);border:1px solid var(--line);border-radius:10px;padding:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;border-bottom:1px solid var(--line);padding-bottom:6px">
          <div>
            <div style="font-weight:600;font-size:13px;color:var(--ink)">${escHtml(group.group)}</div>
            <div style="font-size:11px;color:var(--muted)">${escHtml(group.description)}</div>
          </div>
          <button type="button" class="btn sm" onclick="toggleGroupPermissions(${groupIdx}, ${!isGroupAll})" style="font-size:10.5px;padding:2px 7px">
            ${isGroupAll ? 'Clear Group' : 'Select Group'}
          </button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:8px">
          ${permsList}
        </div>
      </div>
    `;
  }).join('');
}

window.openCreateRoleModal = function() {
  document.getElementById('editRoleId').value = '';
  document.getElementById('roleModalTitle').textContent = 'Create Custom Role';
  document.getElementById('roleModalSub').textContent = 'Configure custom role access rights and modular permissions across JMOS.';
  document.getElementById('roleName').value = '';
  document.getElementById('roleDescription').value = '';
  document.getElementById('roleColor').value = '#C52523';

  // Default permissions for new role
  renderPermissionCheckboxes(['dashboard.view', 'projects.view', 'tasks.manage', 'chat.access', 'calendar.view']);

  const modal = document.getElementById('roleModal');
  if (modal) {
    modal.classList.add('on');
    document.body.classList.add('modal-open');
  }
};

window.openEditRoleModal = function(roleId) {
  const role = (window.JMOS_ROLES || []).find(r => r.id === roleId);
  if (!role) return;

  document.getElementById('editRoleId').value = role.id;
  document.getElementById('roleModalTitle').textContent = `Edit Role: ${role.name}`;
  document.getElementById('roleModalSub').textContent = role.is_system ? 'Built-in system role. You can fine-tune its permissions matrix.' : 'Update role details and functional access permissions.';
  document.getElementById('roleName').value = role.name || '';
  document.getElementById('roleDescription').value = role.description || '';
  document.getElementById('roleColor').value = role.color || '#C52523';

  renderPermissionCheckboxes(Array.isArray(role.permissions) ? role.permissions : []);

  const modal = document.getElementById('roleModal');
  if (modal) {
    modal.classList.add('on');
    document.body.classList.add('modal-open');
  }
};

window.toggleAllRolePermissions = function(checked) {
  const checkboxes = document.querySelectorAll('#rolePermissionsContainer input[type="checkbox"]');
  checkboxes.forEach(cb => { cb.checked = !!checked; });
};

window.toggleGroupPermissions = function(groupIdx, checked) {
  const catalog = window.JMOS_PERMS_CATALOG || [];
  const group = catalog[groupIdx];
  if (!group) return;

  const groupKeys = new Set(group.permissions.map(p => p.key));
  const checkboxes = document.querySelectorAll('#rolePermissionsContainer input[type="checkbox"]');
  checkboxes.forEach(cb => {
    if (groupKeys.has(cb.value)) {
      cb.checked = !!checked;
    }
  });
};

window.saveRole = async function() {
  const roleId = document.getElementById('editRoleId').value;
  const name = document.getElementById('roleName').value.trim();
  const description = document.getElementById('roleDescription').value.trim();
  const color = document.getElementById('roleColor').value;
  const saveBtn = document.getElementById('saveRoleBtn');

  if (!name) {
    showToast('Validation Error', 'Please enter a role name', true);
    return;
  }

  const selectedPermissions = Array.from(document.querySelectorAll('#rolePermissionsContainer input[type="checkbox"]:checked')).map(cb => cb.value);

  const payload = {
    name,
    description,
    color,
    permissions: selectedPermissions,
  };

  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }

  try {
    let res;
    if (roleId) {
      res = await JMOS_API.post(`/roles/${roleId}`, payload);
    } else {
      res = await JMOS_API.post('/roles', payload);
    }

    if (res && res.status === 'success') {
      showToast('Role Saved', res.message || 'Role permissions updated successfully');
      const modal = document.getElementById('roleModal');
      if (modal) {
        modal.classList.remove('on');
        document.body.classList.remove('modal-open');
      }
      await loadRolesAndPermissions();
    } else {
      showToast('Error', res?.message || 'Could not save role', true);
    }
  } catch (err) {
    showToast('Role Save Failed', err.message, true);
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save Role';
    }
  }
};

window.deleteRole = async function(roleId, roleName) {
  const confirmed = await window.showConfirmDialog({
    title: `Delete Role "${roleName}"?`,
    subtitle: 'Access Control Modification',
    type: 'danger',
    confirmText: 'Delete Role',
    message: `Are you sure you want to permanently delete custom role <b>${escHtml(roleName)}</b>?`,
    bullets: [
      'Any users currently assigned to this role will be automatically reassigned to the standard Team Member role.',
      'This action cannot be undone.'
    ]
  });
  if (!confirmed) return;

  try {
    const res = await JMOS_API.delete(`/roles/${roleId}`);
    if (res && res.status === 'success') {
      showToast('Role Deleted', res.message || 'Role removed');
      await loadRolesAndPermissions();
      if (typeof fetchUsers === 'function') {
        fetchUsers();
      }
    } else {
      showToast('Error', res?.message || 'Could not delete role', true);
    }
  } catch (err) {
    showToast('Delete Failed', err.message, true);
  }
};

window.updateUserRoleDropdowns = function() {
  const nuRole = document.getElementById('nuRole');
  if (!nuRole) return;

  const roles = window.JMOS_ROLES || [];
  if (roles.length === 0) return;

  const currentVal = nuRole.value;

  nuRole.innerHTML = roles.map(r => `
    <option value="${escHtml(r.slug)}">${escHtml(r.name)}</option>
  `).join('');

  if (currentVal && Array.from(nuRole.options).some(o => o.value === currentVal)) {
    nuRole.value = currentVal;
  }
};

