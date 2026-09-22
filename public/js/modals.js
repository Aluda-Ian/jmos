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
  if (id === 'scheduleModal') {
    if (typeof window.openScheduleShootModal === 'function') {
      window.openScheduleShootModal();
      return;
    }
    id = 'eventModal';
  }
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
    ['ncName', 'ncContact', 'ncEmail', 'ncPhone', 'ncOwner', 'ncService', 'ncValue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
  } else if (modalId === 'projectModal') {
    ['npName', 'npBudget'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const dInput = document.getElementById('npDeadline');
    if (dInput) {
      const defaultDate = new Date();
      defaultDate.setDate(defaultDate.getDate() + 14);
      dInput.value = defaultDate.toISOString().split('T')[0];
    }
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

    const preselect = window._preselectedProjectId;
    if (preselect) {
      if (typeof window.selectTaskProject === 'function') {
        window.selectTaskProject(preselect);
      }
      if (typeof window.ensureProjectsLoadedForTask === 'function') {
        window.ensureProjectsLoadedForTask(preselect);
      }
    } else {
      if (typeof window.selectTaskProject === 'function') {
        window.selectTaskProject(null);
      }
      if (typeof window.ensureProjectsLoadedForTask === 'function') {
        window.ensureProjectsLoadedForTask();
      }
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
      const niDue = document.getElementById('niDue');
      if (niDue) niDue.value = new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10);
      const st = document.getElementById('niStatus');
      if (st) st.value = 'Sent';
      const etims = document.getElementById('niEtims');
      if (etims) etims.value = '1';
      setupModalClientPicker('niClientSelect', 'niNewClientWrap', 'niNewClientInput', 'niClient', 'niToggleNewClientBtn');
    }
  } else if (modalId === 'expenseModal') {
    const editId = document.getElementById('editExpenseId')?.value;
    if (!editId) {
      const titleEl = document.getElementById('expenseModalTitle');
      if (titleEl) titleEl.textContent = 'Log an expense';
      const subEl = document.getElementById('expenseModalSub');
      if (subEl) subEl.textContent = 'Track project costs and operational expenses with ETR & eTIMS compliance.';
      const saveBtn = document.getElementById('saveExpenseBtn');
      if (saveBtn) saveBtn.textContent = 'Log expense';
      const delBtn = document.getElementById('deleteExpenseModalBtn');
      if (delBtn) delBtn.style.display = 'none';

      ['neName', 'neAmount', 'neEtimsNumber', 'neNotes', 'neReceiptUrl', 'neReceiptName'].forEach(fid => {
        const el = document.getElementById(fid);
        if (el) el.value = '';
      });
      const neCat = document.getElementById('neCat');
      if (neCat) neCat.value = 'Equipment';
      const neEtr = document.getElementById('neEtr');
      if (neEtr) neEtr.value = 'no';
      const neDate = document.getElementById('neDate');
      if (neDate) neDate.value = new Date().toISOString().slice(0, 10);
      const preview = document.getElementById('neReceiptPreview');
      if (preview) preview.style.display = 'none';
      const fileInput = document.getElementById('neReceiptFile');
      if (fileInput) fileInput.value = '';

      if (typeof window.selectExpenseProject === 'function') {
        window.selectExpenseProject('overhead');
      }
    }
    if (typeof window.initExpenseProjectPicker === 'function') {
      window.initExpenseProjectPicker();
    }
    if (typeof window.ensureProjectsLoadedForExpense === 'function') {
      window.ensureProjectsLoadedForExpense();
    }
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
  } else if (modalId === 'quoteModal') {
    if (typeof window.populateQuoteClientLeadDropdown === 'function') {
      const currentVal = document.getElementById('quoteSelectClientOrLead')?.value || '';
      window.populateQuoteClientLeadDropdown(currentVal);
    }
  }

  // Calculate dynamic z-index for stacking overlays (ensures action & danger modals always appear on top)
  const openModals = Array.from(document.querySelectorAll('.modal.on, .cascade.on')).filter(el => el !== m);
  let topZ = 99999;
  openModals.forEach(om => {
    const z = parseInt(window.getComputedStyle(om).zIndex, 10);
    if (!isNaN(z) && z > topZ) topZ = z;
  });

  const isActionOrDanger = m.id === 'confirmModal' || 
                           m.classList.contains('confirm-modal') || 
                           m.classList.contains('danger-modal') || 
                           m.classList.contains('action-modal') ||
                           m.classList.contains('secondary-modal') ||
                           ['convertLeadModal', 'upgradeQuoteModal', 'logLeadCallModal', 'scheduleLeadMeetingModal'].includes(m.id);

  if (openModals.length > 0) {
    m.style.zIndex = isActionOrDanger ? String(Math.max(topZ, 1000000) + 10) : String(topZ + 5);
  } else if (isActionOrDanger) {
    m.style.zIndex = '1000005';
  } else {
    m.style.zIndex = '';
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
    m.style.zIndex = '';
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

function getSafeProjectsList() {
  if (!window.JMOS_STATE) return [];
  const p = window.JMOS_STATE.projects;
  if (Array.isArray(p)) return p;
  if (p && Array.isArray(p.data)) return p.data;
  if (p && Array.isArray(p.projects)) return p.projects;
  return [];
}

window.populateTaskProjectOptions = function(filterText = '') {
  const listEl = document.getElementById('taskProjectOptionsList');
  if (!listEl) return;

  const projects = getSafeProjectsList();
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

  const projects = getSafeProjectsList();
  const project = val ? projects.find(p => String(p.id) === val) : null;

  if (labelEl) {
    if (project) {
      const name = project.project_name || project.name || ('Project #' + project.id);
      const client = project.client || 'Client';
      labelEl.innerHTML = `
        <span style="font-weight:600;color:var(--ink)">${escHtml(name)}</span>
        <span class="searchable-select-badge" style="margin-left:4px">${escHtml(client)}</span>
      `;
    } else if (val) {
      labelEl.innerHTML = `<span style="font-weight:600;color:var(--ink)">Project #${escHtml(val)}</span>`;
    } else {
      labelEl.innerHTML = `<span style="color:var(--muted)">— Select a project —</span>`;
    }
  }

  if (clearBtn) {
    clearBtn.style.display = (project || val) ? 'grid' : 'none';
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

window.ensureProjectsLoadedForTask = async function(preselectId = null) {
  const listEl = document.getElementById('taskProjectOptionsList');
  if (preselectId) {
    window._preselectedProjectId = preselectId;
  }

  function extractProjects(data) {
    if (Array.isArray(data)) return data;
    if (data && Array.isArray(data.data)) return data.data;
    if (data && Array.isArray(data.projects)) return data.projects;
    return null;
  }

  let projects = extractProjects(window.JMOS_STATE?.projects);
  const hasProjects = projects && projects.length > 0;

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
      const pList = extractProjects(res);
      if (pList) {
        JMOS_STATE.projects = pList;
        projects = pList;
      }
    } catch (err) {
      console.warn('Could not load projects for task selector:', err);
    }
  }

  const searchInput = document.getElementById('taskProjectSearchInput');
  window.populateTaskProjectOptions(searchInput ? searchInput.value : '');

  const targetId = window._preselectedProjectId || document.getElementById('ntProject')?.value;
  if (targetId) {
    window.selectTaskProject(targetId);
  }
};

/* ==========================================================================
   Expense Modal Searchable Project Picker Controller
   ========================================================================== */

function getActiveProjectsForExpense() {
  const projects = getSafeProjectsList();
  if (!projects.length) return [];

  // Filter for active projects: exclude completed or archived projects
  const active = projects.filter(p => {
    const status = String(p.status || '').toLowerCase();
    const stage = String(p.stage || '').toLowerCase();
    return status !== 'completed' && status !== 'archived' && status !== 'cancelled' && stage !== 'archived' && stage !== 'cancelled';
  });

  return active.length ? active : projects;
}

window.populateExpenseProjectOptions = function(filterText = '') {
  const listEl = document.getElementById('expenseProjectOptionsList');
  if (!listEl) return;

  const projects = getActiveProjectsForExpense();
  const currentVal = document.getElementById('neProject')?.value || 'overhead';
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

  // Default Overhead / General Operations option
  const isOverheadSelected = !currentVal || currentVal.toLowerCase() === 'overhead' || currentVal === '—';
  html += `
    <div class="searchable-select-item ${isOverheadSelected ? 'selected' : ''}" data-project-value="overhead" role="option" aria-selected="${isOverheadSelected}">
      <div class="searchable-select-item-title">
        <span style="font-weight:600;color:var(--ink)">🏢 General Overhead / Operations</span>
      </div>
      <div class="searchable-select-item-meta">
        <span class="searchable-select-badge" style="background:var(--paper);border:1px solid var(--line);color:var(--muted)">Internal / Unallocated</span>
      </div>
    </div>
  `;

  if (!projects.length) {
    html += `
      <div class="searchable-select-empty">
        No active projects found in database.<br>
        <span style="font-size:11px;opacity:0.8">Expenses will be allocated to general overhead.</span>
      </div>
    `;
  } else if (!filtered.length) {
    html += `
      <div class="searchable-select-empty">
        No active projects match "<b>${escHtml(filterText)}</b>"
      </div>
    `;
  } else {
    html += filtered.map(p => {
      const projName = p.project_name || p.name || ('Project #' + p.id);
      const isSelected = String(currentVal).toLowerCase() === String(projName).toLowerCase() || String(currentVal) === String(p.id);
      const client = p.client || 'Client';
      const stage = p.stage ? p.stage.replace(/_/g, ' ') : (p.status || 'Active');
      return `
        <div class="searchable-select-item ${isSelected ? 'selected' : ''}" data-project-value="${escHtml(projName)}" data-project-id="${p.id}" role="option" aria-selected="${isSelected}">
          <div class="searchable-select-item-title">
            <span style="font-weight:600">${escHtml(projName)}</span>
          </div>
          <div class="searchable-select-item-meta">
            <span class="searchable-select-badge">${escHtml(client)}</span>
            <span style="font-size:10.5px;text-transform:capitalize;color:var(--green);font-weight:500">● ${escHtml(stage)}</span>
          </div>
        </div>
      `;
    }).join('');
  }

  listEl.innerHTML = html;
};

window.selectExpenseProject = function(projectValue, explicitLabel = '') {
  const hiddenInput = document.getElementById('neProject');
  const labelEl = document.getElementById('expenseProjectSelectedLabel');
  const clearBtn = document.getElementById('expenseProjectClearBtn');

  const val = (projectValue != null && projectValue !== '') ? String(projectValue).trim() : 'overhead';
  if (hiddenInput) hiddenInput.value = val;

  const isOverhead = !val || val.toLowerCase() === 'overhead' || val === '—';

  if (labelEl) {
    if (isOverhead) {
      labelEl.innerHTML = '<span style="color:var(--muted)">🏢 General Overhead / Operations</span>';
      if (clearBtn) clearBtn.style.display = 'none';
    } else {
      const projects = getSafeProjectsList();
      const project = projects.find(p => {
        const name = (p.project_name || p.name || '').toLowerCase();
        return name === val.toLowerCase() || String(p.id) === val;
      });

      const name = explicitLabel || (project ? (project.project_name || project.name) : val);
      const client = project ? (project.client || '') : '';

      labelEl.innerHTML = `
        <span style="font-weight:600;color:var(--ink)">${escHtml(name)}</span>
        ${client ? `<span class="searchable-select-badge" style="margin-left:6px;font-size:11px">${escHtml(client)}</span>` : ''}
      `;
      if (clearBtn) clearBtn.style.display = 'inline-flex';
    }
  }

  // Update item selection highlight
  const listEl = document.getElementById('expenseProjectOptionsList');
  if (listEl) {
    listEl.querySelectorAll('.searchable-select-item').forEach(item => {
      const itemVal = item.getAttribute('data-project-value');
      const itemSelected = (isOverhead && itemVal === 'overhead') || (!isOverhead && itemVal && itemVal.toLowerCase() === val.toLowerCase());
      item.classList.toggle('selected', Boolean(itemSelected));
      item.setAttribute('aria-selected', itemSelected ? 'true' : 'false');
    });
  }
};

window.ensureProjectsLoadedForExpense = async function(preselectVal = null) {
  const listEl = document.getElementById('expenseProjectOptionsList');

  function extractProjects(data) {
    if (Array.isArray(data)) return data;
    if (data && Array.isArray(data.data)) return data.data;
    if (data && Array.isArray(data.projects)) return data.projects;
    return null;
  }

  let projects = extractProjects(window.JMOS_STATE?.projects);
  const hasProjects = projects && projects.length > 0;

  if (!hasProjects) {
    if (listEl) {
      listEl.innerHTML = `
        <div class="searchable-select-loading">
          <svg viewBox="0 0 24 24" width="16" height="16" class="spin" style="display:inline-block;vertical-align:middle;margin-right:6px;animation:spin 1s linear infinite"><path d="M23 4v6h-6M1 20v-6h6"/></svg>
          Loading active projects from database…
        </div>
      `;
    }
    try {
      const res = await JMOS_API.get('/projects');
      const pList = extractProjects(res);
      if (pList) {
        JMOS_STATE.projects = pList;
      }
    } catch (err) {
      console.warn('Could not load projects for expense selector:', err);
    }
  }

  const searchInput = document.getElementById('expenseProjectSearchInput');
  window.populateExpenseProjectOptions(searchInput ? searchInput.value : '');

  if (preselectVal !== null) {
    window.selectExpenseProject(preselectVal);
  }
};

window.initExpenseProjectPicker = function() {
  const wrap = document.getElementById('expenseProjectSelectWrap');
  const trigger = document.getElementById('expenseProjectTrigger');
  const dropdown = document.getElementById('expenseProjectDropdown');
  const searchInput = document.getElementById('expenseProjectSearchInput');
  const searchClear = document.getElementById('expenseProjectSearchClear');
  const clearBtn = document.getElementById('expenseProjectClearBtn');
  const listEl = document.getElementById('expenseProjectOptionsList');

  if (!wrap || wrap.dataset.initialized === 'true') return;
  wrap.dataset.initialized = 'true';

  function openDropdown() {
    dropdown.style.display = 'block';
    trigger.classList.add('active');
    trigger.setAttribute('aria-expanded', 'true');
    window.ensureProjectsLoadedForExpense();
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
    if (e.target.closest('#expenseProjectClearBtn')) return;
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
      window.selectExpenseProject('overhead');
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const val = searchInput.value;
      if (searchClear) searchClear.style.display = val ? 'inline-block' : 'none';
      window.populateExpenseProjectOptions(val);
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
          const val = firstItem.getAttribute('data-project-value');
          window.selectExpenseProject(val);
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
      window.populateExpenseProjectOptions('');
      searchInput.focus();
    });
  }

  if (listEl) {
    listEl.addEventListener('click', (e) => {
      const item = e.target.closest('.searchable-select-item');
      if (!item) return;
      const val = item.getAttribute('data-project-value');
      window.selectExpenseProject(val);
      closeDropdown();
      trigger.focus();
    });
  }

  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target)) {
      closeDropdown();
    }
  });
};

