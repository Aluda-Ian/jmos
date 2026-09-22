/* ==========================================================================
   JMOS — Quotations & Invoicing Engine
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_QUOTES = {
    quotes: [],
    activeQuote: null,
    activeFilter: 'all',
    searchQuery: '',

    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Auto calculate totals on dynamic input changes
      document.addEventListener('input', function (e) {
        if (e.target.closest('#quoteItemsTableBody') || e.target.id === 'quoteDiscount' || e.target.id === 'quoteValidity') {
          window.calcQuoteTotals();
        }
      });
    },

    loadQuotes: async function () {
      try {
        const token = localStorage.getItem('jmos_api_token') || (typeof JMOS_STATE !== 'undefined' ? JMOS_STATE.apiToken : null);
        const res = await fetch('/api/quotes', {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (res.ok && data.data) {
          this.quotes = data.data;
          if (typeof JMOS_STATE !== 'undefined') JMOS_STATE.quotes = data.data;
          if (typeof FINANCE_STATE !== 'undefined') FINANCE_STATE.quotes = data.data;
          this.renderQuotesTable();
          this.renderQuotesMainPageTable();
          if (typeof window.renderFinanceQuotes === 'function') {
            window.renderFinanceQuotes();
          }
        }
      } catch (err) {
        console.error('Error fetching quotes:', err);
      }
    },

    openCreateModal: function (leadId, clientId) {
      if (typeof window.showView === 'function') {
        window.showView('budget');
      }
      // If lead or client is provided, send to budget calculator iframe
      if (leadId || clientId) {
        setTimeout(() => {
          let clientName = '';
          let projectName = '';
          if (clientId && typeof JMOS_STATE !== 'undefined' && JMOS_STATE.clients) {
            const client = JMOS_STATE.clients.find(c => String(c.id) === String(clientId));
            if (client) {
              clientName = client.client_name || client.name || '';
              projectName = `${clientName} — Brand Film`;
            }
          } else if (leadId && typeof CRM_STATE !== 'undefined' && CRM_STATE.leads) {
            const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
            if (lead) {
              clientName = lead.lead_name || lead.company || '';
              projectName = `${lead.company || lead.lead_name} — Commercial`;
            }
          }
          const frame = document.getElementById('budgetFrame');
          if (frame && frame.contentWindow) {
            frame.contentWindow.postMessage({
              action: 'setClient',
              client: clientName,
              project: projectName,
              leadId: leadId,
              clientId: clientId
            }, '*');
          }
        }, 120);
      }
    },

    openCreateQuoteModal: function (leadId, clientId) {
      return this.openCreateModal(leadId, clientId);
    },

    openEditModal: function (id) {
      if (typeof window.openEditQuoteModal === 'function') {
        window.openEditQuoteModal(id);
      }
    },

    viewDetail: function (id) {
      if (typeof window.viewQuoteDetail === 'function') {
        window.viewQuoteDetail(id);
      }
    },

    sendWhatsApp: function (id) {
      if (typeof window.sendQuoteWhatsApp === 'function') {
        window.sendQuoteWhatsApp(id);
      }
    },

    sendEmail: function (id) {
      if (typeof window.sendQuoteEmail === 'function') {
        window.sendQuoteEmail(id);
      }
    },

    openUpgradeModal: function (quote) {
      if (typeof window.openUpgradeQuoteModal === 'function') {
        window.openUpgradeQuoteModal(quote);
      }
    },

    renderQuotesTable: function () {
      const tbody = document.getElementById('finQuotesTableBody');
      if (!tbody) return;

      if (this.quotes.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center;padding:32px 20px;color:var(--muted)">
              No quotations created yet. Click <b>+ Create Quotation</b> to draft a proposal.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = this.quotes.map(q => {
        const total = parseFloat(q.total_amount) || 0;
        const recipient = q.recipient_name || (q.client ? q.client.client_name : (q.lead ? q.lead.lead_name : 'Client'));
        const dateStr = q.created_at ? q.created_at.split('T')[0] : '—';
        const st = (q.status || 'draft').toLowerCase();
        let pillClass = 'tint-amber';
        let statusLabel = 'DRAFT';
        if (st === 'sent') { pillClass = 'tint-blue'; statusLabel = 'SENT'; }
        if (st === 'accepted' || st === 'approved') { pillClass = 'tint-green'; statusLabel = 'APPROVED'; }
        if (st === 'invoiced') { pillClass = 'tint-green'; statusLabel = 'INVOICED'; }
        if (st === 'rejected' || st === 'expired' || st === 'declined') { pillClass = 'tint-red'; statusLabel = 'REJECTED'; }

        const canUpgrade = st !== 'invoiced';

        return `
          <tr style="cursor:pointer" onclick="window.viewQuoteDetail(${q.id})">
            <td class="mono" style="font-weight:700;color:var(--red)">${q.quote_number || ('QT-' + q.id)}</td>
            <td>
              <div style="font-weight:600;color:var(--ink)">${q.title || 'Commercial Proposal'}</div>
              <div style="font-size:11px;color:var(--muted)">${recipient}</div>
            </td>
            <td class="mono" style="font-weight:700;color:var(--ink)">KES ${total.toLocaleString()}</td>
            <td><span class="pill ${pillClass}">${statusLabel}</span></td>
            <td style="font-size:12px;color:var(--muted)">${q.validity_days ? q.validity_days + ' days' : '14 days'}</td>
            <td style="font-size:12px;color:var(--muted)">${dateStr}</td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <div style="display:flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap">
                <button type="button" class="btn small" onclick="window.viewQuoteDetail(${q.id})" style="font-size:11px;padding:3px 7px" title="View & Print Quote PDF">PDF / View</button>
                <button type="button" class="btn small" onclick="window.openEditQuoteModal(${q.id})" style="font-size:11px;padding:3px 7px" title="Edit quotation deliverables">Edit</button>
                ${canUpgrade ? `
                  <button type="button" class="btn small primary" onclick="window.openUpgradeQuoteModalFromRow(${q.id})" style="font-size:11px;padding:3px 8px;background:var(--red);border-color:var(--red)" title="Generate official invoice from quote">➔ Invoice</button>
                ` : `
                  <button type="button" class="btn small" onclick="if(window.openInvoiceDetailModal && ${q.converted_invoice_id || 'null'}){ window.openInvoiceDetailModal(${q.converted_invoice_id}); }" style="font-size:11px;padding:3px 7px;color:var(--green);border-color:rgba(19,115,51,0.3)" title="View converted invoice">✓ Invoiced</button>
                `}
              </div>
            </td>
          </tr>
        `;
      }).join('');
    },

    renderQuotesMainPageTable: function () {
      const tbody = document.getElementById('quotesMainTableBody');
      const allQuotes = this.quotes || [];

      // Calculate Metrics
      let totalCount = allQuotes.length;
      let totalValue = 0;
      let approvedValue = 0;
      let invoicedValue = 0;

      allQuotes.forEach(q => {
        const amt = parseFloat(q.total_amount) || 0;
        const st = (q.status || 'draft').toLowerCase();
        totalValue += amt;
        if (st === 'accepted' || st === 'approved') {
          approvedValue += amt;
        } else if (st === 'invoiced') {
          invoicedValue += amt;
        }
      });

      const kpiCount = document.getElementById('qKpiTotalCount');
      const kpiTotal = document.getElementById('qKpiTotalValue');
      const kpiApproved = document.getElementById('qKpiApprovedValue');
      const kpiInvoiced = document.getElementById('qKpiInvoicedValue');
      const badgeAll = document.getElementById('qBadgeAll');

      if (kpiCount) kpiCount.textContent = totalCount;
      if (kpiTotal) kpiTotal.textContent = 'KES ' + Math.round(totalValue).toLocaleString();
      if (kpiApproved) kpiApproved.textContent = 'KES ' + Math.round(approvedValue).toLocaleString();
      if (kpiInvoiced) kpiInvoiced.textContent = 'KES ' + Math.round(invoicedValue).toLocaleString();
      if (badgeAll) badgeAll.textContent = totalCount;

      if (!tbody) return;

      // Filter by status & search
      const filter = this.activeFilter || 'all';
      const search = (this.searchQuery || '').toLowerCase().trim();

      let filtered = allQuotes.filter(q => {
        const st = (q.status || 'draft').toLowerCase();
        if (filter !== 'all') {
          if (filter === 'accepted' || filter === 'approved') {
            if (st !== 'accepted' && st !== 'approved') return false;
          } else if (st !== filter) {
            return false;
          }
        }
        if (search) {
          const num = (q.quote_number || '').toLowerCase();
          const title = (q.title || '').toLowerCase();
          const recip = (q.recipient_name || '').toLowerCase();
          const clientName = q.client ? (q.client.client_name || q.client.name || '').toLowerCase() : '';
          const leadName = q.lead ? (q.lead.lead_name || q.lead.company || '').toLowerCase() : '';
          if (!num.includes(search) && !title.includes(search) && !recip.includes(search) && !clientName.includes(search) && !leadName.includes(search)) {
            return false;
          }
        }
        return true;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center;padding:36px 20px;color:var(--muted)">
              ${allQuotes.length === 0 ? 'No quotations created yet. Click <b>+ Create Quotation</b> or generate one from the <b>Production Budget</b>.' : 'No quotations matching current search/filter.'}
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(q => {
        const total = parseFloat(q.total_amount) || 0;
        const recipient = q.recipient_name || (q.client ? (q.client.client_name || q.client.name) : (q.lead ? (q.lead.lead_name || q.lead.company) : 'Client'));
        const dateStr = q.created_at ? q.created_at.split('T')[0] : '—';
        const st = (q.status || 'draft').toLowerCase();
        let pillClass = 'tint-amber';
        let statusLabel = 'DRAFT';
        if (st === 'sent') { pillClass = 'tint-blue'; statusLabel = 'SENT'; }
        if (st === 'accepted' || st === 'approved') { pillClass = 'tint-green'; statusLabel = 'APPROVED ✓'; }
        if (st === 'invoiced') { pillClass = 'tint-green'; statusLabel = 'INVOICED'; }
        if (st === 'rejected' || st === 'expired' || st === 'declined') { pillClass = 'tint-red'; statusLabel = 'REJECTED'; }

        const canUpgrade = st !== 'invoiced';

        return `
          <tr style="cursor:pointer" onclick="window.viewQuoteDetail(${q.id})">
            <td class="mono" style="font-weight:700;color:var(--red)">${q.quote_number || ('QT-' + q.id)}</td>
            <td>
              <div style="font-weight:600;color:var(--ink)">${esc(q.title || 'Commercial Proposal')}</div>
              <div style="font-size:11.5px;color:var(--muted);margin-top:2px">${esc(recipient)}</div>
            </td>
            <td class="mono" style="font-weight:700;color:var(--ink)">KES ${total.toLocaleString()}</td>
            <td><span class="pill ${pillClass}">${statusLabel}</span></td>
            <td style="font-size:12px;color:var(--muted)">${q.validity_days ? q.validity_days + ' days' : '14 days'}</td>
            <td style="font-size:12px;color:var(--muted)">${dateStr}</td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <div style="display:flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap">
                <button type="button" class="btn small" onclick="window.viewQuoteDetail(${q.id})" style="font-size:11px;padding:3px 7px" title="View & Print Quote PDF">PDF / View</button>
                <button type="button" class="btn small" onclick="window.openEditQuoteModal(${q.id})" style="font-size:11px;padding:3px 7px" title="Edit quotation deliverables">Edit</button>
                <button type="button" class="btn small" onclick="window.sendQuoteEmail(${q.id})" style="font-size:11px;padding:3px 7px" title="Send email with client approval button">📧</button>
                <button type="button" class="btn small" onclick="window.sendQuoteWhatsApp(${q.id})" style="font-size:11px;padding:3px 7px" title="Send via WhatsApp with approval link">💬</button>
                ${canUpgrade ? `
                  <button type="button" class="btn small primary" onclick="window.openUpgradeQuoteModalFromRow(${q.id})" style="font-size:11px;padding:3px 8px;background:var(--red);border-color:var(--red)" title="Generate official invoice from quote">➔ Invoice</button>
                ` : `
                  <button type="button" class="btn small" onclick="if(window.openInvoiceDetailModal && ${q.converted_invoice_id || 'null'}){ window.openInvoiceDetailModal(${q.converted_invoice_id}); }" style="font-size:11px;padding:3px 7px;color:var(--green);border-color:rgba(19,115,51,0.3)" title="View converted invoice">✓ Invoiced</button>
                `}
              </div>
            </td>
          </tr>
        `;
      }).join('');
    }
  };

  // Helper to append a deliverable line item row
  window.addQuoteItemRow = function (item) {
    const tbody = document.getElementById('quoteItemsTableBody');
    if (!tbody) return;

    const desc = item && item.description ? item.description : '';
    const qty = item && item.quantity ? item.quantity : 1;
    const rate = item && (item.rate || item.unit_price) ? (item.rate || item.unit_price) : '';
    const amt = qty * (parseFloat(rate) || 0);

    const tr = document.createElement('tr');
    tr.className = 'quote-line-row';
    tr.innerHTML = `
      <td style="padding:4px">
        <input type="text" class="q-item-desc" placeholder="e.g. 4K Commercial Shoot & Drone Coverage" value="${desc.replace(/"/g, '&quot;')}" required style="font-size:12px;padding:5px 8px;width:100%;border:1px solid var(--line);border-radius:6px;background:var(--surface);color:var(--ink)">
      </td>
      <td style="padding:4px;width:70px">
        <input type="number" class="q-item-qty" min="1" value="${qty}" required oninput="window.calcQuoteTotals()" style="font-size:12px;padding:5px;width:100%;text-align:center;border:1px solid var(--line);border-radius:6px;background:var(--surface);color:var(--ink)">
      </td>
      <td style="padding:4px;width:120px">
        <input type="number" class="q-item-rate" placeholder="Rate" value="${rate}" required oninput="window.calcQuoteTotals()" style="font-size:12px;padding:5px 8px;width:100%;text-align:right;border:1px solid var(--line);border-radius:6px;background:var(--surface);color:var(--ink)">
      </td>
      <td style="padding:4px 8px;width:120px;text-align:right;font-family:'IBM Plex Mono',monospace;font-weight:600;color:var(--ink)" class="q-item-total">
        KES ${amt.toLocaleString()}
      </td>
      <td style="padding:4px;width:30px;text-align:center">
        <button type="button" onclick="this.closest('tr').remove(); window.calcQuoteTotals();" style="border:none;background:none;color:var(--red);cursor:pointer;font-size:16px;font-weight:700">&times;</button>
      </td>
    `;
    tbody.appendChild(tr);
    window.calcQuoteTotals();
  };

  // Compute total amount and update display
  window.calcQuoteTotals = function () {
    const rows = document.querySelectorAll('.quote-line-row');
    let subtotal = 0;

    rows.forEach(row => {
      const qty = parseFloat(row.querySelector('.q-item-qty')?.value) || 0;
      const rate = parseFloat(row.querySelector('.q-item-rate')?.value) || 0;
      const rowAmt = qty * rate;
      const totalCell = row.querySelector('.q-item-total');
      if (totalCell) totalCell.textContent = 'KES ' + rowAmt.toLocaleString();
      subtotal += rowAmt;
    });

    const discount = parseFloat(document.getElementById('quoteDiscount')?.value) || 0;
    const finalTotal = Math.max(0, subtotal - discount);

    const displayEl = document.getElementById('quoteTotalDisplay');
    if (displayEl) displayEl.textContent = 'KES ' + finalTotal.toLocaleString();

    const hiddenTotal = document.getElementById('quoteTotalAmount');
    if (hiddenTotal) hiddenTotal.value = finalTotal;
  };

  // Populate Client / Lead dropdown for Quotation Modals
  window.populateQuoteClientLeadDropdown = async function (selectedVal = '') {
    const select = document.getElementById('quoteSelectClientOrLead');
    if (!select) return;

    let clients = (typeof JMOS_STATE !== 'undefined' && JMOS_STATE.clients) ? JMOS_STATE.clients : [];
    let leads = (typeof CRM_STATE !== 'undefined' && CRM_STATE.leads) ? CRM_STATE.leads : [];

    // If memory cache is empty, fetch in background
    if (clients.length === 0 || leads.length === 0) {
      try {
        const token = localStorage.getItem('jmos_api_token') || (typeof JMOS_STATE !== 'undefined' ? JMOS_STATE.apiToken : null);
        const headers = { 'Accept': 'application/json', 'Authorization': token ? `Bearer ${token}` : '' };
        const [cRes, lRes] = await Promise.all([
          fetch('/api/clients', { headers }).then(r => r.json()).catch(() => null),
          fetch('/api/leads', { headers }).then(r => r.json()).catch(() => null)
        ]);
        if (cRes && cRes.data) {
          clients = cRes.data;
          if (typeof JMOS_STATE !== 'undefined') JMOS_STATE.clients = clients;
        }
        if (lRes && lRes.data) {
          leads = lRes.data;
          if (typeof CRM_STATE !== 'undefined') CRM_STATE.leads = leads;
        }
      } catch (_) {}
    }

    let html = '<option value="">— Choose Client or Lead in System (or enter new) —</option>';

    if (clients && clients.length) {
      html += '<optgroup label="🏢 Existing Clients">';
      clients.forEach(c => {
        const name = c.client_name || c.name || 'Client';
        const contact = c.email || c.phone || '';
        html += `<option value="client_${c.id}">${esc(name)}${contact ? ' (' + esc(contact) + ')' : ''}</option>`;
      });
      html += '</optgroup>';
    }

    if (leads && leads.length) {
      html += '<optgroup label="🎯 Pipeline Leads">';
      leads.forEach(l => {
        const name = l.lead_name || l.company || 'Lead';
        const companyStr = l.company && l.company !== l.lead_name ? ' · ' + l.company : '';
        html += `<option value="lead_${l.id}">${esc(name)}${companyStr}</option>`;
      });
      html += '</optgroup>';
    }

    select.innerHTML = html;
    if (selectedVal) {
      select.value = selectedVal;
    }
  };

  // Handler when user selects a Client or Lead from dropdown in quote modal
  window.onSelectQuoteClientOrLead = function (val) {
    const leadIdInput = document.getElementById('quoteLeadId');
    const clientIdInput = document.getElementById('quoteClientId');
    const recipientInput = document.getElementById('quoteRecipient');
    const titleInput = document.getElementById('quoteTitle');
    const emailInput = document.getElementById('quoteEmail');
    const phoneInput = document.getElementById('quotePhone');

    if (!val) {
      if (leadIdInput) leadIdInput.value = '';
      if (clientIdInput) clientIdInput.value = '';
      return;
    }

    if (val.startsWith('client_')) {
      const cid = val.replace('client_', '');
      const clients = (typeof JMOS_STATE !== 'undefined' && JMOS_STATE.clients) ? JMOS_STATE.clients : [];
      const client = clients.find(c => String(c.id) === String(cid));
      if (client) {
        if (clientIdInput) clientIdInput.value = client.id;
        if (leadIdInput) leadIdInput.value = '';
        if (recipientInput) recipientInput.value = client.client_name || client.name || '';
        if (titleInput && (!titleInput.value || titleInput.value.includes('Proposal'))) {
          titleInput.value = `${client.client_name || client.name} · Commercial Proposal`;
        }
        if (emailInput) emailInput.value = client.email || client.primary_contact_email || '';
        if (phoneInput) phoneInput.value = client.phone || client.primary_contact_phone || '';
      }
    } else if (val.startsWith('lead_')) {
      const lid = val.replace('lead_', '');
      const leads = (typeof CRM_STATE !== 'undefined' && CRM_STATE.leads) ? CRM_STATE.leads : [];
      const lead = leads.find(l => String(l.id) === String(lid));
      if (lead) {
        if (leadIdInput) leadIdInput.value = lead.id;
        if (clientIdInput) clientIdInput.value = '';
        if (recipientInput) recipientInput.value = lead.lead_name || lead.company || '';
        if (titleInput && (!titleInput.value || titleInput.value.includes('Proposal'))) {
          titleInput.value = `${lead.company || lead.lead_name} · Production Proposal`;
        }
        if (emailInput) emailInput.value = lead.email || '';
        if (phoneInput) phoneInput.value = lead.phone || '';
      }
    }
  };

  // Filter handler for dedicated quotes page status tabs
  window.filterQuotesPageStatus = function (status) {
    if (window.JMOS_QUOTES) {
      window.JMOS_QUOTES.activeFilter = status;
      window.JMOS_QUOTES.renderQuotesMainPageTable();
    }

    // Update tab active classes
    const tabs = {
      'all': 'qTabAll',
      'draft': 'qTabDraft',
      'sent': 'qTabSent',
      'accepted': 'qTabApproved',
      'invoiced': 'qTabInvoiced'
    };

    Object.keys(tabs).forEach(k => {
      const el = document.getElementById(tabs[k]);
      if (el) el.classList.toggle('active', k === status);
    });
  };

  // Search input handler for dedicated quotes page
  window.onQuotesPageSearchChange = function (query) {
    if (window.JMOS_QUOTES) {
      window.JMOS_QUOTES.searchQuery = query || '';
      window.JMOS_QUOTES.renderQuotesMainPageTable();
    }
  };

  // Open Create Quote — redirects directly to the Production Budget calculator
  window.openCreateQuoteModal = function (leadId, clientId) {
    if (window.JMOS_QUOTES) {
      window.JMOS_QUOTES.openCreateModal(leadId, clientId);
    } else if (typeof window.showView === 'function') {
      window.showView('budget');
    }
  };

  // Open Edit Quote Modal
  window.openEditQuoteModal = async function (quoteId) {
    let q = window.JMOS_QUOTES.quotes.find(x => String(x.id) === String(quoteId));
    if (!q && window.JMOS_QUOTES.activeQuote && String(window.JMOS_QUOTES.activeQuote.id) === String(quoteId)) {
      q = window.JMOS_QUOTES.activeQuote;
    }

    if (!q) {
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/quotes/${quoteId}`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (res.ok && data.data) {
          q = data.data;
        }
      } catch (_) {}
    }

    if (!q) {
      if (window.showToast) window.showToast('Error', 'Unable to find quotation to edit', true);
      return;
    }

    window.JMOS_QUOTES.activeQuote = q;

    document.getElementById('quoteFormId').value = q.id;
    document.getElementById('quoteModalTitle').textContent = `Edit Quotation · ${q.quote_number || ('QT-' + q.id)}`;
    document.getElementById('quoteLeadId').value = q.lead_id || '';
    document.getElementById('quoteClientId').value = q.client_id || '';
    document.getElementById('quoteRecipient').value = q.recipient_name || '';
    document.getElementById('quoteTitle').value = q.title || '';
    document.getElementById('quoteEmail').value = q.recipient_email || '';
    document.getElementById('quotePhone').value = q.recipient_phone || '';
    document.getElementById('quoteValidity').value = q.validity_days || 14;
    document.getElementById('quoteDiscount').value = q.discount || 0;
    document.getElementById('quoteNotes').value = q.notes || '';

    let selVal = '';
    if (q.client_id) selVal = 'client_' + q.client_id;
    else if (q.lead_id) selVal = 'lead_' + q.lead_id;

    window.populateQuoteClientLeadDropdown(selVal);

    const tbody = document.getElementById('quoteItemsTableBody');
    if (tbody) {
      tbody.innerHTML = '';
      const items = Array.isArray(q.items) && q.items.length ? q.items : [
        { description: q.title || 'Production & Creative Services', quantity: 1, rate: q.total_amount || 150000 }
      ];
      items.forEach(it => {
        window.addQuoteItemRow({
          description: it.description,
          quantity: it.quantity,
          rate: it.rate || it.unit_price
        });
      });
    }

    window.calcQuoteTotals();
    window.openModal('quoteModal');
  };

  window.openEditQuoteModalFromDetail = function () {
    const qid = document.getElementById('qdmQuoteId')?.value;
    if (qid) {
      window.closeModal('quoteDetailModal');
      window.openEditQuoteModal(qid);
    }
  };

  // Submit quote create / update
  window.submitQuoteForm = async function (dispatchMode = null) {
    const quoteId = document.getElementById('quoteFormId')?.value;
    const recipient = document.getElementById('quoteRecipient')?.value.trim();
    const title = document.getElementById('quoteTitle')?.value.trim();
    let email = document.getElementById('quoteEmail')?.value.trim();
    let phone = document.getElementById('quotePhone')?.value.trim();

    if (!recipient || !title) {
      if (window.showToast) window.showToast('Validation Error', 'Please fill in the Recipient and Quote Title', true);
      return;
    }

    if (dispatchMode === 'email' && (!email || !email.includes('@'))) {
      const promptEmail = prompt('Enter recipient email address to send quotation:', email || '');
      if (promptEmail && promptEmail.includes('@')) {
        email = promptEmail.trim();
        if (document.getElementById('quoteEmail')) document.getElementById('quoteEmail').value = email;
      } else {
        if (window.showToast) window.showToast('Email Required', 'Valid recipient email address is required to dispatch via Email', true);
        return;
      }
    }

    if (dispatchMode === 'whatsapp' && !phone) {
      const promptPhone = prompt(`Enter ${recipient}'s WhatsApp phone number:`, '+254');
      if (promptPhone) {
        phone = promptPhone.trim();
        if (document.getElementById('quotePhone')) document.getElementById('quotePhone').value = phone;
      }
    }

    const rows = document.querySelectorAll('.quote-line-row');
    const items = [];
    let subtotal = 0;

    rows.forEach(row => {
      const desc = row.querySelector('.q-item-desc')?.value.trim();
      const qty = parseFloat(row.querySelector('.q-item-qty')?.value) || 1;
      const rate = parseFloat(row.querySelector('.q-item-rate')?.value) || 0;
      if (desc && rate > 0) {
        const amt = qty * rate;
        subtotal += amt;
        items.push({
          description: desc,
          quantity: qty,
          rate: rate,
          amount: amt
        });
      }
    });

    if (items.length === 0) {
      if (window.showToast) window.showToast('Line Items Required', 'Please add at least one line item with a rate', true);
      return;
    }

    const discount = parseFloat(document.getElementById('quoteDiscount')?.value) || 0;
    const totalAmount = Math.max(0, subtotal - discount);

    const payload = {
      title: title,
      recipient_name: recipient,
      recipient_email: email || null,
      recipient_phone: phone || null,
      lead_id: document.getElementById('quoteLeadId')?.value || null,
      client_id: document.getElementById('quoteClientId')?.value || null,
      subtotal: subtotal,
      discount: discount,
      tax: 0,
      total_amount: totalAmount,
      validity_days: parseInt(document.getElementById('quoteValidity')?.value || '14', 10),
      notes: document.getElementById('quoteNotes')?.value.trim() || '',
      items: items
    };

    const saveBtn = document.getElementById('saveQuoteBtn');
    const saveUpgradeBtn = document.getElementById('saveAndUpgradeQuoteBtn');
    const saveOnlyBtn = document.getElementById('saveOnlyQuoteBtn');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving...'; }
    if (saveUpgradeBtn) { saveUpgradeBtn.disabled = true; }
    if (saveOnlyBtn) { saveOnlyBtn.disabled = true; }

    try {
      const token = localStorage.getItem('jmos_api_token');
      const url = quoteId ? `/api/quotes/${quoteId}` : '/api/quotes';
      const method = quoteId ? 'PUT' : 'POST';

      const res = await fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Authorization': token ? `Bearer ${token}` : ''
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to save quotation');

      const savedQuote = data.data || data.quote;
      window.JMOS_QUOTES.activeQuote = savedQuote;
      window.closeModal('quoteModal');

      // Refresh cache & views
      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      window.JMOS_QUOTES.loadQuotes();

      if (dispatchMode === 'upgrade') {
        if (window.showToast) {
          window.showToast('Quotation Saved', 'Now configure invoice terms & preview invoice PDF ➔');
        }
        window.openUpgradeQuoteModal();
      } else if (dispatchMode === 'email' && email && savedQuote?.id) {
        try {
          await fetch(`/api/quotes/${savedQuote.id}/send-email`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': token ? `Bearer ${token}` : ''
            },
            body: JSON.stringify({ email: email })
          });
          if (window.showToast) {
            window.showToast('Quote Generated & Emailed', `Quotation dispatched to ${email} successfully! 📧`);
          }
        } catch (_) {}
      } else if (dispatchMode === 'whatsapp' && savedQuote?.id) {
        try {
          const waRes = await fetch(`/api/quotes/${savedQuote.id}/whatsapp`, {
            headers: {
              'Accept': 'application/json',
              'Authorization': token ? `Bearer ${token}` : ''
            }
          });
          const waData = await waRes.json();
          if (waData.whatsapp_url) {
            window.open(waData.whatsapp_url, '_blank');
          }
          if (window.showToast) {
            window.showToast('Quote Saved & WhatsApp Opened', `Quotation ready for WhatsApp delivery! 💬`);
          }
        } catch (_) {}
      } else if (window.showToast) {
        window.showToast('Quotation Saved', 'Quotation details updated successfully.');
      }
    } catch (err) {
      if (window.showToast) window.showToast('Save Error', err.message, true);
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Save &amp; Email';
      }
      if (saveUpgradeBtn) {
        saveUpgradeBtn.disabled = false;
      }
      if (saveOnlyBtn) {
        saveOnlyBtn.disabled = false;
      }
    }
  };

  // View Quote Detail Drawer / Modal
  window.viewQuoteDetail = async function (quoteId) {
    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${quoteId}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to fetch quotation details');

      const q = data.data || data.quote;
      window.JMOS_QUOTES.activeQuote = q;

      const items = Array.isArray(q.items) ? q.items : [];
      const subtotal = items.reduce((acc, it) => acc + (parseFloat(it.amount || (it.quantity * (it.rate || it.unit_price))) || 0), 0) || parseFloat(q.subtotal || q.total_amount) || 0;
      const discount = parseFloat(q.discount) || 0;
      const total = parseFloat(q.total_amount) || Math.max(0, subtotal - discount);
      const isAccepted = (q.status || '').toLowerCase() === 'accepted';
      const isInvoiced = (q.status || '').toLowerCase() === 'invoiced';

      const dateStr = q.created_at
        ? new Date(q.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
        : new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

      // Header values
      const qdmId = document.getElementById('qdmQuoteId');
      if (qdmId) qdmId.value = q.id;

      const titleEl = document.getElementById('qdmTitle');
      if (titleEl) titleEl.textContent = q.title || 'Commercial Proposal';

      const subEl = document.getElementById('qdmSubtitle');
      if (subEl) subEl.textContent = `${q.quote_number || ('QT-' + q.id)} · Prepared for ${q.recipient_name || 'Client'}`;

      const totalBadge = document.getElementById('qdmTotalBadge');
      if (totalBadge) totalBadge.textContent = 'KES ' + total.toLocaleString();

      const statusBadge = document.getElementById('qdmStatusBadge');
      if (statusBadge) {
        statusBadge.textContent = (q.status ? q.status.toUpperCase() : 'DRAFT');
        statusBadge.className = 'badge ' + (isInvoiced ? 'tint-green' : isAccepted ? 'tint-green' : (q.status || '').toLowerCase() === 'sent' ? 'tint-blue' : 'tint-amber');
      }

      const valText = document.getElementById('qdmValidityText');
      if (valText) valText.textContent = `Valid for ${q.validity_days || 14} days from issue`;

      // Printable Document Elements
      const docNo = document.getElementById('qdmDocQuoteNo');
      if (docNo) docNo.textContent = q.quote_number || ('QT-' + q.id);

      const docDate = document.getElementById('qdmDocDate');
      if (docDate) docDate.textContent = dateStr;

      const docVal = document.getElementById('qdmDocValidity');
      if (docVal) docVal.textContent = `${q.validity_days || 14} Days`;

      const docRecName = document.getElementById('qdmDocRecipientName');
      if (docRecName) docRecName.textContent = q.recipient_name || 'Valued Client';

      const docRecContact = document.getElementById('qdmDocRecipientContact');
      if (docRecContact) {
        const parts = [];
        if (q.recipient_email) parts.push(q.recipient_email);
        if (q.recipient_phone) parts.push(q.recipient_phone);
        docRecContact.textContent = parts.length ? parts.join(' · ') : 'Direct Client Account';
      }

      const docScope = document.getElementById('qdmDocScopeTitle');
      if (docScope) docScope.textContent = q.title || 'Production & Creative Services';

      const docSubtotal = document.getElementById('qdmDocSubtotal');
      if (docSubtotal) docSubtotal.textContent = 'KES ' + subtotal.toLocaleString();

      const docDiscount = document.getElementById('qdmDocDiscount');
      const docDiscountRow = document.getElementById('qdmDocDiscountRow');
      if (docDiscount) docDiscount.textContent = '- KES ' + discount.toLocaleString();
      if (docDiscountRow) docDiscountRow.style.display = discount > 0 ? 'flex' : 'none';

      const docTotal = document.getElementById('qdmDocTotal');
      if (docTotal) docTotal.textContent = 'KES ' + total.toLocaleString();

      const itemsWrap = document.getElementById('qdmItemsContainer');
      if (itemsWrap) {
        itemsWrap.innerHTML = `
          <div class="tablewrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%">
            <table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:12px;min-width:460px">
              <thead>
                <tr style="background:#f4f5f7;color:#555;text-align:left;border-bottom:1px solid #ddd">
                  <th style="padding:8px 10px">Deliverable / Scope Specification</th>
                  <th style="padding:8px 10px;text-align:center;width:60px">Qty</th>
                  <th style="padding:8px 10px;text-align:right;width:120px">Rate (KES)</th>
                  <th style="padding:8px 10px;text-align:right;width:130px">Amount (KES)</th>
                </tr>
              </thead>
              <tbody>
                ${items.map(it => {
                  const itQty = parseFloat(it.quantity) || 1;
                  const itRate = parseFloat(it.rate || it.unit_price) || 0;
                  const itAmt = parseFloat(it.amount) || (itQty * itRate);
                  return `
                    <tr style="border-bottom:1px solid #eee">
                      <td style="padding:8px 10px;font-weight:600;color:#1a1a1a">${it.description}</td>
                      <td style="padding:8px 10px;text-align:center;color:#444">${itQty}</td>
                      <td style="padding:8px 10px;text-align:right;font-family:'IBM Plex Mono',monospace;color:#444">${itRate.toLocaleString()}</td>
                      <td style="padding:8px 10px;text-align:right;font-family:'IBM Plex Mono',monospace;font-weight:700;color:#1a1a1a">${itAmt.toLocaleString()}</td>
                    </tr>
                  `;
                }).join('')}
              </tbody>
            </table>
          </div>
        `;
      }

      const notesBox = document.getElementById('qdmNotesBox');
      if (notesBox) {
        notesBox.textContent = q.notes || 'Full production includes high-end cinema optics, aerial drone coverage, location sound recording, professional edit, color grade & sound design.';
      }

      // Approve button state
      const approveBtn = document.getElementById('qdmApproveBtn');
      if (approveBtn) {
        if (isInvoiced) {
          approveBtn.style.display = 'none';
        } else if (isAccepted) {
          approveBtn.style.display = 'inline-flex';
          approveBtn.disabled = true;
          approveBtn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>✓ Approved';
          approveBtn.style.opacity = '0.7';
        } else {
          approveBtn.style.display = 'inline-flex';
          approveBtn.disabled = false;
          approveBtn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Approve Quote';
          approveBtn.style.opacity = '1';
        }
      }

      // Upgrade button state
      const upgBtn = document.getElementById('qdmUpgradeInvoiceBtn');
      if (upgBtn) {
        if (isInvoiced) {
          upgBtn.disabled = false;
          upgBtn.textContent = 'View Invoice PDF ➔';
          upgBtn.onclick = function () {
            window.closeModal('quoteDetailModal');
            if (q.converted_invoice_id && typeof window.openInvoiceDetailModal === 'function') {
              window.openInvoiceDetailModal(q.converted_invoice_id);
            }
          };
          upgBtn.style.background = 'var(--green)';
          upgBtn.style.borderColor = 'var(--green)';
        } else {
          upgBtn.disabled = false;
          upgBtn.textContent = 'Generate Invoice ➔';
          upgBtn.onclick = function () { window.openUpgradeQuoteModal(); };
          upgBtn.style.background = 'var(--red)';
          upgBtn.style.borderColor = 'var(--red)';
        }
      }

      window.openModal('quoteDetailModal');
    } catch (err) {
      if (window.showToast) window.showToast('Preview Error', err.message, true);
    }
  };

  // Approve Active Quote and trigger upgrade prompt
  window.approveActiveQuote = async function () {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;

    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${q.id}/approve`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to approve quotation');

      const updatedQuote = data.data || data.quote;
      window.JMOS_QUOTES.activeQuote = updatedQuote;

      if (window.showToast) {
        window.showToast('Quotation Approved! 🎉', 'Proposal marked as approved. Configure invoice terms to preview invoice PDF.');
      }

      // Update badges
      const statusBadge = document.getElementById('qdmStatusBadge');
      if (statusBadge) {
        statusBadge.textContent = 'APPROVED';
        statusBadge.className = 'badge tint-green';
      }

      const approveBtn = document.getElementById('qdmApproveBtn');
      if (approveBtn) {
        approveBtn.disabled = true;
        approveBtn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>✓ Approved';
        approveBtn.style.opacity = '0.7';
      }

      // Refresh listings
      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      window.JMOS_QUOTES.loadQuotes();

      // Seamlessly open upgrade modal
      window.openUpgradeQuoteModal();
    } catch (err) {
      if (window.showToast) window.showToast('Approval Error', err.message, true);
    }
  };

  // Print Quote PDF
  window.printQuotePdf = function () {
    const printableDoc = document.getElementById('printableQuoteDoc');
    if (!printableDoc) {
      window.print();
      return;
    }

    const printWindow = window.open('', '_blank', 'width=880,height=900');
    if (!printWindow) {
      window.print();
      return;
    }

    const htmlContent = printableDoc.outerHTML;
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Commercial Quotation - Jeota Media</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=IBM+Plex+Mono:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
          @page { size: A4; margin: 16mm; }
          body { font-family: 'Poppins', -apple-system, sans-serif; background: #fff; color: #1a1a1a; margin: 0; padding: 20px; }
          .mono { font-family: 'IBM Plex Mono', monospace; }
          .printable-invoice-container { border: none !important; padding: 0 !important; }
          table { width: 100%; border-collapse: collapse; }
          @media print {
            body { padding: 0; }
          }
        </style>
      </head>
      <body>
        ${htmlContent}
        <script>
          window.onload = function() {
            setTimeout(function() {
              window.print();
              window.close();
            }, 300);
          };
        </script>
      </body>
      </html>
    `);
    printWindow.document.close();
  };

  // Direct Quote Dispatch via Email
  window.dispatchQuoteEmail = async function () {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;

    let targetEmail = q.recipient_email;
    if (!targetEmail || !targetEmail.includes('@')) {
      const promptEmail = prompt(`Enter email address for ${q.recipient_name}:`, '');
      if (promptEmail && promptEmail.includes('@')) {
        targetEmail = promptEmail.trim();
      } else {
        if (window.showToast) window.showToast('Email Required', 'A valid email address is required to dispatch quotation', true);
        return;
      }
    }

    const confirmed = await window.showConfirmDialog({
      title: 'Dispatch Quotation Email?',
      subtitle: `Quotation #${q.quote_number}`,
      type: 'info',
      confirmText: 'Send Quotation Email',
      message: `Send official commercial quotation <b>${esc(q.quote_number)}</b> directly to <b>${esc(targetEmail)}</b>?`,
      bullets: [
        `Recipient: ${q.recipient_name || 'Client'}`,
        `Quotation Value: KES ${parseFloat(q.total_amount || 0).toLocaleString()}`,
        'Official branded HTML quotation email with line-item breakdown will be delivered.'
      ]
    });
    if (!confirmed) return;

    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${q.id}/send-email`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Authorization': token ? `Bearer ${token}` : ''
        },
        body: JSON.stringify({ email: targetEmail })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to dispatch email');

      if (window.showToast) {
        window.showToast('Quotation Sent via Email', `Quotation dispatched to ${targetEmail} 📧`);
      }
    } catch (err) {
      if (window.showToast) window.showToast('Email Error', err.message, true);
    }
  };

  // Direct Quote Dispatch via WhatsApp
  window.dispatchQuoteWhatsApp = async function () {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;

    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${q.id}/whatsapp`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to generate WhatsApp link');

      if (data.whatsapp_url) {
        window.open(data.whatsapp_url, '_blank');
        if (window.showToast) {
          window.showToast('WhatsApp Opened', 'Quotation summary prepared for WhatsApp! 💬');
        }
      } else {
        if (window.showToast) window.showToast('WhatsApp Error', 'Could not generate WhatsApp dispatch URL. Check phone number.', true);
      }
    } catch (err) {
      if (window.showToast) window.showToast('WhatsApp Error', err.message, true);
    }
  };

  // Open Upgrade Quote to Invoice Modal
  window.openUpgradeQuoteModal = function () {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;

    document.getElementById('upgQuoteId').value = q.id;
    const total = parseFloat(q.total_amount) || 0;
    document.getElementById('upgAmount').value = Math.round(total * 0.6); // Default 60% deposit
    document.getElementById('upgInvoiceType').value = 'Deposit 60%';

    const upgDue = document.getElementById('upgDueDate');
    if (upgDue) {
      upgDue.value = new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10);
    }

    window.openModal('upgradeQuoteModal');
  };

  window.openUpgradeQuoteModalFromRow = function (quoteId) {
    window.viewQuoteDetail(quoteId).then(() => {
      window.openUpgradeQuoteModal();
    });
  };

  window.onUpgTypeChange = function (type) {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;
    const total = parseFloat(q.total_amount) || 0;

    const amountInput = document.getElementById('upgAmount');
    if (type === 'Deposit 60%') {
      amountInput.value = Math.round(total * 0.6);
    } else if (type === 'Full Payment 100%') {
      amountInput.value = total;
    } else if (type === 'Milestone 50%') {
      amountInput.value = Math.round(total * 0.5);
    } else if (type === 'Final Balance 40%') {
      amountInput.value = Math.round(total * 0.4);
    }
  };

  window.submitUpgradeQuoteToInvoice = async function () {
    const quoteId = document.getElementById('upgQuoteId')?.value;
    const amount = parseFloat(document.getElementById('upgAmount')?.value) || 0;
    const dueDate = document.getElementById('upgDueDate')?.value || new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10);
    const invoiceType = document.getElementById('upgInvoiceType')?.value || 'Deposit 60%';

    if (amount <= 0) {
      if (window.showToast) window.showToast('Validation Error', 'Please enter a valid invoice amount', true);
      return;
    }

    const upgBtn = document.getElementById('upgSubmitBtn');
    if (upgBtn) {
      upgBtn.disabled = true;
      upgBtn.textContent = 'Generating Invoice PDF...';
    }

    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${quoteId}/upgrade-invoice`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Authorization': token ? `Bearer ${token}` : ''
        },
        body: JSON.stringify({
          amount: amount,
          due_date: dueDate,
          invoice_type: invoiceType
        })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Failed to upgrade quote to invoice');

      window.closeModal('upgradeQuoteModal');
      window.closeModal('quoteDetailModal');

      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      if (typeof window.ensureInvoices === 'function') window.ensureInvoices();
      window.JMOS_QUOTES.loadQuotes();

      if (window.showToast) {
        window.showToast('Invoice Generated from Quote! 🧾', 'Review invoice PDF and payment details before dispatching to client.');
      }

      // Immediately open the Invoice PDF Detail Modal for preview before sending to client
      if (data.invoice && data.invoice.id && typeof window.openInvoiceDetailModal === 'function') {
        setTimeout(() => {
          window.openInvoiceDetailModal(data.invoice.id);
        }, 150);
      }
    } catch (err) {
      if (window.showToast) window.showToast('Upgrade Error', err.message, true);
    } finally {
      if (upgBtn) {
        upgBtn.disabled = false;
        upgBtn.textContent = 'Generate Invoice & Preview PDF ➔';
      }
    }
  };

  window.deleteActiveQuote = async function () {
    const q = window.JMOS_QUOTES.activeQuote;
    if (!q) return;

    const confirmed = await window.showConfirmDialog({
      title: 'Delete Quotation?',
      subtitle: `Quotation #${q.quote_number}`,
      type: 'danger',
      confirmText: 'Delete Quotation',
      message: `Permanently delete quotation <b>${esc(q.quote_number)}</b> (${esc(q.title || 'Quotation')})?`,
      bullets: [
        'This record will be permanently purged from the database.',
        'This action cannot be undone.'
      ]
    });
    if (!confirmed) return;

    try {
      const token = localStorage.getItem('jmos_api_token');
      const res = await fetch(`/api/quotes/${q.id}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });

      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || 'Failed to delete quotation');
      }

      if (window.showToast) window.showToast('Quotation Deleted', 'Quotation record removed');
      window.closeModal('quoteDetailModal');

      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      window.JMOS_QUOTES.loadQuotes();
    } catch (err) {
      if (window.showToast) window.showToast('Delete Error', err.message, true);
    }
  };

  function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_QUOTES.init();
  });
})();
