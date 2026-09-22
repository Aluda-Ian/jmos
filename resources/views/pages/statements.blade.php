<!-- ==========================================================================
     JMOS — View: Financial Statements & Client / Lead Billing Hub
     ========================================================================== -->
<section class="view" data-view="statements" hidden data-perm="owner finance">
  <div class="page-head">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:11px;font-weight:700">FINANCIAL STATEMENTS</span>
        <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">INVOICES, QUOTES &amp; CLIENT BALANCES</span>
      </div>
      <h1 class="pt">Financial Statements &amp; Ledger</h1>
      <p>Comprehensive tracking of client invoices, commercial proposals, and account balances.</p>
    </div>
    <div class="head-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" id="createQuoteBtn" onclick="showView('budget')" title="Create commercial quotation in Production Budget">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>+ Create Quote
      </button>
      <button type="button" class="btn primary" onclick="openModal('invoiceModal')" data-modal-open="invoiceModal" title="Issue new client invoice">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>+ New Invoice
      </button>
    </div>
  </div>

  <!-- Interactive Client / Lead Billing, Invoices & Quotations Hub -->
  <div class="tablecard" style="border:1px solid var(--line);border-radius:12px;overflow:hidden;box-shadow:var(--shadow-sm)">
    <!-- Navigation Tabs & Search Toolbar -->
    <div style="padding:14px 18px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
        <button type="button" class="crm-nav-tab active" id="finTabInvoices" onclick="window.switchFinanceTab('invoices')">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
          Invoices
          <span class="crm-tab-badge" id="finBadgeInvoices">0</span>
        </button>
        <button type="button" class="crm-nav-tab" id="finTabQuotes" onclick="window.switchFinanceTab('quotes')">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          Quotations &amp; Proposals
          <span class="crm-tab-badge" id="finBadgeQuotes">0</span>
        </button>
        <button type="button" class="crm-nav-tab" id="finTabAccounts" onclick="window.switchFinanceTab('accounts')">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Client &amp; Lead Balances
          <span class="crm-tab-badge" id="finBadgeAccounts">0</span>
        </button>
        <button type="button" class="crm-nav-tab" id="finTabLedger" onclick="window.switchFinanceTab('ledger')">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          Cashflow Activity
        </button>
      </div>

      <div style="display:flex;align-items:center;gap:10px">
        <div style="position:relative">
          <input type="text" id="finSearchInput" placeholder="Search clients, leads, invoices, quotes…" style="font-size:12px;padding:6px 10px 6px 28px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink);width:260px" oninput="window.onFinanceSearchChange(this.value)">
          <svg viewBox="0 0 24 24" width="13" height="13" style="position:absolute;left:9px;top:9px;color:var(--muted)" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
    </div>

    <!-- PANE 1: Invoices Table -->
    <div class="fin-tab-pane" id="finPaneInvoices">
      <div class="tablewrap" style="max-height:600px;overflow-y:auto">
        <table>
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Client / Lead</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Method</th>
              <th>eTIMS</th>
              <th>Status</th>
              <th>Due Date</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody id="finInvoicesTableBody">
            <tr><td colspan="9" style="padding:28px;text-align:center;color:var(--muted)">Loading invoices…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PANE 2: Quotations & Proposals Table -->
    <div class="fin-tab-pane" id="finPaneQuotes" style="display:none">
      <div class="tablewrap" style="max-height:600px;overflow-y:auto">
        <table>
          <thead>
            <tr>
              <th>Quote #</th>
              <th>Project / Deliverable</th>
              <th>Recipient (Client / Lead)</th>
              <th>Subtotal</th>
              <th>Proposal Value</th>
              <th>Validity</th>
              <th>Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody id="finQuotesTableBody">
            <tr><td colspan="8" style="padding:28px;text-align:center;color:var(--muted)">Loading quotations &amp; proposals…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PANE 3: Client & Lead Accounts Ledger -->
    <div class="fin-tab-pane" id="finPaneAccounts" style="display:none">
      <div class="tablewrap" style="max-height:600px;overflow-y:auto">
        <table>
          <thead>
            <tr>
              <th>Client / Lead Account</th>
              <th>Record Type</th>
              <th>Total Invoiced</th>
              <th>Total Collected / Paid</th>
              <th>Outstanding Balance</th>
              <th>Active Proposals Value</th>
              <th>Billing Status</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody id="finAccountsTableBody">
            <tr><td colspan="8" style="padding:28px;text-align:center;color:var(--muted)">Computing client &amp; lead account balances…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PANE 4: Cashflow Activity Ledger -->
    <div class="fin-tab-pane" id="finPaneLedger" style="display:none">
      <div class="tablewrap" style="max-height:600px;overflow-y:auto">
        <table>
          <thead>
            <tr>
              <th>Transaction / Description</th>
              <th>Category</th>
              <th>Money In (+)</th>
              <th>Money Out (−)</th>
            </tr>
          </thead>
          <tbody id="finLedgerDetailed">
            <tr><td colspan="4" style="padding:28px;text-align:center;color:var(--muted)">Loading activity ledger…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
