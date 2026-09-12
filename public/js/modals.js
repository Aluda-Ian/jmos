/* ==========================================================================
   JMOS — Modals, Deal-Won Cascade Flow & Interactive Database Actions
   ========================================================================== */

// Global Modal Open & Close Functions
window.openModal = function(id) {
  const m = typeof id === 'string' ? document.getElementById(id) : id;
  if (!m) return;

  // Prepare / pre-fill modal-specific defaults
  const modalId = m.id;
  if (modalId === 'dealModal') {
    ['ndTitle', 'ndClient', 'ndValue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const st = document.getElementById('ndStage');
    if (st) st.value = 'lead';
  } else if (modalId === 'clientModal') {
    ['ncName', 'ncContact', 'ncOwner', 'ncService', 'ncValue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
  } else if (modalId === 'projectModal') {
    ['npName', 'npClient', 'npType', 'npManager', 'npDeadline', 'npBudget'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
    const st = document.getElementById('npStage');
    if (st) st.value = 'brief';
    const sts = document.getElementById('npStatus');
    if (sts) sts.value = 'On track';
  } else if (modalId === 'taskModal') {
    const el = document.getElementById('ntTitle');
    if (el) el.value = '';
    const st = document.getElementById('ntStage');
    if (st) st.value = 'todo';
  } else if (modalId === 'invoiceModal') {
    const maxNo = (window.JMOS_STATE && JMOS_STATE.invoices && JMOS_STATE.invoices.length)
      ? (145 + JMOS_STATE.invoices.length) 
      : 146;
    const niNo = document.getElementById('niNo');
    if (niNo) niNo.value = 'JM-0' + maxNo;
    ['niClient', 'niAmount', 'niDue'].forEach(fid => {
      const el = document.getElementById(fid);
      if (el) el.value = '';
    });
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
      doBtn.textContent = '✓';
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

    // 4.2 Submit: Add Project
    if (e.target.closest('#saveProjectBtn')) {
      const btn = e.target.closest('#saveProjectBtn');
      const name = document.getElementById('npName')?.value.trim();
      const client = document.getElementById('npClient')?.value.trim();
      if (!name || !client) return showToast('Name & Client required', 'Please fill project and client name', true);

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
      const client = document.getElementById('ndClient')?.value.trim();
      const val = Number(document.getElementById('ndValue')?.value) || 0;
      if (!title || !client) return showToast('Title & Client required', 'Please enter deal title and client', true);

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

      try {
        await JMOS_API.post('/tasks', {
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
      const client = document.getElementById('niClient')?.value.trim();
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
