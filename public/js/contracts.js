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
      'Authorization': token ? `Bearer ${token}` : ''
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

    /* ---------------------------- Builder ----------------------------- */

    ensureMeta: async function () {
      if (this.meta) return this.meta;
      const data = await api('/api/contracts/templates');
      this.meta = data.data;

      const tpl = document.getElementById('ctrTemplate');
      if (tpl) tpl.innerHTML = this.meta.templates.map(t => `<option value="${esc(t.key)}">${esc(t.label)}</option>`).join('');

      const svc = document.getElementById('ctrServices');
      if (svc) {
        svc.innerHTML = this.meta.services.map(s => `<label><input type="checkbox" name="ctService" value="${esc(s.key)}" onchange="window.JMOS_CONTRACTS.syncOther()"> ${esc(s.label)}</label>`).join('');
      }

      const sigName = document.getElementById('ctrdSignatoryName');
      if (sigName && this.meta.signatory) sigName.textContent = this.meta.signatory.name;

      if (!this.meta.ai_available) {
        const useAi = document.getElementById('ctrUseAi');
        if (useAi) { useAi.disabled = true; useAi.checked = false; }
        const note = document.getElementById('ctrAiNote');
        if (note) note.textContent = 'AI drafting is off: add ANTHROPIC_API_KEY to the server .env to enable it. The template draft still works.';
      }
      return this.meta;
    },

    loadPickers: async function () {
      try {
        const [clients, quotes] = await Promise.all([api('/api/clients'), api('/api/quotes')]);
        this.clients = Array.isArray(clients) ? clients : (clients.data || []);
        this.quotes = quotes.data || [];
      } catch (_) { /* pickers are optional */ }

      const cSel = document.getElementById('ctrClientSelect');
      if (cSel) cSel.innerHTML = '<option value="">— New / not in JMOS —</option>' + this.clients.map(c => `<option value="${c.id}">${esc(c.client_name)}</option>`).join('');
      const qSel = document.getElementById('ctrQuoteSelect');
      if (qSel) qSel.innerHTML = '<option value="">— None —</option>' + this.quotes.map(q => `<option value="${q.id}">${esc(q.quote_number)} · ${esc(q.recipient_name)} · ${money(q.total_amount)}</option>`).join('');
    },

    syncOther: function () {
      const other = document.querySelector('input[name="ctService"][value="other"]');
      const wrap = document.getElementById('ctrOtherWrap');
      if (wrap) wrap.style.display = other && other.checked ? '' : 'none';
    },

    setVal: function (id, value) {
      const el = document.getElementById(id);
      if (el) el.value = value == null ? '' : value;
    },

    openBuilder: async function (contract, prefill) {
      try { await this.ensureMeta(); } catch (err) { return toast('Contracts unavailable', err.message, true); }
      await this.loadPickers();

      const editing = contract && contract.id;
      const c = contract || {};
      const f = Object.assign({}, this.meta.defaults, c.fields || {});

      document.getElementById('ctrBuilderTitle').textContent = editing ? `Edit ${c.contract_number}` : 'New Contract';
      document.getElementById('ctrBuilderSubmit').textContent = editing ? 'Save Details' : 'Generate Contract';
      this.setVal('ctrFormId', editing ? c.id : '');
      this.setVal('ctrClientSelect', c.client_id || '');
      this.setVal('ctrQuoteSelect', c.quote_id || '');
      this.setVal('ctrTemplate', c.template || (this.meta.templates[0] && this.meta.templates[0].key));
      this.setVal('ctrClientName', c.client_name);
      this.setVal('ctrTitle', c.title || 'Photography, Videography & Social Media Services');
      this.setVal('ctrRegistration', c.client_registration);
      this.setVal('ctrPoBox', c.client_po_box);
      this.setVal('ctrAddress', c.client_address);
      this.setVal('ctrEmail', c.client_email);
      this.setVal('ctrPhone', c.client_phone);
      this.setVal('ctrSignatoryName', c.signatory_name);
      this.setVal('ctrSignatoryPosition', c.signatory_position);
      this.setVal('ctrAiInstructions', editing ? '' : (c.ai_instructions || ''));

      document.querySelectorAll('[data-ct-field]').forEach(el => {
        const key = el.getAttribute('data-ct-field');
        el.value = f[key] == null ? '' : f[key];
      });
      if (!f.fee) this.setVal('ctrF_fee', '');
      document.querySelectorAll('input[name="ctService"]').forEach(cb => { cb.checked = (f.services || []).includes(cb.value); });
      this.syncOther();

      const useAi = document.getElementById('ctrUseAi');
      if (useAi) useAi.checked = false;
      const regenWrap = document.getElementById('ctrRegenerateWrap');
      if (regenWrap) regenWrap.style.display = editing ? 'flex' : 'none';
      const regen = document.getElementById('ctrRegenerate');
      if (regen) regen.checked = editing ? !c.ai_generated : false;

      if (prefill) {
        if (prefill.quoteId) { this.setVal('ctrQuoteSelect', prefill.quoteId); this.onPickQuote(prefill.quoteId); }
        if (prefill.clientId) { this.setVal('ctrClientSelect', prefill.clientId); this.onPickClient(prefill.clientId); }
      }

      if (editing) window.closeModal('contractDetailModal');
      window.openModal('contractBuilderModal');
    },

    onPickClient: function (id) {
      const c = this.clients.find(x => String(x.id) === String(id));
      if (!c) return;
      this.setVal('ctrClientName', c.client_name);
      if (c.email) this.setVal('ctrEmail', c.email);
      if (c.phone) this.setVal('ctrPhone', c.phone);
      if (c.address) this.setVal('ctrAddress', c.address);
      if (c.contact_person) this.setVal('ctrSignatoryName', c.contact_person);
    },

    onPickQuote: function (id) {
      const q = this.quotes.find(x => String(x.id) === String(id));
      if (!q) return;
      if (!document.getElementById('ctrClientName').value) this.setVal('ctrClientName', q.client ? q.client.client_name : q.recipient_name);
      if (q.client_id) this.setVal('ctrClientSelect', q.client_id);
      this.setVal('ctrTitle', q.title);
      if (q.recipient_email) this.setVal('ctrEmail', q.recipient_email);
      if (q.recipient_phone) this.setVal('ctrPhone', q.recipient_phone);
      this.setVal('ctrF_fee', q.total_amount);

      // Guess services from the quotation wording
      const text = [q.title, q.notes].concat((q.items || []).map(i => i.description || '')).join(' ').toLowerCase();
      const guess = [];
      if (/photo/.test(text)) guess.push(/event|wedding|launch|conference/.test(text) ? 'photography_event' : 'photography_project');
      if (/video|film|reel|commercial|documentary/.test(text)) guess.push(/event|launch|conference/.test(text) ? 'videography_event' : 'videography_project');
      if (/social|content|caption|post/.test(text)) guess.push('social_content');
      if (/account|community|management/.test(text)) guess.push('social_management');
      if (guess.length) document.querySelectorAll('input[name="ctService"]').forEach(cb => { cb.checked = cb.checked || guess.includes(cb.value); });
      this.syncOther();
    },

    collectBuilder: function () {
      const fields = {};
      document.querySelectorAll('[data-ct-field]').forEach(el => { fields[el.getAttribute('data-ct-field')] = el.value.trim(); });
      fields.services = Array.from(document.querySelectorAll('input[name="ctService"]:checked')).map(cb => cb.value);
      fields.fee = fields.fee === '' ? 0 : parseFloat(fields.fee);

      const val = id => (document.getElementById(id).value || '').trim();
      return {
        template: val('ctrTemplate') || null,
        client_id: val('ctrClientSelect') || null,
        quote_id: val('ctrQuoteSelect') || null,
        client_name: val('ctrClientName'),
        title: val('ctrTitle'),
        client_registration: val('ctrRegistration') || null,
        client_po_box: val('ctrPoBox') || null,
        client_address: val('ctrAddress') || null,
        client_email: val('ctrEmail') || null,
        client_phone: val('ctrPhone') || null,
        signatory_name: val('ctrSignatoryName') || null,
        signatory_position: val('ctrSignatoryPosition') || null,
        ai_instructions: val('ctrAiInstructions') || null,
        use_ai: document.getElementById('ctrUseAi').checked,
        fields
      };
    },

    submitBuilder: async function () {
      const payload = this.collectBuilder();
      if (!payload.client_name || !payload.title) return toast('Missing details', 'Client name and contract title are required.', true);
      if (!payload.fields.services.length) return toast('Choose services', 'Tick at least one service for the agreement.', true);
      if (payload.use_ai && !payload.ai_instructions) return toast('AI instructions needed', 'Tell AI what to tailor, or untick the AI option.', true);

      const id = document.getElementById('ctrFormId').value;
      const btn = document.getElementById('ctrBuilderSubmit');
      const label = btn.textContent;
      btn.disabled = true;
      btn.textContent = payload.use_ai ? 'Generating & tailoring with AI…' : (id ? 'Saving…' : 'Generating…');

      try {
        let data;
        if (id) {
          const aiText = payload.ai_instructions;
          const useAi = payload.use_ai;
          delete payload.use_ai;
          delete payload.ai_instructions; // keep the AI instruction history intact
          payload.regenerate = document.getElementById('ctrRegenerate').checked;
          data = await api(`/api/contracts/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
          if (useAi && aiText) {
            try {
              data = await api(`/api/contracts/${id}/ai-revise`, { method: 'POST', body: JSON.stringify({ instructions: aiText }) });
              data.ai_message = data.message;
            } catch (aiErr) {
              data.ai_message = 'AI customisation skipped: ' + aiErr.message;
            }
          }
        } else {
          data = await api('/api/contracts', { method: 'POST', body: JSON.stringify(payload) });
        }
        window.closeModal('contractBuilderModal');
        const aiSkipped = data.ai_message && data.ai_message.indexOf('skipped') > -1;
        toast(id ? 'Contract saved' : 'Contract generated', data.ai_message || data.message, aiSkipped);
        await this.load();
        this.showDetail(data.data);
      } catch (err) {
        toast('Could not save contract', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = label;
      }
    },

    /* --------------------------- Workspace ---------------------------- */

    open: async function (id, tab) {
      try {
        await this.ensureMeta();
        const data = await api(`/api/contracts/${id}`);
        this.showDetail(data.data, tab);
      } catch (err) {
        toast('Could not open contract', err.message, true);
      }
    },

    showDetail: function (c, tab) {
      this.active = c;
      const pill = STATUS_PILL[c.status] || STATUS_PILL.Draft;
      document.getElementById('ctrdId').value = c.id;
      document.getElementById('ctrdTitle').textContent = `${c.contract_number} · ${c.client_name}`;
      document.getElementById('ctrdSubtitle').textContent = `${c.title}${c.services_label ? ' — ' + c.services_label : ''}`;
      const st = document.getElementById('ctrdStatus');
      st.className = `pill ${pill[0]}`;
      st.textContent = pill[1];
      document.getElementById('ctrdFee').textContent = c.fields && c.fields.fee ? money(c.fields.fee) : '';

      const locked = !!c.is_locked;
      const editor = document.getElementById('ctrdEditor');
      editor.innerHTML = c.body || '';
      editor.setAttribute('contenteditable', locked ? 'false' : 'true');
      document.getElementById('ctrdEditorBar').style.display = locked ? 'none' : 'flex';
      document.getElementById('ctrdEditBtn').style.display = locked ? 'none' : '';
      document.getElementById('ctrdVoidBtn').style.display = (locked) ? 'none' : '';
      document.getElementById('ctrdDeleteBtn').style.display = c.status === 'Signed' ? 'none' : '';
      document.querySelector('[data-ct-tab="ai"]').style.display = locked ? 'none' : '';
      document.querySelector('[data-ct-tab="send"]').style.display = locked ? 'none' : '';

      const signedInfo = document.getElementById('ctrdSignedInfo');
      if (c.status === 'Signed') {
        signedInfo.style.display = '';
        signedInfo.innerHTML = `✓ Signed by ${esc(c.client_signed_name)}${c.client_signed_position ? ', ' + esc(c.client_signed_position) : ''} on ${esc(new Date(c.signed_at).toLocaleString())}${c.data_consent ? ' · Appendix 2 consent granted' : ''}. <a href="${esc(c.sign_url)}" target="_blank" rel="noopener" style="color:inherit">Open signed copy ↗</a>`;
      } else if (c.status === 'Void') {
        signedInfo.style.display = '';
        signedInfo.style.background = 'var(--red-soft)'; signedInfo.style.color = 'var(--red)';
        signedInfo.textContent = 'This contract was voided. The client can no longer sign it.';
      } else {
        signedInfo.style.display = 'none';
        signedInfo.style.background = ''; signedInfo.style.color = '';
      }

      const blanks = document.getElementById('ctrdBlanks');
      if (c.blanks > 0 && !locked) {
        blanks.style.display = '';
        blanks.textContent = `${c.blanks} blank${c.blanks > 1 ? 's' : ''} (highlighted) still to fill — use Edit details, type over them, or ask AI.`;
      } else {
        blanks.style.display = 'none';
      }

      document.getElementById('ctrdAiHistory').textContent = c.ai_instructions || '—';
      document.getElementById('ctrdAiInstructions').value = '';
      document.getElementById('ctrdSendEmail').value = c.client_email || '';
      document.getElementById('ctrdLink').textContent = c.sign_url || '';

      this.tab(tab && !locked ? tab : 'text');
      window.openModal('contractDetailModal');
    },

    tab: function (name) {
      document.querySelectorAll('[data-ct-tab]').forEach(b => b.classList.toggle('active', b.getAttribute('data-ct-tab') === name));
      document.querySelectorAll('[data-ct-pane]').forEach(p => { p.hidden = p.getAttribute('data-ct-pane') !== name; });
    },

    fmt: function (cmd, value) {
      document.getElementById('ctrdEditor').focus();
      document.execCommand(cmd, false, value ? `<${value}>` : null);
    },

    refreshActive: function (contract) {
      this.showDetail(contract, document.querySelector('[data-ct-tab].active')?.getAttribute('data-ct-tab'));
      this.load();
    },

    saveText: async function () {
      const c = this.active;
      if (!c) return;
      const btn = document.getElementById('ctrdSaveTextBtn');
      btn.disabled = true;
      try {
        const data = await api(`/api/contracts/${c.id}`, { method: 'PUT', body: JSON.stringify({ body: document.getElementById('ctrdEditor').innerHTML }) });
        toast('Agreement text saved', c.contract_number);
        this.refreshActive(data.data);
      } catch (err) {
        toast('Could not save', err.message, true);
      } finally {
        btn.disabled = false;
      }
    },

    aiRevise: async function () {
      const c = this.active;
      const instructions = document.getElementById('ctrdAiInstructions').value.trim();
      if (!instructions) return toast('Add instructions', 'Tell AI what to change in the agreement.', true);
      if (this.meta && !this.meta.ai_available) return toast('AI not configured', 'Add ANTHROPIC_API_KEY to the server .env.', true);

      const btn = document.getElementById('ctrdAiBtn');
      const status = document.getElementById('ctrdAiStatus');
      btn.disabled = true;
      btn.textContent = '✦ Revising…';
      status.textContent = 'J- ai is rewriting the agreement — this can take up to a minute.';
      try {
        // Save any unsaved manual edits first so AI works on the latest text
        await api(`/api/contracts/${c.id}`, { method: 'PUT', body: JSON.stringify({ body: document.getElementById('ctrdEditor').innerHTML }) });
        const data = await api(`/api/contracts/${c.id}/ai-revise`, { method: 'POST', body: JSON.stringify({ instructions }) });
        toast('Agreement revised', data.message);
        this.refreshActive(data.data);
        this.tab('text');
      } catch (err) {
        toast('AI revision failed', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = '✦ Revise agreement';
        status.textContent = 'Takes up to a minute. You can review and edit the result afterwards.';
      }
    },

    confirmBlanks: function () {
      const c = this.active;
      if (c && c.blanks > 0) {
        return window.confirm(`This agreement still has ${c.blanks} blank${c.blanks > 1 ? 's' : ''}. Send it anyway?`);
      }
      return true;
    },

    sendEmail: async function () {
      const c = this.active;
      const email = document.getElementById('ctrdSendEmail').value.trim();
      if (!email) return toast('Email required', 'Enter the client\'s email address.', true);
      if (!this.confirmBlanks()) return;

      const btn = document.getElementById('ctrdSendEmailBtn');
      btn.disabled = true;
      btn.textContent = 'Sending…';
      try {
        const data = await api(`/api/contracts/${c.id}/send-email`, { method: 'POST', body: JSON.stringify({ email, message: document.getElementById('ctrdSendMessage').value.trim() || null }) });
        toast('Sent for signature', data.message);
        this.refreshActive(data.data);
      } catch (err) {
        toast('Email failed', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send by Email';
      }
    },

    sendWhatsApp: async function () {
      const c = this.active;
      if (!this.confirmBlanks()) return;
      const win = window.open('about:blank', '_blank');
      try {
        const data = await api(`/api/contracts/${c.id}/whatsapp`);
        if (win) win.location = data.whatsapp_url; else window.location.href = data.whatsapp_url;
        toast('WhatsApp ready', 'Signing link shared — Barny\'s signature is applied.');
        this.refreshActive(data.data);
      } catch (err) {
        if (win) win.close();
        toast('Could not prepare WhatsApp', err.message, true);
      }
    },

    copyLink: async function () {
      const c = this.active;
      if (!this.confirmBlanks()) return;
      try {
        let data = { data: c };
        if (c.status === 'Draft') {
          data = await api(`/api/contracts/${c.id}/mark-sent`, { method: 'POST' });
        }
        await navigator.clipboard.writeText(c.sign_url);
        toast('Signing link copied', 'Paste it to the client — the agreement is now open for signature.');
        this.refreshActive(data.data);
      } catch (err) {
        toast('Could not copy link', err.message || 'Copy it from the box below.', true);
      }
    },

    preview: function () {
      if (this.active) window.open(this.active.sign_url + '?preview=1', '_blank', 'noopener');
    },

    voidActive: async function () {
      const c = this.active;
      if (!window.confirm(`Void ${c.contract_number}? The client will no longer be able to sign it.`)) return;
      try {
        const data = await api(`/api/contracts/${c.id}/void`, { method: 'POST' });
        toast('Contract voided', data.message);
        this.refreshActive(data.data);
      } catch (err) {
        toast('Could not void', err.message, true);
      }
    },

    deleteActive: async function () {
      const c = this.active;
      if (!window.confirm(`Delete ${c.contract_number} permanently?`)) return;
      try {
        const data = await api(`/api/contracts/${c.id}`, { method: 'DELETE' });
        toast('Contract deleted', data.message);
        window.closeModal('contractDetailModal');
        this.load();
      } catch (err) {
        toast('Could not delete', err.message, true);
      }
    }
  };

  /** Called from the quotation detail modal: start a contract from the open quote. */
  window.createContractFromQuote = function (quoteId) {
    const id = quoteId || (document.getElementById('qdmQuoteId') || {}).value;
    window.closeModal('quoteDetailModal');
    if (typeof window.showView === 'function') window.showView('contracts');
    window.JMOS_CONTRACTS.openBuilder(null, { quoteId: id });
  };
})();
