<!-- ==========================================================================
     JMOS — View: Quotations & Commercial Proposals
     ========================================================================== -->
<section class="view" data-view="quotes" hidden data-perm="owner finance sales manager">
  <div class="page-head">
    <div>
      <h1 class="pt">Quotations</h1>
      <p>Draft commercial scopes, email proposals to clients, track approvals &amp; convert to invoices.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" onclick="showView('budget')" title="Open Production Budget Calculator">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Create Quotation (Budget)
      </button>
    </div>
  </div>

  <!-- Quotation Summary Metrics -->
  <div class="kpis" style="margin-bottom:18px">
    <div class="kpi">
      <div class="lbl">Total Pipeline Quotations</div>
      <div class="val mono" id="qKpiTotalCount">0</div>
      <div class="sub">Commercial proposals drafted</div>
    </div>
    <div class="kpi">
      <div class="lbl">Total Value Proposed</div>
      <div class="val mono" id="qKpiTotalValue" style="color:var(--ink)">KES 0</div>
      <div class="sub">Gross active quotations</div>
    </div>
    <div class="kpi">
      <div class="lbl">Approved &amp; Accepted</div>
      <div class="val mono" id="qKpiApprovedValue" style="color:var(--green)">KES 0</div>
      <div class="sub">Ready for deposit invoice</div>
    </div>
    <div class="kpi">
      <div class="lbl">Converted to Invoices</div>
      <div class="val mono" id="qKpiInvoicedValue" style="color:var(--red)">KES 0</div>
      <div class="sub">Live billing active</div>
    </div>
  </div>

  <!-- Filter & Search Toolbar -->
  <div class="card" style="padding:14px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <!-- Search Input -->
      <div class="search" style="max-width:320px;flex:1;min-width:220px">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" id="quotesSearchInput" placeholder="Search by quote #, client, scope..." oninput="window.onQuotesPageSearchChange(this.value)">
      </div>

      <!-- Segmented Status Tabs -->
      <div class="crm-segmented" style="margin:0">
        <button type="button" class="crm-nav-tab active" id="qTabAll" onclick="window.filterQuotesPageStatus('all')">
          All <span class="badge" id="qBadgeAll" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">0</span>
        </button>
        <button type="button" class="crm-nav-tab" id="qTabDraft" onclick="window.filterQuotesPageStatus('draft')">
          Drafts
        </button>
        <button type="button" class="crm-nav-tab" id="qTabSent" onclick="window.filterQuotesPageStatus('sent')">
          Sent
        </button>
        <button type="button" class="crm-nav-tab" id="qTabApproved" onclick="window.filterQuotesPageStatus('accepted')">
          Approved ✓
        </button>
        <button type="button" class="crm-nav-tab" id="qTabInvoiced" onclick="window.filterQuotesPageStatus('invoiced')">
          Invoiced
        </button>
      </div>
    </div>
  </div>

  <!-- Main Quotations Table -->
  <div class="tablecard">
    <div class="tablewrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%">
      <table style="width:100%;min-width:760px">
        <thead>
          <tr>
            <th style="width:130px">Quote No</th>
            <th>Title &amp; Client</th>
            <th style="width:140px">Total Amount</th>
            <th style="width:110px">Status</th>
            <th style="width:100px">Validity</th>
            <th style="width:110px">Date</th>
            <th style="text-align:right;width:240px">Actions</th>
          </tr>
        </thead>
        <tbody id="quotesMainTableBody">
          <tr>
            <td colspan="7" style="padding:28px;text-align:center;color:var(--muted)">Loading proposals from database…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
