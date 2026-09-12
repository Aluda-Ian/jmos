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
      <button class="btn">
        <svg viewBox="0 0 24 24"><path d="M12 3v12M8 11l4 4 4-4M4 21h16"/></svg>Export
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

  <!-- Recent Activity / Ledger Table -->
  <div class="tablecard">
    <div class="card-h">
      <h3 style="font-family:'Poppins', sans-serif;font-size:14px;font-weight:600">Recent activity</h3>
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
