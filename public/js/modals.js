/* ==========================================================================
   JMOS — Modals, Deal-Won Cascade Flow & Interactive Database Actions
   ========================================================================== */

// Helper: Setup Reusable Client Dropdown with "+ New Client" option
function setupModalClientPicker(selectId, wrapId, inputId, hiddenId, toggleBtnId) {
  const select = document.getElementById(selectId);
  const wrap = document.getElementById(wrapId);
  const input = document.getElementById(inputId);
  const hidden = document.getElementById(hiddenId);
  const toggleBtn = document.getElementById(toggleBtnId);

  if (!select) return;

  if (wrap) wrap.style.display = 'none';
  if (input) input.value = '';
  if (hidden) hidden.value = '';
  if (toggleBtn) toggleBtn.innerHTML = '+ New Client';

  const clients = JMOS_STATE.clients || [];
  let opts = '<option value="">-- Choose existing client --</option>';
  clients.forEach(c => {
    const name = c.client_name || c.name || '';
    opts += `<option value="${escHtml(name)}">${escHtml(name)}${c.contact_person ? ` (${escHtml(c.contact_person)})` : ''}</option>`;
  });
  opts += '<option value="__new__">+ Add new client...</option>';
  select.innerHTML = opts;
  select.value = '';

  if (!select.dataset.clientPickerBound) {
    select.dataset.clientPickerBound = 'true';
    select.addEventListener('change', () => {
      if (select.value === '__new__') {
        if (wrap) wrap.style.display = 'block';
        if (toggleBtn) toggleBtn.innerHTML = '<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:middle;margin-right:2px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Use Existing';
        if (hidden) hidden.value = '';
        setTimeout(() => input?.focus(), 60);
      } else {
        if (wrap) wrap.style.display = 'none';
        if (toggleBtn) toggleBtn.innerHTML = '<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:middle;margin-right:2px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> New Client';
        if (hidden) hidden.value = select.value;
      }
    });
  }

  if (toggleBtn && !toggleBtn.dataset.toggleBound) {
    toggleBtn.dataset.toggleBound = 'true';
    toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isHidden = wrap ? (wrap.style.display === 'none') : true;
      if (wrap) wrap.style.display = isHidden ? 'block' : 'none';
      toggleBtn.innerHTML = isHidden
        ? '<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:middle;margin-right:2px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Use Existing'
        : '<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block;vertical-align:middle;margin-right:2px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> New Client';
      if (isHidden) {
        select.value = '__new__';
        if (hidden) hidden.value = '';
        setTimeout(() => input?.focus(), 60);
      } else {
        select.value = '';
        if (hidden) hidden.value = '';
      }
    });
  }

  if (input && !input.dataset.inputBound) {
    input.dataset.inputBound = 'true';
    input.addEventListener('input', () => {
      if (hidden) hidden.value = input.value.trim();
    });
  }
}

// Helper: Quick create client inline inside modal
async function quickCreateClientForModal(inputId, selectId, wrapId, toggleBtnId, hiddenId, saveBtn) {
  const input = document.getElementById(inputId);
  const clientName = input?.value.trim();
  if (!clientName) {
    showToast('Client Name Required', 'Please enter a client name', true);
    return null;
  }

  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }

  try {
    await JMOS_API.post('/clients', {
      client_name: clientName,
      client_type: 'Corporate',
      project_status: 'Active'
    });

    await JMOS_API.fetchAll();

    const select = document.getElementById(selectId);
    const wrap = document.getElementById(wrapId);
    const toggleBtn = document.getElementById(toggleBtnId);
    const hidden = document.getElementById(hiddenId);

    if (select) {
      let opts = '<option value="">-- Choose existing client --</option>';
      (JMOS_STATE.clients || []).forEach(c => {
        const name = c.client_name || c.name || '';
        opts += `<option value="${escHtml(name)}">${escHtml(name)}${c.contact_person ? ` (${escHtml(c.contact_person)})` : ''}</option>`;
      });
      opts += '<option value="__new__">+ Add new client...</option>';
      select.innerHTML = opts;
      select.value = clientName;
    }

    if (hidden) hidden.value = clientName;
    if (wrap) wrap.style.display = 'none';
    if (toggleBtn) toggleBtn.innerHTML = '+ New Client';

    showToast('Client created', `${clientName} added & selected`);
    return clientName;
  } catch (err) {
    showToast('Failed to save client', err.message, true);
    return null;
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save & Pick';
    }
  }
}

