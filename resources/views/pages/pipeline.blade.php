<!-- ==========================================================================
     JMOS — View: Lead Generation, CRM Journey & Sales Pipeline
     ========================================================================== -->
<section class="view" data-view="pipeline" hidden>
  <!-- Page Header -->
  <div class="page-head" style="margin-bottom:16px">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:11px;font-weight:700">ZOHO CRM WORKFLOW</span>
        <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">LEAD TO CONVERSION</span>
      </div>
      <h1 class="pt">Lead Generation &amp; Pipeline</h1>
      <p>Capture leads → Qualify with calls &amp; meetings → Convert to Account, Contact &amp; Deal → Advance Pipeline to Won.</p>
    </div>
    <div class="head-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" id="crmRefreshBtn" onclick="window.refreshCrmData()" title="Refresh CRM records">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn" id="crmLogCallTopBtn" onclick="window.openLogCallModal()" title="Log a phone call">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Log Call
      </button>
      <button type="button" class="btn" id="crmScheduleMeetingTopBtn" onclick="window.openScheduleMeetingModal()" title="Schedule a discovery meeting">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Schedule Meeting
      </button>
      <button type="button" class="btn" id="addDealBtn" onclick="openModal('dealModal')" data-modal-open="dealModal" title="Create new pipeline deal">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>New Deal
      </button>
      <button type="button" class="btn primary" id="addLeadBtn" onclick="window.openCreateLeadModal()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Create Lead
      </button>
    </div>
  </div>

  <!-- KPI Quick Stats Row -->
  <div class="crm-kpis-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:20px">
    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Total Leads</span>
        <span class="badge" id="crmKpiHotBadge" style="background:rgba(197,37,35,0.12);color:var(--red);font-size:10px;font-weight:700">0 Hot</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="crmKpiTotalLeads" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span id="crmKpiActiveLeads" style="font-size:12px;color:var(--muted)">active</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Pipeline Potential</div>
      <div class="mono" id="crmKpiPipelineValue" style="font-size:22px;font-weight:700;color:var(--ink);margin-top:4px">KES 0</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Conversion Rate</div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="crmKpiConversionRate" style="font-size:24px;font-weight:700;color:var(--green)">0%</span>
        <span id="crmKpiConvertedCount" style="font-size:12px;color:var(--muted)">converted</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Calls &amp; Touchpoints</div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="crmKpiCallsLogged" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">calls logged</span>
      </div>
    </div>
  </div>

  <!-- Zoho CRM Modules Navigation Tabs -->
  <div class="crm-module-tabs-bar" style="display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--line);margin-bottom:18px;overflow-x:auto;padding-bottom:2px">
    <button type="button" class="crm-nav-tab active" data-crm-tab="leads">
      <svg viewBox="0 0 24 24" width="15" height="15"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/><circle cx="19" cy="11" r="2"/><circle cx="5" cy="11" r="2"/></svg>
      <span>Leads</span>
      <span class="crm-tab-badge" id="crmTabBadgeLeads">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-crm-tab="pipeline">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M4 20V10M10 20V4M16 20v-7M22 20v-11"/></svg>
      <span>Deals Pipeline</span>
      <span class="crm-tab-badge" id="crmTabBadgeDeals">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-crm-tab="contacts">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Contacts</span>
      <span class="crm-tab-badge" id="crmTabBadgeContacts">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-crm-tab="accounts">
      <svg viewBox="0 0 24 24" width="15" height="15"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      <span>Accounts (Clients)</span>
      <span class="crm-tab-badge" id="crmTabBadgeAccounts">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-crm-tab="calls">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      <span>Calls Made</span>
      <span class="crm-tab-badge" id="crmTabBadgeCalls">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-crm-tab="meetings">
      <svg viewBox="0 0 24 24" width="15" height="15"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span>Meetings</span>
      <span class="crm-tab-badge" id="crmTabBadgeMeetings">0</span>
    </button>
  </div>

  <!-- TAB PANE 1: LEADS (ZOHO CRM WORKFLOW & FILTER SIDEBAR) -->
  <div class="crm-tab-pane" id="crmPaneLeads">
    <div class="zoho-crm-container" style="display:grid;grid-template-columns:250px 1fr;gap:18px;align-items:start">
      
      <!-- Zoho-style Left Filter Sidebar -->
      <aside class="zoho-filter-sidebar" style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:16px;box-shadow:var(--shadow-sm)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <h4 style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--ink);margin:0;display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Filter Leads
          </h4>
          <button type="button" class="linkbtn" onclick="window.resetLeadFilters()" style="font-size:11px">Reset</button>
        </div>

        <!-- Search in filters -->
        <div style="position:relative;margin-bottom:16px">
          <input type="text" id="leadSearchInput" placeholder="Search leads by name, company..." style="width:100%;font-size:12px;padding:7px 10px 7px 28px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" oninput="window.onLeadSearchChange(this.value)">
          <svg viewBox="0 0 24 24" width="13" height="13" style="position:absolute;left:9px;top:9px;color:var(--muted)" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>

        <!-- System Views -->
        <div class="zoho-filter-group" style="margin-bottom:16px">
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">System Defined Views</div>
          <label class="zoho-filter-radio"><input type="radio" name="leadStatusFilter" value="all" checked onchange="window.applyLeadFilter()"> <span>All Leads</span></label>
          <label class="zoho-filter-radio"><input type="radio" name="leadStatusFilter" value="active" onchange="window.applyLeadFilter()"> <span>Active Prospects</span></label>
          <label class="zoho-filter-radio"><input type="radio" name="leadStatusFilter" value="Qualified" onchange="window.applyLeadFilter()"> <span>Qualified Leads</span></label>
          <label class="zoho-filter-radio"><input type="radio" name="leadStatusFilter" value="converted" onchange="window.applyLeadFilter()"> <span>Converted Clients</span></label>
          <label class="zoho-filter-radio"><input type="radio" name="leadStatusFilter" value="New" onchange="window.applyLeadFilter()"> <span>New / Untouched</span></label>
        </div>

        <!-- Rating Filter -->
        <div class="zoho-filter-group" style="margin-bottom:16px">
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Lead Rating</div>
          <label class="zoho-filter-check"><input type="checkbox" class="lead-rating-chk" value="Hot" onchange="window.applyLeadFilter()"> <span class="badge" style="background:rgba(197,37,35,0.12);color:var(--red);font-size:10px">Hot 🔥</span></label>
          <label class="zoho-filter-check"><input type="checkbox" class="lead-rating-chk" value="Warm" onchange="window.applyLeadFilter()"> <span class="badge" style="background:rgba(217,119,6,0.12);color:#D97706;font-size:10px">Warm ⚡</span></label>
          <label class="zoho-filter-check"><input type="checkbox" class="lead-rating-chk" value="Cold" onchange="window.applyLeadFilter()"> <span class="badge" style="background:rgba(100,116,139,0.12);color:#64748B;font-size:10px">Cold ❄️</span></label>
        </div>

        <!-- Lead Source Filter -->
        <div class="zoho-filter-group" style="margin-bottom:16px">
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Lead Source</div>
          <select id="leadSourceFilter" style="width:100%;font-size:12px;padding:6px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" onchange="window.applyLeadFilter()">
            <option value="">-- All Sources --</option>
            <option value="Web Research">Web Research</option>
            <option value="LinkedIn">LinkedIn</option>
            <option value="Referral">Referral</option>
            <option value="Cold Outreach">Cold Outreach</option>
            <option value="Website">Website</option>
            <option value="Campaign">Campaign</option>
            <option value="Partner">Partner</option>
          </select>
        </div>

        <!-- Lead Owner Filter -->
        <div class="zoho-filter-group">
          <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Lead Owner</div>
          <select id="leadOwnerFilter" style="width:100%;font-size:12px;padding:6px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" onchange="window.applyLeadFilter()">
            <option value="">-- All Owners --</option>
            <option value="Jeota Media">Jeota Media</option>
            <option value="Barny Kiome">Barny Kiome</option>
            <option value="Patrick Mwendwa">Patrick Mwendwa</option>
            <option value="Lesley Chacha">Lesley Chacha</option>
            <option value="Amos Muthama">Amos Muthama</option>
          </select>
        </div>
      </aside>

      <!-- Main Leads Table & Action Controls -->
      <div class="zoho-main-content">
        <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
          
          <!-- Table Toolbar -->
          <div style="padding:12px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div style="display:flex;align-items:center;gap:12px">
              <span style="font-size:13px;font-weight:700;color:var(--ink)">Lead Records</span>
              <span class="badge" id="crmLeadsCountPill" style="font-size:11px;background:var(--panel-2);color:var(--muted)">0 Records</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <button type="button" class="btn" onclick="window.exportLeadsCsv()" style="font-size:11.5px;padding:4px 10px">
                <svg viewBox="0 0 24 24" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>Export CSV
              </button>
              <button type="button" class="btn primary" onclick="window.openCreateLeadModal()" style="font-size:11.5px;padding:4px 12px">
                <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 5v14M5 12h14"/></svg>Create Lead
              </button>
            </div>
          </div>

          <!-- Table Wrapper -->
          <div class="tablewrap" style="max-height:680px;overflow-y:auto">
            <table class="crm-table">
              <thead>
                <tr>
                  <th style="width:36px;text-align:center"><input type="checkbox" id="selectAllLeadsChk" title="Select all" onchange="window.toggleSelectAllLeads(this.checked)"></th>
                  <th>Lead Name</th>
                  <th>Company / Brand</th>
                  <th>Email Address</th>
                  <th>Phone Number</th>
                  <th>Lead Source</th>
                  <th>Owner</th>
                  <th>Est. Value</th>
                  <th>Status</th>
                  <th style="text-align:right">Actions</th>
                </tr>
              </thead>
              <tbody id="crmLeadsTableBody">
                <tr>
                  <td colspan="10" style="padding:40px;text-align:center;color:var(--muted)">Loading leads from database…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TAB PANE 2: DEALS PIPELINE (KANBAN BOARD) -->
  <div class="crm-tab-pane" id="crmPanePipeline" style="display:none">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <div>
        <h3 style="font-size:16px;font-weight:700;margin:0;color:var(--ink)">Sales Pipeline Kanban</h3>
        <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">Lead → Meeting → Proposal sent → Negotiation → Won. Drag or click deal cards to advance stages.</p>
      </div>
      <button type="button" class="btn primary" onclick="openModal('dealModal')">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New Deal
      </button>
    </div>
    <div class="board" id="pipelineBoard" style="grid-template-columns:repeat(5, 1fr)">
      <div style="grid-column:span 5;padding:40px;text-align:center;color:var(--muted)">Loading pipeline deals…</div>
    </div>
  </div>

  <!-- TAB PANE 3: CONTACTS DIRECTORY -->
  <div class="crm-tab-pane" id="crmPaneContacts" style="display:none">
    <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
      <div style="padding:14px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
          <h3 style="font-size:15px;font-weight:700;margin:0;color:var(--ink)">Stakeholders &amp; Contacts Directory</h3>
          <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">Individual contacts created from converted leads or client profiles.</p>
        </div>
        <button type="button" class="btn primary" onclick="window.openCreateContactModal()" style="font-size:12px;padding:6px 12px">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 5v14M5 12h14"/></svg>Add Contact
        </button>
      </div>
      <div class="tablewrap">
        <table>
          <thead>
            <tr>
              <th>Contact Name</th>
              <th>Company / Account</th>
              <th>Designation / Title</th>
              <th>Email Address</th>
              <th>Phone</th>
              <th>Lead Source / Notes</th>
              <th style="text-align:right">Action</th>
            </tr>
          </thead>
          <tbody id="crmContactsTableBody">
            <tr><td colspan="7" style="padding:30px;text-align:center;color:var(--muted)">Loading contacts…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB PANE 4: ACCOUNTS (CLIENTS) -->
  <div class="crm-tab-pane" id="crmPaneAccounts" style="display:none">
    <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
      <div style="padding:14px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
          <h3 style="font-size:15px;font-weight:700;margin:0;color:var(--ink)">Corporate Accounts &amp; Clients</h3>
          <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">Central accounts directory converted from qualified leads.</p>
        </div>
        <button type="button" class="btn primary" onclick="openModal('clientModal')" style="font-size:12px;padding:6px 12px">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 5v14M5 12h14"/></svg>Add Account
        </button>
      </div>
      <div class="tablewrap">
        <table>
          <thead>
            <tr>
              <th>Account Name</th>
              <th>Industry Type</th>
              <th>Primary Contact</th>
              <th>Account Owner</th>
              <th>Active Projects</th>
              <th>Status</th>
              <th>Total Value (KES)</th>
              <th style="text-align:right">Action</th>
            </tr>
          </thead>
          <tbody id="crmAccountsTableBody">
            <tr><td colspan="8" style="padding:30px;text-align:center;color:var(--muted)">Loading accounts…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB PANE 5: CALLS MADE & LOGGED -->
  <div class="crm-tab-pane" id="crmPaneCalls" style="display:none">
    <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
      <div style="padding:14px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
          <h3 style="font-size:15px;font-weight:700;margin:0;color:var(--ink)">Phone Calls &amp; Touchpoints Log</h3>
          <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">Track outbound discovery calls, follow-ups, durations, and outcomes.</p>
        </div>
        <button type="button" class="btn primary" onclick="window.openLogCallModal()" style="font-size:12px;padding:6px 12px">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Log Call
        </button>
      </div>
      <div class="tablewrap">
        <table>
          <thead>
            <tr>
              <th>Date &amp; Time</th>
              <th>Type</th>
              <th>Lead / Client</th>
              <th>Purpose</th>
              <th>Outcome</th>
              <th>Duration</th>
              <th>Logged By</th>
              <th>Call Notes</th>
            </tr>
          </thead>
          <tbody id="crmCallsTableBody">
            <tr><td colspan="8" style="padding:30px;text-align:center;color:var(--muted)">Loading call logs…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- TAB PANE 6: MEETINGS -->
  <div class="crm-tab-pane" id="crmPaneMeetings" style="display:none">
    <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
      <div style="padding:14px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
          <h3 style="font-size:15px;font-weight:700;margin:0;color:var(--ink)">Scheduled Discovery &amp; Pitch Meetings</h3>
          <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">Integrated with Operations Calendar and auto-generated Google Meet video rooms.</p>
        </div>
        <button type="button" class="btn primary" onclick="window.openScheduleMeetingModal()" style="font-size:12px;padding:6px 12px">
          <svg viewBox="0 0 24 24" width="13" height="13"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Schedule Meeting
        </button>
      </div>
      <div class="tablewrap">
        <table>
          <thead>
            <tr>
              <th>Meeting Title</th>
              <th>Date &amp; Time</th>
              <th>Related Prospect / Client</th>
              <th>Location / Meet</th>
              <th>Attendees</th>
              <th>Status</th>
              <th style="text-align:right">Action</th>
            </tr>
          </thead>
          <tbody id="crmMeetingsTableBody">
            <tr><td colspan="7" style="padding:30px;text-align:center;color:var(--muted)">Loading scheduled meetings…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</section>
