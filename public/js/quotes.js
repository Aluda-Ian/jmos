/* ==========================================================================
   JMOS — Quotations & Invoicing Engine
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_QUOTES = {
    quotes: [],

    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Calculate totals when inputs change in quote modal
      document.addEventListener('input', function (e) {
        if (e.target.closest('#quoteItemsTable') || e.target.id === 'quoteTaxRate' || e.target.id === 'quoteDiscount') {
          window.JMOS_QUOTES.calculateTotals();
        }
      });
    },

    openCreateModal: function (leadId, clientId, dealId) {
      const modal = document.getElementById('quoteModal');
      if (!modal) return;

      const form = document.getElementById('quoteForm');
      if (form) form.reset();

      document.getElementById('quoteId').value = '';
      document.getElementById('quoteModalTitle').textContent = 'Create Quotation';

      // Set lead / client / deal context if provided
      if (leadId) {
        const leadEl = document.getElementById('quoteLeadId');
        if (leadEl) leadEl.value = leadId;

        // Auto-fill recipient from lead if available
        if (typeof CRM_STATE !== 'undefined' && CRM_STATE.leads) {
          const lead = CRM_STATE.leads.find(l => String(l.id) === String(leadId));
          if (lead) {
            if (document.getElementById('quoteRecipientName')) document.getElementById('quoteRecipientName').value = lead.lead_name || lead.company;
            if (document.getElementById('quoteRecipientEmail')) document.getElementById('quoteRecipientEmail').value = lead.email || '';
            if (document.getElementById('quoteRecipientPhone')) document.getElementById('quoteRecipientPhone').value = lead.phone || '';
            if (document.getElementById('quoteTitle')) document.getElementById('quoteTitle').value = `${lead.company || lead.lead_name} · Production Proposal`;
          }
        }
      }
      if (clientId) {
        const clientEl = document.getElementById('quoteClientId');
        if (clientEl) clientEl.value = clientId;
      }
      if (dealId) {
        const dealEl = document.getElementById('quoteDealId');
        if (dealEl) dealEl.value = dealId;
      }

      // Populate default empty line items
      const tbody = document.getElementById('quoteItemsBody');
      if (tbody) {
        tbody.innerHTML = '';
        this.addItemRow({ description: 'Production & Creative Services', quantity: 1, rate: 50000 });
      }

      this.calculateTotals();
      modal.classList.add('active');
    },

    addItemRow: function (item) {
      const tbody = document.getElementById('quoteItemsBody');
      if (!tbody) return;

      const desc = item && item.description ? item.description : '';
      const qty = item && item.quantity ? item.quantity : 1;
      const rate = item && (item.rate || item.unit_price) ? (item.rate || item.unit_price) : 0;
      const total = qty * rate;

      const row = document.createElement('tr');
      row.className = 'quote-item-row';
      row.innerHTML = `
        <td><input type="text" class="input quote-item-desc" style="width:100%" placeholder="e.g., Commercial Video Shoot" value="${desc}" required></td>
        <td style="width:90px"><input type="number" class="input quote-item-qty" style="width:100%" min="1" step="1" value="${qty}" required></td>
        <td style="width:140px"><input type="number" class="input quote-item-rate" style="width:100%" min="0" step="100" value="${rate}" required></td>
        <td style="width:140px;text-align:right;font-family:'IBM Plex Mono',monospace;font-weight:600" class="quote-item-total">KES ${total.toLocaleString()}</td>
        <td style="width:50px;text-align:center">
          <button type="button" class="btn small danger" onclick="this.closest('tr').remove(); window.JMOS_QUOTES.calculateTotals();" style="padding:4px 8px">&times;</button>
        </td>
      `;
      tbody.appendChild(row);
      this.calculateTotals();
    },

    calculateTotals: function () {
      const rows = document.querySelectorAll('.quote-item-row');
      let subtotal = 0;

      rows.forEach(function (row) {
        const qty = parseFloat(row.querySelector('.quote-item-qty')?.value || 0);
        const rate = parseFloat(row.querySelector('.quote-item-rate')?.value || 0);
        const rowTotal = qty * rate;
        const totalCell = row.querySelector('.quote-item-total');
        if (totalCell) totalCell.textContent = 'KES ' + rowTotal.toLocaleString();
        subtotal += rowTotal;
      });

      const taxRate = parseFloat(document.getElementById('quoteTaxRate')?.value || 0);
      const discount = parseFloat(document.getElementById('quoteDiscount')?.value || 0);

      const taxAmount = (subtotal * taxRate) / 100;
      const total = Math.max(0, subtotal + taxAmount - discount);

      if (document.getElementById('quoteSubtotalDisplay')) {
        document.getElementById('quoteSubtotalDisplay').textContent = 'KES ' + subtotal.toLocaleString();
      }
      if (document.getElementById('quoteTaxDisplay')) {
        document.getElementById('quoteTaxDisplay').textContent = 'KES ' + taxAmount.toLocaleString();
      }
      if (document.getElementById('quoteTotalDisplay')) {
        document.getElementById('quoteTotalDisplay').textContent = 'KES ' + total.toLocaleString();
      }
    },

    saveQuote: async function (e) {
      if (e) e.preventDefault();
      const quoteId = document.getElementById('quoteId')?.value;
      const rows = document.querySelectorAll('.quote-item-row');
      const items = [];
      let subtotal = 0;

      rows.forEach(function (row) {
        const desc = row.querySelector('.quote-item-desc')?.value.trim();
        const qty = parseFloat(row.querySelector('.quote-item-qty')?.value || 1);
        const rate = parseFloat(row.querySelector('.quote-item-rate')?.value || 0);
        if (desc) {
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
        alert('Please add at least one line item to the quotation.');
        return;
      }

      const taxRate = parseFloat(document.getElementById('quoteTaxRate')?.value || 0);
      const discount = parseFloat(document.getElementById('quoteDiscount')?.value || 0);
      const taxAmount = (subtotal * taxRate) / 100;
      const totalAmount = Math.max(0, subtotal + taxAmount - discount);

      const recipientName = document.getElementById('quoteRecipientName')?.value || document.getElementById('quoteClientName')?.value || 'Client';

      const payload = {
        title: document.getElementById('quoteTitle')?.value || 'Production Quotation',
        recipient_name: recipientName,
        recipient_email: document.getElementById('quoteRecipientEmail')?.value || document.getElementById('quoteClientEmail')?.value || null,
        recipient_phone: document.getElementById('quoteRecipientPhone')?.value || document.getElementById('quoteClientPhone')?.value || null,
        lead_id: document.getElementById('quoteLeadId')?.value || null,
        client_id: document.getElementById('quoteClientId')?.value || null,
        deal_id: document.getElementById('quoteDealId')?.value || null,
        subtotal: subtotal,
        tax: taxAmount,
        discount: discount,
        total_amount: totalAmount,
        validity_days: parseInt(document.getElementById('quoteValidityDays')?.value || '14', 10),
        notes: document.getElementById('quoteNotes')?.value || '',
        terms: document.getElementById('quoteTerms')?.value || '60% deposit upon confirmation, balance upon delivery approval.',
        items: items
      };

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

        if (window.showToast) {
          window.showToast('Quotation saved successfully', 'success');
        } else {
          alert('Quotation saved successfully: ' + (data.data ? data.data.quote_number : ''));
        }

        document.getElementById('quoteModal').classList.remove('active');
        if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
      } catch (err) {
        alert('Error saving quote: ' + err.message);
      }
    },

    viewDetail: async function (quoteId) {
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/quotes/${quoteId}`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to load quotation');

        const q = data.data || data.quote;
        const modal = document.getElementById('quoteDetailModal');
        if (!modal) return;

        document.getElementById('qdQuoteNumber').textContent = q.quote_number;
        document.getElementById('qdTitle').textContent = q.title;
        document.getElementById('qdClientName').textContent = q.recipient_name || '—';
        document.getElementById('qdClientEmail').textContent = q.recipient_email || '—';
        document.getElementById('qdClientPhone').textContent = q.recipient_phone || '—';
        document.getElementById('qdStatus').innerHTML = `<span class="badge ${q.status}">${q.status.toUpperCase()}</span>`;
        document.getElementById('qdValidUntil').textContent = `${q.validity_days || 14} days`;
        document.getElementById('qdSubtotal').textContent = 'KES ' + Number(q.subtotal || q.total_amount).toLocaleString();
        document.getElementById('qdTax').textContent = `KES ${Number(q.tax || 0).toLocaleString()}`;
        document.getElementById('qdDiscount').textContent = `KES ${Number(q.discount || 0).toLocaleString()}`;
        document.getElementById('qdTotal').textContent = 'KES ' + Number(q.total_amount).toLocaleString();
        document.getElementById('qdNotes').textContent = q.notes || 'No extra scope notes.';
        document.getElementById('qdTerms').textContent = q.terms || '60% Deposit on Kickoff, 40% on Final Delivery Master.';

        // Items table
        const tbody = document.getElementById('qdItemsBody');
        if (tbody) {
          tbody.innerHTML = '';
          const items = Array.isArray(q.items) ? q.items : [];
          items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${it.description}</td>
              <td style="text-align:center">${it.quantity}</td>
              <td style="text-align:right;font-family:'IBM Plex Mono',monospace">KES ${Number(it.rate || it.unit_price).toLocaleString()}</td>
              <td style="text-align:right;font-family:'IBM Plex Mono',monospace;font-weight:600">KES ${Number(it.amount || (it.quantity * (it.rate || it.unit_price))).toLocaleString()}</td>
            `;
            tbody.appendChild(tr);
          });
        }

        // Action buttons inside quote detail
        const emailBtn = document.getElementById('qdEmailBtn');
        if (emailBtn) {
          emailBtn.onclick = () => window.JMOS_QUOTES.sendEmail(q.id);
        }
        const waBtn = document.getElementById('qdWhatsAppBtn');
        if (waBtn) {
          waBtn.onclick = () => window.JMOS_QUOTES.sendWhatsApp(q.id);
        }
        const upgradeBtn = document.getElementById('qdUpgradeBtn');
        if (upgradeBtn) {
          if (q.status === 'Invoiced' || q.converted_invoice_id) {
            upgradeBtn.disabled = true;
            upgradeBtn.textContent = 'Already Converted to Invoice';
          } else {
            upgradeBtn.disabled = false;
            upgradeBtn.textContent = 'Upgrade to Official Invoice';
            upgradeBtn.onclick = () => window.JMOS_QUOTES.openUpgradeModal(q);
          }
        }

        modal.classList.add('active');
      } catch (err) {
        alert('Error viewing quote: ' + err.message);
      }
    },

    sendEmail: async function (quoteId) {
      if (!confirm('Send this official quotation directly to the client via email?')) return;
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/quotes/${quoteId}/send-email`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to dispatch email');

        if (window.showToast) {
          window.showToast(data.message, 'success');
        } else {
          alert(data.message);
        }
      } catch (err) {
        alert('Error sending email: ' + err.message);
      }
    },

    sendWhatsApp: async function (quoteId) {
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/quotes/${quoteId}/whatsapp`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to get WhatsApp link');

        if (data.whatsapp_url) {
          window.open(data.whatsapp_url, '_blank');
        } else {
          alert('Could not generate WhatsApp URL. Please ensure client phone is valid.');
        }
      } catch (err) {
        alert('Error generating WhatsApp link: ' + err.message);
      }
    },

    openUpgradeModal: function (quote) {
      const modal = document.getElementById('upgradeQuoteModal');
      if (!modal) return;

      document.getElementById('uqQuoteId').value = quote.id;
      document.getElementById('uqQuoteNumber').textContent = quote.quote_number;
      document.getElementById('uqClient').textContent = quote.recipient_name || quote.client_name;
      document.getElementById('uqAmount').value = quote.total_amount;

      modal.classList.add('active');
    },

    submitUpgrade: async function (e) {
      if (e) e.preventDefault();
      const quoteId = document.getElementById('uqQuoteId').value;
      const agreedAmount = document.getElementById('uqAmount').value;
      const dueDate = document.getElementById('uqDueDate')?.value;
      const type = document.getElementById('uqType')?.value || 'Deposit 60%';

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
            amount: parseFloat(agreedAmount),
            due_date: dueDate,
            invoice_type: type
          })
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to upgrade quote to invoice');

        if (window.showToast) {
          window.showToast(data.message, 'success');
        } else {
          alert(data.message);
        }

        document.getElementById('upgradeQuoteModal').classList.remove('active');
        const detailModal = document.getElementById('quoteDetailModal');
        if (detailModal) detailModal.classList.remove('active');

        // Refresh finances / invoices / pipeline
        if (typeof window.refreshCrmData === 'function') window.refreshCrmData();
        if (typeof window.refreshFinanceData === 'function') window.refreshFinanceData();
        if (typeof window.ensureInvoices === 'function') window.ensureInvoices();
      } catch (err) {
        alert('Error upgrading to invoice: ' + err.message);
      }
    }
  };

  // Initialize on script load
  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_QUOTES.init();
  });
})();
