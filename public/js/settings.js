/* ==========================================================================
   JMOS — Settings & Integrations Controller (Super Admin / IT)
   ========================================================================== */

async function loadSettings() {
  try {
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
    { group: 'general', key: 'timezone', value: document.getElementById('cfg_timezone')?.value }
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

function initSettings() {
  const saveBtn = document.getElementById('saveAllSettingsBtn');
  if (saveBtn) saveBtn.onclick = saveAllSettings;

  // Test SMTP button
  const testSmtpBtn = document.getElementById('testSmtpBtn');
  const testRecipientInput = document.getElementById('testEmailRecipient');
  const smtpTestResult = document.getElementById('smtpTestResult');

  if (testSmtpBtn) {
    testSmtpBtn.onclick = async () => {
      const recipient = testRecipientInput ? testRecipientInput.value.trim() : 'jmos@jeotamedia.co.ke';
      testSmtpBtn.disabled = true;
      testSmtpBtn.textContent = 'Connecting…';
      if (smtpTestResult) {
        smtpTestResult.style.display = 'block';
        smtpTestResult.style.color = 'var(--muted)';
        smtpTestResult.textContent = `Connecting to SMTP host and sending verification email to ${recipient}…`;
      }

      try {
        const res = await JMOS_API.post('/settings/test-email', { recipient, template: 'general' });
        if (smtpTestResult) {
          smtpTestResult.style.color = 'var(--green)';
          smtpTestResult.innerHTML = `<b>✓ Success:</b> ${escHtml(res.message)}`;
        }
        showToast('SMTP Test Passed', 'Test email sent successfully');
      } catch (err) {
        if (smtpTestResult) {
          smtpTestResult.style.color = 'var(--red)';
          smtpTestResult.innerHTML = `<b>✗ Failed:</b> ${escHtml(err.message)}`;
        }
        showToast('SMTP Connection Failed', err.message, true);
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
          calSyncResult.innerHTML = `<b>✓ Synced:</b> ${escHtml(res.message)}`;
        }
        showToast('Google Calendar Synced', 'Meetings & agenda updated');
      } catch (err) {
        if (calSyncResult) {
          calSyncResult.style.color = 'var(--red)';
          calSyncResult.innerHTML = `<b>✗ Sync Error:</b> ${escHtml(err.message)}`;
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
      const resultBox = document.getElementById('sampleEmailResult');

      sampleBtn.disabled = true;
      const oldText = sampleBtn.textContent;
      sampleBtn.textContent = 'Sending…';

      if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.style.background = 'var(--paper)';
        resultBox.style.color = 'var(--muted)';
        resultBox.textContent = `Dispatching branded ${template.toUpperCase()} email to ${recipient}…`;
      }

      try {
        const res = await JMOS_API.post('/settings/test-email', { recipient, template });
        if (resultBox) {
          resultBox.style.background = 'var(--green-soft)';
          resultBox.style.color = 'var(--green)';
          resultBox.innerHTML = `<b>✓ Delivered:</b> ${escHtml(res.message)}`;
        }
        showToast('Branded Email Sent', `${template.toUpperCase()} notification delivered to ${recipient}`);
      } catch (err) {
        if (resultBox) {
          resultBox.style.background = 'var(--red-soft)';
          resultBox.style.color = 'var(--red-deep)';
          resultBox.innerHTML = `<b>✗ Delivery note:</b> ${escHtml(err.message)}`;
        }
        showToast('Dispatch Note', err.message, true);
      } finally {
        sampleBtn.disabled = false;
        sampleBtn.textContent = oldText;
      }
    }
  });

  loadSettings();
}