/* ==========================================================================
   Expense Editing & Document Attachment Controller
   ========================================================================== */

window.openEditExpenseModal = function(expenseId) {
  const expenses = (window.JMOS_STATE && Array.isArray(JMOS_STATE.expenses)) ? JMOS_STATE.expenses : [];
  const exp = expenses.find(e => String(e.id) === String(expenseId));
  if (!exp) return showToast('Error', 'Expense record not found', true);

  const editId = document.getElementById('editExpenseId');
  if (editId) editId.value = exp.id;

  const titleEl = document.getElementById('expenseModalTitle');
  if (titleEl) titleEl.textContent = 'Edit expense';
  const subEl = document.getElementById('expenseModalSub');
  if (subEl) subEl.textContent = 'Update expense details, project allocation, or support document.';
  const saveBtn = document.getElementById('saveExpenseBtn');
  if (saveBtn) saveBtn.textContent = 'Update expense';
  const delBtn = document.getElementById('deleteExpenseModalBtn');
  if (delBtn) delBtn.style.display = 'inline-block';

  if (document.getElementById('neName')) document.getElementById('neName').value = exp.name || '';
  if (document.getElementById('neCat')) document.getElementById('neCat').value = exp.category || exp.cat || 'Equipment';

  // Setup project picker for edit
  if (typeof window.initExpenseProjectPicker === 'function') {
    window.initExpenseProjectPicker();
  }
  if (typeof window.selectExpenseProject === 'function') {
    window.selectExpenseProject(exp.project || 'overhead');
  }
  if (typeof window.ensureProjectsLoadedForExpense === 'function') {
    window.ensureProjectsLoadedForExpense(exp.project || 'overhead');
  }

  if (document.getElementById('neAmount')) document.getElementById('neAmount').value = exp.amount || '';
  if (document.getElementById('neEtr')) document.getElementById('neEtr').value = exp.etr || 'no';
  if (document.getElementById('neEtimsNumber')) document.getElementById('neEtimsNumber').value = exp.etims_number || '';
  if (document.getElementById('neDate')) document.getElementById('neDate').value = exp.date || '';
  if (document.getElementById('neNotes')) document.getElementById('neNotes').value = exp.notes || '';

  // Setup receipt document preview
  const preview = document.getElementById('neReceiptPreview');
  const nameEl = document.getElementById('neReceiptDisplayName');
  const viewBtn = document.getElementById('neReceiptViewBtn');
  const urlHidden = document.getElementById('neReceiptUrl');
  const nameHidden = document.getElementById('neReceiptName');

  if (exp.receipt_url) {
    if (urlHidden) urlHidden.value = exp.receipt_url;
    if (nameHidden) nameHidden.value = exp.receipt_name || 'Attached document';
    if (nameEl) nameEl.textContent = exp.receipt_name || 'Attached document';
    if (viewBtn) {
      viewBtn.href = exp.receipt_url;
      viewBtn.style.display = 'inline-block';
    }
    if (preview) preview.style.display = 'flex';
  } else {
    if (urlHidden) urlHidden.value = '';
    if (nameHidden) nameHidden.value = '';
    if (preview) preview.style.display = 'none';
  }

  openModal('expenseModal');
};

