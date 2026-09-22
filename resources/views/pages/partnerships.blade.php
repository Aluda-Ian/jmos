<!-- ==========================================================================
     JMOS — View: Partnership Exploration (Separated Institutional & CBO Master)
     ========================================================================== -->
<section class="view" data-view="partnerships" hidden>
  <!-- Page Header -->
  <div class="page-head" style="margin-bottom:16px">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="badge" style="background:rgba(124,58,237,0.15);color:#7C3AED;font-size:11px;font-weight:700">STRATEGIC ALLIANCES</span>
        <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">JEOTA FUNDRAISING MASTER</span>
      </div>
      <h1 class="pt">Partnership Exploration</h1>
      <p>Institutional outreach, CBOs, cooperatives, corporate institutions &amp; academia for strategic co-production and grants.</p>
    </div>
    <div class="head-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" onclick="window.refreshPartnerships()" title="Refresh partnership records">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn" onclick="window.openImportFundraisingModal('partnerships')" title="Import Partnership Entities from CSV/Excel">
        <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Import CSV
      </button>
      <button type="button" class="btn" onclick="window.exportPartnershipsCsv()" title="Export partnerships data to CSV">
        <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>Export CSV
      </button>
      <button type="button" class="btn primary" onclick="window.openAddPartnershipModal()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>+ Add Partner / Entity
      </button>
    </div>
  </div>

  <!-- KPI Quick Stats Row -->
  <div class="crm-kpis-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(190px, 1fr));gap:12px;margin-bottom:20px">
    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Total Partner Entities</div>
      <div class="mono" id="partKpiTotalCount" style="font-size:22px;font-weight:700;color:var(--ink);margin-top:4px">0</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">In master directory</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Grantmakers &amp; CBOs</span>
        <span class="badge" style="background:rgba(43,138,90,0.12);color:#2B8A5A;font-size:10px;font-weight:700">Section A</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="partKpiCboCount" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">foundations</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Corporate Institutions</span>
        <span class="badge" style="background:rgba(197,37,35,0.12);color:var(--red);font-size:10px;font-weight:700">Section C</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="partKpiCorporateCount" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">institutions</span>
      </div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:14px 16px;box-shadow:var(--shadow-sm)">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Associations &amp; Academia</span>
        <span class="badge" style="background:rgba(59,130,246,0.12);color:#2563EB;font-size:10px;font-weight:700">B &amp; D</span>
      </div>
      <div style="display:flex;align-items:baseline;gap:6px;margin-top:4px">
        <span id="partKpiAssocAcademiaCount" style="font-size:24px;font-weight:700;color:var(--ink)">0</span>
        <span style="font-size:12px;color:var(--muted)">coops &amp; universities</span>
      </div>
    </div>
  </div>

  <!-- Sub-Navigation Entity Section Tabs (Matching Master File) -->
  <div class="crm-module-tabs-bar" style="display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--line);margin-bottom:18px;overflow-x:auto;padding-bottom:2px">
    <button type="button" class="crm-nav-tab active" data-part-tab="all" onclick="window.switchPartnershipTab('all')">
      <svg viewBox="0 0 24 24" width="15" height="15"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg>
      <span>All Entities</span>
      <span class="crm-tab-badge" id="partTabBadgeAll">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-part-tab="Grantmakers / CBOs" onclick="window.switchPartnershipTab('Grantmakers / CBOs')">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      <span>a. Grantmakers / CBOs</span>
      <span class="crm-tab-badge" id="partTabBadgeCbo">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-part-tab="Associations / Cooperatives" onclick="window.switchPartnershipTab('Associations / Cooperatives')">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>b. Associations / Cooperatives</span>
      <span class="crm-tab-badge" id="partTabBadgeAssoc">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-part-tab="Corporate Institutions" onclick="window.switchPartnershipTab('Corporate Institutions')">
      <svg viewBox="0 0 24 24" width="15" height="15"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      <span>c. Corporate Institutions</span>
      <span class="crm-tab-badge" id="partTabBadgeCorporate">0</span>
    </button>
    <button type="button" class="crm-nav-tab" data-part-tab="Academia / Educational Institutions" onclick="window.switchPartnershipTab('Academia / Educational Institutions')">
      <svg viewBox="0 0 24 24" width="15" height="15"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      <span>d. Academia / Educational Institutions</span>
      <span class="crm-tab-badge" id="partTabBadgeAcademia">0</span>
    </button>
  </div>

  <!-- Search & Filter Controls -->
  <div style="background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:10px 14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:260px">
      <div style="position:relative;flex:1">
        <input type="text" id="partSearchInput" placeholder="Search entity name, nature, website, comments..." style="font-size:12px;padding:6px 10px 6px 28px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink);width:100%" oninput="window.onPartSearchChange(this.value)">
        <svg viewBox="0 0 24 24" width="13" height="13" style="position:absolute;left:9px;top:8px;color:var(--muted)" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px">
      <select id="partNatureFilter" style="font-size:12px;padding:6px 10px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" onchange="window.applyPartnershipFilters()">
        <option value="all">-- All Nature Types --</option>
        <option value="Grants Application">Grants Application</option>
        <option value="Partnership Invitation">Partnership Invitation</option>
      </select>

      <select id="partStatusFilter" style="font-size:12px;padding:6px 10px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink)" onchange="window.applyPartnershipFilters()">
        <option value="all">-- All Statuses --</option>
        <option value="Pending">Pending</option>
        <option value="In Progress">In Progress</option>
        <option value="Submitted">Submitted</option>
        <option value="Closed">Closed</option>
      </select>
    </div>
  </div>

  <!-- Partnership Exploration Master Table (Matching Google Sheet Structure) -->
  <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
    <div class="tablewrap" style="max-height:680px;overflow-y:auto">
      <table>
        <thead>
          <tr>
            <th style="width:45px;text-align:center">#</th>
            <th>ENTITY NAME</th>
            <th>SECTION / CLASSIFICATION</th>
            <th>NATURE</th>
            <th>WEBSITE</th>
            <th>STATUS</th>
            <th>COMMENTS</th>
            <th style="text-align:right">ACTION</th>
          </tr>
        </thead>
        <tbody id="partnershipsTableBody">
          <tr>
            <td colspan="8" style="padding:40px;text-align:center;color:var(--muted)">Loading partnership exploration entities…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
