<!-- ==========================================================================
     JMOS — View: Invoices
     ========================================================================== -->
<section class="view" data-view="invoices" hidden data-perm="owner finance">
  <div class="page-head">
    <div>
      <h1 class="pt">Invoices</h1>
      <p>Live billing tracker · 60% deposit / 40% balance · eTIMS on request.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addInvoiceBtn" onclick="openModal('invoiceModal')" data-modal-open="invoiceModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New invoice
      </button>
    </div>
  </div>

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Invoice</th>
            <th>Client</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Method</th>
            <th>eTIMS</th>
            <th>Status</th>
            <th>Due</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="invBody">
          <tr>
            <td colspan="9" style="padding:26px;text-align:center;color:var(--muted)">Loading invoices from database…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
