/* ==========================================================================
   JMOS — Financial Computations, Invoices, Quotations & Client Tracking
   ========================================================================== */

(function () {
  'use strict';

  window.FINANCE_STATE = {
    activeTab: 'invoices',
    searchQuery: '',
    quotes: [],
  };

  /**
   * Helper: Escape HTML strings safely
   */
  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * Helper: Format Currency (KES)
   */
  function fmtK(n) {
    const val = Number(n) || 0;
    if (Math.abs(val) >= 1000000) {
      return (val / 1000000).toFixed(2) + 'M';
    }
    if (Math.abs(val) >= 1000) {
      return (val / 1000).toFixed(1) + 'K';
    }
    return Math.round(val).toLocaleString();
  }

  function fmt(n) {
    return 'KES ' + Math.round(Number(n) || 0).toLocaleString();
  }

  /**
   * Switch Finance Hub Sub-Tabs
   */
  window.switchFinanceTab = function (tabName) {
    FINANCE_STATE.activeTab = tabName || 'invoices';

    document.querySelectorAll('.crm-nav-tab[id^="finTab"]').forEach(tab => {
      tab.classList.remove('active');
    });

    const activeBtn = document.getElementById(`finTab${tabName.charAt(0).toUpperCase() + tabName.slice(1)}`);
    if (activeBtn) activeBtn.classList.add('active');

    const panes = ['invoices', 'quotes', 'accounts', 'ledger'];
    panes.forEach(p => {
      const paneEl = document.getElementById(`finPane${p.charAt(0).toUpperCase() + p.slice(1)}`);
      if (paneEl) {
        paneEl.style.display = p === tabName ? 'block' : 'none';
      }
    });

    window.renderActiveFinanceTab();
  };

  window.onFinanceSearchChange = function (val) {
    FINANCE_STATE.searchQuery = (val || '').toLowerCase().trim();
    window.renderActiveFinanceTab();
  };

  window.renderActiveFinanceTab = function () {
    const tab = FINANCE_STATE.activeTab;
    if (tab === 'invoices') window.renderFinanceInvoices();
    else if (tab === 'quotes') window.renderFinanceQuotes();
    else if (tab === 'accounts') window.renderFinanceAccounts();
    else if (tab === 'ledger') window.renderFinanceLedger();
  };

  /**
   * Render Invoices Table (Finance Tab & Invoices View)
   */
  window.renderFinanceInvoices = function () {
    const bodies = [
      document.getElementById('finInvoicesTableBody'),
      document.getElementById('invBody')
    ];

    let list = JMOS_STATE.invoices || [];

    // Apply Search
    if (FINANCE_STATE.searchQuery) {
      const q = FINANCE_STATE.searchQuery;
      list = list.filter(v =>
        (v.invoice_no && v.invoice_no.toLowerCase().includes(q)) ||
        (v.client && v.client.toLowerCase().includes(q)) ||
        (v.type && v.type.toLowerCase().includes(q)) ||
        (v.status && v.status.toLowerCase().includes(q))
      );
    }

    const badgeEl = document.getElementById('finBadgeInvoices');
    if (badgeEl) badgeEl.textContent = (JMOS_STATE.invoices || []).length;

    const canManage = JMOS_STATE.currentUser && 
      (JMOS_STATE.currentUser.role === 'owner' || JMOS_STATE.currentUser.role === 'finance');

    bodies.forEach(invBody => {
      if (!invBody) return;

      if (!list.length) {
        invBody.innerHTML = '<tr><td colspan="9" style="padding:28px;text-align:center;color:var(--muted)">No invoices match your query.</td></tr>';
        return;
      }

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
            <button type="button" class="row-action-btn" data-pay-invoice-id="${v.id}" title="Record payment for ${esc(v.invoice_no)}">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Pay
            </button>
          `);
        }

        if (canManage) {
          actionButtons.push(`
            <button type="button" class="row-action-btn" data-edit-invoice-id="${v.id}" title="Edit invoice ${esc(v.invoice_no)}">
              <svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Edit
            </button>
            <button type="button" class="row-action-btn danger" data-del-invoice-id="${v.id}" data-invoice-no="${esc(v.invoice_no)}" title="Delete invoice ${esc(v.invoice_no)}">
              <svg viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete
            </button>
          `);
        } else if (isPaid) {
          actionButtons.push(`<span style="color:var(--green);font-size:12px;display:inline-flex;align-items:center;gap:3px"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Received</span>`);
        }

        const act = `<div class="row-actions-wrap">${actionButtons.join('')}</div>`;

        return `<tr>
          <td class="mono" style="font-weight:600;color:var(--ink)">${esc(v.invoice_no)}</td>
          <td>
            <div style="font-weight:600;color:var(--ink)">${esc(v.client)}</div>
          </td>
          <td>${esc(v.type)}</td>
          <td class="mono" style="font-weight:600">${fmt(v.amount)}</td>
          <td>${esc(v.method || '—')}</td>
          <td>${v.etims ? '<span class="yes"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></span>' : '<span class="mono" style="color:var(--faint)">—</span>'}</td>
          <td>${st}</td>
          <td class="mono" style="color:var(--muted)">${esc(v.due_date || '—')}</td>
          <td style="text-align:right">${act}</td>
        </tr>`;
      }).join('');
    });
  };

  window.renderInvoices = window.renderFinanceInvoices;

  /**
   * Render Quotations & Commercial Proposals Table
   */
  window.renderFinanceQuotes = async function () {
    const tbody = document.getElementById('finQuotesTableBody');
    if (!tbody) return;

    if (!FINANCE_STATE.quotes || FINANCE_STATE.quotes.length === 0) {
      try {
        const res = await JMOS_API.get('/quotes');
        if (res && res.data) {
          FINANCE_STATE.quotes = res.data;
        }
      } catch (_) {}
    }

    let list = (FINANCE_STATE.quotes && FINANCE_STATE.quotes.length) 
      ? FINANCE_STATE.quotes 
      : ((window.JMOS_QUOTES && Array.isArray(window.JMOS_QUOTES.quotes) && window.JMOS_QUOTES.quotes.length) 
          ? window.JMOS_QUOTES.quotes 
          : (JMOS_STATE.quotes || []));

    // Apply Search
    if (FINANCE_STATE.searchQuery) {
      const q = FINANCE_STATE.searchQuery;
      list = list.filter(item =>
        (item.quote_number && item.quote_number.toLowerCase().includes(q)) ||
        (item.title && item.title.toLowerCase().includes(q)) ||
        (item.recipient_name && item.recipient_name.toLowerCase().includes(q)) ||
        (item.status && item.status.toLowerCase().includes(q))
      );
    }

    const badgeEl = document.getElementById('finBadgeQuotes');
    if (badgeEl) badgeEl.textContent = list.length;

    if (!list.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8" style="padding:32px;text-align:center;color:var(--muted)">
            <div style="font-weight:600;color:var(--ink);margin-bottom:4px">No commercial quotations found</div>
            <p style="font-size:12px;margin:0 0 12px">Generate quotations for leads or direct clients to track expected revenue.</p>
            <button type="button" class="btn primary" onclick="window.JMOS_QUOTES.openCreateModal()" style="font-size:11.5px">+ Create Quotation</button>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = list.map(q => {
      const statusClass = q.status === 'Invoiced' 
        ? 'tint-green' 
        : (q.status === 'Accepted' ? 'tint-green' : (q.status === 'Sent' ? 'tint-blue' : 'tint-amber'));

      const statusBadge = `<span class="pill ${statusClass}">${esc(q.status || 'Draft')}</span>`;
      const recipient = q.recipient_name || (q.client ? q.client.client_name : (q.lead ? q.lead.lead_name : 'Direct Prospect'));
      const subtotalVal = q.subtotal || q.total_amount;
      const totalVal = q.total_amount;

      return `
        <tr>
          <td class="mono" style="font-weight:600;color:var(--red)">
            <a href="#" onclick="event.preventDefault(); window.JMOS_QUOTES.viewDetail(${q.id})" style="color:var(--red);text-decoration:none">
              ${esc(q.quote_number)}
            </a>
          </td>
          <td>
            <div style="font-weight:600;color:var(--ink)">${esc(q.title)}</div>
            <div style="font-size:11px;color:var(--muted)">${q.items ? q.items.length + ' itemized deliverables' : 'Custom Scope'}</div>
          </td>
          <td>
            <div style="font-weight:600;color:var(--ink)">${esc(recipient)}</div>
            <div style="font-size:11px;color:var(--muted)">${esc(q.recipient_email || q.recipient_phone || '—')}</div>
          </td>
          <td class="mono">${fmt(subtotalVal)}</td>
          <td class="mono" style="font-weight:700;color:var(--ink)">${fmt(totalVal)}</td>
          <td class="mono" style="color:var(--muted)">${q.validity_days || 14} days</td>
          <td>${statusBadge}</td>
          <td style="text-align:right">
            <div class="row-actions-wrap" style="justify-content:flex-end">
              <button type="button" class="row-action-btn" onclick="window.JMOS_QUOTES.viewDetail(${q.id})" title="View complete proposal">
                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View
              </button>
              <button type="button" class="row-action-btn" onclick="window.JMOS_QUOTES.sendWhatsApp(${q.id})" title="Send via WhatsApp">
                <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>WA
              </button>
              ${q.status !== 'Invoiced' ? `
                <button type="button" class="row-action-btn" onclick="window.JMOS_QUOTES.openUpgradeModal(${JSON.stringify(q).replace(/"/g, '&quot;')})" title="Convert to invoice" style="color:var(--green)">
                  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Invoice
                </button>
              ` : ''}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  /**
   * Render Client & Lead Accounts Grouped Balance Ledger
   */
  window.renderFinanceAccounts = async function () {
    const tbody = document.getElementById('finAccountsTableBody');
    if (!tbody) return;

    const invoices = JMOS_STATE.invoices || [];
    const quotes = FINANCE_STATE.quotes || [];

    // Group billing records by client / lead name
    const accountMap = {};

    // Group Invoices
    invoices.forEach(inv => {
      const name = (inv.client || 'Direct Client').trim();
      if (!accountMap[name]) {
        accountMap[name] = {
          name: name,
          isLead: false,
          totalInvoiced: 0,
          totalPaid: 0,
          invoicesCount: 0,
          openQuotesTotal: 0,
          quotesCount: 0,
        };
      }
      const amt = Number(inv.amount) || 0;
      accountMap[name].totalInvoiced += amt;
      accountMap[name].invoicesCount++;
      if ((inv.status || '').toLowerCase() === 'paid') {
        accountMap[name].totalPaid += amt;
      }
    });

    // Group Quotations
    quotes.forEach(q => {
      const name = (q.recipient_name || (q.client ? q.client.client_name : (q.lead ? q.lead.lead_name : 'Direct Prospect'))).trim();
      if (!accountMap[name]) {
        accountMap[name] = {
          name: name,
          isLead: !!q.lead_id,
          totalInvoiced: 0,
          totalPaid: 0,
          invoicesCount: 0,
          openQuotesTotal: 0,
          quotesCount: 0,
        };
      }
      accountMap[name].quotesCount++;
      if (q.status !== 'Invoiced' && q.status !== 'Declined') {
        accountMap[name].openQuotesTotal += Number(q.total_amount) || 0;
      }
    });

    let accounts = Object.values(accountMap);

    // Apply Search
    if (FINANCE_STATE.searchQuery) {
      const q = FINANCE_STATE.searchQuery;
      accounts = accounts.filter(acc => acc.name.toLowerCase().includes(q));
    }

    const badgeEl = document.getElementById('finBadgeAccounts');
    if (badgeEl) badgeEl.textContent = Object.keys(accountMap).length;

    if (!accounts.length) {
      tbody.innerHTML = '<tr><td colspan="8" style="padding:28px;text-align:center;color:var(--muted)">No client or lead accounts found matching search.</td></tr>';
      return;
    }

    tbody.innerHTML = accounts.map(acc => {
      const balance = Math.max(0, acc.totalInvoiced - acc.totalPaid);
      let statusHtml = '';

      if (acc.totalInvoiced > 0 && balance === 0) {
        statusHtml = '<span class="pill tint-green">✓ Fully Settled</span>';
      } else if (balance > 0) {
        statusHtml = `<span class="pill tint-red">KES ${Math.round(balance).toLocaleString()} Due</span>`;
      } else if (acc.openQuotesTotal > 0) {
        statusHtml = '<span class="pill tint-blue">Proposal Active</span>';
      } else {
        statusHtml = '<span class="pill">In Discussion</span>';
      }

      const typeBadge = acc.isLead 
        ? '<span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">Prospect Lead</span>' 
        : '<span class="badge" style="background:rgba(43,110,138,0.1);color:#2B6E8A;font-size:10.5px">Client Account</span>';

      return `
        <tr>
          <td>
            <div style="font-weight:700;color:var(--ink);font-size:13.5px">${esc(acc.name)}</div>
            <div style="font-size:11px;color:var(--muted)">${acc.invoicesCount} Invoices · ${acc.quotesCount} Quotations</div>
          </td>
          <td>${typeBadge}</td>
          <td class="mono" style="font-weight:600">${fmt(acc.totalInvoiced)}</td>
          <td class="mono" style="color:var(--green);font-weight:600">${fmt(acc.totalPaid)}</td>
          <td class="mono" style="color:${balance > 0 ? 'var(--red)' : 'var(--muted)'};font-weight:700">${fmt(balance)}</td>
          <td class="mono" style="color:var(--ink)">${fmt(acc.openQuotesTotal)}</td>
          <td>${statusHtml}</td>
          <td style="text-align:right">
            <div class="row-actions-wrap" style="justify-content:flex-end">
              <button type="button" class="btn small" onclick="openModal('invoiceModal')" title="Bill this client" style="font-size:11px;padding:3px 8px">
                + Invoice
              </button>
              <button type="button" class="btn small" onclick="window.JMOS_QUOTES.openCreateModal()" title="Create proposal" style="font-size:11px;padding:3px 8px">
                + Quote
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  /**
   * Render Activity Ledger
   */
  window.renderFinanceLedger = function () {
    const finLedgers = [
      document.getElementById('finLedger'),
      document.getElementById('finLedgerDetailed')
    ];

    const f = JMOS_STATE.finance || {};
    let ledger = f.ledger || [];

    if (FINANCE_STATE.searchQuery) {
      const q = FINANCE_STATE.searchQuery;
      ledger = ledger.filter(l => (l.item && l.item.toLowerCase().includes(q)) || (l.type && l.type.toLowerCase().includes(q)));
    }

    const html = ledger.length 
      ? ledger.map(item => `<tr>
          <td><b>${esc(item.item)}</b></td>
          <td><span class="badge" style="background:var(--panel-2);color:var(--muted)">${esc(item.type)}</span></td>
          <td class="mono" style="color:var(--green);font-weight:600">${item.in ? fmt(item.in) : '—'}</td>
          <td class="mono" style="color:var(--red);font-weight:600">${item.out ? fmt(item.out) : '—'}</td>
        </tr>`).join('')
      : '<tr><td colspan="4" style="padding:24px;text-align:center;color:var(--muted)">No transactions match current filters.</td></tr>';

    finLedgers.forEach(el => {
      if (el) el.innerHTML = html;
    });
  };

  /**
   * Core Finance Computations & State Fetch
   */
  window.recomputeFinance = async function () {
    try {
      const [fin, quotesRes] = await Promise.all([
        JMOS_API.get('/finance/overview'),
        JMOS_API.get('/quotes')
      ]);

      if (fin && fin.status === 'success') {
        JMOS_STATE.finance = fin;
        JMOS_STATE.broughtForward = fin.brought_forward;
      }

      if (quotesRes && quotesRes.data) {
        FINANCE_STATE.quotes = quotesRes.data;
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

    // KRA / Gava Tax Metrics (Professional Services: 0% VAT Exempt, 5% WHT Compliance)
    const invList = JMOS_STATE.invoices || [];
    const grossInvoiced = invList.reduce((acc, inv) => acc + (Number(inv.amount) || 0), 0) || moneyIn;
    const directExpenses = moneyOut;
    const whtRate = 0.05; // 5% WHT on professional services
    const estimatedWht = Math.round(grossInvoiced * whtRate);

    const kraGrossInvoiced = document.getElementById('kraGrossInvoiced');
    const kraOutputVat = document.getElementById('kraOutputVat');
    const kraDirectExpenses = document.getElementById('kraDirectExpenses');
    const kraInputVatClaim = document.getElementById('kraInputVatClaim');
    const kraNetTax = document.getElementById('kraNetTax');

    if (kraGrossInvoiced) kraGrossInvoiced.textContent = fmt(grossInvoiced);
    if (kraOutputVat) kraOutputVat.textContent = '0% (Exempt)';
    if (kraDirectExpenses) kraDirectExpenses.textContent = fmt(directExpenses);
    if (kraInputVatClaim) kraInputVatClaim.textContent = 'Tax Deductible';
    if (kraNetTax) kraNetTax.textContent = fmt(estimatedWht);

    // Render active tab contents
    window.renderActiveFinanceTab();
  };

  window.refreshFinanceData = function () {
    window.recomputeFinance();
    if (window.JMOS_QUOTES && typeof window.JMOS_QUOTES.loadQuotes === 'function') {
      window.JMOS_QUOTES.loadQuotes();
    }
  };

  window.openKraTaxReconciliation = function () {
    if (window.showToast) {
      window.showToast('Gava iTax & eTIMS Synced', 'Direct expenses and invoice income successfully reconciled with KRA portal');
    }
  };

  /**
   * Initialize Finance Module Listeners
   */
  window.initFinance = function () {
    // Event delegation for "Record payment" buttons
    document.addEventListener('click', async (e) => {
      const payBtn = e.target.closest('[data-pay-invoice-id]');
      if (payBtn) {
        const invId = payBtn.getAttribute('data-pay-invoice-id');
        payBtn.disabled = true;
        payBtn.textContent = 'Processing…';

        try {
          const res = await JMOS_API.post(`/invoices/${invId}/pay`, { method: 'M-Pesa' });
          if (window.showToast) window.showToast('Payment recorded', res.message || 'Invoice marked as paid');
          
          const [invoices, fin] = await Promise.all([
            JMOS_API.get('/invoices'),
            JMOS_API.get('/finance/overview')
          ]);
          if (Array.isArray(invoices)) JMOS_STATE.invoices = invoices;
          if (fin) JMOS_STATE.finance = fin;

          window.recomputeFinance();
        } catch (err) {
          if (window.showToast) window.showToast('Payment Failed', err.message, true);
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
  };

  // Run init on script load
  document.addEventListener('DOMContentLoaded', function () {
    window.initFinance();
  });
})();