async function uploadExpenseReceipt(file) {
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);

  const preview = document.getElementById('neReceiptPreview');
  const nameEl = document.getElementById('neReceiptDisplayName');
  const viewBtn = document.getElementById('neReceiptViewBtn');
  const urlHidden = document.getElementById('neReceiptUrl');
  const nameHidden = document.getElementById('neReceiptName');
  const dropText = document.getElementById('expenseUploadText');

  if (dropText) dropText.textContent = 'Uploading document…';

  try {
    const res = await JMOS_API.upload('/expenses/upload-receipt', formData);
    if (res && res.status === 'success') {
      if (urlHidden) urlHidden.value = res.receipt_url;
      if (nameHidden) nameHidden.value = res.receipt_name;
      if (nameEl) nameEl.textContent = res.receipt_name;
      if (viewBtn) {
        viewBtn.href = res.receipt_url;
        viewBtn.style.display = 'inline-block';
      }
      if (preview) preview.style.display = 'flex';
      showToast('Document attached', res.receipt_name);
      // Auto-set ETR received if receipt is attached
      const neEtr = document.getElementById('neEtr');
      if (neEtr && neEtr.value === 'no') {
        neEtr.value = 'yes';
      }
    }
  } catch (err) {
    showToast('Upload failed', err.message, true);
  } finally {
    if (dropText) dropText.textContent = 'Click or drag & drop receipt / eTIMS file';
    const fileInput = document.getElementById('neReceiptFile');
    if (fileInput) fileInput.value = '';
  }
}

function initExpenseUploader() {
  const fileInput = document.getElementById('neReceiptFile');
  const dropzone = document.getElementById('expenseDropzone');
  const removeBtn = document.getElementById('neReceiptRemoveBtn');

  if (fileInput && !fileInput.dataset.initialized) {
    fileInput.dataset.initialized = 'true';
    fileInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (file) uploadExpenseReceipt(file);
    });
  }

  if (dropzone && !dropzone.dataset.initialized) {
    dropzone.dataset.initialized = 'true';
    ['dragenter', 'dragover'].forEach(name => {
      dropzone.addEventListener(name, (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
      });
    });
    ['dragleave', 'drop'].forEach(name => {
      dropzone.addEventListener(name, (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
      });
    });
    dropzone.addEventListener('drop', (e) => {
      const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
      if (file) uploadExpenseReceipt(file);
    });
  }

  if (removeBtn && !removeBtn.dataset.initialized) {
    removeBtn.dataset.initialized = 'true';
    removeBtn.addEventListener('click', () => {
      const urlHidden = document.getElementById('neReceiptUrl');
      const nameHidden = document.getElementById('neReceiptName');
      const preview = document.getElementById('neReceiptPreview');
      if (urlHidden) urlHidden.value = '';
      if (nameHidden) nameHidden.value = '';
      if (preview) preview.style.display = 'none';
      showToast('Document removed', 'Attachment detached from expense');
    });
  }
}

window.deleteExpense = async function(expenseId, expenseName = 'this expense') {
  const confirmed = typeof window.showConfirmDialog === 'function'
    ? await window.showConfirmDialog({
        title: 'Delete Expense?',
        message: `Are you sure you want to permanently remove <b>${escHtml(expenseName)}</b> from expenses and recalculate finances?`,
        confirmText: 'Delete Expense',
        isDanger: true
      })
    : confirm(`Delete expense "${expenseName}"?`);

  if (!confirmed) return;

  try {
    await JMOS_API.delete(`/expenses/${expenseId}`);
    closeModal('expenseModal');
    showToast('Expense removed', `${expenseName} deleted`);
    await JMOS_API.fetchAll();
    renderAllViews();
  } catch (err) {
    showToast('Failed to delete expense', err.message, true);
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
    if (typeof window.initTaskProjectPicker === 'function') window.initTaskProjectPicker();
    if (typeof window.initExpenseProjectPicker === 'function') window.initExpenseProjectPicker();
    if (typeof window.initExpenseUploader === 'function') window.initExpenseUploader();
  });
} else {
  if (typeof window.initTaskProjectPicker === 'function') window.initTaskProjectPicker();
  if (typeof window.initExpenseProjectPicker === 'function') window.initExpenseProjectPicker();
  if (typeof window.initExpenseUploader === 'function') window.initExpenseUploader();
}

window.openTaskModal = function(projectId) {
  const pId = projectId ? String(projectId) : null;
  window._preselectedProjectId = pId;
  openModal('taskModal');
  if (pId) {
    if (typeof window.selectTaskProject === 'function') {
      window.selectTaskProject(pId);
    }
    if (typeof window.ensureProjectsLoadedForTask === 'function') {
      window.ensureProjectsLoadedForTask(pId);
    }
  }
};

