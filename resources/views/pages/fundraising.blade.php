<!-- ==========================================================================
     JMOS — View: Fundraising & Impact Grants (Leads for Impact Projects)
     ========================================================================== -->
<section class="view" data-view="fundraising" hidden>
  <!-- Page Header -->
  <div class="page-head" style="margin-bottom:16px">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-size:11px;font-weight:700">IMPACT &amp; GRANT FUNDING</span>
        <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">JEOTA FUNDRAISING MASTER</span>
      </div>
      <h1 class="pt">Fundraising &amp; Impact Grants</h1>
      <p>Track grant opportunities, fellowships, climate impact funds, open calls, and institutional partners.</p>
    </div>
    <div class="head-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" onclick="window.refreshFundraising()" title="Refresh fundraising records">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn" onclick="window.exportFundraisingCsv()" title="Export grants data to CSV">
        <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>Export CSV
      </button>
      <button type="button" class="btn primary" onclick="window.openAddFundraisingModal()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add Grant / Call
      </button>
    </div>
  </div>

  <!-- KPI Quick Stats Row -->
  <div class="crm-kpis-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;margin-bottom:20px">
    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Total Funding Pipeline</div>
      <div class="mono" id="frKpiTotalPipeline" style="font-size:22px;font-weight:700;color:var(--ink);margin-top:4px">KES 0</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Open Grants &amp; Calls</span>
        <span class="badge" id="frKpiOpenBadge" style="background:rgba(43,138,90,0.12);color:#2B8A5A;font-size:10px;font-weight:700">Active</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="frKpiOpenCallsCount" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">opportunities</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Submitted / Applied</div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="frKpiSubmittedCount" style="font-size:24px;font-weight:700;color:var(--green)">0</span>
        <span style="font-size:12px;color:var(--muted)">proposals sent</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Partnership Leads</div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="frKpiPartnershipsCount" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">foundations/CBOs</span>
      </div>
    </div>
  </div>

  <!-- Sub-Navigation Category Tabs -->
  <div class="crm-module-tabs-bar" style="display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--line);margin-bottom:18px;overflow-x:auto;padding-bottom:2px">
    <button type="button" class="crm-nav-tab active" data-fr-tab="open_calls" onclick="window.switchFundraisingTab('open_calls')">
      <svg viewBox="0 0 24 24" width="15" height="15"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span>Open Calls &amp; Grants</span>
      <span class="crm-tab-badge" id="frTabBadgeOpen">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-fr-tab="partnerships" onclick="window.switchFundraisingTab('partnerships')">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Partnership Exploration (Foundations &amp; CBOs)</span>
      <span class="crm-tab-badge" id="frTabBadgePartnerships">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-fr-tab="all" onclick="window.switchFundraisingTab('all')">
      <svg viewBox="0 0 24 24" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>
      <span>Master Directory</span>
      <span class="crm-tab-badge" id="frTabBadgeAll">0</span>
    </button>
  </div>

  <!-- Search & Filter Controls -->
  <div style="background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:10px 14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:260px">
      <div style="position:relative;flex:1">
        <input type="text" id="frSearchInput" placeholder="Search funding organization, program title, partner..." style="font-size:12px;padding:6px 10px 6px 28px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink);width:100%" oninput="window.onFrSearchChange(this.value)">
        <svg viewBox="0 0 24 24" width="13" height="13" style="position:absolute;left:9px;top:8px;color:var(--muted)" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px">
      <select id="frStatusFilter" style="font-size:12px;padding:6px 10px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" onchange="window.applyFundraisingFilters()">
        <option value="all">-- All Statuses --</option>
        <option value="Identified">Identified / Open</option>
        <option value="In Progress">In Progress</option>
        <option value="Submitted">Submitted / Applied</option>
        <option value="Won / Awarded">Won / Awarded</option>
        <option value="Missed">Missed Deadline</option>
      </select>
    </div>
  </div>

  <!-- Main Grants / Opportunities Table -->
  <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
    <div class="tablewrap" style="max-height:680px;overflow-y:auto">
      <table>
        <thead>
          <tr>
            <th>Organization / Grantmaker</th>
            <th>Program / Particulars</th>
            <th>Funding Value (KES)</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Partners / Notes</th>
            <th style="text-align:right">Action</th>
          </tr>
        </thead>
        <tbody id="fundraisingTableBody">
          <tr>
            <td colspan="7" style="padding:40px;text-align:center;color:var(--muted)">Loading fundraising opportunities…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
