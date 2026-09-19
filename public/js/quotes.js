/* ==========================================================================
   JMOS — Quotations & Invoicing Engine
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_QUOTES = {
    quotes: [],
    activeQuote: null,

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
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch('/api/quotes', {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (res.ok && data.data) {
          this.quotes = data.data;
          this.renderQuotesTable();
        }
      } catch (err) {
        console.error('Error fetching quotes:', err);
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
        if (st === 'sent') pillClass = 'tint-blue';
        if (st === 'accepted' || st === 'invoiced') pillClass = 'tint-green';
        if (st === 'rejected' || st === 'expired') pillClass = 'tint-red';

        return `
          <tr style="cursor:pointer" onclick="window.viewQuoteDetail(${q.id})">
            <td class="mono" style="font-weight:700;color:var(--red)">${q.quote_number || ('QT-' + q.id)}</td>
            <td>
              <div style="font-weight:600;color:var(--ink)">${q.title || 'Commercial Proposal'}</div>
              <div style="font-size:11px;color:var(--muted)">${recipient}</div>
            </td>
            <td class="mono" style="font-weight:700;color:var(--ink)">KES ${total.toLocaleString()}</td>
            <td><span class="pill ${pillClass}">${q.status ? q.status.toUpperCase() : 'DRAFT'}</span></td>
            <td style="font-size:12px;color:var(--muted)">${q.validity_days ? q.validity_days + ' days' : '14 days'}</td>
            <td style="font-size:12px;color:var(--muted)">${dateStr}</td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                <button type="button" class="btn small" onclick="window.viewQuoteDetail(${q.id})" style="font-size:11px;padding:3px 8px">View</button>
                <button type="button" class="btn small primary" onclick="window.openUpgradeQuoteModalFromRow(${q.id})" style="font-size:11px;padding:3px 8px;background:var(--red);border-color:var(--red)">➔ Invoice</button>
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
        <input type="text" class="q-item-desc" placeholder="e.g. 4K Commercial Shoot & Drone Coverage" value="${desc}" required style="font-size:12px;padding:5px 8px;width:100%;border:1px solid var(--line);border-radius:6px;background:var(--surface);color:var(--ink)">
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

  // Open Create Quote Modal
  window.openCreateQuoteModal = function (leadId, clientId) {
    document.getElementById('quoteFormId').value = '';
    document.getElementById('quoteModalTitle').textContent = 'Generate Quotation';
    document.getElementById('quoteLeadId').value = leadId || '';
    document.getElementById('quoteClientId').value = clientId || '';
    document.getElementById('quoteRecipient').value = '';
    document.getElementById('quoteTitle').value = '';
    document.getElementById('quoteEmail').value = '';
    document.getElementById('quotePhone').value = '';
    document.getElementById('quoteValidity').value = '14';
    document.getElementById('quoteDiscount').value = '0';
    document.getElementById('quoteNotes').value = '';

    // Auto fill from lead if present
    if (leadId && typeof CRM_STATE !== 'undefined' && CRM_STATE.leads) {
      const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
      if (lead) {
        document.getElementById('quoteRecipient').value = lead.lead_name || lead.company;
        document.getElementById('quoteTitle').value = `${lead.company || lead.lead_name} · Production Proposal`;
        document.getElementById('quoteEmail').value = lead.email || '';
        document.getElementById('quotePhone').value = lead.phone || '';
      }
    }

    // Auto fill from client if present
    if (clientId && typeof JMOS_STATE !== 'undefined' && JMOS_STATE.clients) {
      const client = JMOS_STATE.clients.find(c => String(c.id) === String(clientId));
      if (client) {
        document.getElementById('quoteRecipient').value = client.name || client.client_name;
        document.getElementById('quoteTitle').value = `${client.name || client.client_name} · Commercial Video Proposal`;
        document.getElementById('quoteEmail').value = client.email || '';
        document.getElementById('quotePhone').value = client.phone || '';
      }
    }

    const tbody = document.getElementById('quoteItemsTableBody');
    if (tbody) {
      tbody.innerHTML = '';
      window.addQuoteItemRow({ description: 'Production & Creative Services', quantity: 1, rate: 150000 });
    }

    window.openModal('quoteModal');
  };

  // Submit quote create / update
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
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving...';
    }

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
      window.closeModal('quoteModal');

      // Dispatch directly if requested
      if (dispatchMode === 'email' && email && savedQuote?.id) {
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
        window.showToast('Quotation generated successfully', 'success');
      }

      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      window.JMOS_QUOTES.loadQuotes();
    } catch (err) {
      if (window.showToast) window.showToast('Save Error', err.message, true);
    } finally {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save & Send to Email';
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

      document.getElementById('qdmQuoteId').value = q.id;
      document.getElementById('qdmTitle').textContent = q.title || 'Quotation Preview';
      document.getElementById('qdmSubtitle').textContent = `${q.quote_number || ('QT-' + q.id)} · Prepared for ${q.recipient_name || 'Client'}`;
      document.getElementById('qdmTotalBadge').textContent = 'KES ' + (parseFloat(q.total_amount) || 0).toLocaleString();
      document.getElementById('qdmStatusBadge').textContent = (q.status || 'Draft').toUpperCase();
      document.getElementById('qdmValidityText').textContent = `Valid for ${q.validity_days || 14} days`;

      const itemsWrap = document.getElementById('qdmItemsContainer');
      if (itemsWrap) {
        const items = Array.isArray(q.items) ? q.items : [];
        itemsWrap.innerHTML = `
          <div class="tablewrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px;margin-bottom:12px;min-width:460px">
              <thead>
                <tr style="background:var(--panel-2);color:var(--muted);text-align:left">
                  <th style="padding:8px 10px">Deliverable</th>
                  <th style="padding:8px 10px;text-align:center">Qty</th>
                  <th style="padding:8px 10px;text-align:right">Rate (KES)</th>
                  <th style="padding:8px 10px;text-align:right">Total (KES)</th>
                </tr>
              </thead>
              <tbody>
                ${items.map(it => `
                  <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:8px 10px;font-weight:600;color:var(--ink)">${it.description}</td>
                    <td style="padding:8px 10px;text-align:center">${it.quantity}</td>
                    <td style="padding:8px 10px;text-align:right;font-family:'IBM Plex Mono',monospace">${Number(it.rate || it.unit_price).toLocaleString()}</td>
                    <td style="padding:8px 10px;text-align:right;font-family:'IBM Plex Mono',monospace;font-weight:700">${Number(it.amount || (it.quantity * (it.rate || it.unit_price))).toLocaleString()}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `;
      }

      const notesBox = document.getElementById('qdmNotesBox');
      if (notesBox) {
        notesBox.textContent = q.notes || 'Scope notes: Full production includes filming gear, lighting kit, sound recording, editing & color grading.';
      }

      // Upgrade button state
      const upgBtn = document.getElementById('qdmUpgradeInvoiceBtn');
      if (upgBtn) {
        if (q.status === 'Invoiced' || q.converted_invoice_id) {
          upgBtn.disabled = true;
          upgBtn.textContent = 'Already Converted to Invoice';
          upgBtn.style.opacity = '0.6';
        } else {
          upgBtn.disabled = false;
          upgBtn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>Upgrade to Invoice ➔';
          upgBtn.style.opacity = '1';
        }
      }

      window.openModal('quoteDetailModal');
    } catch (err) {
      if (window.showToast) window.showToast('Preview Error', err.message, true);
    }
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
      message: `Send official commercial quotation <b>${escHtml(q.quote_number)}</b> directly to <b>${escHtml(targetEmail)}</b>?`,
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
    }
  };

  window.submitUpgradeQuoteToInvoice = async function () {
    const quoteId = document.getElementById('upgQuoteId')?.value;
    const amount = parseFloat(document.getElementById('upgAmount')?.value) || 0;
    const dueDate = document.getElementById('upgDueDate')?.value || '7 days';
    const invoiceType = document.getElementById('upgInvoiceType')?.value || 'Deposit 60%';

    if (amount <= 0) {
      if (window.showToast) window.showToast('Validation Error', 'Please enter a valid invoice amount', true);
      return;
    }

    const upgBtn = document.getElementById('upgSubmitBtn');
    if (upgBtn) {
      upgBtn.disabled = true;
      upgBtn.textContent = 'Converting...';
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

      if (window.showToast) {
        window.showToast('Invoice Created', 'Quote upgraded to live invoice successfully! 🧾');
      }

      window.closeModal('upgradeQuoteModal');
      window.closeModal('quoteDetailModal');

      if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
      if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      if (typeof window.ensureInvoices === 'function') window.ensureInvoices();
      window.JMOS_QUOTES.loadQuotes();
    } catch (err) {
      if (window.showToast) window.showToast('Upgrade Error', err.message, true);
    } finally {
      if (upgBtn) {
        upgBtn.disabled = false;
        upgBtn.textContent = 'Convert to Invoice Now';
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
      message: `Permanently delete quotation <b>${escHtml(q.quote_number)}</b> (${escHtml(q.title || 'Quotation')})?`,
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

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_QUOTES.init();
  });
})();
