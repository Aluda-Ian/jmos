<!-- ==========================================================================
     JMOS — View: Finance Overview
     ========================================================================== -->
<section class="view" data-view="finance" hidden data-perm="owner finance">
  <div class="page-head">
    <div>
      <h1 class="pt">Finance</h1>
      <p>Your whole money picture — calculated inside JMOS.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn" data-view="statements" onclick="showView('statements')" title="View comprehensive financial statements &amp; client ledger">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>Financial Statements
      </button>
      <button type="button" class="btn primary" onclick="openModal('invoiceModal')" data-modal-open="invoiceModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New invoice
      </button>
    </div>
  </div>

  <div class="fin-grid">
    <!-- Balance Card -->
    <div class="balcard">
      <h3>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--red)" stroke-width="1.8">
          <rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/>
        </svg>
        Account balance
      </h3>
      <div class="balrow">
        <span class="lbl2"><span class="dt" style="background:var(--faint)"></span>Brought forward</span>
        <span class="mono" id="finBf"></span>
      </div>
      <div class="balrow">
        <span class="lbl2"><span class="dt" style="background:var(--green)"></span>Money in (paid invoices)</span>
        <span class="mono" style="color:var(--green)" id="finIn"></span>
      </div>
      <div class="balrow">
        <span class="lbl2"><span class="dt" style="background:var(--red)"></span>Money out (expenses)</span>
        <span class="mono" style="color:var(--red)" id="finOut"></span>
      </div>
      <div class="balrow total">
        <span class="lbl2">Current balance</span>
        <span class="mono" id="finBalance"></span>
      </div>
      <div class="finnote">
        <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        Calculated live from your invoices and expenses — no external accounting.
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="fin-stats">
      <div class="fstat">
        <div class="l">Revenue received</div>
        <div class="v" style="color:var(--green)" id="finRevenue"></div>
      </div>
      <div class="fstat">
        <div class="l">Total expenses</div>
        <div class="v" style="color:var(--red)" id="finExpenses"></div>
      </div>
      <div class="fstat">
        <div class="l">Profit (received − spent)</div>
        <div class="v" id="finProfit"></div>
      </div>
      <div class="fstat">
        <div class="l">Outstanding / unpaid</div>
        <div class="v" style="color:var(--amber)" id="finUnpaid"></div>
      </div>
    </div>
  </div>

  <!-- Connect Gava: KRA Tax, Direct Expenses & Income Tracking Card -->
  <div class="card" style="margin-top:17px;padding:20px;border-left:4px solid var(--red)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:8px;background:rgba(197,37,35,0.1);color:var(--red);display:grid;place-items:center">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 10h18M5 10v11M9 10v11M15 10v11M19 10v11M12 2L2 7h20L12 2z"/></svg>
        </div>
        <div>
          <div style="display:flex;align-items:center;gap:8px">
            <h3 style="font-family:'Poppins',sans-serif;font-size:15px;font-weight:600;margin:0">Connect Gava · KRA eTIMS Compliance</h3>
            <span class="badge" style="background:var(--green-soft);color:var(--green);font-size:10.5px">PIN: P052209707D (Active)</span>
          </div>
          <p style="font-size:12px;color:var(--muted);margin:2px 0 0 0">eTIMS tracking: direct expenses ETR deductions &amp; tax exemption ledger (Taxes deactivated).</p>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <button type="button" class="btn" onclick="openKraTaxReconciliation()" style="font-size:11.5px;padding:6px 12px">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
          Audit Sync with Gava
        </button>
      </div>
    </div>

    <!-- 4 Tax Metric KPIs -->
    <div class="kpis" style="grid-template-columns:repeat(4, 1fr);gap:12px">
      <div class="kpi" style="padding:14px">
        <div class="lbl">Gross Invoiced Income</div>
        <div class="val" id="kraGrossInvoiced" style="font-size:17px">KES 0</div>
        <div class="sub">100% recorded to eTIMS</div>
      </div>
      <div class="kpi" style="padding:14px">
        <div class="lbl">VAT Status (0%)</div>
        <div class="val" id="kraOutputVat" style="color:var(--green);font-size:17px">0% (Exempt)</div>
        <div class="sub">Professional Service</div>
      </div>
      <div class="kpi" style="padding:14px">
        <div class="lbl">Direct Expenses (ETR Verified)</div>
        <div class="val" id="kraDirectExpenses" style="color:var(--ink);font-size:17px">KES 0</div>
        <div class="sub" id="kraInputVatClaim">Tax Deductible</div>
      </div>
      <div class="kpi" style="padding:14px">
        <div class="lbl">Withholding Tax (WHT)</div>
        <div class="val" id="kraNetTax" style="color:var(--muted);font-size:17px">0% (Deactivated)</div>
        <div class="sub">Tax calculation off</div>
      </div>
    </div>
  </div>

  <!-- Recent Activity / Ledger Table -->
  <div class="tablecard">
    <div class="card-h" style="display:flex;align-items:center;justify-content:space-between">
      <h3 style="font-family:'Poppins', sans-serif;font-size:14px;font-weight:600">Recent financial activity</h3>
      <a href="#" onclick="event.preventDefault(); showView('statements');" style="font-size:12px;color:var(--red);text-decoration:none;font-weight:600">View Full Statements &rarr;</a>
    </div>
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Type</th>
            <th>In</th>
            <th>Out</th>
          </tr>
        </thead>
        <tbody id="finLedger"></tbody>
      </table>
    </div>
  </div>
</section>
