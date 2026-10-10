/* ==========================================================================
   JMOS — Client Contracts (template + AI drafting + e-signature)
   ========================================================================== */

(function () {
  'use strict';

  function esc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function toast(title, sub, isRed) {
    if (typeof window.showToast === 'function') window.showToast(title, sub || '', !!isRed);
  }

  async function api(path, options) {
    options = options || {};
    const token = localStorage.getItem('jmos_api_token') || (typeof JMOS_STATE !== 'undefined' ? JMOS_STATE.apiToken : null);
    const headers = Object.assign({
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : '',
      // Fallback for hosts that strip the Authorization header
      'X-Api-Token': token || ''
    }, options.body ? { 'Content-Type': 'application/json' } : {});
    const res = await fetch(path, Object.assign({}, options, { headers }));
    let data = {};
    try { data = await res.json(); } catch (_) {}
    if (!res.ok) {
      let msg = data.message || `Request failed (${res.status})`;
      if (data.errors) msg = Object.values(data.errors).flat()[0] || msg;
      const err = new Error(msg);
      err.status = res.status;
      throw err;
    }
    return data;
  }

  const money = n => 'KES ' + Math.round(parseFloat(n) || 0).toLocaleString();

  const STATUS_PILL = {
    Draft: ['tint-amber', 'DRAFT'],
    Sent: ['tint-blue', 'SENT'],
    Viewed: ['tint-purple', 'VIEWED'],
    Signed: ['tint-green', 'SIGNED ✓'],
    Void: ['tint-red', 'VOID']
  };

  window.JMOS_CONTRACTS = {
    contracts: [],
    meta: null,
    active: null,
    filter: 'all',
    search: '',
    clients: [],
    quotes: [],

    /* ------------------------------ List ------------------------------ */

    load: async function () {
      try {
        const data = await api('/api/contracts');
        this.contracts = data.data || [];
        this.render();
      } catch (err) {
        const tbody = document.getElementById('contractsTableBody');
        if (tbody) tbody.innerHTML = `<tr><td colspan="6" style="padding:28px;text-align:center;color:var(--muted)">${esc(err.message)}</td></tr>`;
      }
    },

    setFilter: function (filter) {
      this.filter = filter;
      document.querySelectorAll('[data-ct-filter]').forEach(b => b.classList.toggle('active', b.getAttribute('data-ct-filter') === filter));
      this.render();
    },

    setSearch: function (q) {
      this.search = (q || '').toLowerCase().trim();
      this.render();
    },

    render: function () {
      const all = this.contracts;
      const awaiting = all.filter(c => c.status === 'Sent' || c.status === 'Viewed');
      const signed = all.filter(c => c.status === 'Signed');
      const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
      setText('ctrKpiTotal', all.length);
      setText('ctrKpiAwaiting', awaiting.length);
      setText('ctrKpiSigned', signed.length);
      setText('ctrKpiSignedValue', money(signed.reduce((s, c) => s + (parseFloat(c.fields && c.fields.fee) || 0), 0)) + ' contracted');
      setText('ctrKpiDrafts', all.filter(c => c.status === 'Draft').length);

      const tbody = document.getElementById('contractsTableBody');
      if (!tbody) return;

      const rows = all.filter(c => {
        if (this.filter === 'awaiting' && !(c.status === 'Sent' || c.status === 'Viewed')) return false;
        if (this.filter !== 'all' && this.filter !== 'awaiting' && c.status !== this.filter) return false;
        if (this.search) {
          const hay = [c.contract_number, c.client_name, c.title, c.client_email].join(' ').toLowerCase();
          if (!hay.includes(this.search)) return false;
        }
        return true;
      });

      if (!rows.length) {
        tbody.innerHTML = `<tr><td colspan="6" style="padding:36px 20px;text-align:center;color:var(--muted)">${all.length ? 'No contracts match this filter.' : 'No contracts yet. Click <b>New Contract</b> to generate one from the Jeota template.'}</td></tr>`;
        return;
      }

      tbody.innerHTML = rows.map(c => {
        const pill = STATUS_PILL[c.status] || STATUS_PILL.Draft;
        const fee = c.fields && c.fields.fee ? money(c.fields.fee) : '—';
        const updated = (c.signed_at || c.updated_at || '').split('T')[0];
        const blanks = c.blanks > 0 && !c.is_locked ? `<span class="pill tint-amber" style="margin-left:6px;font-size:10px">${c.blanks} blank${c.blanks > 1 ? 's' : ''}</span>` : '';
        const ai = c.ai_generated ? '<span class="pill" style="margin-left:6px;font-size:10px;background:rgba(245,158,11,.12);color:#B45309">✦ AI</span>' : '';
        return `
          <tr style="cursor:pointer" onclick="window.JMOS_CONTRACTS.open(${c.id})">
            <td class="mono" style="font-weight:700;color:var(--red)">${esc(c.contract_number)}</td>
            <td>
              <div style="font-weight:600;color:var(--ink)">${esc(c.client_name)}${ai}${blanks}</div>
              <div style="font-size:11px;color:var(--muted)">${esc(c.title)}</div>
            </td>
            <td class="mono" style="font-weight:700;color:var(--ink)">${fee}</td>
            <td><span class="pill ${pill[0]}">${pill[1]}</span></td>
            <td style="font-size:12px;color:var(--muted)">${esc(updated)}</td>
            <td style="text-align:right" onclick="event.stopPropagation()">
              <div style="display:flex;justify-content:flex-end;gap:5px;flex-wrap:wrap">
                <button type="button" class="btn small" style="font-size:11px;padding:3px 8px" onclick="window.JMOS_CONTRACTS.open(${c.id})">Open</button>
                <button type="button" class="btn small" style="font-size:11px;padding:3px 8px" onclick="window.open('${esc(c.sign_url)}?preview=1','_blank')">Preview ↗</button>
                ${!c.is_locked ? `<button type="button" class="btn small primary" style="font-size:11px;padding:3px 8px;background:var(--red);border-color:var(--red)" onclick="window.JMOS_CONTRACTS.open(${c.id}, 'send')">Send</button>` : ''}
              </div>
            </td>
          </tr>`;
      }).join('');
    },


    /* ------------------------- Document editor ------------------------ */

    active: null,
    dirty: false,
    isNew: false,
    meta: null,
    clients: [],
    quotes: [],

    PARTY_KEYS: ['client_name', 'title', 'client_registration', 'client_po_box', 'client_address', 'client_email', 'client_phone', 'signatory_name', 'signatory_position'],
    NUMBER_KEYS: ['deposit_percent', 'payment_days', 'late_interest', 'feedback_days', 'revision_rounds', 'reschedule_days', 'termination_days'],
    WORD_KEYS: ['payment_days', 'feedback_days', 'revision_rounds', 'reschedule_days', 'termination_days'],

    ensureMeta: async function () {
      if (!this.meta) {
        const data = await api('/api/contracts/templates');
        this.meta = data.data;
      }
      return this.meta;
    },

    loadPickers: async function () {
      try {
        const [clients, quotes] = await Promise.all([api('/api/clients'), api('/api/quotes')]);
        this.clients = Array.isArray(clients) ? clients : (clients.data || []);
        this.quotes = quotes.data || [];
      } catch (_) { /* optional */ }
      const sel = document.getElementById('ctrEdFill');
      if (!sel) return;
      sel.innerHTML = '<option value="">Fill from client / quote…</option>' +
        (this.clients.length ? '<optgroup label="Clients">' + this.clients.map(c => `<option value="c:${c.id}">${esc(c.client_name)}</option>`).join('') + '</optgroup>' : '') +
        (this.quotes.length ? '<optgroup label="Quotations">' + this.quotes.map(q => `<option value="q:${q.id}">${esc(q.quote_number)} · ${esc(q.recipient_name)} · ${money(q.total_amount)}</option>`).join('') + '</optgroup>' : '');
    },

    /** New contract: create a draft from the template and open it as a document. */
    openBuilder: async function (_unused, prefill) {
      try {
        await this.ensureMeta();
        const data = await api('/api/contracts', { method: 'POST', body: JSON.stringify({}) });
        this.isNew = true;
        await this.showEditor(data.data);
        if (prefill && prefill.quoteId) this.fillFrom('q:' + prefill.quoteId);
      } catch (err) {
        toast('Could not start a contract', err.message, true);
      }
    },

    open: async function (id, then) {
      try {
        await this.ensureMeta();
        const data = await api(`/api/contracts/${id}`);
        this.isNew = false;
        await this.showEditor(data.data);
        if (then === 'send' && !data.data.is_locked) this.openSend();
      } catch (err) {
        toast('Could not open contract', err.message, true);
      }
    },

    showEditor: async function (c) {
      this.active = c;
      this.dirty = false;
      const locked = !!c.is_locked;
      const pill = STATUS_PILL[c.status] || STATUS_PILL.Draft;
      const doc = document.getElementById('ctrEdDoc');

      document.getElementById('ctrEdNumber').textContent = c.contract_number;
      document.getElementById('ctrEdRef').textContent = c.contract_number;
      const st = document.getElementById('ctrEdStatus');
      st.className = `pill ${pill[0]}`;
      st.textContent = pill[1];
      document.getElementById('ctrEdBannerStatus').textContent = c.status;

      document.getElementById('ctrEdClauses').innerHTML = c.body || '';

      // Party fields outside the clauses (cover, parties paragraph, signature block)
      this.PARTY_KEYS.forEach(key => {
        doc.querySelectorAll(`.ce-cover [data-f="${key}"], .ce-intro [data-f="${key}"], .ce-sigs [data-f="${key}"]`).forEach(el => this.setField(el, c[key]));
      });

      doc.classList.toggle('locked', locked);
      doc.querySelectorAll('[contenteditable]').forEach(el => el.setAttribute('contenteditable', locked ? 'false' : 'true'));
      document.getElementById('ctrEdTools').style.display = locked ? 'none' : 'flex';
      document.getElementById('ctrEdLockedTools').style.display = locked ? 'flex' : 'none';
      document.getElementById('ctrEdVoid').style.display = c.status === 'Draft' ? 'none' : '';
      document.getElementById('ctrEdProviderNote').textContent = c.provider_signed_at ? 'Signed ' + new Date(c.provider_signed_at).toLocaleDateString() : 'Signature applied when sent';
      document.getElementById('ctrEdClientSig').textContent = c.status === 'Signed'
        ? `Signed by ${c.client_signed_name} on ${new Date(c.signed_at).toLocaleDateString()}`
        : 'Signed online by the client';

      this.toggleAi(false);
      if (this.meta && !this.meta.ai_available) {
        document.getElementById('ctrEdAiNote').textContent = 'AI is not configured on the server yet (GEMINI_API_KEY). You can still edit everything by hand.';
      }
      this.updateHint();
      document.getElementById('ctrEditor').classList.add('on');
      document.body.style.overflow = 'hidden';
      this.loadPickers();
    },

    setField: function (el, value) {
      const v = value == null ? '' : String(value).trim();
      el.textContent = v || '________';
      el.classList.toggle('blank', !v);
    },

    fieldValue: function (el) {
      const t = (el.textContent || '').replace(/ /g, ' ').trim();
      return /^_+$/.test(t) ? '' : t;
    },

    updateHint: function () {
      const c = this.active;
      const hint = document.getElementById('ctrEdHint');
      if (!c) return;
      if (c.is_locked) {
        hint.innerHTML = c.status === 'Signed' ? '✓ Signed by both parties — this agreement is locked.' : 'This contract was voided.';
        return;
      }
      const blanks = document.querySelectorAll('#ctrEdDoc .cf.blank').length;
      hint.innerHTML = 'Click any text on the agreement to edit it.' +
        (blanks ? ` <span class="warn">${blanks} highlighted field${blanks > 1 ? 's' : ''} still to fill.</span>` : ' All fields filled.') +
        (this.dirty ? ' <span class="warn">Unsaved changes.</span>' : '');
    },

    /** Copy an edited field to every other place it appears, and keep amounts in words in step. */
    onInput: function (e) {
      const c = this.active;
      if (!c || c.is_locked) return;
      this.dirty = true;
      let el = e.target.closest && e.target.closest('[data-f]');
      if (!el) {
        const sel = window.getSelection();
        const node = sel && sel.anchorNode;
        el = node ? (node.nodeType === 1 ? node : node.parentElement).closest('[data-f]') : null;
      }
      if (el && el.closest('#ctrEdDoc')) {
        const key = el.getAttribute('data-f');
        const value = this.fieldValue(el);
        el.classList.toggle('blank', !value);
        document.querySelectorAll(`#ctrEdDoc [data-f="${key}"]`).forEach(other => {
          if (other !== el) this.setField(other, value);
        });
        if (key === 'fee_formatted') {
          const n = parseFloat(value.replace(/[^0-9.]/g, ''));
          document.querySelectorAll('#ctrEdDoc [data-f="fee_words"]').forEach(w => this.setField(w, n > 0 ? numberWords(n) + ' Only' : ''));
        }
        if (this.WORD_KEYS.includes(key)) {
          const n = parseInt(value, 10);
          document.querySelectorAll(`#ctrEdDoc [data-f="${key}_words"]`).forEach(w => this.setField(w, n >= 0 ? numberWords(n).toLowerCase() : ''));
        }
      }
      clearTimeout(this._hintTimer);
      this._hintTimer = setTimeout(() => this.updateHint(), 250);
    },

    onClick: function (e) {
      const tick = e.target.closest('#ctrEdDoc .tick');
      if (tick && this.active && !this.active.is_locked) {
        e.preventDefault();
        tick.textContent = tick.textContent.trim() === '☑' ? '☐' : '☑';
        this.dirty = true;
        this.updateHint();
      }
    },

    collect: function () {
      const doc = document.getElementById('ctrEdDoc');
      const payload = { fields: {} };
      const seen = {};
      doc.querySelectorAll('[data-f]').forEach(el => {
        const key = el.getAttribute('data-f');
        if (seen[key] || key.startsWith('svc_') || key.endsWith('_words') && key !== 'fee_words') return;
        seen[key] = true;
        const value = this.fieldValue(el);
        if (this.PARTY_KEYS.includes(key)) {
          payload[key] = value || null;
        } else if (key === 'fee_formatted') {
          payload.fields.fee = parseFloat(value.replace(/[^0-9.]/g, '')) || 0;
        } else if (this.NUMBER_KEYS.includes(key)) {
          const n = parseFloat(value);
          if (!isNaN(n)) payload.fields[key] = n;
        } else {
          payload.fields[key] = value;
        }
      });
      payload.fields.services = Array.from(doc.querySelectorAll('.tick[data-f^="svc_"]'))
        .filter(t => t.textContent.trim() === '☑')
        .map(t => t.getAttribute('data-f').slice(4));
      payload.client_name = payload.client_name || 'Client Name';
      payload.title = payload.title || 'Photography, Videography and Social Media Services';
      payload.body = document.getElementById('ctrEdClauses').innerHTML;
      if (this.active.client_id) payload.client_id = this.active.client_id;
      if (this.active.quote_id) payload.quote_id = this.active.quote_id;
      return payload;
    },

    save: async function (quiet) {
      const c = this.active;
      if (!c || c.is_locked) return c;
      const btn = document.getElementById('ctrEdSave');
      btn.disabled = true;
      btn.textContent = 'Saving…';
      try {
        const data = await api(`/api/contracts/${c.id}`, { method: 'PUT', body: JSON.stringify(this.collect()) });
        this.active = data.data;
        this.dirty = false;
        this.isNew = false;
        this.updateHint();
        if (!quiet) toast('Contract saved', c.contract_number);
        this.load();
        return data.data;
      } catch (err) {
        toast('Could not save', err.message, true);
        throw err;
      } finally {
        btn.disabled = false;
        btn.textContent = 'Save';
      }
    },

    close: async function () {
      const c = this.active;
      if (c && this.dirty && !c.is_locked) {
        if (window.confirm('Save your changes before closing?')) {
          try { await this.save(true); } catch (_) { return; }
        }
      } else if (c && this.isNew) {
        // Opened "New Contract" and left without touching it: don't keep an empty draft
        try { await api(`/api/contracts/${c.id}`, { method: 'DELETE' }); } catch (_) {}
      }
      document.getElementById('ctrEditor').classList.remove('on');
      document.body.style.overflow = '';
      this.active = null;
      this.load();
    },

    fillFrom: function (value) {
      if (!value || !this.active || this.active.is_locked) return;
      const [kind, id] = value.split(':');
      const set = (key, v) => {
        if (v == null || v === '') return;
        document.querySelectorAll(`#ctrEdDoc [data-f="${key}"]`).forEach(el => this.setField(el, v));
      };
      if (kind === 'c') {
        const c = this.clients.find(x => String(x.id) === String(id));
        if (!c) return;
        set('client_name', c.client_name); set('client_address', c.address); set('client_email', c.email);
        set('client_phone', c.phone); set('signatory_name', c.contact_person);
        this.active.client_id = c.id;
      } else {
        const q = this.quotes.find(x => String(x.id) === String(id));
        if (!q) return;
        set('client_name', q.client ? q.client.client_name : q.recipient_name);
        set('client_email', q.recipient_email); set('client_phone', q.recipient_phone);
        const fee = parseFloat(q.total_amount) || 0;
        if (fee > 0) {
          set('fee_formatted', fee.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
          set('fee_words', numberWords(fee) + ' Only');
        }
        this.active.quote_id = q.id;
      }
      this.dirty = true;
      this.updateHint();
      toast('Details filled', 'Check the highlighted fields, then Save.');
    },

    toggleAi: function (force) {
      const panel = document.getElementById('ctrEdAi');
      const on = typeof force === 'boolean' ? force : !panel.classList.contains('on');
      panel.classList.toggle('on', on);
      if (on) document.getElementById('ctrEdAiText').focus();
    },

    aiRevise: async function () {
      const c = this.active;
      const instructions = document.getElementById('ctrEdAiText').value.trim();
      if (!instructions) return toast('Add instructions', 'Tell AI what to change in the agreement.', true);
      const btn = document.getElementById('ctrEdAiRun');
      btn.disabled = true;
      btn.textContent = '✦ Revising…';
      try {
        await this.save(true);
        const data = await api(`/api/contracts/${c.id}/ai-revise`, { method: 'POST', body: JSON.stringify({ instructions }) });
        await this.showEditor(data.data);
        document.getElementById('ctrEdAiText').value = '';
        toast('Agreement revised', data.message);
      } catch (err) {
        toast('AI revision failed', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = '✦ Revise agreement';
      }
    },

    preview: async function (signed) {
      const c = this.active;
      if (!c) return;
      if (!signed && this.dirty) { try { await this.save(true); } catch (_) { return; } }
      window.open(c.sign_url + (signed ? '' : '?preview=1'), '_blank', 'noopener');
    },

    openSend: async function () {
      const c = this.active;
      if (!c) return;
      try { await this.save(true); } catch (_) { return; }
      const blanks = document.querySelectorAll('#ctrEdDoc .cf.blank').length;
      if (blanks && !window.confirm(`${blanks} field${blanks > 1 ? 's are' : ' is'} still blank (highlighted). Send anyway?`)) return;
      document.getElementById('ctrSendEmail').value = this.active.client_email || '';
      document.getElementById('ctrSendPhone').value = this.active.client_phone || '';
      window.openModal('ctrSendModal');
    },

    afterSend: async function (data, title, msg) {
      window.closeModal('ctrSendModal');
      toast(title, msg);
      await this.showEditor(data.data);
      this.load();
    },

    sendEmail: async function () {
      const c = this.active;
      const email = document.getElementById('ctrSendEmail').value.trim();
      if (!email) return toast('Email required', 'Enter the client\'s email address.', true);
      const btn = document.getElementById('ctrSendEmailBtn');
      btn.disabled = true;
      btn.textContent = 'Sending…';
      try {
        const data = await api(`/api/contracts/${c.id}/send-email`, { method: 'POST', body: JSON.stringify({ email, message: document.getElementById('ctrSendMessage').value.trim() || null }) });
        await this.afterSend(data, 'Sent for signature', data.message);
      } catch (err) {
        toast('Email failed', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send by email';
      }
    },

    sendWhatsApp: async function () {
      const c = this.active;
      const phone = document.getElementById('ctrSendPhone').value.trim();
      const win = window.open('about:blank', '_blank');
      try {
        if (phone && phone !== c.client_phone) {
          await api(`/api/contracts/${c.id}`, { method: 'PUT', body: JSON.stringify({ client_phone: phone }) });
        }
        const data = await api(`/api/contracts/${c.id}/whatsapp`);
        if (win) win.location = data.whatsapp_url; else window.location.href = data.whatsapp_url;
        await this.afterSend(data, 'WhatsApp ready', 'Signing link shared — Barny\'s signature is applied.');
      } catch (err) {
        if (win) win.close();
        toast('Could not prepare WhatsApp', err.message, true);
      }
    },

    copyLink: async function () {
      const c = this.active;
      try {
        const data = c.status === 'Draft' ? await api(`/api/contracts/${c.id}/mark-sent`, { method: 'POST' }) : { data: c };
        try { await navigator.clipboard.writeText(c.sign_url); } catch (_) { window.prompt('Copy the signing link:', c.sign_url); }
        await this.afterSend(data, 'Signing link copied', 'Paste it to the client — the agreement is open for signature.');
      } catch (err) {
        toast('Could not issue link', err.message, true);
      }
    },

    voidActive: async function () {
      const c = this.active;
      if (!window.confirm(`Void ${c.contract_number}? The client will no longer be able to sign it.`)) return;
      try {
        const data = await api(`/api/contracts/${c.id}/void`, { method: 'POST' });
        toast('Contract voided', data.message);
        await this.showEditor(data.data);
        this.load();
      } catch (err) {
        toast('Could not void', err.message, true);
      }
    },

    deleteActive: async function () {
      const c = this.active;
      if (!window.confirm(`Delete ${c.contract_number} permanently?`)) return;
      try {
        await api(`/api/contracts/${c.id}`, { method: 'DELETE' });
        toast('Contract deleted', c.contract_number);
        this.dirty = false;
        this.isNew = false;
        document.getElementById('ctrEditor').classList.remove('on');
        document.body.style.overflow = '';
        this.active = null;
        this.load();
      } catch (err) {
        toast('Could not delete', err.message, true);
      }
    }
  };

  /** Number to words, e.g. 150000 => "One Hundred and Fifty Thousand". */
  function numberWords(num) {
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    const under1000 = n => {
      const parts = [];
      if (n >= 100) { parts.push(ones[Math.floor(n / 100)] + ' Hundred'); n %= 100; if (n) parts.push('and'); }
      if (n >= 20) parts.push(tens[Math.floor(n / 10)] + (n % 10 ? '-' + ones[n % 10] : ''));
      else if (n > 0) parts.push(ones[n]);
      return parts.join(' ');
    };
    const whole = Math.floor(num);
    const cents = Math.round((num - whole) * 100);
    if (whole === 0 && !cents) return 'Zero';
    let n = whole;
    const parts = [];
    [[1e9, 'Billion'], [1e6, 'Million'], [1e3, 'Thousand']].forEach(([v, label]) => {
      if (n >= v) { parts.push(under1000(Math.floor(n / v)) + ' ' + label); n %= v; }
    });
    if (n > 0) parts.push((parts.length && n < 100 ? 'and ' : '') + under1000(n));
    let words = parts.join(' ');
    if (cents) words += ' and ' + under1000(cents) + ' Cents';
    return words;
  }

  document.addEventListener('input', e => {
    if (e.target.closest && e.target.closest('#ctrEdDoc')) window.JMOS_CONTRACTS.onInput(e);
  });
  document.addEventListener('click', e => {
    if (e.target.closest && e.target.closest('#ctrEdDoc')) window.JMOS_CONTRACTS.onClick(e);
  });
  // Paste as plain text so pasted Word/web formatting doesn't break the branded layout
  document.addEventListener('paste', e => {
    if (!(e.target.closest && e.target.closest('#ctrEdDoc [contenteditable="true"]'))) return;
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text/plain');
    document.execCommand('insertText', false, text);
  });
  document.addEventListener('keydown', e => {
    const ed = document.getElementById('ctrEditor');
    if (!ed || !ed.classList.contains('on')) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); window.JMOS_CONTRACTS.save(); }
    // Enter inside a single-line field would break it into paragraphs
    if (e.key === 'Enter' && e.target.matches && e.target.matches('span.cf[contenteditable]')) { e.preventDefault(); e.target.blur(); }
  });

  /** Called from the quotation detail modal: start a contract from the open quote. */
  window.createContractFromQuote = function (quoteId) {
    const id = quoteId || (document.getElementById('qdmQuoteId') || {}).value;
    window.closeModal('quoteDetailModal');
    if (typeof window.showView === 'function') window.showView('contracts');
    window.JMOS_CONTRACTS.openBuilder(null, { quoteId: id });
  };
})();
