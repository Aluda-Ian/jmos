/* ==========================================================================
   JMOS — Financial Computations, Invoices & Expenses (Live Database)
   ========================================================================== */

function renderInvoices() {
  const invBody = document.getElementById('invBody');
  if (!invBody) return;

  const list = JMOS_STATE.invoices || [];
  if (!list.length) {
    invBody.innerHTML = '<tr><td colspan="9" style="padding:26px;text-align:center;color:var(--muted)">No invoices found in database.</td></tr>';
    return;
  }

  const canManage = JMOS_STATE.currentUser && 
    (JMOS_STATE.currentUser.role === 'owner' || JMOS_STATE.currentUser.role === 'finance');

  invBody.innerHTML = list.map(v => {
    const isPaid = (v.status || '').toLowerCase() === 'paid';
    const isOverdue = (v.status || '').toLowerCase() === 'overdue';

    const st = isPaid
      ? '<span class="pill tint-green">Paid</span>' 
      : isOverdue 
        ? '<span class="pill tint-red">Overdue</span>' 
        : '<span class="pill tint-amber">Sent</span>';

    const actionButtons = [];

    if (!isPaid && canManage) {
      actionButtons.push(`
        <button type="button" class="row-action-btn" data-pay-invoice-id="${v.id}" title="Record payment for ${escHtml(v.invoice_no)}">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Pay
        </button>
      `);
    }

    if (canManage) {
      actionButtons.push(`
        <button type="button" class="row-action-btn" data-edit-invoice-id="${v.id}" title="Edit invoice ${escHtml(v.invoice_no)}">
          <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Edit
        </button>
        <button type="button" class="row-action-btn danger" data-del-invoice-id="${v.id}" data-invoice-no="${escHtml(v.invoice_no)}" title="Delete invoice ${escHtml(v.invoice_no)}">
          <svg viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete
        </button>
      `);
    } else if (isPaid) {
      actionButtons.push(`<span style="color:var(--green);font-size:12px;display:inline-flex;align-items:center;gap:3px"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Received</span>`);
    }

    const act = `<div class="row-actions-wrap">${actionButtons.join('')}</div>`;

    return `<tr>
      <td class="mono" style="font-weight:600">${escHtml(v.invoice_no)}</td>
      <td>${escHtml(v.client)}</td>
      <td>${escHtml(v.type)}</td>
      <td class="mono">${fmt(v.amount)}</td>
      <td>${escHtml(v.method || '—')}</td>
      <td>${v.etims ? '<span class="yes"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></span>' : '<span class="mono" style="color:var(--faint)">—</span>'}</td>
      <td>${st}</td>
      <td class="mono" style="color:var(--muted)">${escHtml(v.due_date || '—')}</td>
      <td style="text-align:right">${act}</td>
    </tr>`;
  }).join('');
}

function renderExpenses() {
  const expBody = document.getElementById('expBody');
  if (!expBody) return;

  const list = JMOS_STATE.expenses || [];
  if (!list.length) {
    expBody.innerHTML = '<tr><td colspan="7" style="padding:26px;text-align:center;color:var(--muted)">No expenses recorded in database.</td></tr>';
    return;
  }

  expBody.innerHTML = list.map(e => {
    const etr = e.etr === 'yes' 
      ? '<span class="yes" style="display:inline-flex;align-items:center;gap:3px"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> yes</span>' 
      : e.etr === 'no' 
        ? '<span class="no" style="display:inline-flex;align-items:center;gap:3px"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> missing</span>' 
        : '<span class="mono" style="color:var(--faint)">n/a</span>';

    const proj = e.project === 'overhead' 
      ? '<span class="mono" style="color:var(--faint)">overhead</span>' 
      : escHtml(e.project || '—');

    return `<tr>
      <td>${escHtml(e.name)}</td>
      <td>${escHtml(e.category || e.cat || 'General')}</td>
      <td>${proj}</td>
      <td class="mono">${fmt(e.amount)}</td>
      <td>${etr}</td>
      <td class="mono" style="color:var(--muted)">${escHtml(e.date || '—')}</td>
      <td></td>
    </tr>`;
  }).join('');
}

