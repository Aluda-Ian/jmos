/* ==========================================================================
   JMOS — Master Application Bootloader & Dynamic Views Controller
   ========================================================================== */

// 1. Dashboard View Renderer
function renderDashboard() {
  const pList = JMOS_STATE.projects || [];
  const pipe = JMOS_STATE.pipeline || [];
  const invs = JMOS_STATE.invoices || [];
  const tasks = JMOS_STATE.tasks || [];
  const users = JMOS_STATE.users || [];

  // Time-Bound Greeting and Operations Brief
  const greetName = document.getElementById('greetName');
  if (greetName && JMOS_STATE.currentUser) {
    const greeting = typeof getTimeBoundGreeting === 'function' ? getTimeBoundGreeting() : 'Good day';
    const firstName = JMOS_STATE.currentUser.name ? JMOS_STATE.currentUser.name.split(' ')[0] : 'there';
    greetName.textContent = `${greeting}, ${firstName}.`;
  }

  const briefTagText = document.getElementById('briefTagText');
  if (briefTagText && typeof getTimeBoundBriefTag === 'function') {
    briefTagText.textContent = getTimeBoundBriefTag();
  }

  const overdueInvs = invs.filter(v => (v.status || '').toLowerCase() === 'overdue');
  const openDeals = pipe.filter(d => d.stage !== 'won');
  const briefHeadline = document.getElementById('briefHeadline');
  const briefBody = document.getElementById('briefBody');

  if (briefHeadline && briefBody) {
    const alertCount = overdueInvs.length + (openDeals.length ? 1 : 0) + (pList.length ? 1 : 0);
    if (alertCount === 0) {
      briefHeadline.textContent = 'Operations dashboard ready.';
      briefBody.innerHTML = `Welcome to JMOS. Your workspace is clean and ready. Click <b>Quick Action</b> or add your first client, pipeline deal, or live project to begin tracking your operations.`;
    } else {
      briefHeadline.textContent = `${alertCount} items active in your pipeline & operations.`;
      let briefParts = [];
      if (overdueInvs.length) {
        briefParts.push(`Invoice <b>${escHtml(overdueInvs[0].invoice_no)}</b> (${escHtml(overdueInvs[0].client)}, ${fmt(overdueInvs[0].amount)}) is <span class="u">overdue</span>.`);
      }
      if (openDeals.length) {
        briefParts.push(`<b>${escHtml(openDeals[0].title)}</b> is currently in <b>${escHtml(openDeals[0].stage)}</b> (${fmt(openDeals[0].value)}).`);
      }
      if (pList.length) {
        briefParts.push(`<b>${escHtml(pList[0].project_name)}</b> is at stage <b>${escHtml(pList[0].stage)}</b> (${escHtml(pList[0].status)}).`);
      }
      briefParts.push(`Cash balance is <b>${fmtK(JMOS_STATE.finance.current_balance)}</b>.`);
      briefBody.innerHTML = briefParts.join(' ');
    }
  }

  // KPIs
  const kpiPipeline = document.getElementById('kpiPipeline');
  const kpiPipelineSub = document.getElementById('kpiPipelineSub');
  const pipeTotal = pipe.reduce((sum, d) => sum + (Number(d.value) || 0), 0);
  if (kpiPipeline) kpiPipeline.innerHTML = fmtK(pipeTotal).replace(/(M|K)$/, '<small>$1</small>');
  if (kpiPipelineSub) kpiPipelineSub.innerHTML = `<span class="mono">${pipe.length}</span> open deals`;

  // Triage List
  const triageList = document.getElementById('triageList');
  const triageCount = document.getElementById('triageCount');
  if (triageList) {
    const triageItems = [];
    overdueInvs.forEach(inv => {
      triageItems.push(`
        <div class="titem">
          <span class="flag" style="background:var(--red)"></span>
          <div class="body">
            <div class="t">Overdue — invoice ${escHtml(inv.invoice_no)} (${escHtml(inv.client)})</div>
            <div class="m"><span class="tc">${fmt(inv.amount)}</span> · Awaiting payment</div>
          </div>
          <button class="act" data-pay-invoice-id="${inv.id}">Record payment</button>
        </div>
      `);
    });

    openDeals.slice(0, 3).forEach(deal => {
      const isNeg = deal.stage === 'negotiation' || deal.stage === 'proposal';
      triageItems.push(`
        <div class="titem">
          <span class="flag" style="background:${isNeg ? 'var(--red)' : 'var(--amber)'}"></span>
          <div class="body">
            <div class="t">${escHtml(deal.title)}</div>
            <div class="m">Stage: <b>${escHtml(deal.stage)}</b> · ${fmt(deal.value)}</div>
          </div>
          <button class="act" data-win-deal-id="${deal.id}">Win deal</button>
        </div>
      `);
    });

    if (triageCount) triageCount.textContent = triageItems.length;
    triageList.innerHTML = triageItems.length ? triageItems.join('') : '<div style="padding:20px;text-align:center;color:var(--muted)">All clear — no pending alerts!</div>';
  }

  // Active Projects Widget
  const dashProjectsList = document.getElementById('dashProjectsList');
  const dashProjectsCount = document.getElementById('dashProjectsCount');
  if (dashProjectsList) {
    if (dashProjectsCount) dashProjectsCount.textContent = pList.length;
    if (pList.length) {
      dashProjectsList.innerHTML = pList.slice(0, 4).map(p => {
        const pTasks = (p.tasks && Array.isArray(p.tasks) && p.tasks.length)
          ? p.tasks
          : ((JMOS_STATE.tasks && Array.isArray(JMOS_STATE.tasks)) ? JMOS_STATE.tasks.filter(t => t.project_id === p.id) : []);
        const totalTasks = pTasks.length;
        const doneTasks = pTasks.filter(t => t.stage === 'done').length;
        const pct = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : (Number(p.progress_pct) || 0);

        const isGreen = pct === 100 || (p.status || '').toLowerCase().includes('track') || (p.status || '').toLowerCase().includes('delivering');
        const colorVar = isGreen ? 'var(--green)' : (pct > 0 ? 'var(--amber)' : 'var(--muted)');
        return `
          <div class="prow" onclick="window.openProjectWorkspace && window.openProjectWorkspace(${p.id})" style="cursor:pointer" title="Open project workspace">
            <div class="top"><span class="name">${escHtml(p.project_name)}</span><span class="due">due ${escHtml(p.deadline || 'Soon')}</span></div>
            <div class="meta">
              <div class="bar"><i style="width:${pct}%;background:${colorVar}"></i></div>
              <span class="pct">${pct}%</span>
              <span class="status ${getProjectStatusClass(p.status)}">${escHtml(p.status || 'On track')}</span>
            </div>
            <div class="wait">Stage: <b>${escHtml(p.stage || 'brief')}</b> · Waiting on <b class="${p.waiting_on === 'client' ? 'cl' : 'us'}">${escHtml(p.waiting_on || 'us')}</b></div>
          </div>
        `;
      }).join('');
    } else {
      dashProjectsList.innerHTML = '<div style="padding:24px;text-align:center;color:var(--muted)">No projects yet. Click “New” or add a project.</div>';
    }
  }

  // Sales Pipeline Widget
  const dashPipeWidget = document.getElementById('dashPipeWidget');
  const dashPipelineTotal = document.getElementById('dashPipelineTotal');
  if (dashPipeWidget) {
    if (dashPipelineTotal) dashPipelineTotal.textContent = fmtK(pipeTotal);
    
    const stages = ['lead', 'meeting', 'proposal', 'negotiation', 'won'];
    const stageLabels = { lead: 'Lead', meeting: 'Meeting', proposal: 'Proposal', negotiation: 'Negotiation', won: 'Won' };
    const stageTracks = {
      lead: '',
      meeting: 'background:#F3D6BF',
      proposal: 'background:var(--amber-soft)',
      negotiation: 'background:var(--red-line)',
      won: 'background:var(--red)'
    };

    const stageBars = stages.map(s => {
      const count = pipe.filter(d => d.stage === s).length;
      return `<div class="s"><div class="n">${count}</div><div class="l">${stageLabels[s]}</div><div class="track" style="${stageTracks[s]}"></div></div>`;
    }).join('');

    if (pipe.length === 0) {
      dashPipeWidget.innerHTML = `
        <div class="stage-bar">${stageBars}</div>
        <div style="padding:24px;text-align:center;color:var(--muted)">
          No deals in the pipeline yet.<br>
          <button class="btn primary" style="margin-top:10px" id="quickAddDeal"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add your first deal</button>
        </div>
      `;
    } else {
      const targetDeal = pipe.find(d => d.stage === 'negotiation' || d.stage === 'proposal' || d.stage === 'meeting') || pipe[0];
      const dealCardHtml = `
        <div class="deal">
          <div class="logo">${getInitials(targetDeal.client_name || targetDeal.title)}</div>
          <div class="info">
            <div class="n">${escHtml(targetDeal.title)}</div>
            <div class="v">Stage: <b>${escHtml(targetDeal.stage || 'Lead')}</b> · ${fmt(targetDeal.value)}</div>
          </div>
          <button class="won-btn" id="winDeal" data-win-deal-id="${targetDeal.id}"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>Win &amp; spin up project</button>
        </div>
      `;
      dashPipeWidget.innerHTML = `<div class="stage-bar">${stageBars}</div>${dealCardHtml}`;
    }
  }

  // Team Workload Widget
  const dashWorkloadList = document.getElementById('dashWorkloadList');
  if (dashWorkloadList) {
    const activeUsers = users.filter(u => u.role === 'team' || u.role === 'owner').slice(0, 5);
    dashWorkloadList.innerHTML = activeUsers.map(u => {
      const userTasks = tasks.filter(t => (t.assigned_to || '').includes(u.name.split(' ')[0]) && t.stage !== 'done');
      const count = userTasks.length;
      const pct = count ? Math.min(100, Math.max(25, count * 25)) : 5;
      const color = count ? (pct >= 75 ? 'var(--amber)' : 'var(--green)') : 'var(--line)';
      return `
        <div class="wrow">
          <span class="who"><span class="av" style="background:${u.color}">${escHtml(u.ini)}</span>${escHtml(u.name.split(' ')[0])}</span>
          <div class="wbar"><i style="width:${pct}%;background:${color}"></i></div>
          <span class="n">${count} ${count === 1 ? 'task' : 'tasks'}</span>
        </div>
      `;
    }).join('');
  }

  // Audit Trail & Activity Feed (Owner, Admin, IT Manager)
  const auditCard = document.getElementById('dashAuditTrailCard');
  if (auditCard && JMOS_STATE.currentUser && ['owner', 'admin', 'manager'].includes(JMOS_STATE.currentUser.role)) {
    if (typeof window.fetchAuditLogs === 'function') {
      window.fetchAuditLogs();
    }
  }
}