function initModals() {
  if (typeof window.initTaskProjectPicker === 'function') {
    window.initTaskProjectPicker();
  }
  if (typeof window.initExpenseProjectPicker === 'function') {
    window.initExpenseProjectPicker();
  }
  if (typeof window.initExpenseUploader === 'function') {
    window.initExpenseUploader();
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
      const editId = document.getElementById('editExpenseId');
      if (editId) editId.value = '';
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
          email: document.getElementById('ncEmail')?.value.trim() || null,
          phone: document.getElementById('ncPhone')?.value.trim() || null,
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

      const rawDeadline = document.getElementById('npDeadline')?.value.trim();
      let deadlineStr = rawDeadline || 'To be scheduled';
      if (rawDeadline) {
        try {
          const dParts = rawDeadline.split('-');
          if (dParts.length === 3) {
            const dObj = new Date(parseInt(dParts[0], 10), parseInt(dParts[1], 10) - 1, parseInt(dParts[2], 10));
            if (!isNaN(dObj.getTime())) {
              deadlineStr = dObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }
          }
        } catch (_) {}
      }

      const rawCategory = document.getElementById('npCategory')?.value || 'video_production';
      const category = rawCategory === 'custom'
        ? (document.getElementById('npCustomCategoryInput')?.value.trim() || 'Custom')
        : rawCategory;

      const rawType = document.getElementById('npType')?.value.trim() || 'Brand film';
      const project_type = rawType === 'custom'
        ? (document.getElementById('npCustomTypeInput')?.value.trim() || 'Custom Project')
        : rawType;

      const rawStage = document.getElementById('npStage')?.value.trim() || 'Brief';
      const stage = rawStage === 'custom'
        ? (document.getElementById('npCustomStageInput')?.value.trim() || 'Planning')
        : rawStage;

      const rawStatus = document.getElementById('npStatus')?.value.trim() || 'On track';
      const status = rawStatus === 'custom'
        ? (document.getElementById('npCustomStatusInput')?.value.trim() || 'In Progress')
        : rawStatus;

      btn.disabled = true;
      btn.textContent = 'Creating…';
      try {
        await JMOS_API.post('/projects', {
          project_name: name,
          client: client,
          category: category,
          project_type: project_type,
          project_manager: document.getElementById('npManager')?.value.trim() || 'Barny Kiome',
          stage: stage,
          status: status,
          priority: 'High',
          deadline: deadlineStr,
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
      const stickyColorVal = document.getElementById('ntStickyColor')?.value || '#FFFBEB';
      const descriptionVal = document.getElementById('ntDescription')?.value.trim() || null;
      const priorityVal = document.getElementById('ntPriority')?.value || 'medium';
      const dueDateVal = document.getElementById('ntDueDate')?.value || null;

      try {
        await JMOS_API.post('/tasks', {
          project_id: projectIdVal ? Number(projectIdVal) : null,
          title,
          description: descriptionVal,
          stage: document.getElementById('ntStage')?.value || 'todo',
          priority: priorityVal,
          due_date: dueDateVal,
          sticky_color: stickyColorVal,
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

        let savedInv = null;
        if (isEdit) {
          const res = await JMOS_API.put(`/invoices/${editId}`, payload);
          savedInv = res.data || res;
          showToast('Invoice updated', `${no} for ${client} (${fmt(amt)})`);
        } else {
          const res = await JMOS_API.post('/invoices', payload);
          savedInv = res.data || res;
          showToast('Invoice issued', `${no} for ${client} (${fmt(amt)})`);
        }

        closeModal('invoiceModal');
        await JMOS_API.fetchAll();
        renderAllViews();
        if (typeof recomputeFinance === 'function') {
          await recomputeFinance();
        }

        if (savedInv && savedInv.id && typeof window.openInvoiceDetailModal === 'function') {
          window.openInvoiceDetailModal(savedInv.id);
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

    // 4.6 Submit: Log / Edit Expense
    if (e.target.closest('#saveExpenseBtn')) {
      const btn = e.target.closest('#saveExpenseBtn');
      const editId = document.getElementById('editExpenseId')?.value;
      const isEdit = Boolean(editId);

      const name = document.getElementById('neName')?.value.trim();
      const amt = Number(document.getElementById('neAmount')?.value) || 0;
      if (!name || !amt) return showToast('Expense details required', 'Enter name and amount', true);

      btn.disabled = true;
      btn.textContent = isEdit ? 'Updating…' : 'Logging…';

      const payload = {
        name,
        category: document.getElementById('neCat')?.value || 'Equipment',
        project: document.getElementById('neProject')?.value.trim() || 'overhead',
        amount: amt,
        etr: document.getElementById('neEtr')?.value || 'no',
        etims_number: document.getElementById('neEtimsNumber')?.value.trim() || null,
        receipt_url: document.getElementById('neReceiptUrl')?.value || null,
        receipt_name: document.getElementById('neReceiptName')?.value || null,
        notes: document.getElementById('neNotes')?.value.trim() || null,
        date: document.getElementById('neDate')?.value.trim() || 'Today'
      };

      try {
        if (isEdit) {
          await JMOS_API.put(`/expenses/${editId}`, payload);
          showToast('Expense updated', `${name} — ${fmt(amt)}`);
        } else {
          await JMOS_API.post('/expenses', payload);
          showToast('Expense recorded', `${name} — ${fmt(amt)}`);
        }
        closeModal('expenseModal');
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast(isEdit ? 'Failed to update expense' : 'Failed to log expense', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = isEdit ? 'Update expense' : 'Log expense';
      }
      return;
    }

    // 4.6b Delete Expense Modal Button & Table Row Actions
    if (e.target.closest('#deleteExpenseModalBtn')) {
      const editId = document.getElementById('editExpenseId')?.value;
      const name = document.getElementById('neName')?.value || 'this expense';
      if (editId && typeof window.deleteExpense === 'function') {
        window.deleteExpense(editId, name);
      }
      return;
    }

    if (e.target.closest('[data-del-expense]')) {
      const delBtn = e.target.closest('[data-del-expense]');
      const expId = delBtn.getAttribute('data-del-expense');
      const expName = delBtn.getAttribute('data-expense-name') || 'this expense';
      if (expId && typeof window.deleteExpense === 'function') {
        window.deleteExpense(expId, expName);
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
window.showConfirmDialog = function({
  title = 'Confirm Action',
  subtitle = '',
  message = 'Are you sure you want to proceed?',
  bullets = [],
  confirmText = 'Confirm & Proceed',
  cancelText = 'Cancel',
  type = 'danger',
  isDanger = null
} = {}) {
  return new Promise((resolve) => {
    const modal = document.getElementById('confirmModal');
    const titleEl = document.getElementById('confirmTitle');
    const subEl = document.getElementById('confirmSubtitle');
    const msgEl = document.getElementById('confirmMsg');
    const bulletsEl = document.getElementById('confirmBullets');
    const iconWrap = document.getElementById('confirmIconWrap');
    const iconSvg = document.getElementById('confirmIconSvg');
    const yesBtn = document.getElementById('confirmYes');
    const yesText = document.getElementById('confirmYesText') || yesBtn;
    const cancelBtn = document.getElementById('confirmCancelBtn');

    if (!modal || !yesBtn) {
      const ok = window.confirm(`${title}\n\n${message}`);
      return resolve(ok);
    }

    // Determine type & style
    let finalType = type;
    if (isDanger === true) finalType = 'danger';
    else if (isDanger === false && type === 'danger') finalType = 'info';

    // Update Title & Subtitle
    if (titleEl) titleEl.textContent = title;
    if (subEl) {
      if (subtitle) {
        subEl.textContent = subtitle;
        subEl.style.display = 'block';
      } else {
        subEl.textContent = finalType === 'danger' ? 'Permanent database action' : 'Please verify before proceeding';
        subEl.style.display = 'block';
      }
    }

    // Update Message
    if (msgEl) msgEl.innerHTML = message;

    // Update Bullets
    if (bulletsEl) {
      if (Array.isArray(bullets) && bullets.length > 0) {
        bulletsEl.innerHTML = bullets.map(b => `
          <div class="confirm-bullet-item">
            <span class="confirm-bullet-dot" style="${finalType === 'upgrade' ? 'background:var(--red)' : ''}"></span>
            <span>${escHtml(b)}</span>
          </div>
        `).join('');
        bulletsEl.style.display = 'flex';
      } else {
        bulletsEl.innerHTML = '';
        bulletsEl.style.display = 'none';
      }
    }

    // Update Button Labels & Styling
    if (yesText) yesText.textContent = confirmText;
    if (cancelBtn) cancelBtn.textContent = cancelText;

    if (iconWrap) {
      iconWrap.className = `confirm-icon-wrap ${finalType}`;
    }

    if (iconSvg) {
      if (finalType === 'upgrade') {
        iconSvg.innerHTML = `<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4.5c1.47-1.49 4.5-2 4.5-2"></path><path d="M15 9v5s3.03-.55 4.5-2c1.49-1.47 2-4.5 2-4.5"></path>`;
      } else if (finalType === 'warning') {
        iconSvg.innerHTML = `<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>`;
      } else if (finalType === 'success') {
        iconSvg.innerHTML = `<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>`;
      } else if (finalType === 'info' || finalType === 'primary') {
        iconSvg.innerHTML = `<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>`;
      } else {
        // default danger / delete
        iconSvg.innerHTML = `<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>`;
      }
    }

    if (finalType === 'danger' || finalType === 'upgrade') {
      yesBtn.style.background = 'var(--red, #C52523)';
      yesBtn.style.borderColor = 'var(--red, #C52523)';
      yesBtn.style.color = '#ffffff';
    } else if (finalType === 'success') {
      yesBtn.style.background = 'var(--green, #1C7A4E)';
      yesBtn.style.borderColor = 'var(--green, #1C7A4E)';
      yesBtn.style.color = '#ffffff';
    } else {
      yesBtn.style.background = 'var(--brand, #2B6E8A)';
      yesBtn.style.borderColor = 'var(--brand, #2B6E8A)';
      yesBtn.style.color = '#ffffff';
    }

    let resolved = false;

    const cleanup = () => {
      yesBtn.removeEventListener('click', onYes);
      modal.querySelectorAll('[data-close="confirmModal"]').forEach(b => b.removeEventListener('click', onCancel));
      document.removeEventListener('keydown', onKeyDown);
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

    const onKeyDown = (e) => {
      if (e.key === 'Escape') {
        onCancel();
      }
    };

    yesBtn.addEventListener('click', onYes);
    modal.querySelectorAll('[data-close="confirmModal"]').forEach(b => b.addEventListener('click', onCancel));
    document.addEventListener('keydown', onKeyDown);

    modal.style.display = 'flex';
    requestAnimationFrame(() => modal.classList.add('on'));
    document.body.style.overflow = 'hidden';
  });
};

window.jmosConfirm = window.showConfirmDialog;

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

// 9. Invoice Detail Preview, PDF Generation & Edit/Delete Handlers
window.activeInvoiceData = null;

window.openInvoiceDetailModal = async function(invoiceId) {
  let list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  let inv = list.find(x => String(x.id) === String(invoiceId) || (x.invoice_no && String(x.invoice_no).toLowerCase() === String(invoiceId).toLowerCase()));

  if (!inv) {
    try {
      const res = await JMOS_API.get(`/invoices/${invoiceId}`);
      inv = res.data || res;
    } catch (_) {}
  }

  if (!inv) {
    showToast('Invoice not found', 'Unable to locate invoice in database', true);
    return;
  }

  window.activeInvoiceData = inv;

  const idmInvId = document.getElementById('idmInvoiceId');
  if (idmInvId) idmInvId.value = inv.id;

  const isPaid = (inv.status || '').toLowerCase() === 'paid';
  const isOverdue = (inv.status || '').toLowerCase() === 'overdue';
  const amountFormatted = Number(inv.amount || 0).toLocaleString();

  // Find Client Contact info
  const clients = (window.JMOS_STATE && JMOS_STATE.clients) ? JMOS_STATE.clients : [];
  const clientObj = clients.find(c => (c.client_name || c.name || '').toLowerCase() === (inv.client || '').toLowerCase());
  const clientContact = clientObj
    ? `${clientObj.email || 'Email on file'} · ${clientObj.phone || 'Phone on file'}`
    : 'Client Account · Commercial Deliverables';

  const issueDateStr = inv.created_at
    ? new Date(inv.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    : new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

  // Update Modal Header & Badges
  const titleEl = document.getElementById('idmTitle');
  if (titleEl) titleEl.textContent = `Invoice ${inv.invoice_no}`;

  const subEl = document.getElementById('idmSubtitle');
  if (subEl) subEl.textContent = `Billed to ${inv.client} · ${inv.type || 'Commercial Invoice'}`;

  const totalBadge = document.getElementById('idmTotalBadge');
  if (totalBadge) totalBadge.textContent = `KES ${amountFormatted}`;

  const statusBadge = document.getElementById('idmStatusBadge');
  if (statusBadge) {
    statusBadge.textContent = (inv.status || 'Sent').toUpperCase();
    statusBadge.className = 'badge ' + (isPaid ? 'tint-green' : isOverdue ? 'tint-red' : 'tint-amber');
    statusBadge.style.background = isPaid ? 'var(--green-soft)' : isOverdue ? 'var(--red-soft)' : 'var(--amber-soft)';
    statusBadge.style.color = isPaid ? 'var(--green)' : isOverdue ? 'var(--red)' : 'var(--amber)';
  }

  const etimsBadge = document.getElementById('idmEtimsBadge');
  if (etimsBadge) {
    etimsBadge.style.display = inv.etims ? 'inline-flex' : 'none';
  }

  const payBtn = document.getElementById('idmPayBtn');
  if (payBtn) {
    payBtn.style.display = isPaid ? 'none' : 'inline-flex';
  }

  // Populate Printable Document
  const docNo = document.getElementById('idmDocInvoiceNo');
  if (docNo) docNo.textContent = inv.invoice_no;

  const docIssue = document.getElementById('idmDocIssueDate');
  if (docIssue) docIssue.textContent = issueDateStr;

  const docDue = document.getElementById('idmDocDueDate');
  if (docDue) docDue.textContent = inv.due_date || 'Upon Receipt';

  const docClient = document.getElementById('idmDocClientName');
  if (docClient) docClient.textContent = inv.client;

  const docContact = document.getElementById('idmDocClientContact');
  if (docContact) docContact.textContent = clientContact;

  const docStatus = document.getElementById('idmDocPaymentStatus');
  if (docStatus) {
    docStatus.textContent = isPaid ? '✓ Paid & Settled' : isOverdue ? 'Overdue (Pending)' : 'Sent (Unpaid)';
    docStatus.style.color = isPaid ? '#16a34a' : isOverdue ? '#dc2626' : '#d97706';
  }

  const docMethod = document.getElementById('idmDocPaymentMethod');
  if (docMethod) docMethod.textContent = `Payment Mode: ${inv.method || 'Bank Transfer / M-Pesa'}`;

  const docMpesaAcc = document.getElementById('idmDocMpesaAcc');
  if (docMpesaAcc) docMpesaAcc.textContent = inv.invoice_no;

  const itemDesc = document.getElementById('idmItemDesc');
  if (itemDesc) itemDesc.textContent = `${inv.type || 'Commercial Services'} — ${inv.client}`;

  const itemMilestone = document.getElementById('idmItemMilestone');
  if (itemMilestone) itemMilestone.textContent = inv.type || 'Deliverable';

  const itemAmt = document.getElementById('idmItemAmount');
  if (itemAmt) itemAmt.textContent = `KES ${amountFormatted}`;

  const docSub = document.getElementById('idmDocSubtotal');
  if (docSub) docSub.textContent = `KES ${amountFormatted}`;

  const docTax = document.getElementById('idmDocTax');
  if (docTax) docTax.textContent = inv.etims ? 'KES 0.00 (eTIMS Direct Filing)' : 'KES 0.00 (Exempt/Direct)';

  const docGrand = document.getElementById('idmDocGrandTotal');
  if (docGrand) docGrand.textContent = `KES ${amountFormatted}`;

  const footerTime = document.getElementById('idmFooterTimestamp');
  if (footerTime) footerTime.textContent = `Generated on ${issueDateStr} · JMOS Financial Engine`;

  openModal('invoiceDetailModal');
};

window.printInvoicePdf = function() {
  window.print();
};

window.dispatchInvoiceEmail = async function() {
  const inv = window.activeInvoiceData;
  if (!inv) return;

  const clients = (window.JMOS_STATE && JMOS_STATE.clients) ? JMOS_STATE.clients : [];
  const clientObj = clients.find(c => (c.client_name || c.name || '').toLowerCase() === (inv.client || '').toLowerCase());
  let targetEmail = clientObj?.email || '';

  if (!targetEmail || !targetEmail.includes('@')) {
    const promptEmail = prompt(`Enter email address for ${inv.client}:`, '');
    if (promptEmail && promptEmail.includes('@')) {
      targetEmail = promptEmail.trim();
    } else {
      showToast('Email Required', 'A valid email address is required to dispatch invoice', true);
      return;
    }
  }

  const confirmed = await window.showConfirmDialog({
    title: 'Dispatch Invoice Email?',
    subtitle: `Invoice #${inv.invoice_no}`,
    type: 'info',
    confirmText: 'Send Invoice Email',
    message: `Send official commercial invoice <b>${escHtml(inv.invoice_no)}</b> (KES ${Number(inv.amount || 0).toLocaleString()}) directly to <b>${escHtml(targetEmail)}</b>?`,
    bullets: [
      `Client: ${inv.client}`,
      `Invoice Amount: KES ${Number(inv.amount || 0).toLocaleString()}`,
      `M-Pesa Paybill: 880100 · Account: ${inv.invoice_no}`,
      'Official branded HTML invoice notification with payment remittance details will be delivered.'
    ]
  });
  if (!confirmed) return;

  try {
    const res = await JMOS_API.post(`/invoices/${inv.id}/send-reminder`, { email: targetEmail });
    showToast('Invoice Dispatched', res.message || `Invoice sent to ${targetEmail} 📧`);
  } catch (err) {
    showToast('Email Error', err.message, true);
  }
};

window.dispatchInvoiceWhatsApp = async function() {
  const inv = window.activeInvoiceData;
  if (!inv) return;

  try {
    const res = await JMOS_API.get(`/invoices/${inv.id}/whatsapp`);
    if (res && res.whatsapp_url) {
      window.open(res.whatsapp_url, '_blank');
      showToast('WhatsApp Opened', 'Invoice summary & payment instructions prepared! 💬');
    } else {
      showToast('WhatsApp Notice', 'Could not prepare WhatsApp message link', true);
    }
  } catch (err) {
    showToast('WhatsApp Error', err.message, true);
  }
};

window.recordInvoicePaymentFromModal = async function() {
  const inv = window.activeInvoiceData;
  if (!inv) return;

  try {
    const res = await JMOS_API.post(`/invoices/${inv.id}/pay`, { method: 'M-Pesa' });
    showToast('Payment Recorded', res.message || `Invoice ${inv.invoice_no} marked as paid`);

    inv.status = 'Paid';
    window.activeInvoiceData = inv;

    await JMOS_API.fetchAll();
    renderAllViews();
    if (typeof recomputeFinance === 'function') {
      await recomputeFinance();
    }

    // Refresh active modal display
    window.openInvoiceDetailModal(inv.id);
  } catch (err) {
    showToast('Payment Record Failed', err.message, true);
  }
};

window.openEditInvoiceModalFromDetail = function() {
  const inv = window.activeInvoiceData;
  if (inv) {
    closeModal('invoiceDetailModal');
    window.openEditInvoiceModal(inv.id);
  }
};

window.deleteActiveInvoiceFromDetail = function() {
  const inv = window.activeInvoiceData;
  if (inv) {
    closeModal('invoiceDetailModal');
    window.deleteInvoice(inv.id, inv.invoice_no);
  }
};

window.openEditInvoiceModal = async function(invoiceId) {
  let list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  let inv = list.find(x => String(x.id) === String(invoiceId) || (x.invoice_no && String(x.invoice_no).toLowerCase() === String(invoiceId).toLowerCase()));

  if (!inv) {
    try {
      const res = await JMOS_API.get(`/invoices/${invoiceId}`);
      inv = res.data || res;
    } catch (_) {}
  }

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
  let list = (window.JMOS_STATE && JMOS_STATE.invoices) ? JMOS_STATE.invoices : [];
  let inv = list.find(x => String(x.id) === String(id) || (x.invoice_no && String(x.invoice_no).toLowerCase() === String(id).toLowerCase()));

  if (!inv) {
    try {
      const res = await JMOS_API.get(`/invoices/${id}`);
      inv = res.data || res;
    } catch (_) {}
  }

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
    closeModal('invoiceDetailModal');
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

/* ==========================================================================
   STICKY NOTE TASK DETAIL & WORKSPACE CONTROLLER
   ========================================================================== */

window.detectTaskLinkProvider = function(url) {
  if (!url) return { name: 'Cloud Link', cls: 'generic' };
  const u = url.toLowerCase();
  if (u.includes('drive.google.com') || u.includes('docs.google.com')) return { name: 'Google Drive', cls: 'gdrive' };
  if (u.includes('frame.io')) return { name: 'Frame.io', cls: 'frameio' };
  if (u.includes('dropbox.com')) return { name: 'Dropbox', cls: 'dropbox' };
  if (u.includes('playbook.com')) return { name: 'Playbook', cls: 'playbook' };
  if (u.includes('figma.com')) return { name: 'Figma', cls: 'figma' };
  if (u.includes('notion.so') || u.includes('notion.site')) return { name: 'Notion', cls: 'notion' };
  if (u.includes('canva.com')) return { name: 'Canva', cls: 'canva' };
  if (u.includes('youtube.com') || u.includes('youtu.be')) return { name: 'YouTube', cls: 'youtube' };
  if (u.includes('vimeo.com')) return { name: 'Vimeo', cls: 'vimeo' };
  return { name: 'Web Link', cls: 'generic' };
};

function fmtRelativeTime(isoStr) {
  if (!isoStr) return '';
  try {
    const diff = (Date.now() - new Date(isoStr).getTime()) / 1000;
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return new Date(isoStr).toLocaleDateString('en-KE', { month: 'short', day: 'numeric' });
  } catch (_) {
    return '';
  }
}

window.openTaskDetailModal = function(taskId) {
  const task = (JMOS_STATE.tasks || []).find(t => String(t.id) === String(taskId));
  if (!task) return;

  const idEl = document.getElementById('tdTaskId');
  const titleEl = document.getElementById('tdTaskTitle');
  const projBadge = document.getElementById('tdProjectBadge');
  const priorityBadge = document.getElementById('tdPriorityBadge');
  const stageBadge = document.getElementById('tdStageBadge');
  const currentStagePill = document.getElementById('tdCurrentStagePill');
  const assigneeName = document.getElementById('tdAssigneeName');
  const assigneeAvatar = document.getElementById('tdAssigneeAvatar');
  const assignerName = document.getElementById('tdAssignerName');
  const dueDateText = document.getElementById('tdDueDateText');
  const descBox = document.getElementById('tdDescriptionBox');
  const banner = document.getElementById('tdBanner');

  if (idEl) idEl.value = task.id;
  if (titleEl) titleEl.textContent = task.title || 'Untitled Task';

  // Project
  const proj = task.project || (task.project_id && JMOS_STATE.projects ? JMOS_STATE.projects.find(p => p.id === task.project_id) : null);
  if (projBadge) {
    if (proj) {
      projBadge.textContent = proj.project_name;
      projBadge.title = `Client: ${proj.client || 'Internal'}`;
      projBadge.style.cursor = 'pointer';
      projBadge.onclick = () => {
        closeModal('taskDetailModal');
        if (typeof window.openProjectDetailModal === 'function') {
          window.openProjectDetailModal(proj.id);
        }
      };
    } else {
      projBadge.textContent = 'General Workspace Task';
      projBadge.style.cursor = 'default';
      projBadge.onclick = null;
    }
  }

  // Priority
  const priority = task.priority || 'medium';
  if (priorityBadge) {
    priorityBadge.textContent = priority.toUpperCase() + ' PRIORITY';
    if (priority === 'urgent') {
      priorityBadge.style.background = 'rgba(220, 38, 38, 0.15)';
      priorityBadge.style.color = '#DC2626';
      priorityBadge.style.borderColor = 'rgba(220, 38, 38, 0.3)';
    } else if (priority === 'high') {
      priorityBadge.style.background = 'rgba(245, 158, 11, 0.15)';
      priorityBadge.style.color = '#D97706';
      priorityBadge.style.borderColor = 'rgba(245, 158, 11, 0.3)';
    } else {
      priorityBadge.style.background = 'var(--panel-2)';
      priorityBadge.style.color = 'var(--muted)';
      priorityBadge.style.borderColor = 'var(--line)';
    }
  }

  // Stage
  const stageLabels = {
    'todo': 'To do',
    'in_progress': 'In progress',
    'review_internal': 'Internal Review',
    'review_client': 'Client Review',
    'done': 'Done ✓'
  };
  const currentStageLabel = stageLabels[task.stage] || task.stage;
  if (stageBadge) stageBadge.textContent = currentStageLabel;
  if (currentStagePill) currentStagePill.textContent = 'Current: ' + currentStageLabel;

  // Assignee
  const personColor = task.assigned_color || '#C52523';
  const ini = task.assigned_initials || getInitials(task.assigned_to || 'JM');
  const taskAvatar = document.getElementById('tdTaskAvatar');
  if (taskAvatar) {
    taskAvatar.style.background = personColor;
    taskAvatar.textContent = ini;
  }
  if (assigneeAvatar) {
    assigneeAvatar.style.background = personColor;
    assigneeAvatar.textContent = ini;
  }
  if (assigneeName) assigneeName.textContent = task.assigned_to || 'Unassigned';

  // Assigner & Due date
  const creator = task.assigned_by?.name || (task.assigned_by_id && JMOS_STATE.users ? (JMOS_STATE.users.find(u => u.id === task.assigned_by_id)?.name) : 'Production Lead');
  if (assignerName) assignerName.textContent = creator;
  if (dueDateText) dueDateText.textContent = task.due_date ? fmtDate(task.due_date) : 'Flexible Timeline';

  // Sticky Theme Color
  const stickyColor = task.sticky_color || '#FFFBEB';
  const modalBox = document.getElementById('tdModalBox');
  if (modalBox) {
    modalBox.style.setProperty('--sticky-theme-bg', stickyColor);
    modalBox.style.backgroundColor = stickyColor;
  }
  if (banner) {
    banner.style.setProperty('--sticky-theme-bg', stickyColor);
    banner.style.backgroundColor = stickyColor;
  }
  const modalBody = document.querySelector('#taskDetailModal .sticky-modal-body');
  if (modalBody) {
    modalBody.style.backgroundColor = stickyColor;
  }
  const modalFooter = document.querySelector('#taskDetailModal .workspace-modal-footer');
  if (modalFooter) {
    modalFooter.style.backgroundColor = stickyColor;
  }
  document.querySelectorAll('#tdColorPickerMini .swatch-mini').forEach(sw => {
    if (sw.getAttribute('data-color') === stickyColor) {
      sw.classList.add('active');
    } else {
      sw.classList.remove('active');
    }
  });

  // Description
  if (descBox) {
    descBox.textContent = task.description ? task.description : 'No detailed instructions provided for this task.';
  }

  // Render Deliverable Review Links
  window.renderTaskDetailLinks(task);

  // Render Stepper & PM Workflow Buttons
  window.renderTaskWorkflowControls(task);

  // Render Comments & Activity Stream
  window.renderTaskDetailComments(task);

  // Clear new link & comment input fields
  const linkTitleInput = document.getElementById('tdNewLinkTitle');
  const linkUrlInput = document.getElementById('tdNewLinkUrl');
  const linkDetectBadge = document.getElementById('tdProviderDetectBadge');
  const commentMsgInput = document.getElementById('tdCommentMessage');
  if (linkTitleInput) linkTitleInput.value = '';
  if (linkUrlInput) linkUrlInput.value = '';
  if (linkDetectBadge) linkDetectBadge.style.display = 'none';
  if (commentMsgInput) commentMsgInput.value = '';

  openModal('taskDetailModal');
};

window.renderTaskDetailLinks = function(task) {
  const container = document.getElementById('tdLinksList');
  const countBadge = document.getElementById('tdLinksCountBadge');
  if (!container) return;

  const links = Array.isArray(task.links) ? task.links : [];
  if (countBadge) countBadge.textContent = `${links.length} link${links.length === 1 ? '' : 's'}`;

  if (!links.length) {
    container.innerHTML = `<div style="padding:16px;text-align:center;font-size:12px;color:var(--muted);background:var(--panel);border-radius:10px;border:1px dashed var(--line)">No review links attached yet. Team members can attach Google Drive cuts, Frame.io videos, Figma prototypes, or review documents below.</div>`;
    return;
  }

  container.innerHTML = links.map(lnk => {
    const provInfo = window.detectTaskLinkProvider(lnk.url);
    const provName = lnk.provider || provInfo.name;
    const provCls = provInfo.cls;
    const author = lnk.created_by || 'Team';
    const timeText = lnk.created_at ? fmtRelativeTime(lnk.created_at) : '';

    return `
      <div class="sticky-link-card">
        <div class="link-card-meta">
          <div class="link-card-title">${escHtml(lnk.title || 'Review Deliverable')}</div>
          <div class="link-card-sub">
            <span class="provider-pill ${provCls}">${escHtml(provName)}</span>
            <span>By ${escHtml(author)} ${timeText ? '• ' + escHtml(timeText) : ''}</span>
          </div>
        </div>
        <div class="link-card-actions">
          <a href="${escHtml(lnk.url)}" target="_blank" rel="noopener noreferrer" class="btn-open-link" title="Open ${escHtml(lnk.title)} in new tab">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Review ↗
          </a>
          <button type="button" class="linkbtn" data-delete-task-link="${lnk.id}" style="color:var(--muted);font-size:14px;padding:4px" title="Delete link">&times;</button>
        </div>
      </div>
    `;
  }).join('');
};

window.renderTaskWorkflowControls = function(task) {
  const stagesOrder = ['todo', 'in_progress', 'review_internal', 'review_client', 'done'];
  const currentIdx = stagesOrder.indexOf(task.stage);

  // Stepper buttons
  document.querySelectorAll('#tdStageStepper .stage-step-btn').forEach((btn, idx) => {
    btn.classList.remove('completed', 'current');
    if (idx < currentIdx) {
      btn.classList.add('completed');
    } else if (idx === currentIdx) {
      btn.classList.add('current');
    }
  });

  const proceedBtn = document.getElementById('tdProceedBtn');
  const sendBackBtn = document.getElementById('tdSendBackBtn');
  const revBox = document.getElementById('tdRevisionNoteBox');
  if (revBox) revBox.style.display = 'none';

  if (proceedBtn) {
    if (task.stage === 'todo') {
      proceedBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg> Start Task →`;
      proceedBtn.style.display = 'inline-flex';
    } else if (task.stage === 'in_progress') {
      proceedBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg> Submit for Internal Review →`;
      proceedBtn.style.display = 'inline-flex';
    } else if (task.stage === 'review_internal') {
      proceedBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg> Approve & Move to Client Review →`;
      proceedBtn.style.display = 'inline-flex';
    } else if (task.stage === 'review_client') {
      proceedBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Approve & Mark Done ✓`;
      proceedBtn.style.display = 'inline-flex';
    } else {
      proceedBtn.style.display = 'none';
    }
  }

  if (sendBackBtn) {
    if (task.stage === 'todo') {
      sendBackBtn.style.display = 'none';
    } else if (task.stage === 'in_progress') {
      sendBackBtn.style.display = 'inline-flex';
      sendBackBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg> Revert to To Do`;
    } else {
      sendBackBtn.style.display = 'inline-flex';
      sendBackBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg> Send Back for Changes`;
    }
  }
};

window.renderTaskDetailComments = function(task) {
  const feed = document.getElementById('tdCommentsFeed');
  const countBadge = document.getElementById('tdCommentsCountBadge');
  if (!feed) return;

  const comments = Array.isArray(task.comments) ? task.comments : [];
  if (countBadge) countBadge.textContent = `${comments.length} entr${comments.length === 1 ? 'y' : 'ies'}`;

  if (!comments.length) {
    feed.innerHTML = `<div style="padding:16px;text-align:center;font-size:12px;color:var(--muted);background:var(--panel);border-radius:10px;border:1px dashed var(--line)">No comments or recommendations yet. Use the box below to share review feedback, recommendations, or revision notes.</div>`;
    return;
  }

  feed.innerHTML = comments.map(c => {
    const authorName = c.user_name || 'Team Member';
    const authorColor = c.user_color || '#C52523';
    const authorIni = getInitials(authorName);
    const role = c.user_role || 'team';
    const timeText = c.created_at ? fmtRelativeTime(c.created_at) : '';
    const type = c.type || 'comment';

    let tagHtml = '';
    if (type === 'recommendation') {
      tagHtml = `<span class="comment-type-tag recommendation">💡 Recommendation</span>`;
    } else if (type === 'change_request') {
      tagHtml = `<span class="comment-type-tag change_request">⚠️ Revision Requested</span>`;
    } else if (type === 'approval') {
      tagHtml = `<span class="comment-type-tag approval">✅ Stage Advance</span>`;
    } else if (type === 'link_attached') {
      tagHtml = `<span class="comment-type-tag" style="background:rgba(66,133,244,0.15);color:#3B82F6">📎 Review Link Attached</span>`;
    }

    return `
      <div class="comment-bubble ${escHtml(type)}">
        <div class="comment-author-row">
          <div class="comment-author-left">
            <span class="av-chip" style="background:${authorColor};width:20px;height:20px;font-size:9px">${escHtml(authorIni)}</span>
            <b style="font-size:12.5px;color:var(--ink)">${escHtml(authorName)}</b>
            <span class="comment-role-pill">${escHtml(role)}</span>
          </div>
          <span class="comment-time">${escHtml(timeText)}</span>
        </div>
        ${tagHtml ? `<div style="margin:2px 0">${tagHtml}</div>` : ''}
        <div class="comment-body-text">${escHtml(c.message || '')}</div>
      </div>
    `;
  }).join('');

  feed.scrollTop = feed.scrollHeight;
};

// Global Event Listeners for Task Detail Workspace Modal
document.addEventListener('DOMContentLoaded', () => {
  // 1. Live Cloud Provider Detection on URL Input
  const linkUrlInput = document.getElementById('tdNewLinkUrl');
  if (linkUrlInput) {
    linkUrlInput.addEventListener('input', (e) => {
      const val = e.target.value.trim();
      const badge = document.getElementById('tdProviderDetectBadge');
      if (!badge) return;
      if (val) {
        const prov = window.detectTaskLinkProvider(val);
        badge.textContent = prov.name;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
  }

  // 2. Sticky Color Swatches in Create Task Modal
  const createSwatches = document.querySelectorAll('#taskStickyColorSwatches .color-swatch-btn');
  createSwatches.forEach(btn => {
    btn.addEventListener('click', () => {
      createSwatches.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const color = btn.getAttribute('data-color');
      const hidden = document.getElementById('ntStickyColor');
      if (hidden) hidden.value = color;
    });
  });
});

// Click Delegations for Task Detail Modal Actions
document.addEventListener('click', async (e) => {
  // A. Change Theme Color in Task Detail Modal
  const swatchBtn = e.target.closest('#tdColorPickerMini .swatch-mini');
  if (swatchBtn) {
    const taskId = document.getElementById('tdTaskId')?.value;
    const color = swatchBtn.getAttribute('data-color');
    if (!taskId || !color) return;

    document.querySelectorAll('#tdColorPickerMini .swatch-mini').forEach(b => b.classList.remove('active'));
    swatchBtn.classList.add('active');
    
    const modalBox = document.getElementById('tdModalBox');
    if (modalBox) {
      modalBox.style.setProperty('--sticky-theme-bg', color);
      modalBox.style.backgroundColor = color;
    }
    const banner = document.getElementById('tdBanner');
    if (banner) {
      banner.style.setProperty('--sticky-theme-bg', color);
      banner.style.backgroundColor = color;
    }
    const modalBody = document.querySelector('#taskDetailModal .sticky-modal-body');
    if (modalBody) {
      modalBody.style.backgroundColor = color;
    }
    const modalFooter = document.querySelector('#taskDetailModal .workspace-modal-footer');
    if (modalFooter) {
      modalFooter.style.backgroundColor = color;
    }

    try {
      await JMOS_API.put(`/tasks/${taskId}`, { sticky_color: color });
      const task = (JMOS_STATE.tasks || []).find(t => String(t.id) === String(taskId));
      if (task) task.sticky_color = color;
      renderTasks();
    } catch (err) {
      showToast('Color update failed', err.message, true);
    }
    return;
  }

  // B. Add Review Link
  if (e.target.closest('#tdAddLinkBtn')) {
    const btn = e.target.closest('#tdAddLinkBtn');
    const taskId = document.getElementById('tdTaskId')?.value;
    const title = document.getElementById('tdNewLinkTitle')?.value.trim();
    const url = document.getElementById('tdNewLinkUrl')?.value.trim();

    if (!taskId) return;
    if (!title) return showToast('Link Label Required', 'Please enter a name for this deliverable (e.g. Rough Cut v1)', true);
    if (!url) return showToast('Link URL Required', 'Please enter a valid review URL (e.g. Google Drive, Frame.io)', true);

    btn.disabled = true;
    btn.textContent = 'Attaching…';

    try {
      const res = await JMOS_API.post(`/tasks/${taskId}/links`, { title, url });
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.openTaskDetailModal(taskId);
        renderTasks();
      }
      showToast('Review Link Added', `Attached "${title}" for review`);
    } catch (err) {
      showToast('Failed to add link', err.message, true);
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Attach Review Link`;
    }
    return;
  }

  // C. Delete Review Link
  const delLinkBtn = e.target.closest('[data-delete-task-link]');
  if (delLinkBtn) {
    const linkId = delLinkBtn.getAttribute('data-delete-task-link');
    const taskId = document.getElementById('tdTaskId')?.value;
    if (!taskId || !linkId) return;

    try {
      const res = await JMOS_API.delete(`/tasks/${taskId}/links/${linkId}`);
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.openTaskDetailModal(taskId);
        renderTasks();
      }
      showToast('Review Link Removed', 'Deliverable link deleted');
    } catch (err) {
      showToast('Delete failed', err.message, true);
    }
    return;
  }

  // D. Proceed to Next Stage
  if (e.target.closest('#tdProceedBtn')) {
    const btn = e.target.closest('#tdProceedBtn');
    const taskId = document.getElementById('tdTaskId')?.value;
    if (!taskId) return;

    btn.disabled = true;
    try {
      const res = await JMOS_API.post(`/tasks/${taskId}/workflow`, { action: 'proceed' });
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.openTaskDetailModal(taskId);
        renderTasks();
      }
      showToast('Task Advanced', `Stage moved to ${updatedTask.stage.replace('_', ' ')}`);
    } catch (err) {
      showToast('Workflow failed', err.message, true);
    } finally {
      btn.disabled = false;
    }
    return;
  }

  // E. Toggle Send Back Revision Box
  if (e.target.closest('#tdSendBackBtn')) {
    const revBox = document.getElementById('tdRevisionNoteBox');
    if (revBox) {
      revBox.style.display = (revBox.style.display === 'none' || !revBox.style.display) ? 'block' : 'none';
      if (revBox.style.display === 'block') {
        document.getElementById('tdRevisionText')?.focus();
      }
    }
    return;
  }

  if (e.target.closest('#tdCancelRevisionBtn')) {
    const revBox = document.getElementById('tdRevisionNoteBox');
    if (revBox) revBox.style.display = 'none';
    return;
  }

  // F. Confirm Send Back for Changes
  if (e.target.closest('#tdConfirmSendBackBtn')) {
    const btn = e.target.closest('#tdConfirmSendBackBtn');
    const taskId = document.getElementById('tdTaskId')?.value;
    const recommendation = document.getElementById('tdRevisionText')?.value.trim();

    if (!taskId) return;
    if (!recommendation) return showToast('Revision Note Required', 'Please specify what adjustments or changes are required', true);

    btn.disabled = true;
    btn.textContent = 'Reverting…';

    try {
      const res = await JMOS_API.post(`/tasks/${taskId}/workflow`, { action: 'send_back', recommendation });
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.openTaskDetailModal(taskId);
        renderTasks();
      }
      showToast('Changes Requested', 'Task reverted for team member revisions');
    } catch (err) {
      showToast('Revert failed', err.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Submit Revisions & Revert';
    }
    return;
  }

  // G. Stage Stepper Direct Click in Task Detail Modal
  const taskStageStepBtn = e.target.closest('#tdStageStepper .stage-step-btn');
  if (taskStageStepBtn) {
    const newStage = taskStageStepBtn.getAttribute('data-stage');
    const taskId = document.getElementById('tdTaskId')?.value;
    if (!taskId || !newStage) return;

    try {
      taskStageStepBtn.disabled = true;
      const res = await JMOS_API.put(`/tasks/${taskId}`, { stage: newStage });
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.openTaskDetailModal(taskId);
        renderTasks();
      }
      showToast('Stage Updated', `Task moved to ${newStage.replace('_', ' ')}`);
    } catch (err) {
      showToast('Stage change unauthorized', err.message, true);
    } finally {
      taskStageStepBtn.disabled = false;
    }
    return;
  }

  // H. Post Comment / Recommendation
  if (e.target.closest('#tdPostCommentBtn')) {
    const btn = e.target.closest('#tdPostCommentBtn');
    const taskId = document.getElementById('tdTaskId')?.value;
    const message = document.getElementById('tdCommentMessage')?.value.trim();
    const type = document.getElementById('tdCommentTypeSelect')?.value || 'comment';

    if (!taskId) return;
    if (!message) return showToast('Comment Required', 'Please enter your message or recommendation', true);

    btn.disabled = true;
    btn.textContent = 'Posting…';

    try {
      const res = await JMOS_API.post(`/tasks/${taskId}/comments`, { message, type });
      const updatedTask = res.data;
      if (updatedTask && JMOS_STATE.tasks) {
        const idx = JMOS_STATE.tasks.findIndex(t => String(t.id) === String(taskId));
        if (idx !== -1) JMOS_STATE.tasks[idx] = updatedTask;
        window.renderTaskDetailComments(updatedTask);
        renderTasks();
      }
      document.getElementById('tdCommentMessage').value = '';
      showToast('Comment Posted', 'Feedback recorded on task');
    } catch (err) {
      showToast('Post comment failed', err.message, true);
    } finally {
      btn.disabled = false;
      btn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Post Comment`;
    }
    return;
  }

  // I. Delete Task
  if (e.target.closest('#tdDeleteTaskBtn')) {
    const taskId = document.getElementById('tdTaskId')?.value;
    if (!taskId) return;
    const task = (JMOS_STATE.tasks || []).find(t => String(t.id) === String(taskId));
    const title = task ? task.title : 'this task';

    const confirmed = await window.showConfirmDialog({
      title: 'Remove Task?',
      message: `Are you sure you want to delete task <b>${escHtml(title)}</b>? Associated calendar deadlines will also be removed.`,
      confirmText: 'Delete Task',
      isDanger: true
    });

    if (!confirmed) return;

    try {
      await JMOS_API.delete(`/tasks/${taskId}`);
      closeModal('taskDetailModal');
      showToast('Task Deleted', `Removed "${title}" from workspace`);
      await JMOS_API.fetchAll();
      renderAllViews();
    } catch (err) {
      showToast('Delete failed', err.message, true);
    }
    return;
  }
});

// Universal Calendar Date & Time Picker Activator (No manual typing required)
document.addEventListener('click', (e) => {
  const target = e.target;
  if (target && target.tagName === 'INPUT' && (target.type === 'date' || target.type === 'time' || target.type === 'datetime-local')) {
    if (typeof target.showPicker === 'function') {
      try {
        target.showPicker();
      } catch (_) {}
    }
  }
});

// Dynamic Project Custom Field & Category Switching Helpers
window.onProjectCategoryChange = function(cat, prefix) {
  const customWrap = document.getElementById(prefix === 'np' ? 'npCustomCategoryWrap' : 'pdmCustomCategoryWrap');
  const customInput = document.getElementById(prefix === 'np' ? 'npCustomCategoryInput' : 'pdmCustomCategoryInput');
  const typeSelect = document.getElementById(prefix === 'np' ? 'npType' : 'pdmTypeSelect');
  const stageSelect = document.getElementById(prefix === 'np' ? 'npStage' : 'pdmStageSelect');

  if (cat === 'custom') {
    if (customWrap) customWrap.style.display = 'block';
    if (customInput) customInput.focus();
  } else {
    if (customWrap) customWrap.style.display = 'none';
  }

  // Auto-suggest appropriate default type and stage based on chosen category
  if (typeSelect && stageSelect) {
    if (cat === 'development') {
      const devType = typeSelect.querySelector('option[value="Internal System Development (JMOS / Tech)"]');
      if (devType) typeSelect.value = devType.value;
      const devStage = stageSelect.querySelector('option[value="Backlog / Requirements"]');
      if (devStage) stageSelect.value = devStage.value;
    } else if (cat === 'graphic_design') {
      const gdType = typeSelect.querySelector('option[value="Brand Identity & Logo Design"]');
      if (gdType) typeSelect.value = gdType.value;
      const gdStage = stageSelect.querySelector('option[value="Creative Brief"]');
      if (gdStage) stageSelect.value = gdStage.value;
    } else if (cat === 'content_calendar') {
      const ccType = typeSelect.querySelector('option[value="Monthly Content Calendar & Production"]');
      if (ccType) typeSelect.value = ccType.value;
      const ccStage = stageSelect.querySelector('option[value="Content Strategy"]');
      if (ccStage) stageSelect.value = ccStage.value;
    } else if (cat === 'video_production') {
      const vpType = typeSelect.querySelector('option[value="Brand film"]');
      if (vpType) typeSelect.value = vpType.value;
      const vpStage = stageSelect.querySelector('option[value="Brief"]');
      if (vpStage) stageSelect.value = vpStage.value;
    } else if (cat === 'internal') {
      const inType = typeSelect.querySelector('option[value="Internal Operations & Studio R&D"]');
      if (inType) typeSelect.value = inType.value;
      const inStage = stageSelect.querySelector('option[value="Planning"]');
      if (inStage) stageSelect.value = inStage.value;
    }
  }
};

window.onProjectTypeChange = function(val, prefix) {
  const customWrap = document.getElementById(prefix === 'np' ? 'npCustomTypeWrap' : 'pdmCustomTypeWrap');
  const customInput = document.getElementById(prefix === 'np' ? 'npCustomTypeInput' : 'pdmCustomTypeInput');
  if (val === 'custom') {
    if (customWrap) customWrap.style.display = 'block';
    if (customInput) customInput.focus();
  } else {
    if (customWrap) customWrap.style.display = 'none';
  }
};

window.onProjectStageChange = function(val, prefix) {
  const customWrap = document.getElementById(prefix === 'np' ? 'npCustomStageWrap' : 'pdmCustomStageWrap');
  const customInput = document.getElementById(prefix === 'np' ? 'npCustomStageInput' : 'pdmCustomStageInput');
  if (val === 'custom') {
    if (customWrap) customWrap.style.display = 'block';
    if (customInput) customInput.focus();
  } else {
    if (customWrap) customWrap.style.display = 'none';
  }
};

window.onProjectStatusChange = function(val, prefix) {
  const customWrap = document.getElementById(prefix === 'np' ? 'npCustomStatusWrap' : 'pdmCustomStatusWrap');
  const customInput = document.getElementById(prefix === 'np' ? 'npCustomStatusInput' : 'pdmCustomStatusInput');
  if (val === 'custom') {
    if (customWrap) customWrap.style.display = 'block';
    if (customInput) customInput.focus();
  } else {
    if (customWrap) customWrap.style.display = 'none';
  }
};