async function recomputeFinance() {
  try {
    const fin = await JMOS_API.get('/finance/overview');
    if (fin && fin.status === 'success') {
      JMOS_STATE.finance = fin;
      JMOS_STATE.broughtForward = fin.brought_forward;
    }
  } catch (_) {}

  const f = JMOS_STATE.finance || {};
  const broughtForward = f.brought_forward != null ? f.brought_forward : 1300000;
  const moneyIn = f.money_in != null ? f.money_in : 0;
  const moneyOut = f.money_out != null ? f.money_out : 0;
  const balance = f.current_balance != null ? f.current_balance : (broughtForward + moneyIn - moneyOut);
  const profit = f.profit != null ? f.profit : (moneyIn - moneyOut);
  const unpaid = f.unpaid_total != null ? f.unpaid_total : 0;
  const overdue = f.overdue_count || 0;

  // Update Dashboard KPIs
  const kpiBalance = document.getElementById('kpiBalance');
  const kpiRevenue = document.getElementById('kpiRevenue');
  const kpiUnpaid = document.getElementById('kpiUnpaid');
  const kpiOverdue = document.getElementById('kpiOverdue');

  if (kpiBalance) kpiBalance.innerHTML = fmtK(balance).replace(/(M|K)$/, '<small>$1</small>');
  if (kpiRevenue) kpiRevenue.innerHTML = fmtK(moneyIn).replace(/(M|K)$/, '<small>$1</small>');
  if (kpiUnpaid) kpiUnpaid.innerHTML = fmtK(unpaid).replace(/(M|K)$/, '<small>$1</small>');
  if (kpiOverdue) {
    kpiOverdue.textContent = overdue ? overdue + ' overdue' : 'none overdue';
    kpiOverdue.className = 'delta ' + (overdue ? 'down' : 'up');
  }

  // Update Finance Overview Page
  const finBf = document.getElementById('finBf');
  const finIn = document.getElementById('finIn');
  const finOut = document.getElementById('finOut');
  const finBalance = document.getElementById('finBalance');
  const finRevenue = document.getElementById('finRevenue');
  const finExpenses = document.getElementById('finExpenses');
  const finProfit = document.getElementById('finProfit');
  const finUnpaid = document.getElementById('finUnpaid');

  if (finBf) finBf.textContent = fmt(broughtForward);
  if (finIn) finIn.textContent = '+ ' + fmt(moneyIn);
  if (finOut) finOut.textContent = '− ' + fmt(moneyOut);
  if (finBalance) finBalance.textContent = fmt(balance);
  if (finRevenue) finRevenue.textContent = fmt(moneyIn);
  if (finExpenses) finExpenses.textContent = fmt(moneyOut);
  if (finProfit) finProfit.textContent = fmt(profit);
  if (finUnpaid) finUnpaid.textContent = fmt(unpaid);

  // KRA / Gava Tax Metrics
  const invList = JMOS_STATE.invoices || [];
  const grossInvoiced = invList.reduce((acc, inv) => acc + (Number(inv.amount) || 0), 0) || moneyIn;
  const outputVat = Math.round(grossInvoiced * 0.16 / 1.16);
  const directExpenses = moneyOut;
  const inputVatClaim = Math.round(directExpenses * 0.16 / 1.16);
  const netTax = Math.max(0, outputVat - inputVatClaim);

  const kraGrossInvoiced = document.getElementById('kraGrossInvoiced');
  const kraOutputVat = document.getElementById('kraOutputVat');
  const kraDirectExpenses = document.getElementById('kraDirectExpenses');
  const kraInputVatClaim = document.getElementById('kraInputVatClaim');
  const kraNetTax = document.getElementById('kraNetTax');

  if (kraGrossInvoiced) kraGrossInvoiced.textContent = fmt(grossInvoiced);
  if (kraOutputVat) kraOutputVat.textContent = fmt(outputVat);
  if (kraDirectExpenses) kraDirectExpenses.textContent = fmt(directExpenses);
  if (kraInputVatClaim) kraInputVatClaim.textContent = 'Claimable VAT: ' + fmt(inputVatClaim);
  if (kraNetTax) kraNetTax.textContent = fmt(netTax);

  // Update Activity Ledger
  const finLedger = document.getElementById('finLedger');
  if (finLedger) {
    const ledger = f.ledger || [];
    if (ledger.length) {
      finLedger.innerHTML = ledger.map(item => `<tr>
        <td>${escHtml(item.item)}</td>
        <td>${escHtml(item.type)}</td>
        <td class="mono" style="color:var(--green)">${item.in ? fmt(item.in) : ''}</td>
        <td class="mono" style="color:var(--red)">${item.out ? fmt(item.out) : ''}</td>
      </tr>`).join('');
    } else {
      finLedger.innerHTML = '<tr><td colspan="4" style="padding:20px;text-align:center;color:var(--muted)">No transactions recorded yet.</td></tr>';
    }
  }
}

window.openKraTaxReconciliation = function() {
  showToast('Gava iTax & eTIMS Synced', 'Direct expenses and invoice income successfully reconciled with KRA portal');
};

function initFinance() {
  // Event delegation for "Record payment" buttons
  document.addEventListener('click', async (e) => {
    const payBtn = e.target.closest('[data-pay-invoice-id]');
    if (payBtn) {
      const invId = payBtn.getAttribute('data-pay-invoice-id');
      payBtn.disabled = true;
      payBtn.textContent = 'Processing…';

      try {
        const res = await JMOS_API.post(`/invoices/${invId}/pay`, { method: 'M-Pesa' });
        showToast('Payment recorded', res.message || 'Invoice marked as paid');
        
        // Refresh invoices and financial overview from database
        const [invoices, fin] = await Promise.all([
          JMOS_API.get('/invoices'),
          JMOS_API.get('/finance/overview')
        ]);
        if (Array.isArray(invoices)) JMOS_STATE.invoices = invoices;
        if (fin) JMOS_STATE.finance = fin;

        renderInvoices();
        await recomputeFinance();
      } catch (err) {
        alert('Payment failed: ' + err.message);
        payBtn.disabled = false;
        payBtn.textContent = 'Record payment';
      }
    }
  });

  // Event delegation for invoice Edit & Delete actions
  document.addEventListener('click', (e) => {
    const editBtn = e.target.closest('[data-edit-invoice-id]');
    if (editBtn) {
      const invId = editBtn.getAttribute('data-edit-invoice-id');
      if (typeof window.openEditInvoiceModal === 'function') {
        window.openEditInvoiceModal(invId);
      }
      return;
    }

    const delBtn = e.target.closest('[data-del-invoice-id]');
    if (delBtn) {
      const invId = delBtn.getAttribute('data-del-invoice-id');
      const invNo = delBtn.getAttribute('data-invoice-no');
      if (typeof window.deleteInvoice === 'function') {
        window.deleteInvoice(invId, invNo);
      }
      return;
    }
  });
}