// 2. Pipeline Kanban Board Renderer
function renderPipeline() {
  const board = document.getElementById('pipelineBoard');
  if (!board) return;

  const deals = JMOS_STATE.pipeline || [];
  const stages = [
    { key: 'lead', name: 'Lead' },
    { key: 'meeting', name: 'Meeting' },
    { key: 'proposal', name: 'Proposal', border: 'var(--amber)' },
    { key: 'negotiation', name: 'Negotiation', border: 'var(--red-line)' },
    { key: 'won', name: 'Won', border: 'var(--red)', bg: 'var(--red-soft)', text: 'var(--red-deep)' }
  ];

  board.innerHTML = stages.map(st => {
    const inStage = deals.filter(d => d.stage === st.key);
    const cards = inStage.map(d => {
      const style = st.border ? `style="border-color:${st.border};${st.bg ? `background:${st.bg};` : ''}"` : '';
      const valStyle = st.text ? `style="font-size:11px;color:${st.text}"` : 'style="font-size:11px;color:var(--muted)"';
      const winBtn = st.key !== 'won' ? `<button class="linkbtn" style="font-size:11px;margin-top:6px" data-win-deal-id="${d.id}">Win deal →</button>` : '';

      return `
        <div class="tcard" ${style} data-deal-id="${d.id}" title="Click to view & move stage">
          <div class="tn">${escHtml(d.title)}</div>
          <div class="tf" style="display:flex;align-items:center;justify-content:space-between">
            <span class="mono" ${valStyle}>${fmt(d.value)}</span>
            ${d.client_name ? `<span style="font-size:10px;color:var(--muted);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(d.client_name)}</span>` : ''}
          </div>
          ${winBtn}
        </div>
      `;
    }).join('');

    return `
      <div class="bcol">
        <h4>${st.name} <span>${inStage.length}</span></h4>
        ${cards || '<div style="padding:16px 8px;font-size:12px;color:var(--faint);text-align:center">No deals</div>'}
      </div>
    `;
  }).join('');
}

// 3. Tasks Kanban Board Renderer
function renderTasks() {
  const board = document.getElementById('tasksBoard');
  if (!board) return;

  const tasks = JMOS_STATE.tasks || [];
  const stages = [
    { key: 'todo', name: 'To do' },
    { key: 'in_progress', name: 'In progress' },
    { key: 'review_internal', name: 'Review (internal)' },
    { key: 'review_client', name: 'Review (client)' },
    { key: 'done', name: 'Done' }
  ];

  board.innerHTML = stages.map(st => {
    const inStage = tasks.filter(t => t.stage === st.key);
    const cards = inStage.map(t => {
      const ini = t.assigned_initials || getInitials(t.assigned_to || 'JM');
      const color = t.assigned_color || '#C52523';
      const who = (t.assigned_to || '').split(' ')[0] || 'Team';

      // Quick advance action
      let nextStageBtn = '';
      if (st.key === 'todo') nextStageBtn = `<button class="linkbtn" style="font-size:11px" data-move-task="${t.id}" data-to-stage="in_progress">Start →</button>`;
      else if (st.key === 'in_progress') nextStageBtn = `<button class="linkbtn" style="font-size:11px" data-move-task="${t.id}" data-to-stage="review_internal">Submit review →</button>`;
      else if (st.key === 'review_internal') nextStageBtn = `<button class="linkbtn" style="font-size:11px" data-move-task="${t.id}" data-to-stage="done">Mark done ✓</button>`;

      const proj = t.project || (t.project_id && JMOS_STATE.projects ? JMOS_STATE.projects.find(p => p.id === t.project_id) : null);
      const projBadge = proj ? `<div style="margin-bottom:6px"><span class="badge" style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(197,37,35,0.08);color:var(--red);font-weight:600;display:inline-block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="Attached to ${escHtml(proj.project_name)}">${escHtml(proj.project_name)}</span></div>` : '';

      return `
        <div class="tcard">
          ${projBadge}
          <div class="tn">${escHtml(t.title)}</div>
          <div class="tf">
            <span class="av" style="background:${color}">${escHtml(ini)}</span>
            <span class="who">${escHtml(who)}</span>
          </div>
          ${nextStageBtn ? `<div style="margin-top:6px">${nextStageBtn}</div>` : ''}
        </div>
      `;
    }).join('');

    return `
      <div class="bcol">
        <h4>${st.name} <span>${inStage.length}</span></h4>
        ${cards || '<div style="padding:16px 8px;font-size:12px;color:var(--faint);text-align:center">No tasks</div>'}
      </div>
    `;
  }).join('');
}

// 4. Services Grid Renderer
function renderServices() {
  const grid = document.getElementById('svcGrid');
  if (!grid) return;

  const svcs = JMOS_STATE.services || [];
  if (!svcs.length) {
    grid.innerHTML = '<div style="grid-column:span 2;padding:30px;text-align:center;color:var(--muted)">No service recipes found.</div>';
    return;
  }

  grid.innerHTML = svcs.map(s => {
    const name = s.name || s[0];
    const code = s.code || s[1];
    const stages = s.stages || s[2] || [];
    const deliv = s.deliverables || s[3] || 'Deliverables';

    const stagePills = Array.isArray(stages) 
      ? stages.map(st => `<span class="pill" style="font-size:11px">${escHtml(st)}</span>`).join(' ')
      : '';

    const isOwner = JMOS_STATE.currentUser && JMOS_STATE.currentUser.role === 'owner';
    const delBtn = (isOwner && s.id) ? `<button class="linkbtn" style="color:var(--red);font-size:11px" data-del-service="${s.id}">Delete</button>` : '';

    return `
      <div class="card" style="padding:18px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <b style="font-size:15px;color:var(--ink)">${escHtml(name)}</b>
          <div style="display:flex;align-items:center;gap:8px">
            <span class="badge" style="background:var(--panel-2);color:var(--muted)">${escHtml(code)}</span>
            ${delBtn}
          </div>
        </div>
        <div style="font-size:12px;color:var(--muted);margin-bottom:10px">
          <b>Deliverables:</b> ${escHtml(deliv)}
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:5px">
          ${stagePills}
        </div>
      </div>
    `;
  }).join('');
}

// Master Render All Active Views
function renderAllViews() {
  renderDashboard();
  renderClientsTable();
  renderPipeline();
  renderProjectsTable();
  renderTasks();
  renderInvoices();
  renderExpenses();
  recomputeFinance();
  renderPeople();
  renderServices();
  if (typeof renderDashboardCalendar === 'function') renderDashboardCalendar();
  if (typeof renderFullCalendar === 'function') renderFullCalendar();
}

// Task Stage Advancing Event Delegation
document.addEventListener('click', async (e) => {
  const moveTaskBtn = e.target.closest('[data-move-task]');
  if (moveTaskBtn) {
    const taskId = moveTaskBtn.getAttribute('data-move-task');
    const newStage = moveTaskBtn.getAttribute('data-to-stage');
    moveTaskBtn.disabled = true;

    try {
      await JMOS_API.put(`/tasks/${taskId}`, { stage: newStage });
      showToast('Task updated', `Moved to ${newStage.replace('_', ' ')}`);
      await JMOS_API.fetchAll();
      renderAllViews();
    } catch (err) {
      alert('Failed to update task: ' + err.message);
    }
  }

  const delSvcBtn = e.target.closest('[data-del-service]');
  if (delSvcBtn) {
    const svcId = delSvcBtn.getAttribute('data-del-service');
    if (confirm('Delete this service recipe from database?')) {
      delSvcBtn.disabled = true;
      try {
        await JMOS_API.delete(`/services/${svcId}`);
        showToast('Service deleted', 'Recipe removed from database');
        await JMOS_API.fetchAll();
        renderAllViews();
      } catch (err) {
        showToast('Delete failed', err.message, true);
      }
    }
  }

  if (e.target.closest('#dashQuickActionBtn') || e.target.closest('#quickAddDeal')) {
    openModal('dealModal');
  }

  // Pipeline Kanban card click -> open deal detail modal
  const dealCard = e.target.closest('.tcard[data-deal-id]');
  if (dealCard && !e.target.closest('[data-win-deal-id]')) {
    const dealId = dealCard.getAttribute('data-deal-id');
    window.openDealDetailModal(dealId);
  }

  // Pipeline stage stepper click in dealDetailModal -> immediate stage move
  const stageStepBtn = e.target.closest('#ddStageStepper .stage-step-btn');
  if (stageStepBtn) {
    const newStage = stageStepBtn.getAttribute('data-stage');
    const dealId = document.getElementById('ddDealId')?.value;
    if (!dealId || !newStage) return;

    if (newStage === 'won') {
      closeModal('dealDetailModal');
      triggerWinDeal(dealId);
      return;
    }

    try {
      stageStepBtn.disabled = true;
      await JMOS_API.put(`/deals/${dealId}`, { stage: newStage });
      document.getElementById('ddActiveStage').value = newStage;
      const stageBadge = document.getElementById('ddCurrentStageBadge');
      if (stageBadge) stageBadge.textContent = 'Stage: ' + newStage.toUpperCase();
      window.updateDealStepperActiveState(newStage);

      showToast('Stage Updated', `Moved deal to ${newStage.charAt(0).toUpperCase() + newStage.slice(1)}`);
      
      // Update local state and re-render board
      const deal = (JMOS_STATE.pipeline || []).find(d => String(d.id) === String(dealId));
      if (deal) deal.stage = newStage;
      renderPipeline();
    } catch (err) {
      showToast('Error', err.message, true);
    } finally {
      stageStepBtn.disabled = false;
    }
  }
});

// Deal Detail Modal Controller
window.openDealDetailModal = function(dealId) {
  const deals = JMOS_STATE.pipeline || [];
  const deal = deals.find(d => String(d.id) === String(dealId));
  if (!deal) return;

  const idEl = document.getElementById('ddDealId');
  const stageEl = document.getElementById('ddActiveStage');
  const titleEl = document.getElementById('ddTitle');
  const clientEl = document.getElementById('ddClient');
  const valEl = document.getElementById('ddValue');
  const notesEl = document.getElementById('ddNotes');
  const titleHeader = document.getElementById('dealDetailTitle');
  const valBadge = document.getElementById('ddValueBadge');
  const stageBadge = document.getElementById('ddCurrentStageBadge');

  if (idEl) idEl.value = deal.id;
  if (stageEl) stageEl.value = deal.stage || 'lead';
  if (titleEl) titleEl.value = deal.title || '';
  if (clientEl) clientEl.value = deal.client_name || '';
  if (valEl) valEl.value = deal.value || '';
  if (notesEl) notesEl.value = deal.meta_text || '';

  if (titleHeader) titleHeader.textContent = deal.title || 'Deal Details';
  if (valBadge) valBadge.textContent = fmt(deal.value);
  if (stageBadge) stageBadge.textContent = 'Stage: ' + (deal.stage || 'lead').toUpperCase();

  window.updateDealStepperActiveState(deal.stage || 'lead');
  openModal('dealDetailModal');
};

window.updateDealStepperActiveState = function(activeStage) {
  document.querySelectorAll('#ddStageStepper .stage-step-btn').forEach(btn => {
    if (btn.getAttribute('data-stage') === activeStage) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
};

// Save Deal Changes from Modal
document.getElementById('ddSaveBtn')?.addEventListener('click', async () => {
  const dealId = document.getElementById('ddDealId')?.value;
  const title = document.getElementById('ddTitle')?.value.trim();
  const client_name = document.getElementById('ddClient')?.value.trim();
  const value = parseFloat(document.getElementById('ddValue')?.value) || 0;
  const meta_text = document.getElementById('ddNotes')?.value.trim();

  if (!title || !client_name) {
    showToast('Validation Error', 'Deal Title and Client are required', true);
    return;
  }

  try {
    const saveBtn = document.getElementById('ddSaveBtn');
    if (saveBtn) saveBtn.disabled = true;

    await JMOS_API.put(`/deals/${dealId}`, {
      title,
      client_name,
      value,
      meta_text
    });

    showToast('Deal Updated', 'Changes saved to pipeline');
    closeModal('dealDetailModal');

    // Refresh state and pipeline
    const deals = await JMOS_API.get('/deals');
    if (Array.isArray(deals)) {
      JMOS_STATE.pipeline = deals;
      renderPipeline();
    }
  } catch (err) {
    showToast('Error', err.message, true);
  } finally {
    const saveBtn = document.getElementById('ddSaveBtn');
    if (saveBtn) saveBtn.disabled = false;
  }
});

// Delete Deal from Pipeline
document.getElementById('ddDeleteBtn')?.addEventListener('click', async () => {
  const dealId = document.getElementById('ddDealId')?.value;
  const title = document.getElementById('ddTitle')?.value || 'this deal';

  const confirmed = typeof window.showConfirmDialog === 'function'
    ? await window.showConfirmDialog({
        title: 'Delete Pipeline Deal?',
        message: `Are you sure you want to delete deal <b>${title}</b>? This action cannot be undone.`,
        confirmText: 'Delete Deal',
        isDanger: true
      })
    : confirm(`Delete deal "${title}"?`);

  if (confirmed) {
    try {
      await JMOS_API.delete(`/deals/${dealId}`);
      showToast('Deal Deleted', `"${title}" removed from pipeline`);
      closeModal('dealDetailModal');

      const deals = await JMOS_API.get('/deals');
      if (Array.isArray(deals)) {
        JMOS_STATE.pipeline = deals;
        renderPipeline();
      }
    } catch (err) {
      showToast('Error', err.message, true);
    }
  }
});

/* ==========================================================================
   JMOS — Audit Trail & System Activity Feed (Admin & IT Managers)
   ========================================================================== */
window.JMOS_AUDIT = {
  items: [],
  currentFilter: 'all',
  searchQuery: '',
};

window.fetchAuditLogs = async function(filter = null) {
  if (!JMOS_STATE.currentUser) return;
  const role = JMOS_STATE.currentUser.role;
  if (!['owner', 'admin', 'manager'].includes(role)) return;

  const card = document.getElementById('dashAuditTrailCard');
  if (!card) return;

  if (filter !== null) {
    window.JMOS_AUDIT.currentFilter = filter;
  }

  const tbody = document.getElementById('dashAuditLogsBody');
  if (tbody && (!window.JMOS_AUDIT.items || !window.JMOS_AUDIT.items.length)) {
    tbody.innerHTML = '<tr><td colspan="6" style="padding:24px;text-align:center;color:var(--muted)">Loading system audit records…</td></tr>';
  }

  try {
    let url = '/audit-logs';
    if (window.JMOS_AUDIT.currentFilter && window.JMOS_AUDIT.currentFilter !== 'all') {
      url += `?action=${encodeURIComponent(window.JMOS_AUDIT.currentFilter)}`;
    }
    const res = await JMOS_API.get(url);
    if (res && res.status === 'success') {
      window.JMOS_AUDIT.items = res.data || [];
      if (res.stats) {
        updateAuditStats(res.stats);
      }
      applyAuditFilters();
    }
  } catch (err) {
    console.warn('Failed to load audit logs:', err);
    if (tbody) {
      tbody.innerHTML = `<tr><td colspan="6" style="padding:20px;text-align:center;color:var(--red)">Failed to load audit trail: ${escHtml(err.message)}</td></tr>`;
    }
  }
};

function updateAuditStats(stats) {
  const badge = document.getElementById('dashAuditTotalBadge');
  if (badge) badge.textContent = `${stats.total || 0} events`;

  const stToday = document.getElementById('dashAuditStatToday');
  const stCreates = document.getElementById('dashAuditStatCreates');
  const stUpdates = document.getElementById('dashAuditStatUpdates');
  const stDeletes = document.getElementById('dashAuditStatDeletes');
  const stAuth = document.getElementById('dashAuditStatAuth');
  const stSystem = document.getElementById('dashAuditStatSystem');

  if (stToday) stToday.textContent = stats.today ?? 0;
  if (stCreates) stCreates.textContent = stats.creates ?? 0;
  if (stUpdates) stUpdates.textContent = stats.updates ?? 0;
  if (stDeletes) stDeletes.textContent = stats.deletes ?? 0;
  if (stAuth) stAuth.textContent = stats.auth ?? 0;
  if (stSystem) stSystem.textContent = stats.system ?? 0;
}

window.filterAuditLogs = function(filter) {
  window.JMOS_AUDIT.currentFilter = filter;
  document.querySelectorAll('.audit-filter-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-audit-filter') === filter);
    if (btn.getAttribute('data-audit-filter') === filter) {
      btn.style.fontWeight = '600';
    } else {
      btn.style.fontWeight = '400';
    }
  });
  window.fetchAuditLogs(filter);
};

window.handleAuditSearch = function(query) {
  window.JMOS_AUDIT.searchQuery = (query || '').toLowerCase().trim();
  applyAuditFilters();
};

function applyAuditFilters() {
  let list = window.JMOS_AUDIT.items || [];
  const q = window.JMOS_AUDIT.searchQuery;
  if (q) {
    list = list.filter(item => {
      return (item.description || '').toLowerCase().includes(q)
        || (item.user_name || '').toLowerCase().includes(q)
        || (item.entity_type || '').toLowerCase().includes(q)
        || (item.action || '').toLowerCase().includes(q)
        || (item.ip_address || '').toLowerCase().includes(q);
    });
  }
  renderAuditLogsTable(list);
}

function renderAuditLogsTable(list) {
  const tbody = document.getElementById('dashAuditLogsBody');
  if (!tbody) return;

  if (!list.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="padding:28px;text-align:center;color:var(--muted)">No audit events recorded for this view.</td></tr>';
    return;
  }

  const actionStyles = {
    CREATE: 'background:rgba(43,138,90,0.12);color:var(--green);border:1px solid rgba(43,138,90,0.25)',
    UPDATE: 'background:rgba(2,132,199,0.12);color:var(--blue);border:1px solid rgba(2,132,199,0.25)',
    DELETE: 'background:rgba(197,37,35,0.12);color:var(--red);border:1px solid rgba(197,37,35,0.25)',
    AUTH: 'background:rgba(139,92,246,0.12);color:#8b5cf6;border:1px solid rgba(139,92,246,0.25)',
    SYSTEM: 'background:rgba(217,119,6,0.12);color:var(--amber);border:1px solid rgba(217,119,6,0.25)'
  };

  tbody.innerHTML = list.map(log => {
    const act = (log.action || 'SYSTEM').toUpperCase();
    const style = actionStyles[act] || actionStyles.SYSTEM;
    const initials = log.user_name ? log.user_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'SY';
    const roleBadge = log.user_role ? `<span style="font-size:10px;color:var(--muted);font-weight:400">(${escHtml(log.user_role)})</span>` : '';
    const dateObj = new Date(log.created_at);
    const timeFormatted = typeof formatRelativeTime === 'function' ? formatRelativeTime(log.created_at) : log.created_at;

    return `
      <tr>
        <td>
          <span class="badge" style="font-size:10px;font-weight:700;letter-spacing:0.3px;padding:2px 7px;border-radius:6px;display:inline-block;${style}">
            ${escHtml(act)}
          </span>
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:6px">
            <span style="width:22px;height:22px;border-radius:50%;background:var(--paper);border:1px solid var(--line);font-size:9.5px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;color:var(--ink)">
              ${initials}
            </span>
            <span style="font-weight:600;color:var(--ink)">${escHtml(log.user_name)}</span>
            ${roleBadge}
          </div>
        </td>
        <td style="color:var(--ink);line-height:1.45">
          ${escHtml(log.description)}
        </td>
        <td>
          <span class="badge" style="font-size:11px;padding:2px 7px;background:var(--paper);border:1px solid var(--line);color:var(--muted)">
            ${escHtml(log.entity_type || 'System')}
          </span>
        </td>
        <td class="mono" style="font-size:11px;color:var(--muted)">
          ${escHtml(log.ip_address || '127.0.0.1')}
        </td>
        <td style="text-align:right;white-space:nowrap;color:var(--muted);font-size:11px" title="${dateObj.toLocaleString()}">
          ${timeFormatted}
        </td>
      </tr>
    `;
  }).join('');
}

/* ==========================================================================
   JMOS — Progressive Web App (PWA) & Application Installation Manager
   ========================================================================== */
window.JMOS_PWA = {
  deferredPrompt: null,
  isInstalled: false,
  isIos: /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream,
  isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,

  init() {
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js')
        .then(reg => {
          console.log('JMOS Service Worker active:', reg.scope);
        })
        .catch(err => {
          console.warn('JMOS Service Worker registration failed:', err);
        });
    }

    // 2. Capture install prompt on Chromium, Edge & Android
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
      this.updateInstallButtons(true);
    });

    // 3. Detect when app is successfully installed
    window.addEventListener('appinstalled', () => {
      this.isInstalled = true;
      this.deferredPrompt = null;
      this.updateInstallButtons(false, true);
      if (typeof showToast === 'function') {
        showToast('App Installed 🎉', 'JMOS is now installed on your device!');
      }
      if (window.JMOS_API && window.JMOS_API.post) {
        window.JMOS_API.post('/audit-logs', {
          action: 'SYSTEM',
          description: 'JMOS application installed on device (' + (this.isIos ? 'iOS' : (navigator.userAgent.includes('Android') ? 'Android' : 'PC/Mac')) + ')',
          entity_type: 'System'
        }).catch(() => {});
      }
    });

    // Standalone check
    if (this.isStandalone) {
      this.isInstalled = true;
      this.updateInstallButtons(false, true);
    }
  },

  async triggerInstall() {
    if (this.isStandalone || this.isInstalled) {
      if (typeof showToast === 'function') {
        showToast('Already Installed', 'JMOS is already installed and running as an application.');
      }
      return;
    }

    if (this.deferredPrompt) {
      try {
        this.deferredPrompt.prompt();
        const choice = await this.deferredPrompt.userChoice;
        if (choice.outcome === 'accepted') {
          this.isInstalled = true;
          this.deferredPrompt = null;
          this.updateInstallButtons(false, true);
        }
      } catch (err) {
        console.warn('Install prompt error:', err);
      }
      return;
    }

    if (typeof openModal === 'function') {
      openModal('pwaInstallModal');
    }
  },

  updateInstallButtons(canInstall = false, isInstalled = false) {
    const btns = document.querySelectorAll('.pwa-install-btn');
    const badges = document.querySelectorAll('.pwa-status-badge');

    btns.forEach(b => {
      if (isInstalled || this.isStandalone) {
        b.innerHTML = `
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--green)"><polyline points="20 6 9 17 4 12"/></svg>
          <span>App Installed</span>
        `;
        b.disabled = true;
        b.style.opacity = '0.85';
      } else {
        b.disabled = false;
        b.style.opacity = '1';
      }
    });

    badges.forEach(badge => {
      if (isInstalled || this.isStandalone) {
        badge.textContent = 'Installed (Standalone App)';
        badge.style.background = 'rgba(43,138,90,0.15)';
        badge.style.color = 'var(--green)';
      } else if (canInstall) {
        badge.textContent = 'Ready to Install';
        badge.style.background = 'rgba(2,132,199,0.15)';
        badge.style.color = 'var(--blue)';
      } else {
        badge.textContent = 'Available via Browser Menu';
        badge.style.background = 'rgba(217,119,6,0.15)';
        badge.style.color = 'var(--amber)';
      }
    });
  }
};

window.triggerDownloadApp = function() {
  window.JMOS_PWA.triggerInstall();
};

// App Master Bootloader
document.addEventListener('DOMContentLoaded', async () => {
  initAuth();
  initNavigation();
  initFinance();
  initPeople();
  if (typeof initCalendar === 'function') initCalendar();
  if (typeof initChat === 'function') initChat();
  if (typeof initSettings === 'function') initSettings();
  initModals();

  // Initialize Progressive Web App services
  window.JMOS_PWA.init();

  // Load database state
  await JMOS_API.fetchAll();
  renderAllViews();
});

