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

// Automatically assign next ascending invoice number (e.g. JM-0146, JM-0147)
window.getNextInvoiceNo = function() {
  const list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  let maxNum = 145;
  list.forEach(inv => {
    if (inv && inv.invoice_no) {
      const m = String(inv.invoice_no).match(/(\d+)/);
      if (m) {
        const n = parseInt(m[1], 10);
        if (!isNaN(n) && n > maxNum) {
          maxNum = n;
        }
      }
    }
  });
  const next = maxNum + 1;
  return 'JM-' + String(next).padStart(4, '0');
};

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

    // Reset searchable project picker
    const searchInput = document.getElementById('taskProjectSearchInput');
    if (searchInput) searchInput.value = '';
    const searchClear = document.getElementById('taskProjectSearchClear');
    if (searchClear) searchClear.style.display = 'none';
    const dropdown = document.getElementById('taskProjectDropdown');
    if (dropdown) dropdown.style.display = 'none';
    const trigger = document.getElementById('taskProjectTrigger');
    if (trigger) trigger.classList.remove('active');

    if (window._preselectedProjectId) {
      if (typeof window.selectTaskProject === 'function') {
        window.selectTaskProject(window._preselectedProjectId);
      }
      delete window._preselectedProjectId;
    } else {
      if (typeof window.selectTaskProject === 'function') {
        window.selectTaskProject(null);
      }
    }

    if (typeof window.ensureProjectsLoadedForTask === 'function') {
      window.ensureProjectsLoadedForTask();
    }
  } else if (modalId === 'invoiceModal') {
    const editId = document.getElementById('editInvoiceId')?.value;
    if (!editId) {
      const titleEl = document.getElementById('invoiceModalTitle');
      if (titleEl) titleEl.textContent = 'Issue new invoice';
      const subEl = document.getElementById('invoiceModalSub');
      if (subEl) subEl.textContent = 'Record an outgoing client invoice in JMOS.';
      const saveBtn = document.getElementById('saveInvoiceBtn');
      if (saveBtn) saveBtn.textContent = 'Issue invoice';
      const delBtn = document.getElementById('deleteInvoiceModalBtn');
      if (delBtn) delBtn.style.display = 'none';

      const niNo = document.getElementById('niNo');
      if (niNo) niNo.value = window.getNextInvoiceNo();
      ['niAmount', 'niDue', 'niMethod'].forEach(fid => {
        const el = document.getElementById(fid);
        if (el) el.value = '';
      });
      const st = document.getElementById('niStatus');
      if (st) st.value = 'Sent';
      const etims = document.getElementById('niEtims');
      if (etims) etims.value = '1';
      setupModalClientPicker('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient', 'niToggleNewClientBtn');
    }
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
    if (m.id === 'invoiceModal') {
      const editId = document.getElementById('editInvoiceId');
      if (editId) editId.value = '';
      const delBtn = document.getElementById('deleteInvoiceModalBtn');
      if (delBtn) delBtn.style.display = 'none';
    }
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

/* ==========================================================================
   Task Modal Searchable Project Picker Controller
   ========================================================================== */

window.populateTaskProjectOptions = function(filterText = '') {
  const listEl = document.getElementById('taskProjectOptionsList');
  if (!listEl) return;

  const projects = (window.JMOS_STATE && Array.isArray(JMOS_STATE.projects)) ? JMOS_STATE.projects : [];
  const currentVal = document.getElementById('ntProject')?.value || '';
  const term = (filterText || '').trim().toLowerCase();

  const filtered = term
    ? projects.filter(p => {
        const name = (p.project_name || p.name || '').toLowerCase();
        const client = (p.client || '').toLowerCase();
        const type = (p.project_type || '').toLowerCase();
        return name.includes(term) || client.includes(term) || type.includes(term);
      })
    : projects;

  let html = '';

  // Standalone option (No project)
  const isNoneSelected = !currentVal;
  html += `
    <div class="searchable-select-item ${isNoneSelected ? 'selected' : ''}" data-project-id="" role="option" aria-selected="${isNoneSelected}">
      <div class="searchable-select-item-title">
        <span style="color:var(--muted)">— None (Standalone task) —</span>
      </div>
    </div>
  `;

  if (!projects.length) {
    html += `
      <div class="searchable-select-empty">
        No active projects found in database.<br>
        <span style="font-size:11px;opacity:0.8">You can proceed without a project or create one first.</span>
      </div>
    `;
  } else if (!filtered.length) {
    html += `
      <div class="searchable-select-empty">
        No projects match "<b>${escHtml(filterText)}</b>"
      </div>
    `;
  } else {
    html += filtered.map(p => {
      const isSelected = String(p.id) === String(currentVal);
      const name = p.project_name || p.name || ('Project #' + p.id);
      const client = p.client || 'Client';
      const stage = p.stage ? p.stage.replace(/_/g, ' ') : '';
      return `
        <div class="searchable-select-item ${isSelected ? 'selected' : ''}" data-project-id="${p.id}" role="option" aria-selected="${isSelected}">
          <div class="searchable-select-item-title">
            <span style="font-weight:600">${escHtml(name)}</span>
          </div>
          <div class="searchable-select-item-meta">
            <span class="searchable-select-badge">${escHtml(client)}</span>
            ${stage ? `<span style="font-size:10.5px;text-transform:capitalize;opacity:0.75">${escHtml(stage)}</span>` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  listEl.innerHTML = html;
};

window.selectTaskProject = function(projectId) {
  const hiddenInput = document.getElementById('ntProject');
  const labelEl = document.getElementById('taskProjectSelectedLabel');
  const clearBtn = document.getElementById('taskProjectClearBtn');

  const val = (projectId != null && projectId !== '') ? String(projectId) : '';
  if (hiddenInput) hiddenInput.value = val;

  const projects = (window.JMOS_STATE && Array.isArray(JMOS_STATE.projects)) ? JMOS_STATE.projects : [];
  const project = val ? projects.find(p => String(p.id) === val) : null;

  if (labelEl) {
    if (project) {
      const name = project.project_name || project.name || ('Project #' + project.id);
      const client = project.client || 'Client';
      labelEl.innerHTML = `
        <span style="font-weight:600;color:var(--ink)">${escHtml(name)}</span>
        <span class="searchable-select-badge" style="margin-left:4px">${escHtml(client)}</span>
      `;
    } else {
      labelEl.innerHTML = `<span style="color:var(--muted)">— Select a project —</span>`;
    }
  }

  if (clearBtn) {
    clearBtn.style.display = project ? 'grid' : 'none';
  }

  // Update selected class in dropdown
  const listEl = document.getElementById('taskProjectOptionsList');
  if (listEl) {
    listEl.querySelectorAll('.searchable-select-item').forEach(item => {
      const itemId = item.getAttribute('data-project-id');
      const isSelected = (itemId === '' && !val) || (itemId === val);
      item.classList.toggle('selected', isSelected);
      item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
  }
};

window.ensureProjectsLoadedForTask = async function() {
  const listEl = document.getElementById('taskProjectOptionsList');
  const hasProjects = window.JMOS_STATE && Array.isArray(JMOS_STATE.projects) && JMOS_STATE.projects.length > 0;

  if (!hasProjects) {
    if (listEl) {
      listEl.innerHTML = `
        <div class="searchable-select-loading">
          <svg viewBox="0 0 24 24" width="16" height="16" class="spin" style="display:inline-block;vertical-align:middle;margin-right:6px;animation:spin 1s linear infinite"><path d="M23 4v6h-6M1 20v-6h6"/></svg>
          Loading projects from database…
        </div>
      `;
    }
    try {
      const res = await JMOS_API.get('/projects');
      if (Array.isArray(res)) {
        JMOS_STATE.projects = res;
      }
    } catch (err) {
      console.warn('Could not load projects for task selector:', err);
    }
  }

  const searchInput = document.getElementById('taskProjectSearchInput');
  window.populateTaskProjectOptions(searchInput ? searchInput.value : '');

  if (window._preselectedProjectId) {
    window.selectTaskProject(window._preselectedProjectId);
    delete window._preselectedProjectId;
  }
};

window.initTaskProjectPicker = function() {
  const wrap = document.getElementById('taskProjectSelectWrap');
  const trigger = document.getElementById('taskProjectTrigger');
  const dropdown = document.getElementById('taskProjectDropdown');
  const searchInput = document.getElementById('taskProjectSearchInput');
  const searchClear = document.getElementById('taskProjectSearchClear');
  const clearBtn = document.getElementById('taskProjectClearBtn');
  const listEl = document.getElementById('taskProjectOptionsList');

  if (!wrap || wrap.dataset.initialized === 'true') return;
  wrap.dataset.initialized = 'true';

  function openDropdown() {
    dropdown.style.display = 'block';
    trigger.classList.add('active');
    trigger.setAttribute('aria-expanded', 'true');
    window.ensureProjectsLoadedForTask();
    setTimeout(() => searchInput?.focus(), 40);
  }

  function closeDropdown() {
    dropdown.style.display = 'none';
    trigger.classList.remove('active');
    trigger.setAttribute('aria-expanded', 'false');
  }

  function toggleDropdown() {
    if (dropdown.style.display === 'none' || !dropdown.style.display) {
      openDropdown();
    } else {
      closeDropdown();
    }
  }

  trigger.addEventListener('click', (e) => {
    if (e.target.closest('#taskProjectClearBtn')) return;
    toggleDropdown();
  });

  trigger.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
      e.preventDefault();
      openDropdown();
    }
  });

  if (clearBtn) {
    clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      window.selectTaskProject(null);
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const val = searchInput.value;
      if (searchClear) searchClear.style.display = val ? 'inline-block' : 'none';
      window.populateTaskProjectOptions(val);
    });

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        e.preventDefault();
        closeDropdown();
        trigger.focus();
      } else if (e.key === 'Enter') {
        e.preventDefault();
        const firstItem = listEl?.querySelector('.searchable-select-item');
        if (firstItem) {
          const id = firstItem.getAttribute('data-project-id');
          window.selectTaskProject(id);
          closeDropdown();
          trigger.focus();
        }
      }
    });
  }

  if (searchClear) {
    searchClear.addEventListener('click', () => {
      searchInput.value = '';
      searchClear.style.display = 'none';
      window.populateTaskProjectOptions('');
      searchInput.focus();
    });
  }

  if (listEl) {
    listEl.addEventListener('click', (e) => {
      const item = e.target.closest('.searchable-select-item');
      if (!item) return;
      const id = item.getAttribute('data-project-id');
      window.selectTaskProject(id);
      closeDropdown();
      trigger.focus();
    });
  }

  document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target) && dropdown.style.display !== 'none') {
      closeDropdown();
    }
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.initTaskProjectPicker === 'function') {
      window.initTaskProjectPicker();
    }
  });
} else {
  if (typeof window.initTaskProjectPicker === 'function') {
    window.initTaskProjectPicker();
  }
}

window.openTaskModal = function(projectId) {
  if (projectId) {
    window._preselectedProjectId = projectId;
  }
  openModal('taskModal');
  if (projectId && typeof window.selectTaskProject === 'function') {
    window.selectTaskProject(projectId);
  }
};

function initModals() {
  if (typeof window.initTaskProjectPicker === 'function') {
    window.initTaskProjectPicker();
  }

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
      const editId = document.getElementById('editInvoiceId');
      if (editId) editId.value = '';
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

    // 4.5 Submit: Add / Edit Invoice
    if (e.target.closest('#saveInvoiceBtn')) {
      const btn = e.target.closest('#saveInvoiceBtn');
      const editId = document.getElementById('editInvoiceId')?.value;
      const isEdit = Boolean(editId);

      let no = document.getElementById('niNo')?.value.trim();
      if (!no && !isEdit) {
        no = window.getNextInvoiceNo();
      }
      const client = resolveModalClient('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient');
      const amt = Number(document.getElementById('niAmount')?.value) || 0;
      if (!client || !amt) return showToast('Invoice details required', 'Enter client and amount', true);

      btn.disabled = true;
      btn.textContent = isEdit ? 'Updating…' : 'Issuing…';
      try {
        const payload = {
          invoice_no: no,
          client: client,
          type: document.getElementById('niType')?.value || 'Deposit 60%',
          amount: amt,
          method: document.getElementById('niMethod')?.value.trim() || null,
          etims: document.getElementById('niEtims')?.value === '1',
          status: document.getElementById('niStatus')?.value || 'Sent',
          due_date: document.getElementById('niDue')?.value.trim() || 'Sep 30'
        };

        if (isEdit) {
          await JMOS_API.put(`/invoices/${editId}`, payload);
          showToast('Invoice updated', `${no} for ${client} (${fmt(amt)})`);
        } else {
          await JMOS_API.post('/invoices', payload);
          showToast('Invoice issued', `${no} for ${client} (${fmt(amt)})`);
        }

        closeModal('invoiceModal');
        await JMOS_API.fetchAll();
        renderAllViews();
        if (typeof recomputeFinance === 'function') {
          await recomputeFinance();
        }
      } catch (err) {
        showToast(isEdit ? 'Failed to update invoice' : 'Failed to issue invoice', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = isEdit ? 'Save Changes' : 'Issue invoice';
      }
      return;
    }

    // 4.5b Modal Delete Invoice Button
    if (e.target.closest('#deleteInvoiceModalBtn')) {
      const delBtn = e.target.closest('#deleteInvoiceModalBtn');
      const invId = delBtn.getAttribute('data-del-invoice-id') || document.getElementById('editInvoiceId')?.value;
      const invNo = delBtn.getAttribute('data-invoice-no') || document.getElementById('niNo')?.value;
      if (invId && typeof window.deleteInvoice === 'function') {
        window.deleteInvoice(invId, invNo);
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

// 9. Invoice Edit & Delete Handlers
window.openEditInvoiceModal = function(invoiceId) {
  const list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  const inv = list.find(x => String(x.id) === String(invoiceId));
  if (!inv) {
    showToast('Invoice not found', 'Unable to locate invoice in database', true);
    return;
  }

  const editId = document.getElementById('editInvoiceId');
  if (editId) editId.value = inv.id;

  const titleEl = document.getElementById('invoiceModalTitle');
  if (titleEl) titleEl.textContent = `Edit Invoice — ${inv.invoice_no}`;
  const subEl = document.getElementById('invoiceModalSub');
  if (subEl) subEl.textContent = `Update invoice details, status, or billing info.`;
  const saveBtn = document.getElementById('saveInvoiceBtn');
  if (saveBtn) saveBtn.textContent = 'Save Changes';
  const delBtn = document.getElementById('deleteInvoiceModalBtn');
  if (delBtn) {
    delBtn.style.display = 'inline-flex';
    delBtn.setAttribute('data-del-invoice-id', inv.id);
    delBtn.setAttribute('data-invoice-no', inv.invoice_no);
  }

  const niNo = document.getElementById('niNo');
  if (niNo) niNo.value = inv.invoice_no || '';

  const niAmount = document.getElementById('niAmount');
  if (niAmount) niAmount.value = inv.amount != null ? inv.amount : '';

  const niType = document.getElementById('niType');
  if (niType) niType.value = inv.type || 'Deposit 60%';

  const niDue = document.getElementById('niDue');
  if (niDue) niDue.value = inv.due_date || '';

  const niEtims = document.getElementById('niEtims');
  if (niEtims) niEtims.value = inv.etims ? '1' : '0';

  const niStatus = document.getElementById('niStatus');
  if (niStatus) niStatus.value = inv.status || 'Sent';

  const niMethod = document.getElementById('niMethod');
  if (niMethod) niMethod.value = inv.method || '';

  setupModalClientPicker('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient', 'niToggleNewClientBtn');
  const sel = document.getElementById('niClientSelect');
  const hid = document.getElementById('niClient');
  if (sel && inv.client) {
    let exists = false;
    for (let i = 0; i < sel.options.length; i++) {
      if (sel.options[i].value.toLowerCase() === inv.client.toLowerCase()) {
        sel.selectedIndex = i;
        exists = true;
        break;
      }
    }
    if (!exists) {
      const opt = document.createElement('option');
      opt.value = inv.client;
      opt.textContent = inv.client;
      sel.insertBefore(opt, sel.options[1] || null);
      sel.value = inv.client;
    }
    if (hid) hid.value = inv.client;
  }

  openModal('invoiceModal');
};

window.deleteInvoice = async function(id, invoiceNo) {
  const list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  const inv = list.find(x => String(x.id) === String(id));
  const no = invoiceNo || (inv ? inv.invoice_no : 'this invoice');
  const details = inv ? ` (${inv.client} — ${fmt(inv.amount)})` : '';

  const confirmed = await window.showConfirmDialog({
    title: 'Delete Invoice?',
    message: `Are you sure you want to permanently delete invoice <b>${escHtml(no)}</b>${escHtml(details)} from the database? This will recalculate revenue and finance metrics.`,
    confirmText: 'Delete Invoice',
    isDanger: true
  });

  if (!confirmed) return;

  try {
    await JMOS_API.delete(`/invoices/${id}`);
    closeModal('invoiceModal');
    showToast('Invoice deleted', `Invoice ${no} removed from database`);
    await JMOS_API.fetchAll();
    renderAllViews();
    if (typeof recomputeFinance === 'function') {
      await recomputeFinance();
    }
  } catch (err) {
    showToast('Delete failed', err.message, true);
  }
};
