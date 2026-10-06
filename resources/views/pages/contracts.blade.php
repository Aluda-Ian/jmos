<!-- ==========================================================================
     JMOS — View: Client Contracts & E-Signatures
     ========================================================================== -->
<section class="view" data-view="contracts" hidden data-perm="owner">
  <div class="page-head">
    <div>
      <h1 class="pt">Contracts</h1>
      <p>Generate client agreements from the Jeota template, tailor them with AI, and send them for e-signature — Barny's signature is applied automatically.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" onclick="window.JMOS_CONTRACTS.openBuilder()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New Contract
      </button>
    </div>
  </div>

  <div class="kpis" style="margin-bottom:18px">
    <div class="kpi">
      <div class="lbl">Total Contracts</div>
      <div class="val mono" id="ctrKpiTotal">0</div>
      <div class="sub">All agreements drafted</div>
    </div>
    <div class="kpi">
      <div class="lbl">Awaiting Signature</div>
      <div class="val mono" id="ctrKpiAwaiting" style="color:var(--amber)">0</div>
      <div class="sub">Sent or viewed by client</div>
    </div>
    <div class="kpi">
      <div class="lbl">Signed</div>
      <div class="val mono" id="ctrKpiSigned" style="color:var(--green)">0</div>
      <div class="sub" id="ctrKpiSignedValue">KES 0 contracted</div>
    </div>
    <div class="kpi">
      <div class="lbl">Drafts</div>
      <div class="val mono" id="ctrKpiDrafts" style="color:var(--ink)">0</div>
      <div class="sub">Not yet sent</div>
    </div>
  </div>

  <div class="card" style="padding:14px 18px;margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div class="search" style="max-width:320px;flex:1;min-width:220px">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input type="text" id="contractsSearchInput" placeholder="Search by contract #, client, title…" oninput="window.JMOS_CONTRACTS.setSearch(this.value)">
      </div>
      <div class="crm-segmented" style="margin:0" id="contractsStatusTabs">
        <button type="button" class="crm-nav-tab active" data-ct-filter="all" onclick="window.JMOS_CONTRACTS.setFilter('all')">All</button>
        <button type="button" class="crm-nav-tab" data-ct-filter="Draft" onclick="window.JMOS_CONTRACTS.setFilter('Draft')">Drafts</button>
        <button type="button" class="crm-nav-tab" data-ct-filter="awaiting" onclick="window.JMOS_CONTRACTS.setFilter('awaiting')">Awaiting signature</button>
        <button type="button" class="crm-nav-tab" data-ct-filter="Signed" onclick="window.JMOS_CONTRACTS.setFilter('Signed')">Signed ✓</button>
      </div>
    </div>
  </div>

  <div class="tablecard">
    <div class="tablewrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%">
      <table style="width:100%;min-width:760px">
        <thead>
          <tr>
            <th style="width:130px">Contract No</th>
            <th>Client &amp; Scope</th>
            <th style="width:140px">Contract Fee</th>
            <th style="width:130px">Status</th>
            <th style="width:120px">Updated</th>
            <th style="text-align:right;width:220px">Actions</th>
          </tr>
        </thead>
        <tbody id="contractsTableBody">
          <tr><td colspan="6" style="padding:28px;text-align:center;color:var(--muted)">Loading contracts…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
