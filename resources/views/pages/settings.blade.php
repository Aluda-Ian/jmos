<!-- ==========================================================================
     JMOS — View: System Settings & API Gateway (Super Admin & IT)
     ========================================================================== -->
<section class="view" data-view="settings" hidden data-perm="owner">
  <div class="page-head">
    <div>
      <h1 class="pt">Settings &amp; Integrations</h1>
      <p>Configure SMTP email gateways, Google Calendar APIs, notification triggers, and core system parameters.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="saveAllSettingsBtn">
        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save all settings
      </button>
    </div>
  </div>

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
        <p style="font-size:12px;color:var(--muted);margin-bottom:10px">Send a live test verification email to confirm server delivery.</p>
        <div style="display:flex;gap:8px">
          <input id="testEmailRecipient" placeholder="Recipient (e.g. jmos@jeotamedia.co.ke)" value="jmos@jeotamedia.co.ke" style="flex:1;padding:8px 12px;border:1px solid var(--line-strong);border-radius:8px;font-size:13px;background:var(--surface)">
          <button type="button" class="btn" id="testSmtpBtn" style="white-space:nowrap">
            <svg viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Test Email
          </button>
        </div>
        <div id="smtpTestResult" style="margin-top:10px;font-size:12px;display:none"></div>
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
  </div>
</section>
