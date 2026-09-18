/* ==========================================================================
   JMOS — Zoho CRM Lead Generation, Customer Journey & Pipeline Controller
   ========================================================================== */

(function () {
  'use strict';

  // CRM Module State
  window.CRM_STATE = {
    leads: [],
    stats: {},
    calls: [],
    contacts: [],
    accounts: [],
    meetings: [],
    activeTab: 'leads',
    selectedLeadId: null,
    filter: {
      search: '',
      status: 'all',
      ratings: [],
      source: '',
      owner: '',
    },
    selectedLeadIds: new Set(),
  };

  /**
   * Helper: Format Currency
   */
  function formatMoney(val) {
    const num = parseFloat(val) || 0;
    return 'KES ' + Math.round(num).toLocaleString();
  }

  function getInitials(name) {
    if (!name) return 'LD';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * 1. Initialize CRM Module
   */
  window.initCrmModule = function () {
    // Setup Module Navigation Tabs
    document.querySelectorAll('.crm-nav-tab').forEach((tab) => {
      tab.addEventListener('click', function () {
        const targetTab = this.getAttribute('data-crm-tab');
        window.switchCrmTab(targetTab);
      });
    });

    // Initial Data Fetch
    window.refreshCrmData();
  };

  /**
   * Switch Active CRM Module Tab
   */
  window.switchCrmTab = function (tabName) {
    CRM_STATE.activeTab = tabName;

    // Toggle Tab Buttons
    document.querySelectorAll('.crm-nav-tab').forEach((tab) => {
      tab.classList.toggle('active', tab.getAttribute('data-crm-tab') === tabName);
    });

    // Toggle Tab Panes
    document.querySelectorAll('.crm-tab-pane').forEach((pane) => {
      pane.style.display = 'none';
    });

    const targetPaneMap = {
      leads: 'crmPaneLeads',
      pipeline: 'crmPanePipeline',
      contacts: 'crmPaneContacts',
      accounts: 'crmPaneAccounts',
      calls: 'crmPaneCalls',
      meetings: 'crmPaneMeetings',
    };

    const targetPaneId = targetPaneMap[tabName] || 'crmPaneLeads';
    const targetPane = document.getElementById(targetPaneId);
    if (targetPane) targetPane.style.display = 'block';

    if (tabName === 'pipeline') {
      if (typeof renderPipeline === 'function') renderPipeline();
    } else if (tabName === 'contacts') {
      window.renderContactsTable();
    } else if (tabName === 'accounts') {
      window.renderAccountsTable();
    } else if (tabName === 'calls') {
      window.renderCallsTable();
    } else if (tabName === 'meetings') {
      window.renderMeetingsTable();
    }
  };

  /**
   * 2. Fetch & Refresh All CRM Data
   */
  window.refreshCrmData = async function () {
    const refreshBtn = document.getElementById('crmRefreshBtn');
    if (refreshBtn) refreshBtn.classList.add('loading');

    try {
      const [leadsRes, callsRes, contactsRes, calRes] = await Promise.all([
        JMOS_API.get('/leads').catch(() => ({ data: [], stats: {} })),
        JMOS_API.get('/lead-calls').catch(() => ({ data: [] })),
        JMOS_API.get('/contacts').catch(() => ({ data: [] })),
        JMOS_API.get('/calendar/events').catch(() => []),
      ]);

      // Update Leads & Stats
      CRM_STATE.leads = leadsRes.data || (Array.isArray(leadsRes) ? leadsRes : []);
      CRM_STATE.stats = leadsRes.stats || {};
      CRM_STATE.calls = callsRes.data || (Array.isArray(callsRes) ? callsRes : []);
      CRM_STATE.contacts = contactsRes.data || (Array.isArray(contactsRes) ? contactsRes : []);

      const rawCal = Array.isArray(calRes) ? calRes : (calRes.data || []);
      CRM_STATE.meetings = rawCal.filter(e => e.event_type === 'meeting' || e.event_type === 'status_meeting' || e.related_type === 'Lead');

      // Update KPIs & Renderings
      window.updateCrmKpis();
      window.renderLeadsTable();
      window.updateTabBadges();

      if (CRM_STATE.activeTab === 'contacts') window.renderContactsTable();
      if (CRM_STATE.activeTab === 'accounts') window.renderAccountsTable();
      if (CRM_STATE.activeTab === 'calls') window.renderCallsTable();
      if (CRM_STATE.activeTab === 'meetings') window.renderMeetingsTable();

    } catch (err) {
      console.error('Error refreshing CRM data:', err);
    } finally {
      if (refreshBtn) refreshBtn.classList.remove('loading');
    }
  };

  /**
   * 3. Update Top KPI Bar & Module Badges
   */
  window.updateCrmKpis = function () {
    const s = CRM_STATE.stats || {};
    const totalLeads = s.total_leads ?? CRM_STATE.leads.length;
    const activeLeads = s.active_leads ?? CRM_STATE.leads.filter(l => !l.is_converted && l.lead_status !== 'Junk/Lost').length;
    const hotLeads = s.hot_leads ?? CRM_STATE.leads.filter(l => l.rating === 'Hot').length;
    const pot = s.pipeline_potential ?? CRM_STATE.leads.reduce((sum, l) => sum + (parseFloat(l.annual_revenue) || 0), 0);
    const convRate = s.conversion_rate ?? 0;
    const convCount = s.converted_leads ?? CRM_STATE.leads.filter(l => l.is_converted).length;
    const callsLogged = s.total_calls_logged ?? CRM_STATE.calls.length;

    const elTotal = document.getElementById('crmKpiTotalLeads');
    const elActive = document.getElementById('crmKpiActiveLeads');
    const elHot = document.getElementById('crmKpiHotBadge');
    const elPot = document.getElementById('crmKpiPipelineValue');
    const elConv = document.getElementById('crmKpiConversionRate');
    const elConvCount = document.getElementById('crmKpiConvertedCount');
    const elCalls = document.getElementById('crmKpiCallsLogged');

    if (elTotal) elTotal.textContent = totalLeads;
    if (elActive) elActive.textContent = `${activeLeads} active`;
    if (elHot) elHot.textContent = `${hotLeads} Hot 🔥`;
    if (elPot) elPot.textContent = formatMoney(pot);
    if (elConv) elConv.textContent = `${convRate}%`;
    if (elConvCount) elConvCount.textContent = `${convCount} converted`;
    if (elCalls) elCalls.textContent = callsLogged;
  };

  window.updateTabBadges = function () {
    const bLeads = document.getElementById('crmTabBadgeLeads');
    const bDeals = document.getElementById('crmTabBadgeDeals');
    const bContacts = document.getElementById('crmTabBadgeContacts');
    const bAccounts = document.getElementById('crmTabBadgeAccounts');
    const bCalls = document.getElementById('crmTabBadgeCalls');
    const bMeetings = document.getElementById('crmTabBadgeMeetings');

    if (bLeads) bLeads.textContent = CRM_STATE.leads.length;
    if (bDeals) bDeals.textContent = (window.JMOS_STATE && JMOS_STATE.pipeline) ? JMOS_STATE.pipeline.length : 0;
    if (bContacts) bContacts.textContent = CRM_STATE.contacts.length;
    if (bAccounts) bAccounts.textContent = (window.JMOS_STATE && JMOS_STATE.clients) ? JMOS_STATE.clients.length : 0;
    if (bCalls) bCalls.textContent = CRM_STATE.calls.length;
    if (bMeetings) bMeetings.textContent = CRM_STATE.meetings.length;
  };

  /**
   * 4. Lead Filters & Search Handlers
   */
  window.onLeadSearchChange = function (val) {
    CRM_STATE.filter.search = val.trim().toLowerCase();
    window.renderLeadsTable();
  };

  window.applyLeadFilter = function () {
    const statusRadio = document.querySelector('input[name="leadStatusFilter"]:checked');
    CRM_STATE.filter.status = statusRadio ? statusRadio.value : 'all';

    const ratingChks = Array.from(document.querySelectorAll('.lead-rating-chk:checked')).map(c => c.value);
    CRM_STATE.filter.ratings = ratingChks;

    const srcSel = document.getElementById('leadSourceFilter');
    CRM_STATE.filter.source = srcSel ? srcSel.value : '';

    const ownerSel = document.getElementById('leadOwnerFilter');
    CRM_STATE.filter.owner = ownerSel ? ownerSel.value : '';

    window.renderLeadsTable();
  };

  window.resetLeadFilters = function () {
    CRM_STATE.filter = {
      search: '',
      status: 'all',
      ratings: [],
      source: '',
      owner: '',
    };

    const sIn = document.getElementById('leadSearchInput');
    if (sIn) sIn.value = '';

    const allRadio = document.querySelector('input[name="leadStatusFilter"][value="all"]');
    if (allRadio) allRadio.checked = true;

    document.querySelectorAll('.lead-rating-chk').forEach(c => c.checked = false);

    const srcSel = document.getElementById('leadSourceFilter');
    if (srcSel) srcSel.value = '';

    const ownerSel = document.getElementById('leadOwnerFilter');
    if (ownerSel) ownerSel.value = '';

    window.renderLeadsTable();
  };

  /**
   * 5. Filter & Render Leads Table
   */
  window.renderLeadsTable = function () {
    const tbody = document.getElementById('crmLeadsTableBody');
    const countPill = document.getElementById('crmLeadsCountPill');
    if (!tbody) return;

    let filtered = [...CRM_STATE.leads];

    // Status filter
    if (CRM_STATE.filter.status === 'active') {
      filtered = filtered.filter(l => !l.is_converted && l.lead_status !== 'Junk/Lost');
    } else if (CRM_STATE.filter.status === 'converted') {
      filtered = filtered.filter(l => l.is_converted);
    } else if (CRM_STATE.filter.status !== 'all') {
      filtered = filtered.filter(l => l.lead_status === CRM_STATE.filter.status);
    }

    // Rating filter
    if (CRM_STATE.filter.ratings && CRM_STATE.filter.ratings.length > 0) {
      filtered = filtered.filter(l => CRM_STATE.filter.ratings.includes(l.rating));
    }

    // Source filter
    if (CRM_STATE.filter.source) {
      filtered = filtered.filter(l => l.lead_source === CRM_STATE.filter.source);
    }

    // Owner filter
    if (CRM_STATE.filter.owner) {
      filtered = filtered.filter(l => l.lead_owner === CRM_STATE.filter.owner);
    }

    // Search filter
    if (CRM_STATE.filter.search) {
      const q = CRM_STATE.filter.search;
      filtered = filtered.filter(l => 
        (l.lead_name && l.lead_name.toLowerCase().includes(q)) ||
        (l.company && l.company.toLowerCase().includes(q)) ||
        (l.email && l.email.toLowerCase().includes(q)) ||
        (l.phone && l.phone.toLowerCase().includes(q)) ||
        (l.title && l.title.toLowerCase().includes(q))
      );
    }

    if (countPill) countPill.textContent = `${filtered.length} of ${CRM_STATE.leads.length} Records`;

    if (filtered.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="10" style="padding:40px;text-align:center;color:var(--muted)">
            <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">No matching leads found</div>
            <p style="font-size:12px;margin:0 0 12px">Try resetting your filters or create a new prospect lead.</p>
            <button type="button" class="btn primary" onclick="window.openCreateLeadModal()" style="font-size:11.5px">+ Create Lead</button>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = filtered.map(lead => {
      const ini = getInitials(lead.lead_name || lead.company);
      const isConverted = lead.is_converted;
      
      let ratingBadge = '';
      if (lead.rating === 'Hot') ratingBadge = '<span class="badge" style="background:rgba(197,37,35,0.12);color:var(--red);font-size:10.5px;font-weight:700">Hot 🔥</span>';
      else if (lead.rating === 'Warm') ratingBadge = '<span class="badge" style="background:rgba(217,119,6,0.12);color:#D97706;font-size:10.5px">Warm ⚡</span>';
      else ratingBadge = '<span class="badge" style="background:rgba(100,116,139,0.12);color:#64748B;font-size:10.5px">Cold ❄️</span>';

      let statusPill = '';
      if (isConverted) {
        statusPill = '<span class="pill tint-green" style="font-weight:700">✓ Converted</span>';
      } else if (lead.lead_status === 'Qualified') {
        statusPill = '<span class="pill tint-amber" style="font-weight:700">Qualified</span>';
      } else if (lead.lead_status === 'In Discussion') {
        statusPill = '<span class="pill tint-blue">In Discussion</span>';
      } else if (lead.lead_status === 'Contacted') {
        statusPill = '<span class="pill tint-blue">Contacted</span>';
      } else {
        statusPill = `<span class="pill">${esc(lead.lead_status || 'New')}</span>`;
      }

      const convertBtn = !isConverted 
        ? `<button type="button" class="linkbtn" onclick="window.openConvertLeadModal(${lead.id})" style="font-size:11.5px;font-weight:700;color:var(--red)" title="Convert to Account & Deal">Convert ➔</button>`
        : `<span style="font-size:11px;color:var(--green);font-weight:600">Converted</span>`;

      return `
        <tr data-lead-row-id="${lead.id}">
          <td style="text-align:center">
            <input type="checkbox" class="lead-row-chk" value="${lead.id}" onchange="window.onLeadRowCheckChange(${lead.id}, this.checked)">
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;cursor:pointer" onclick="window.openLeadDetailModal(${lead.id})">
              <div class="av" style="background:var(--red);width:32px;height:32px;font-size:11.5px;font-weight:700">${esc(ini)}</div>
              <div>
                <div style="font-weight:700;color:var(--ink);font-size:13px">${esc(lead.lead_name)}</div>
                <div style="font-size:11px;color:var(--muted)">${esc(lead.title || 'Prospect')}</div>
              </div>
            </div>
          </td>
          <td>
            <div style="font-weight:600;color:var(--ink);font-size:12.5px">${esc(lead.company || '—')}</div>
            <div style="font-size:10.5px;color:var(--muted)">${esc(lead.industry || 'Direct')}</div>
          </td>
          <td>
            ${lead.email ? `<a href="mailto:${esc(lead.email)}" style="color:var(--ink);font-size:12px;text-decoration:none">${esc(lead.email)}</a>` : '<span style="color:var(--faint)">—</span>'}
          </td>
          <td>
            ${lead.phone ? `<a href="tel:${esc(lead.phone)}" style="color:var(--ink);font-size:12px;text-decoration:none;font-family:'IBM Plex Mono',monospace">${esc(lead.phone)}</a>` : '<span style="color:var(--faint)">—</span>'}
          </td>
          <td>
            <span class="badge" style="font-size:10.5px;background:var(--panel-2);color:var(--muted)">${esc(lead.lead_source || 'Web Research')}</span>
          </td>
          <td>
            <div style="font-size:11.5px;color:var(--ink)">${esc(lead.lead_owner || 'Jeota Media')}</div>
          </td>
          <td>
            <div class="mono" style="font-size:12px;font-weight:600;color:var(--ink)">${formatMoney(lead.annual_revenue)}</div>
          </td>
          <td>
            <div style="display:flex;flex-direction:column;gap:4px">
              ${statusPill}
              ${ratingBadge}
            </div>
          </td>
          <td style="text-align:right">
            <div style="display:inline-flex;align-items:center;gap:6px">
              ${convertBtn}
              <button type="button" class="icon-btn" onclick="window.openLeadDetailModal(${lead.id})" title="View Lead Journey" style="width:26px;height:26px;padding:0">
                <svg viewBox="0 0 24 24" width="13" height="13"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  /**
   * 6. Lead Detail Workspace Drawer
   */
  window.openLeadDetailModal = async function (leadId) {
    CRM_STATE.selectedLeadId = leadId;
    let lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));

    try {
      const res = await JMOS_API.get(`/leads/${leadId}`);
      if (res && res.data) lead = res.data;
    } catch (_) {}

    if (!lead) return;

    // Populate Header & Subtitles
    const elId = document.getElementById('ldmLeadId');
    const elName = document.getElementById('ldmLeadName');
    const elSub = document.getElementById('ldmSubtitle');
    const elAvatar = document.getElementById('ldmAvatar');
    const elRating = document.getElementById('ldmRatingBadge');
    const elSource = document.getElementById('ldmSourceBadge');
    const elStatus = document.getElementById('ldmStatusBadge');
    const elConvertBtn = document.getElementById('ldmConvertBtn');

    if (elId) elId.value = lead.id;
    if (elName) elName.textContent = lead.lead_name;
    if (elSub) elSub.textContent = `${lead.title || 'Prospect'} at ${lead.company || 'Direct'}`;
    if (elAvatar) elAvatar.textContent = getInitials(lead.lead_name || lead.company);

    if (elRating) elRating.innerHTML = lead.rating === 'Hot' ? 'Hot 🔥' : (lead.rating === 'Warm' ? 'Warm ⚡' : 'Cold ❄️');
    if (elSource) elSource.textContent = lead.lead_source || 'Web Research';
    
    if (elStatus) {
      elStatus.textContent = lead.is_converted ? 'Converted' : (lead.lead_status || 'New');
      elStatus.className = 'pill ' + (lead.is_converted ? 'tint-green' : (lead.lead_status === 'Qualified' ? 'tint-amber' : 'tint-blue'));
    }

    if (elConvertBtn) {
      elConvertBtn.style.display = lead.is_converted ? 'none' : 'inline-flex';
    }

    // Update Stepper
    window.updateLeadStepperState(lead.lead_status, lead.is_converted);

    // Update Stats Summary
    const elVal = document.getElementById('ldmStatValue');
    const elOwner = document.getElementById('ldmStatOwner');
    const elCalls = document.getElementById('ldmStatCalls');
    const elMeets = document.getElementById('ldmStatMeetings');

    if (elVal) elVal.textContent = formatMoney(lead.annual_revenue);
    if (elOwner) elOwner.textContent = lead.lead_owner || 'Jeota Media';
    if (elCalls) elCalls.textContent = `${lead.calls ? lead.calls.length : 0} calls`;
    if (elMeets) elMeets.textContent = `${lead.meetings ? lead.meetings.length : 0} scheduled`;

    // Populate Information Tab
    const elEmail = document.getElementById('ldmInfoEmail');
    const elPhone = document.getElementById('ldmInfoPhone');
    const elIndustry = document.getElementById('ldmInfoIndustry');
    const elCity = document.getElementById('ldmInfoCity');
    const elNotes = document.getElementById('ldmInfoNotes');

    if (elEmail) elEmail.innerHTML = lead.email ? `<a href="mailto:${esc(lead.email)}" style="color:var(--red);text-decoration:none;font-weight:600">${esc(lead.email)}</a>` : '—';
    if (elPhone) elPhone.innerHTML = lead.phone ? `<a href="tel:${esc(lead.phone)}" style="color:var(--ink);text-decoration:none;font-weight:600;font-family:'IBM Plex Mono',monospace">${esc(lead.phone)}</a>` : '—';
    if (elIndustry) elIndustry.textContent = lead.industry || 'Corporate';
    if (elCity) elCity.textContent = lead.city || 'Nairobi, Kenya';
    if (elNotes) elNotes.textContent = lead.notes || 'No qualification notes recorded yet.';

    // Populate Calls & Meetings
    window.renderLeadCallsList(lead.calls || []);
    window.renderLeadMeetingsList(lead.meetings || []);

    const tabCallsCount = document.getElementById('ldmTabCallsCount');
    const tabMeetsCount = document.getElementById('ldmTabMeetingsCount');
    if (tabCallsCount) tabCallsCount.textContent = (lead.calls || []).length;
    if (tabMeetsCount) tabMeetsCount.textContent = (lead.meetings || []).length;

    // Open modal
    window.switchLeadDrawerTab('info');
    openModal('leadDetailModal');
  };

  window.updateLeadStepperState = function (stage, isConverted) {
    const stages = ['New', 'Contacted', 'In Discussion', 'Qualified', 'Converted'];
    const currentStageIndex = isConverted ? 4 : stages.indexOf(stage);

    const label = document.getElementById('ldmStepperLabel');
    if (label) label.textContent = 'Stage: ' + (isConverted ? 'CONVERTED ✓' : (stage || 'NEW').toUpperCase());

    document.querySelectorAll('#ldmJourneyStepper .lead-step-btn').forEach((btn, idx) => {
      const st = btn.getAttribute('data-lead-stage');
      btn.classList.remove('active', 'completed');

      if (isConverted && st === 'Converted') {
        btn.classList.add('active');
      } else if (st === stage && !isConverted) {
        btn.classList.add('active');
      } else if (idx < currentStageIndex) {
        btn.classList.add('completed');
      }
    });
  };

  window.changeLeadStage = async function (newStage) {
    const leadId = document.getElementById('ldmLeadId')?.value;
    if (!leadId) return;

    try {
      await JMOS_API.put(`/leads/${leadId}`, { lead_status: newStage });
      showToast('Stage Updated', `Moved lead to stage: ${newStage}`);
      
      const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
      if (lead) lead.lead_status = newStage;

      window.updateLeadStepperState(newStage, false);
      window.renderLeadsTable();
      window.updateCrmKpis();
    } catch (err) {
      showToast('Error', err.message, true);
    }
  };

  window.switchLeadDrawerTab = function (tabName) {
    document.querySelectorAll('[data-ldm-tab]').forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-ldm-tab') === tabName);
    });

    const panes = {
      info: 'ldmTabPaneInfo',
      calls: 'ldmTabPaneCalls',
      meetings: 'ldmTabPaneMeetings',
    };

    Object.keys(panes).forEach(k => {
      const p = document.getElementById(panes[k]);
      if (p) p.style.display = (k === tabName) ? 'block' : 'none';
    });
  };

  window.renderLeadCallsList = function (calls) {
    const container = document.getElementById('ldmCallsList');
    if (!container) return;

    if (!calls || calls.length === 0) {
      container.innerHTML = `<div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No calls logged with this prospect yet.</div>`;
      return;
    }

    container.innerHTML = calls.map(c => `
      <div class="call-item-card">
        <div style="display:flex;align-items:flex-start;gap:10px">
          <div style="width:32px;height:32px;border-radius:8px;background:${c.call_type === 'Outbound' ? 'rgba(197,37,35,0.1)' : 'rgba(43,110,138,0.1)'};display:grid;place-items:center;color:${c.call_type === 'Outbound' ? 'var(--red)' : '#2B6E8A'}">
            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--ink)">${esc(c.purpose || 'Call')} · <span style="font-weight:500;color:var(--muted)">${esc(c.outcome || 'Logged')}</span></div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">${esc(c.notes || 'No summary notes.')}</div>
            <div style="font-size:10.5px;color:var(--faint);margin-top:4px">By ${esc(c.logged_by || 'Team')} · Duration: ${c.duration_minutes || 0} mins</div>
          </div>
        </div>
      </div>
    `).join('');
  };

  window.renderLeadMeetingsList = function (meetings) {
    const container = document.getElementById('ldmMeetingsList');
    if (!container) return;

    if (!meetings || meetings.length === 0) {
      container.innerHTML = `<div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No discovery meetings scheduled yet.</div>`;
      return;
    }

    container.innerHTML = meetings.map(m => `
      <div class="call-item-card">
        <div style="display:flex;align-items:flex-start;gap:10px">
          <div style="width:32px;height:32px;border-radius:8px;background:rgba(43,138,90,0.1);display:grid;place-items:center;color:#2B8A5A">
            <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--ink)">${esc(m.title)}</div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">${m.start_time ? new Date(m.start_time).toLocaleString() : 'Upcoming'}</div>
            <div style="font-size:11px;color:var(--ink);margin-top:4px">Location: <b>${esc(m.location || 'Google Meet')}</b></div>
          </div>
        </div>
        ${m.meet_link ? `<a href="${esc(m.meet_link)}" target="_blank" class="btn primary" style="font-size:11px;padding:4px 8px;text-decoration:none">Join Meet 📹</a>` : ''}
      </div>
    `).join('');
  };

  /**
   * 7. Zoho CRM Lead Conversion Dialog & Action
   */
  window.openConvertLeadModal = function (leadId) {
    const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
    if (!lead) return;

    document.getElementById('convLeadId').value = lead.id;
    document.getElementById('convAccountName').value = lead.company || lead.lead_name;
    document.getElementById('convContactName').value = lead.lead_name;
    document.getElementById('convContactTitle').value = lead.title || '';
    document.getElementById('convDealTitle').value = lead.company ? `${lead.company} · Commercial Film` : `${lead.lead_name} · Production Deal`;
    document.getElementById('convDealValue').value = lead.annual_revenue > 0 ? lead.annual_revenue : 350000;
    document.getElementById('convDealStage').value = 'meeting';

    openModal('convertLeadModal');
  };

  window.submitConvertLead = async function () {
    const leadId = document.getElementById('convLeadId')?.value;
    const accountName = document.getElementById('convAccountName')?.value.trim();
    const contactName = document.getElementById('convContactName')?.value.trim();
    const contactTitle = document.getElementById('convContactTitle')?.value.trim();
    const dealTitle = document.getElementById('convDealTitle')?.value.trim();
    const dealValue = parseFloat(document.getElementById('convDealValue')?.value) || 0;
    const dealStage = document.getElementById('convDealStage')?.value || 'meeting';

    if (!accountName || !contactName || !dealTitle) {
      showToast('Validation Error', 'Account Name, Contact Name, and Deal Title are required', true);
      return;
    }

    const btn = document.getElementById('convSubmitBtn');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Converting Lead…';
    }

    try {
      const res = await JMOS_API.post(`/leads/${leadId}/convert`, {
        create_account: true,
        account_name: accountName,
        create_contact: true,
        contact_name: contactName,
        contact_title: contactTitle,
        create_deal: true,
        deal_title: dealTitle,
        deal_value: dealValue,
        deal_stage: dealStage,
      });

      closeModal('convertLeadModal');
      closeModal('leadDetailModal');

      showToast('Lead Converted! 🚀', `Created Account "${accountName}", Contact "${contactName}", and Pipeline Deal.`);

      // Refresh Local App States
      await Promise.all([
        window.refreshCrmData(),
        (typeof ensureClients === 'function' ? ensureClients() : Promise.resolve()),
        (typeof ensureProjects === 'function' ? ensureProjects() : Promise.resolve()),
      ]);

      // Refresh pipeline deals state
      const deals = await JMOS_API.get('/deals').catch(() => []);
      if (Array.isArray(deals) && window.JMOS_STATE) {
        JMOS_STATE.pipeline = deals;
      }

      // Switch to Pipeline tab so user sees their new Opportunity in the Kanban!
      window.switchCrmTab('pipeline');
    } catch (err) {
      showToast('Conversion Failed', err.message, true);
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Convert Lead Now';
      }
    }
  };

  /**
   * 8. Create / Edit Lead Modal Handlers
   */
  window.openCreateLeadModal = function () {
    document.getElementById('leadFormId').value = '';
    document.getElementById('leadModalTitle').textContent = 'Create Lead';
    document.getElementById('leadFirstName').value = '';
    document.getElementById('leadLastName').value = '';
    document.getElementById('leadCompany').value = '';
    document.getElementById('leadTitle').value = '';
    document.getElementById('leadEmail').value = '';
    document.getElementById('leadPhone').value = '';
    document.getElementById('leadSource').value = 'Web Research';
    document.getElementById('leadOwner').value = 'Jeota Media';
    document.getElementById('leadRating').value = 'Warm';
    document.getElementById('leadStatus').value = 'New';
    document.getElementById('leadRevenue').value = '';
    document.getElementById('leadIndustry').value = 'Corporate';
    document.getElementById('leadCity').value = 'Nairobi, Kenya';
    document.getElementById('leadNotes').value = '';

    openModal('leadModal');
  };

  window.editActiveLead = function () {
    const leadId = document.getElementById('ldmLeadId')?.value;
    const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
    if (!lead) return;

    closeModal('leadDetailModal');

    document.getElementById('leadFormId').value = lead.id;
    document.getElementById('leadModalTitle').textContent = 'Edit Lead';
    document.getElementById('leadFirstName').value = lead.first_name || '';
    document.getElementById('leadLastName').value = lead.last_name || lead.lead_name;
    document.getElementById('leadCompany').value = lead.company || '';
    document.getElementById('leadTitle').value = lead.title || '';
    document.getElementById('leadEmail').value = lead.email || '';
    document.getElementById('leadPhone').value = lead.phone || '';
    document.getElementById('leadSource').value = lead.lead_source || 'Web Research';
    document.getElementById('leadOwner').value = lead.lead_owner || 'Jeota Media';
    document.getElementById('leadRating').value = lead.rating || 'Warm';
    document.getElementById('leadStatus').value = lead.lead_status || 'New';
    document.getElementById('leadRevenue').value = lead.annual_revenue || '';
    document.getElementById('leadIndustry').value = lead.industry || 'Corporate';
    document.getElementById('leadCity').value = lead.city || 'Nairobi, Kenya';
    document.getElementById('leadNotes').value = lead.notes || '';

    openModal('leadModal');
  };

  window.submitLeadForm = async function () {
    const id = document.getElementById('leadFormId')?.value;
    const firstName = document.getElementById('leadFirstName')?.value.trim();
    const lastName = document.getElementById('leadLastName')?.value.trim();
    const company = document.getElementById('leadCompany')?.value.trim();
    const title = document.getElementById('leadTitle')?.value.trim();
    const email = document.getElementById('leadEmail')?.value.trim();
    const phone = document.getElementById('leadPhone')?.value.trim();
    const source = document.getElementById('leadSource')?.value;
    const owner = document.getElementById('leadOwner')?.value;
    const rating = document.getElementById('leadRating')?.value;
    const status = document.getElementById('leadStatus')?.value;
    const revenue = parseFloat(document.getElementById('leadRevenue')?.value) || 0;
    const industry = document.getElementById('leadIndustry')?.value;
    const city = document.getElementById('leadCity')?.value.trim();
    const notes = document.getElementById('leadNotes')?.value.trim();

    if (!lastName && !company) {
      showToast('Validation Error', 'Please specify at least a Contact Name or Company', true);
      return;
    }

    const payload = {
      first_name: firstName,
      last_name: lastName,
      lead_name: [firstName, lastName].filter(Boolean).join(' ') || company,
      company: company,
      title: title,
      email: email,
      phone: phone,
      lead_source: source,
      lead_owner: owner,
      rating: rating,
      lead_status: status,
      annual_revenue: revenue,
      industry: industry,
      city: city,
      notes: notes,
    };

    const btn = document.getElementById('saveLeadBtn');
    if (btn) btn.disabled = true;

    try {
      if (id) {
        await JMOS_API.put(`/leads/${id}`, payload);
        showToast('Lead Updated', 'Lead details updated successfully.');
      } else {
        await JMOS_API.post('/leads', payload);
        showToast('Lead Captured', 'New prospect added to Lead Generation pipeline.');
      }

      closeModal('leadModal');
      await window.refreshCrmData();
    } catch (err) {
      showToast('Error', err.message, true);
    } finally {
      if (btn) btn.disabled = false;
    }
  };

  window.deleteActiveLead = async function () {
    const leadId = document.getElementById('ldmLeadId')?.value;
    const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
    if (!lead) return;

    const confirmed = typeof window.showConfirmDialog === 'function'
      ? await window.showConfirmDialog({
          title: 'Delete Lead Record?',
          message: `Are you sure you want to delete lead <b>${esc(lead.lead_name)}</b>?`,
          confirmText: 'Delete Lead',
          isDanger: true
        })
      : confirm(`Delete lead "${lead.lead_name}"?`);

    if (confirmed) {
      try {
        await JMOS_API.delete(`/leads/${leadId}`);
        closeModal('leadDetailModal');
        showToast('Lead Deleted', `"${lead.lead_name}" removed from pipeline.`);
        await window.refreshCrmData();
      } catch (err) {
        showToast('Error', err.message, true);
      }
    }
  };

  /**
   * 9. Phone Call Logger
   */
  window.openLogCallModal = function (leadId = null) {
    document.getElementById('callLeadId').value = leadId || '';
    document.getElementById('callType').value = 'Outbound';
    document.getElementById('callPurpose').value = 'Discovery';
    document.getElementById('callOutcome').value = 'Interested';
    document.getElementById('callDuration').value = '15';
    document.getElementById('callNotes').value = '';

    openModal('logLeadCallModal');
  };

  window.openLogCallForActiveLead = function () {
    const leadId = document.getElementById('ldmLeadId')?.value;
    window.openLogCallModal(leadId);
  };

  window.submitLogCall = async function () {
    const leadId = document.getElementById('callLeadId')?.value || null;
    const callType = document.getElementById('callType')?.value;
    const purpose = document.getElementById('callPurpose')?.value;
    const outcome = document.getElementById('callOutcome')?.value;
    const duration = parseInt(document.getElementById('callDuration')?.value, 10) || 0;
    const notes = document.getElementById('callNotes')?.value.trim();

    if (!notes) {
      showToast('Validation Error', 'Please write a brief summary of the call discussion', true);
      return;
    }

    try {
      await JMOS_API.post('/lead-calls', {
        lead_id: leadId ? parseInt(leadId, 10) : null,
        call_type: callType,
        purpose: purpose,
        outcome: outcome,
        duration_minutes: duration,
        notes: notes,
        logged_by: 'Patrick Mwendwa',
      });

      closeModal('logLeadCallModal');
      showToast('Call Logged ✓', `Saved ${callType} ${purpose} call.`);

      await window.refreshCrmData();

      // If active drawer is open, refresh it
      if (leadId) window.openLeadDetailModal(leadId);
    } catch (err) {
      showToast('Error', err.message, true);
    }
  };

  /**
   * 10. Schedule Meeting & Google Meet Generator
   */
  window.openScheduleMeetingModal = function (leadId = null) {
    const lead = leadId ? CRM_STATE.leads.find(l => String(l.id) === String(leadId)) : null;
    
    document.getElementById('schLeadId').value = leadId || '';
    document.getElementById('schTitle').value = lead ? `Discovery & Proposal Session — ${lead.lead_name}` : 'Client Discovery & Proposal Meeting';
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('schDate').value = today;
    document.getElementById('schTime').value = '10:00';
    document.getElementById('schDuration').value = '60';
    document.getElementById('schLocation').value = 'Google Meet';
    document.getElementById('schAttendees').value = lead && lead.email ? lead.email : '';
    document.getElementById('schNotes').value = lead ? `Discussion on ${lead.company || lead.lead_name} commercial video production requirements.` : '';

    openModal('scheduleLeadMeetingModal');
  };

  window.openScheduleMeetingForActiveLead = function () {
    const leadId = document.getElementById('ldmLeadId')?.value;
    window.openScheduleMeetingModal(leadId);
  };

  window.submitScheduleMeeting = async function () {
    const leadId = document.getElementById('schLeadId')?.value || null;
    const title = document.getElementById('schTitle')?.value.trim();
    const date = document.getElementById('schDate')?.value;
    const time = document.getElementById('schTime')?.value;
    const duration = parseInt(document.getElementById('schDuration')?.value, 10) || 60;
    const location = document.getElementById('schLocation')?.value;
    const attendees = document.getElementById('schAttendees')?.value.trim();
    const notes = document.getElementById('schNotes')?.value.trim();

    if (!title || !date || !time) {
      showToast('Validation Error', 'Title, Date, and Time are required', true);
      return;
    }

    const startTime = `${date}T${time}:00`;
    const startDate = new Date(startTime);
    const endDate = new Date(startDate.getTime() + duration * 60000);

    const btn = document.getElementById('saveSchMeetingBtn');
    if (btn) btn.disabled = true;

    try {
      await JMOS_API.post('/calendar/events', {
        title: title,
        description: notes,
        event_type: 'meeting',
        start_time: startTime,
        end_time: endDate.toISOString(),
        location: location,
        attendees: attendees,
        related_type: leadId ? 'Lead' : null,
        related_id: leadId ? parseInt(leadId, 10) : null,
        generate_meet: (location === 'Google Meet'),
      });

      closeModal('scheduleLeadMeetingModal');
      showToast('Meeting Scheduled! 📅', 'Added to Operations Calendar with Google Meet link.');

      await window.refreshCrmData();
      if (leadId) window.openLeadDetailModal(leadId);
    } catch (err) {
      showToast('Error', err.message, true);
    } finally {
      if (btn) btn.disabled = false;
    }
  };

  /**
   * 11. Render Contacts, Accounts, Calls & Meetings Tables
   */
  window.renderContactsTable = function () {
    const tbody = document.getElementById('crmContactsTableBody');
    if (!tbody) return;

    if (CRM_STATE.contacts.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="padding:30px;text-align:center;color:var(--muted)">No contacts recorded yet. Convert a lead to automatically create one!</td></tr>`;
      return;
    }

    tbody.innerHTML = CRM_STATE.contacts.map(c => `
      <tr>
        <td>
          <div style="font-weight:700;color:var(--ink);font-size:13px">${esc(c.contact_name)}</div>
        </td>
        <td>
          <div style="font-weight:600;color:var(--ink)">${esc(c.company_name || '—')}</div>
        </td>
        <td>
          <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">${esc(c.title || 'Stakeholder')}</span>
        </td>
        <td>
          ${c.email ? `<a href="mailto:${esc(c.email)}" style="color:var(--red);text-decoration:none">${esc(c.email)}</a>` : '—'}
        </td>
        <td>
          ${c.phone ? `<span class="mono">${esc(c.phone)}</span>` : '—'}
        </td>
        <td>
          <span style="font-size:12px;color:var(--muted)">${esc(c.notes || 'Converted Contact')}</span>
        </td>
        <td style="text-align:right">
          <button type="button" class="linkbtn" onclick="window.openLogCallModal()" style="font-size:11.5px">Call</button>
        </td>
      </tr>
    `).join('');
  };

  window.renderAccountsTable = function () {
    const tbody = document.getElementById('crmAccountsTableBody');
    if (!tbody) return;

    const clients = (window.JMOS_STATE && JMOS_STATE.clients) ? JMOS_STATE.clients : [];

    if (clients.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="padding:30px;text-align:center;color:var(--muted)">No corporate accounts found.</td></tr>`;
      return;
    }

    tbody.innerHTML = clients.map(cl => `
      <tr>
        <td>
          <div style="font-weight:700;color:var(--ink);font-size:13px;cursor:pointer" onclick="openClientDetailModal(${cl.id})">${esc(cl.client_name)}</div>
        </td>
        <td>
          <span class="badge" style="background:rgba(43,110,138,0.12);color:#2B6E8A;font-size:11px">${esc(cl.client_type || 'Corporate')}</span>
        </td>
        <td>
          <div style="font-weight:500;color:var(--ink)">${esc(cl.contact_person || '—')}</div>
        </td>
        <td>
          <div style="font-size:11.5px;color:var(--muted)">${esc(cl.owner || 'Barny Kiome')}</div>
        </td>
        <td>
          <span class="badge" style="background:var(--panel-2);color:var(--ink)">${cl.projects || 0} active</span>
        </td>
        <td>
          <span class="pill tint-green">${esc(cl.project_status || 'Active')}</span>
        </td>
        <td>
          <span class="mono" style="font-weight:700;color:var(--ink)">${formatMoney(cl.project_value)}</span>
        </td>
        <td style="text-align:right">
          <button type="button" class="btn" onclick="openClientDetailModal(${cl.id})" style="font-size:11.5px;padding:3px 8px">View Account</button>
        </td>
      </tr>
    `).join('');
  };

  window.renderCallsTable = function () {
    const tbody = document.getElementById('crmCallsTableBody');
    if (!tbody) return;

    if (CRM_STATE.calls.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="padding:30px;text-align:center;color:var(--muted)">No phone calls logged yet.</td></tr>`;
      return;
    }

    tbody.innerHTML = CRM_STATE.calls.map(c => `
      <tr>
        <td>
          <div style="font-size:11.5px;color:var(--ink)">${c.call_time ? new Date(c.call_time).toLocaleString() : 'Recent'}</div>
        </td>
        <td>
          <span class="badge" style="font-size:10.5px;background:${c.call_type === 'Outbound' ? 'rgba(197,37,35,0.1)' : 'rgba(43,110,138,0.1)'};color:${c.call_type === 'Outbound' ? 'var(--red)' : '#2B6E8A'}">
            ${esc(c.call_type)}
          </span>
        </td>
        <td>
          <div style="font-weight:700;color:var(--ink);font-size:12.5px">${esc(c.lead ? c.lead.lead_name : (c.client ? c.client.client_name : 'Prospect'))}</div>
        </td>
        <td>
          <span style="font-weight:600;font-size:12px">${esc(c.purpose)}</span>
        </td>
        <td>
          <span class="pill tint-amber" style="font-size:11px">${esc(c.outcome || 'Logged')}</span>
        </td>
        <td>
          <span class="mono" style="font-size:11.5px">${c.duration_minutes || 0} mins</span>
        </td>
        <td>
          <span style="font-size:11.5px;color:var(--muted)">${esc(c.logged_by || 'Team')}</span>
        </td>
        <td>
          <div style="font-size:11.5px;color:var(--ink);max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(c.notes || '—')}</div>
        </td>
      </tr>
    `).join('');
  };

  window.renderMeetingsTable = function () {
    const tbody = document.getElementById('crmMeetingsTableBody');
    if (!tbody) return;

    if (CRM_STATE.meetings.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" style="padding:30px;text-align:center;color:var(--muted)">No discovery meetings scheduled yet.</td></tr>`;
      return;
    }

    tbody.innerHTML = CRM_STATE.meetings.map(m => `
      <tr>
        <td>
          <div style="font-weight:700;color:var(--ink);font-size:13px">${esc(m.title)}</div>
        </td>
        <td>
          <div style="font-size:11.5px;color:var(--ink)">${m.start_time ? new Date(m.start_time).toLocaleString() : 'Scheduled'}</div>
        </td>
        <td>
          <div style="font-size:12px;color:var(--muted)">${esc(m.attendees || 'Client Stakeholders')}</div>
        </td>
        <td>
          <span class="badge" style="background:rgba(43,138,90,0.1);color:#2B8A5A;font-weight:600;font-size:11px">${esc(m.location || 'Google Meet')}</span>
        </td>
        <td>
          <span style="font-size:11.5px;color:var(--muted)">${esc(m.attendees || 'Team')}</span>
        </td>
        <td>
          <span class="pill tint-green">${esc(m.status || 'Confirmed')}</span>
        </td>
        <td style="text-align:right">
          ${m.meet_link ? `<a href="${esc(m.meet_link)}" target="_blank" class="btn primary" style="font-size:11px;padding:3px 8px;text-decoration:none">Join Video Meet 📹</a>` : ''}
        </td>
      </tr>
    `).join('');
  };

  /**
   * 12. Contact Form Management
   */
  window.openCreateContactModal = function () {
    const nameInput = document.getElementById('ctName');
    if (nameInput) nameInput.value = '';
    const companyInput = document.getElementById('ctCompany');
    if (companyInput) companyInput.value = '';
    const titleInput = document.getElementById('ctTitle');
    if (titleInput) titleInput.value = '';
    const ownerInput = document.getElementById('ctOwner');
    if (ownerInput) ownerInput.value = 'Jeota Media';
    const emailInput = document.getElementById('ctEmail');
    if (emailInput) emailInput.value = '';
    const phoneInput = document.getElementById('ctPhone');
    if (phoneInput) phoneInput.value = '';
    const notesInput = document.getElementById('ctNotes');
    if (notesInput) notesInput.value = '';

    openModal('contactModal');
  };

  window.submitContactForm = async function () {
    const name = document.getElementById('ctName')?.value.trim();
    if (!name) {
      showToast('Validation Error', 'Contact name is required.', true);
      return;
    }

    const payload = {
      contact_name: name,
      company_name: document.getElementById('ctCompany')?.value.trim() || null,
      title: document.getElementById('ctTitle')?.value.trim() || null,
      owner: document.getElementById('ctOwner')?.value.trim() || 'Jeota Media',
      email: document.getElementById('ctEmail')?.value.trim() || null,
      phone: document.getElementById('ctPhone')?.value.trim() || null,
      notes: document.getElementById('ctNotes')?.value.trim() || null
    };

    const saveBtn = document.getElementById('saveContactBtn');
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving...';
    }

    try {
      await JMOS_API.post('/contacts', payload);
      closeModal('contactModal');
      showToast('Contact Saved ✓', `Added "${name}" to contacts directory.`);
      await window.refreshCrmData();
    } catch (err) {
      showToast('Error', err.message, true);
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Contact';
      }
    }
  };

  /**
   * 13. Export Leads to CSV
   */
  window.exportLeadsCsv = function () {
    if (CRM_STATE.leads.length === 0) {
      showToast('Export', 'No lead records to export.', true);
      return;
    }

    const headers = ['Lead ID', 'Lead Name', 'Company', 'Title', 'Email', 'Phone', 'Source', 'Status', 'Rating', 'Owner', 'Est. Value', 'City', 'Converted'];
    const rows = CRM_STATE.leads.map(l => [
      l.id,
      `"${(l.lead_name || '').replace(/"/g, '""')}"`,
      `"${(l.company || '').replace(/"/g, '""')}"`,
      `"${(l.title || '').replace(/"/g, '""')}"`,
      `"${(l.email || '').replace(/"/g, '""')}"`,
      `"${(l.phone || '').replace(/"/g, '""')}"`,
      `"${(l.lead_source || '').replace(/"/g, '""')}"`,
      `"${(l.lead_status || '').replace(/"/g, '""')}"`,
      `"${(l.rating || '').replace(/"/g, '""')}"`,
      `"${(l.lead_owner || '').replace(/"/g, '""')}"`,
      l.annual_revenue || 0,
      `"${(l.city || '').replace(/"/g, '""')}"`,
      l.is_converted ? 'Yes' : 'No'
    ]);

    const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `JMOS_Leads_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  /**
   * Initialize on DOM Ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initCrmModule);
  } else {
    window.initCrmModule();
  }
})();
