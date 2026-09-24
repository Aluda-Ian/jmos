<!-- ==========================================================================
     JMOS — View: Expenses
     ========================================================================== -->
<section class="view" data-view="expenses" hidden data-perm="owner">
  <div class="page-head">
    <div>
      <h1 class="pt">Expenses</h1>
      <p>Feeds profit-per-project. Every purchase needs an ETR.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addExpenseBtn" onclick="openModal('expenseModal')" data-modal-open="expenseModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Log expense
      </button>
    </div>
  </div>

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Expense</th>
            <th>Category</th>
            <th>Project</th>
            <th>Amount</th>
            <th>ETR / eTIMS</th>
            <th>Document</th>
            <th>Date</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody id="expBody">
          <tr>
            <td colspan="8" style="padding:26px;text-align:center;color:var(--muted)">Loading expenses from database…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
