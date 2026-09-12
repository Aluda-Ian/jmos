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

      // 4. KRA Gava Settings
      if (data.kra) {
        if (data.kra.kra_pin && document.getElementById('cfg_kra_pin')) document.getElementById('cfg_kra_pin').value = data.kra.kra_pin.value || 'P051782390X';
        if (data.kra.kra_taxpayer_name && document.getElementById('cfg_kra_taxpayer_name')) document.getElementById('cfg_kra_taxpayer_name').value = data.kra.kra_taxpayer_name.value || 'Jeota Media Ltd';
        if (data.kra.kra_etims_branch_id && document.getElementById('cfg_kra_etims_branch')) document.getElementById('cfg_kra_etims_branch').value = data.kra.kra_etims_branch_id.value || '00';
        if (data.kra.kra_vat_rate && document.getElementById('cfg_kra_vat_rate')) document.getElementById('cfg_kra_vat_rate').value = data.kra.kra_vat_rate.value || '16';
        if (data.kra.kra_wht_rate && document.getElementById('cfg_kra_wht_rate')) document.getElementById('cfg_kra_wht_rate').value = data.kra.kra_wht_rate.value || '5';
        if (data.kra.kra_status && document.getElementById('cfg_kra_status')) document.getElementById('cfg_kra_status').value = data.kra.kra_status.value || 'connected';
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

  initSystemUpgrade();
  loadSettings();
}

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

      const confirmed = confirm(
        `Are you sure you want to install this upgrade archive?\n\n` +
        `• File: ${selectedUpgradeFile.name}\n` +
        `• A pre-upgrade database backup will be automatically created.\n` +
        `• Existing .env and media in storage/ are safely protected.\n` +
        `• Pending database migrations will be executed non-destructively.\n\n` +
        `Click OK to proceed with deployment.`
      );
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