// Helper: Resolve chosen client name (selected or newly entered)
function resolveModalClient(selectId, wrapId, inputId, hiddenId) {
  const wrap = document.getElementById(wrapId);
  const isNewOpen = wrap && wrap.style.display !== 'none';
  const newName = document.getElementById(inputId)?.value.trim();
  const selectVal = document.getElementById(selectId)?.value;
  const hiddenVal = document.getElementById(hiddenId)?.value.trim();

  if (isNewOpen && newName) {
    // Automatically save in background if not already registered
    const exists = (JMOS_STATE.clients || []).some(c => (c.client_name || '').toLowerCase() === newName.toLowerCase());
    if (!exists) {
      JMOS_API.post('/clients', {
        client_name: newName,
        client_type: 'Corporate',
        project_status: 'Active'
      }).catch(console.warn);
    }
    return newName;
  }

  if (selectVal && selectVal !== '__new__') return selectVal;
  if (hiddenVal && hiddenVal !== '__new__') return hiddenVal;
  return '';
}

// Global Modal Open & Close Functions
window.openModal = function(id) {
  const m = typeof id === 'string' ? document.getElementById(id) : id;
  if (!m) return;

  // Prepare / pre-fill modal-specific defaults
  const modalId = m.id;
  if (modalId === 'dealModal') {
    ['ndTitle', 'ndValue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    setupModalClientPicker('ndClientSelect', 'ndNewClientWrap', 'ndNewClientInput', 'ndClient', 'ndToggleNewClientBtn');
    const st = document.getElementById('ndStage');
    if (st) st.value = 'lead';
  } else if (modalId === 'clientModal') {
    ['ncName', 'ncContact', 'ncOwner', 'ncService', 'ncValue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
  } else if (modalId === 'projectModal') {
    ['npName', 'npDeadline', 'npBudget'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    setupModalClientPicker('npClientSelect', 'npNewClientWrap', 'npNewClientInput', 'npClient', 'npToggleNewClientBtn');

    // Populate project managers from active team members
    const mgrSelect = document.getElementById('npManager');
    if (mgrSelect && JMOS_STATE.users && JMOS_STATE.users.length) {
      mgrSelect.innerHTML = JMOS_STATE.users.map(u => `
        <option value="${escHtml(u.name)}">${escHtml(u.name)} (${escHtml(u.title || u.role)})</option>
      `).join('');
    }

    // Populate project types from registered service recipes
    const typeSelect = document.getElementById('npType');
    if (typeSelect && JMOS_STATE.services && JMOS_STATE.services.length) {
      const standardTypes = JMOS_STATE.services.map(s => s.name);
      typeSelect.innerHTML = `
        ${standardTypes.map(t => `<option value="${escHtml(t)}">${escHtml(t)}</option>`).join('')}
        <option value="Commercial">Commercial</option>
        <option value="Other">Other / Custom</option>
      `;
    }

    const st = document.getElementById('npStage');
    if (st) st.value = 'brief';
    const sts = document.getElementById('npStatus');
    if (sts) sts.value = 'On track';
  } else if (modalId === 'taskModal') {
    const el = document.getElementById('ntTitle');
    if (el) el.value = '';
    const st = document.getElementById('ntStage');
    if (st) st.value = 'todo';

    // Populate projects dropdown
    const pSel = document.getElementById('ntProject');
    if (pSel) {
      const pList = (window.JMOS_STATE && JMOS_STATE.projects) ? JMOS_STATE.projects : [];
      let opts = '<option value="">— Select a project —</option>';
      opts += pList.map(p => `<option value="${p.id}">${escHtml(p.project_name)} (${escHtml(p.client || 'Client')})</option>`).join('');
      pSel.innerHTML = opts;

      if (window._preselectedProjectId) {
        pSel.value = String(window._preselectedProjectId);
        delete window._preselectedProjectId;
      }
    }
  } else if (modalId === 'invoiceModal') {
    const maxNo = (window.JMOS_STATE && JMOS_STATE.invoices && JMOS_STATE.invoices.length)
      ? (145 + JMOS_STATE.invoices.length) 
      : 146;
    const niNo = document.getElementById('niNo');
    if (niNo) niNo.value = 'JM-0' + maxNo;
    ['niAmount', 'niDue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    setupModalClientPicker('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient', 'niToggleNewClientBtn');
  } else if (modalId === 'expenseModal') {
    ['neName', 'neProject', 'neAmount'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const neDate = document.getElementById('neDate');
    if (neDate) neDate.value = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  } else if (modalId === 'userModal') {
    ['nuName', 'nuTitle', 'nuEmail', 'nuPay'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const nuPass = document.getElementById('nuPass');
    if (nuPass) nuPass.value = 'jeota2024';
  } else if (modalId === 'serviceModal') {
    ['nsName', 'nsCode', 'nsDeliverables'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const nsStages = document.getElementById('nsStages');
    if (nsStages) nsStages.value = 'brief, concept, pre-pro, shoot, edit, review, delivery';
  }

  m.classList.add('on');
  m.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  // Auto-focus first input in the modal
  setTimeout(() => {
    const firstInput = m.querySelector('input:not([type="hidden"]), select, textarea');
    if (firstInput) firstInput.focus();
  }, 60);
};

window.closeModal = function(id) {
  const m = typeof id === 'string' ? document.getElementById(id) : id;
  if (m) {
    m.classList.remove('on');
    m.style.display = 'none';
  }
  if (!document.querySelector('.modal.on') && !document.querySelector('.cascade.on')) {
    document.body.style.overflow = '';
  }
};

window.showToast = function(title, subtitle, isRed = false) {
  const toastsContainer = document.getElementById('toasts');
  if (!toastsContainer) return;

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <div class="tk ${isRed ? 'red' : ''}">
      <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div>
      <b>${escHtml(title)}</b>
      <small>${escHtml(subtitle || '')}</small>
    </div>
  `;

  toastsContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'opacity .4s, transform .4s';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    setTimeout(() => toast.remove(), 400);
  }, 3400);
};

window.openTaskModal = function(projectId) {
  if (projectId) {
    window._preselectedProjectId = projectId;
  }
  openModal('taskModal');
  if (projectId) {
    const pSel = document.getElementById('ntProject');
    if (pSel) pSel.value = String(projectId);
  }
};

function initModals() {
  const cascade = document.getElementById('cascade');
  const cascadeDone = document.getElementById('cascadeDone');
  const cascadeBg = document.getElementById('cascadeBg');

  // 1. Close modal on [data-close], .mclose, or backdrop (.mbg) click
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) {
      e.preventDefault();
      closeModal(closeBtn.getAttribute('data-close'));
      return;
    }

    if (e.target.closest('.mclose')) {
      e.preventDefault();
      const modal = e.target.closest('.modal');
      if (modal) closeModal(modal);
      return;
    }

    if (e.target.classList.contains('mbg')) {
      const modal = e.target.closest('.modal');
      if (modal) closeModal(modal);
    }
  });

  // 2. Action buttons [data-do] feedback
  document.addEventListener('click', (e) => {
    const doBtn = e.target.closest('[data-do]');
    if (doBtn) {
      showToast('Done', doBtn.getAttribute('data-do'));
      doBtn.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>';
      doBtn.style.background = 'var(--green)';
      doBtn.style.color = '#fff';
      doBtn.style.borderColor = 'var(--green)';
    }
  });

  // 3. Global listener for opening modals via data-modal-open or button IDs
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-modal-open]');
    if (trigger) {
      e.preventDefault();
      const modalId = trigger.getAttribute('data-modal-open');
      openModal(modalId);
      return;
    }

    if (e.target.closest('#addClientBtn')) {
      e.preventDefault();
      openModal('clientModal');
      return;
    }

    if (e.target.closest('#addProjectBtn')) {
      e.preventDefault();
      openModal('projectModal');
      return;
    }

    if (e.target.closest('#addDealBtn') || e.target.closest('#dashQuickActionBtn') || e.target.closest('#quickAddDeal')) {
      e.preventDefault();
      openModal('dealModal');
      return;
    }

    if (e.target.closest('#addTaskBtn')) {
      e.preventDefault();
      openModal('taskModal');
      return;
    }

    if (e.target.closest('#addInvoiceBtn')) {
      e.preventDefault();
      openModal('invoiceModal');
      return;
    }

    if (e.target.closest('#addExpenseBtn')) {
      e.preventDefault();
      openModal('expenseModal');
      return;
    }

    if (e.target.closest('#addUserBtn')) {
      e.preventDefault();
      openModal('userModal');
      return;
    }

    if (e.target.closest('#addServiceBtn')) {
      e.preventDefault();
      openModal('serviceModal');
      return;
    }
  });

  // 4. Modal Submissions via Event Delegation
  document.addEventListener('click', async (e) => {
    // 4.1 Submit: Add Client
    if (e.target.closest('#saveClientBtn')) {
      const btn = e.target.closest('#saveClientBtn');
      const name = document.getElementById('ncName')?.value.trim();
      if (!name) return showToast('Client name required', 'Please enter a client name', true);

      btn.disabled = true;
      btn.textContent = 'Saving…';
      try {
        await JMOS_API.post('/clients', {
          client_name: name,
          client_type: document.getElementById('ncType')?.value,
          contact_person: document.getElementById('ncContact')?.value.trim(),
          owner: document.getElementById('ncOwner')?.value.trim(),
          service: document.getElementById('ncService')?.value.trim(),
          project_value: Number(document.getElementById('ncValue')?.value) || 0,
          project_status: 'Active',
          projects: 1
        });
        closeModal('clientModal');
        showToast('Client created', `${name} saved to database`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to save client', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Save client';
      }
      return;
    }

    // Quick Add Client Buttons inside modals
    if (e.target.closest('#npSaveNewClientBtn')) {
      e.preventDefault();
      await quickCreateClientForModal('npNewClientInput', 'npClientSelect', 'npNewClientWrap', 'npToggleNewClientBtn', 'npClient', e.target.closest('#npSaveNewClientBtn'));
      return;
    }
    if (e.target.closest('#ndSaveNewClientBtn')) {
      e.preventDefault();
      await quickCreateClientForModal('ndNewClientInput', 'ndClientSelect', 'ndNewClientWrap', 'ndToggleNewClientBtn', 'ndClient', e.target.closest('#ndSaveNewClientBtn'));
      return;
    }
    if (e.target.closest('#niSaveNewClientBtn')) {
      e.preventDefault();
      await quickCreateClientForModal('niNewClientInput', 'niClientSelect', 'niNewClientWrap', 'niToggleNewClientBtn', 'niClient', e.target.closest('#niSaveNewClientBtn'));
      return;
    }

    // 4.2 Submit: Add Project
    if (e.target.closest('#saveProjectBtn')) {
      const btn = e.target.closest('#saveProjectBtn');
      const name = document.getElementById('npName')?.value.trim();
      const client = resolveModalClient('npClientSelect', 'npNewClientWrap', 'npNewClientInput', 'npClient');
      if (!name || !client) return showToast('Name & Client required', 'Please fill project name and select or add a client', true);

      btn.disabled = true;
      btn.textContent = 'Creating…';
      try {
        await JMOS_API.post('/projects', {
          project_name: name,
          client: client,
          project_type: document.getElementById('npType')?.value.trim() || 'Brand Film',
          project_manager: document.getElementById('npManager')?.value.trim() || 'Barny Kiome',
          stage: document.getElementById('npStage')?.value || 'brief',
          status: document.getElementById('npStatus')?.value || 'On track',
          priority: 'High',
          deadline: document.getElementById('npDeadline')?.value.trim() || 'Sep 30',
          budget: Number(document.getElementById('npBudget')?.value) || 0,
          progress_pct: 10,
          waiting_on: 'us'
        });
        closeModal('projectModal');
        showToast('Project created', `${name} added to delivery board`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to create project', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Create project';
      }
      return;
    }

    // 4.3 Submit: Add Pipeline Deal
    if (e.target.closest('#saveDealBtn')) {
      const btn = e.target.closest('#saveDealBtn');
      const title = document.getElementById('ndTitle')?.value.trim();
      const client = resolveModalClient('ndClientSelect', 'ndNewClientWrap', 'ndNewClientInput', 'ndClient');
      const val = Number(document.getElementById('ndValue')?.value) || 0;
      if (!title || !client) return showToast('Title & Client required', 'Please enter deal title and select or add a client', true);

      btn.disabled = true;
      btn.textContent = 'Adding…';
      try {
        await JMOS_API.post('/pipeline', {
          title,
          client_name: client,
          stage: document.getElementById('ndStage')?.value || 'lead',
          value: val,
          meta_text: fmtK(val)
        });
        closeModal('dealModal');
        showToast('Deal added', `${title} added to pipeline`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to add deal', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Add to pipeline';
      }
      return;
    }

    // 4.4 Submit: Add Task
    if (e.target.closest('#saveTaskBtn')) {
      const btn = e.target.closest('#saveTaskBtn');
      const title = document.getElementById('ntTitle')?.value.trim();
      if (!title) return showToast('Task title required', 'Please enter a title', true);

      btn.disabled = true;
      btn.textContent = 'Creating…';
      const assigned = document.getElementById('ntAssigned')?.value || 'Barny Kiome';
      const userObj = (window.JMOS_STATE && JMOS_STATE.users) ? JMOS_STATE.users.find(u => u.name === assigned) : null;
      const projectIdVal = document.getElementById('ntProject')?.value;

      try {
        await JMOS_API.post('/tasks', {
          project_id: projectIdVal ? Number(projectIdVal) : null,
          title,
          stage: document.getElementById('ntStage')?.value || 'todo',
          assigned_to: assigned,
          assigned_initials: userObj ? userObj.ini : getInitials(assigned),
          assigned_color: userObj ? userObj.color : '#C52523'
        });
        closeModal('taskModal');
        showToast('Task created', `${title} assigned to ${assigned}`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to create task', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Create task';
      }
      return;
    }

    // 4.5 Submit: Add Invoice
    if (e.target.closest('#saveInvoiceBtn')) {
      const btn = e.target.closest('#saveInvoiceBtn');
      const no = document.getElementById('niNo')?.value.trim();
      const client = resolveModalClient('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient');
      const amt = Number(document.getElementById('niAmount')?.value) || 0;
      if (!no || !client || !amt) return showToast('Invoice details required', 'Enter invoice number, client and amount', true);

      btn.disabled = true;
      btn.textContent = 'Issuing…';
      try {
        await JMOS_API.post('/invoices', {
          invoice_no: no,
          client: client,
          type: document.getElementById('niType')?.value || 'Deposit 60%',
          amount: amt,
          method: null,
          etims: document.getElementById('niEtims')?.value === '1',
          status: 'Sent',
          due_date: document.getElementById('niDue')?.value.trim() || 'Sep 30'
        });
        closeModal('invoiceModal');
        showToast('Invoice issued', `${no} for ${client} (${fmt(amt)})`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to issue invoice', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Issue invoice';
      }
      return;
    }

    // 4.6 Submit: Log Expense
    if (e.target.closest('#saveExpenseBtn')) {
      const btn = e.target.closest('#saveExpenseBtn');
      const name = document.getElementById('neName')?.value.trim();
      const amt = Number(document.getElementById('neAmount')?.value) || 0;
      if (!name || !amt) return showToast('Expense details required', 'Enter name and amount', true);

      btn.disabled = true;
      btn.textContent = 'Logging…';
      try {
        await JMOS_API.post('/expenses', {
          name,
          category: document.getElementById('neCat')?.value || 'Equipment',
          project: document.getElementById('neProject')?.value.trim() || 'overhead',
          amount: amt,
          etr: document.getElementById('neEtr')?.value || 'no',
          date: document.getElementById('neDate')?.value.trim() || 'Today'
        });
        closeModal('expenseModal');
        showToast('Expense recorded', `${name} — ${fmt(amt)}`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to log expense', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Log expense';
      }
      return;
    }

    // 4.7 Submit: Add Service Recipe
    if (e.target.closest('#saveServiceBtn')) {
      const btn = e.target.closest('#saveServiceBtn');
      const name = document.getElementById('nsName')?.value.trim();
      const code = document.getElementById('nsCode')?.value.trim();
      const deliv = document.getElementById('nsDeliverables')?.value.trim() || '';
      const stagesStr = document.getElementById('nsStages')?.value.trim() || '';

      if (!name || !code) return showToast('Name & Code required', 'Please enter service name and code', true);

      btn.disabled = true;
      btn.textContent = 'Saving…';

      const stages = stagesStr ? stagesStr.split(',').map(s => s.trim()).filter(Boolean) : [];

      try {
        await JMOS_API.post('/services', {
          name,
          code,
          deliverables: deliv,
          stages
        });
        closeModal('serviceModal');
        showToast('Service recipe added', `${name} saved`);
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Failed to save service', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Save service';
      }
      return;
    }
  });

  // Handle Enter key on inline quick new client inputs
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      if (e.target.id === 'npNewClientInput') {
        e.preventDefault();
        document.getElementById('npSaveNewClientBtn')?.click();
      } else if (e.target.id === 'ndNewClientInput') {
        e.preventDefault();
        document.getElementById('ndSaveNewClientBtn')?.click();
      } else if (e.target.id === 'niNewClientInput') {
        e.preventDefault();
        document.getElementById('niSaveNewClientBtn')?.click();
      }
    }
  });

  // 5. Deal-Won Cascade Flow
  async function triggerCascadeWin(dealId) {
    const deal = (window.JMOS_STATE && JMOS_STATE.pipeline)
      ? (JMOS_STATE.pipeline.find(d => String(d.id) === String(dealId)) || JMOS_STATE.pipeline[0])
      : null;
    if (!deal) return;

    if (cascade) {
      cascade.classList.add('on');
      cascade.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      const title = document.getElementById('cascadeTitle');
      if (title) title.innerHTML = `Winning <b>${escHtml(deal.client_name || deal.title)}</b>…`;
      const s1 = document.getElementById('cstep1sub');
      if (s1) s1.textContent = `${deal.title} · ${fmt(deal.value)} booked`;
      const s5 = document.getElementById('cstep5sub');
      if (s5) s5.textContent = `60% · ${fmt(deal.value * 0.6)}`;

      const steps = cascade.querySelectorAll('.cstep');
      steps.forEach(x => x.classList.remove('done'));
      steps.forEach((x, i) => {
        setTimeout(() => x.classList.add('done'), 220 + i * 320);
      });
    }

    try {
      if (deal.id) {
        await JMOS_API.post(`/pipeline/${deal.id}/win`, {});
      }
      await JMOS_API.fetchAll();
      renderAllViews();
    } catch (err) {
      console.warn('Cascade API sync note:', err.message);
    }
  }

  // Listen for per-deal win buttons
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-win-deal-id]');
    if (btn) {
      e.preventDefault();
      const id = btn.getAttribute('data-win-deal-id');
      triggerCascadeWin(id);
    }
  });

  if (cascadeDone && cascade) {
    cascadeDone.onclick = () => {
      cascade.classList.remove('on');
      cascade.style.display = 'none';
      if (!document.querySelector('.modal.on')) document.body.style.overflow = '';
      showToast('Deal won & project live', '8 tasks created · invoice drafted · team notified');
    };
  }

  if (cascadeBg && cascade) {
    cascadeBg.onclick = () => {
      cascade.classList.remove('on');
      cascade.style.display = 'none';
      if (!document.querySelector('.modal.on')) document.body.style.overflow = '';
    };
  }

  // 6. Global Escape key listener
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (cascade) {
        cascade.classList.remove('on');
        cascade.style.display = 'none';
      }
      document.querySelectorAll('.modal.on').forEach(m => {
        m.classList.remove('on');
        m.style.display = 'none';
      });
      document.body.style.overflow = '';
    }
  });
}

// 7. Global Reusable Custom Confirm Dialog (connects to #confirmModal)
window.showConfirmDialog = function({ title = 'Remove item?', message = 'Are you sure you want to remove this record?', confirmText = 'Remove & delete', isDanger = true } = {}) {
  return new Promise((resolve) => {
    const modal = document.getElementById('confirmModal');
    const titleEl = document.getElementById('confirmTitle');
    const msgEl = document.getElementById('confirmMsg');
    const yesBtn = document.getElementById('confirmYes');

    if (!modal || !yesBtn) {
      // Fallback if modal is absent
      const ok = window.confirm(`${title}\n\n${message}`);
      return resolve(ok);
    }

    if (titleEl) titleEl.textContent = title;
    if (msgEl) msgEl.innerHTML = message;
    yesBtn.textContent = confirmText;
    if (isDanger) {
      yesBtn.style.background = 'var(--red)';
      yesBtn.style.borderColor = 'var(--red)';
    } else {
      yesBtn.style.background = 'var(--brand)';
      yesBtn.style.borderColor = 'var(--brand)';
    }

    let resolved = false;

    const cleanup = () => {
      yesBtn.removeEventListener('click', onYes);
      modal.querySelectorAll('[data-close="confirmModal"]').forEach(b => b.removeEventListener('click', onCancel));
    };

    const onYes = () => {
      if (resolved) return;
      resolved = true;
      cleanup();
      modal.classList.remove('on');
      modal.style.display = 'none';
      if (!document.querySelector('.modal.on')) document.body.style.overflow = '';
      resolve(true);
    };

    const onCancel = () => {
      if (resolved) return;
      resolved = true;
      cleanup();
      modal.classList.remove('on');
      modal.style.display = 'none';
      if (!document.querySelector('.modal.on')) document.body.style.overflow = '';
      resolve(false);
    };

    yesBtn.addEventListener('click', onYes);
    modal.querySelectorAll('[data-close="confirmModal"]').forEach(b => b.addEventListener('click', onCancel));

    modal.style.display = 'flex';
    requestAnimationFrame(() => modal.classList.add('on'));
    document.body.style.overflow = 'hidden';
  });
};

// 8. Action Card Triggers for Manage & Delete
window.triggerCleanupProjects = async function() {
  const projects = JMOS_STATE.projects || [];
  const completedOrDelivered = projects.filter(p => {
    const st = (p.status || '').toLowerCase();
    const sg = (p.stage || '').toLowerCase();
    return st === 'completed' || sg === 'deliver' || sg === 'delivered';
  });

  if (!projects.length) {
    showToast('No projects', 'There are no projects to manage in the database.');
    return;
  }

  const confirmed = await window.showConfirmDialog({
    title: 'Manage & Delete Projects',
    message: `Database has <b>${projects.length}</b> total project(s).<br>Found <b>${completedOrDelivered.length}</b> completed or delivered projects.<br><br>To delete individual projects, click the red <b>Delete</b> action on any row below. Would you like to filter to review completed projects now?`,
    confirmText: 'Review Completed',
    isDanger: false
  });

  if (confirmed && completedOrDelivered.length) {
    showToast('Filtered View', `Showing ${completedOrDelivered.length} completed projects for review`);
  }
};

window.triggerCleanupClients = async function() {
  const clients = JMOS_STATE.clients || [];
  const zeroProjectClients = clients.filter(c => !c.projects || parseInt(c.projects, 10) === 0);

  if (!clients.length) {
    showToast('No clients', 'There are no clients to manage in the database.');
    return;
  }

  await window.showConfirmDialog({
    title: 'Client Directory Audit',
    message: `Directory has <b>${clients.length}</b> client(s) on file.<br><b>${zeroProjectClients.length}</b> client(s) currently have 0 active projects linked.<br><br>To remove a client, click the <b>Delete</b> button on their row in the table below.`,
    confirmText: 'Got it',
    isDanger: false
  });
};
