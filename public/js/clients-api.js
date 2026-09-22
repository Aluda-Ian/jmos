/* ==========================================================================
   JMOS — Live Client & Project Database Integration (Laravel API)
   ========================================================================== */

function getProjectStatusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'on track' || s === 'completed') return 'tint-green';
  if (s.includes('blocked') || s.includes('overdue') || s.includes('at risk')) return 'tint-alert';
  return 'tint-amber';
}

// 1. Clients View
function renderClientsTable() {
  const body = document.getElementById('clientsBody');
  if (!body) return;

  const list = JMOS_STATE.clients || [];
  if (!list.length) {
    body.innerHTML = '<tr><td colspan="9" style="padding:30px;text-align:center;color:var(--muted)"><b style="color:var(--ink)">No clients found in database.</b><br>Click “Add client” to record your first client.</td></tr>';
    return;
  }

  const canDelete = JMOS_STATE.currentUser && (JMOS_STATE.currentUser.role === 'owner');

  body.innerHTML = list.map(c => {
    const nm = (c.client_name || '').toString();
    const ini = nm.trim().split(/\s+/).map(w => w[0] || '').slice(0, 2).join('').toUpperCase();
    
    // Action buttons inside row
    let actionsHtml = `
      <div class="row-actions-wrap">
        <button type="button" class="row-action-btn" title="Add project for ${escHtml(nm)}" onclick="openProjectForClient('${escHtml(nm)}')">
          <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Project
        </button>
        <button type="button" class="row-action-btn" title="Draft invoice for ${escHtml(nm)}" onclick="openInvoiceForClient('${escHtml(nm)}')">
          <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>Invoice
        </button>
        ${canDelete ? `
          <button type="button" class="row-action-btn danger" title="Delete client record" data-del-client="${c.id}" data-client-name="${escHtml(nm)}">
            <svg viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete
          </button>
        ` : ''}
      </div>
    `;

    return `<tr data-client-id="${c.id}" class="clickable-client-row" style="cursor:pointer" title="Click to view client workspace and projects">
      <td><div class="cn"><span class="lg" style="background:var(--red)">${escHtml(ini)}</span><span style="font-weight:600;color:var(--ink)">${escHtml(nm)}</span></div></td>
      <td>${escHtml(c.client_type || 'Corporate')}</td>
      <td>${escHtml(c.contact_person || '—')}</td>
      <td>${escHtml(c.owner || '—')}</td>
      <td><span class="badge" style="font-size:11px;background:var(--panel-2);color:var(--ink);font-weight:600;padding:2px 8px;border-radius:10px">${escHtml(c.projects || 0)}</span></td>
      <td>${escHtml(c.service || '—')}</td>
      <td><span class="pill tint-green">${escHtml(c.project_status || 'Active')}</span></td>
      <td class="mono">${fmt(c.project_value)}</td>
      <td>${actionsHtml}</td>
    </tr>`;
  }).join('');
}

async function ensureClients() {
  const body = document.getElementById('clientsBody');
  if (body && (!JMOS_STATE.clients || !JMOS_STATE.clients.length)) {
    body.innerHTML = '<tr><td colspan="9" style="padding:26px;text-align:center;color:var(--muted)">Loading clients from database…</td></tr>';
  }
  try {
    const clients = await JMOS_API.get('/clients');
    if (Array.isArray(clients)) {
      JMOS_STATE.clients = clients;
      renderClientsTable();
    }
  } catch (err) {
    console.error('Error fetching clients:', err);
    renderClientsTable();
  }
}

// Helpers for quick client row actions
window.openProjectForClient = function(clientName) {
  openModal('projectModal');
  setTimeout(() => {
    const select = document.getElementById('modalProjectClientSelect');
    const hidden = document.getElementById('modalProjectClientHidden');
    if (select) select.value = clientName;
    if (hidden) hidden.value = clientName;
  }, 50);
};

window.openInvoiceForClient = function(clientName) {
  openModal('invoiceModal');
  setTimeout(() => {
    const select = document.getElementById('modalInvoiceClientSelect');
    const hidden = document.getElementById('modalInvoiceClientHidden');
    if (select) select.value = clientName;
    if (hidden) hidden.value = clientName;
  }, 50);
};

// Helper to identify internal projects (e.g. JMOS system development)
function isProjectInternal(p) {
  if (!p) return false;
  if (p.is_internal) return true;
  if (p.category === 'internal') return true;
  const t = (p.project_type || '').toLowerCase();
  const c = (p.client || '').toLowerCase();
  return t.includes('internal') || t.includes('system') || t.includes('jmos') || c.includes('internal');
}

// 2. Projects View
function renderProjectsTable() {
  const body = document.getElementById('projectsBody');
  if (!body) return;

  const allList = JMOS_STATE.projects || [];
  
  const isDev = p => p.category === 'development' || (p.project_type || '').toLowerCase().includes('development') || (p.project_type || '').toLowerCase().includes('system') || (p.project_type || '').toLowerCase().includes('jmos');
  const isDesign = p => p.category === 'graphic_design' || (p.project_type || '').toLowerCase().includes('design') || (p.project_type || '').toLowerCase().includes('branding') || (p.project_type || '').toLowerCase().includes('ui/ux') || (p.project_type || '').toLowerCase().includes('motion');
  const isContent = p => p.category === 'content_calendar' || (p.project_type || '').toLowerCase().includes('content') || (p.project_type || '').toLowerCase().includes('calendar') || (p.project_type || '').toLowerCase().includes('social media');
  const isVideo = p => p.category === 'video_production' || (!isProjectInternal(p) && !isDev(p) && !isDesign(p) && !isContent(p));
  const isInt = p => isProjectInternal(p);
  const isClient = p => !isProjectInternal(p);

  // Update Category Tab Counters
  const countAll = document.getElementById('projFilterAllCount');
  if (countAll) countAll.textContent = allList.length;
  const countDev = document.getElementById('projFilterDevCount');
  if (countDev) countDev.textContent = allList.filter(isDev).length;
  const countDesign = document.getElementById('projFilterDesignCount');
  if (countDesign) countDesign.textContent = allList.filter(isDesign).length;
  const countContent = document.getElementById('projFilterContentCount');
  if (countContent) countContent.textContent = allList.filter(isContent).length;
  const countVideo = document.getElementById('projFilterVideoCount');
  if (countVideo) countVideo.textContent = allList.filter(isVideo).length;
  const countInternal = document.getElementById('projFilterInternalCount');
  if (countInternal) countInternal.textContent = allList.filter(isInt).length;
  const countClient = document.getElementById('projFilterClientCount');
  if (countClient) countClient.textContent = allList.filter(isClient).length;

  // Filter list by selected category tab
  const currentFilter = JMOS_STATE.projectCategoryFilter || 'all';
  const list = allList.filter(p => {
    if (currentFilter === 'development') return isDev(p);
    if (currentFilter === 'graphic_design') return isDesign(p);
    if (currentFilter === 'content_calendar') return isContent(p);
    if (currentFilter === 'video_production') return isVideo(p);
    if (currentFilter === 'internal') return isInt(p);
    if (currentFilter === 'client') return isClient(p);
    return true;
  });

  if (!list.length) {
    let emptyMsg = 'No active projects found in this category.';
    if (currentFilter === 'development') emptyMsg = 'No Development or Software Engineering projects found.';
    else if (currentFilter === 'graphic_design') emptyMsg = 'No Graphic Design or Branding projects found.';
    else if (currentFilter === 'content_calendar') emptyMsg = 'No Content Calendar or Social Media projects found.';
    else if (currentFilter === 'internal') emptyMsg = 'No internal system projects found.';
    else if (currentFilter === 'client') emptyMsg = 'No client deliverable projects found.';
    
    body.innerHTML = `<tr><td colspan="11" style="padding:30px;text-align:center;color:var(--muted)"><b style="color:var(--ink)">${emptyMsg}</b><br>Click “Add project” or “Internal Project” to record work.</td></tr>`;
    return;
  }

  const canDelete = JMOS_STATE.currentUser && (JMOS_STATE.currentUser.role === 'owner');

  body.innerHTML = list.map(p => {
    const st = p.status || 'On track';
    const pName = p.project_name || 'Project';
    const isInternal = isProjectInternal(p);

    // Calculate task completion ratio
    const pTasks = (p.tasks && Array.isArray(p.tasks) && p.tasks.length)
      ? p.tasks
      : ((JMOS_STATE.tasks && Array.isArray(JMOS_STATE.tasks)) ? JMOS_STATE.tasks.filter(t => t.project_id === p.id) : []);
    const totalTasks = pTasks.length;
    const doneTasks = pTasks.filter(t => t.stage === 'done').length;
    const pct = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : (p.progress_pct || 0);

    const isGreen = pct === 100 || (p.status || '').toLowerCase().includes('delivering') || (p.status || '').toLowerCase().includes('completed');
    const colorVar = isGreen ? 'var(--green)' : (pct > 0 ? 'var(--amber)' : 'var(--muted)');
    
    // Action buttons inside row
    let actionsHtml = `
      <div class="row-actions-wrap">
        <button type="button" class="row-action-btn" title="Schedule shoot or review for this project" onclick="event.stopPropagation();openScheduleShootModal('${escHtml(pName)}', '${escHtml(p.client || '')}')">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Shoot
        </button>
        <button type="button" class="row-action-btn" title="Create task for this project" onclick="openTaskModal(${p.id})">
          <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Task
        </button>
        ${canDelete ? `
          <button type="button" class="row-action-btn danger" title="Delete project record" data-del-project="${p.id}" data-project-name="${escHtml(pName)}">
            <svg viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete
          </button>
        ` : ''}
      </div>
    `;

    // Category tag logic
    let catBadge = '';
    const cat = p.category || '';
    if (cat === 'development' || isDev(p)) {
      catBadge = `<span class="badge" style="background:rgba(59,130,246,0.15);color:#3B82F6;font-size:10px;font-weight:600;margin-left:5px">Dev</span>`;
    } else if (cat === 'graphic_design' || isDesign(p)) {
      catBadge = `<span class="badge" style="background:rgba(236,72,153,0.15);color:#EC4899;font-size:10px;font-weight:600;margin-left:5px">Design</span>`;
    } else if (cat === 'content_calendar' || isContent(p)) {
      catBadge = `<span class="badge" style="background:rgba(16,185,129,0.15);color:#10B981;font-size:10px;font-weight:600;margin-left:5px">Content</span>`;
    } else if (isInternal) {
      catBadge = `<span class="badge" style="background:rgba(110,43,138,0.15);color:#8A2BE2;font-size:10px;font-weight:600;margin-left:5px">Internal</span>`;
    }

    return `<tr data-project-id="${p.id}" class="clickable-project-row" style="cursor:pointer" title="Click to open project workspace">
      <td style="font-weight:600">
        <span style="color:var(--ink);font-weight:600;display:inline-flex;align-items:center;gap:6px">
          <span style="color:${isInternal ? '#8A2BE2' : (isDev(p) ? '#3B82F6' : (isDesign(p) ? '#EC4899' : (isContent(p) ? '#10B981' : 'var(--red)')))}">▶</span> ${escHtml(p.project_name)} ${catBadge}
        </span>
      </td>
      <td>${escHtml(p.client)}</td>
      <td><span class="badge" style="font-size:11px;background:${isInternal ? 'rgba(110,43,138,0.12);color:#8A2BE2' : 'var(--panel-2);color:var(--ink)'}">${escHtml(p.project_type || 'Production')}</span></td>
      <td>${escHtml(p.project_manager || '—')}</td>
      <td>${escHtml(p.stage || 'brief')}</td>
      <td><span class="pill ${getProjectStatusClass(p.status)}">${escHtml(st)}</span></td>
      <td>
        <div style="display:flex;align-items:center;gap:8px;min-width:110px">
          <div style="flex:1;height:6px;background:var(--panel-2);border-radius:999px;overflow:hidden">
            <div style="width:${pct}%;height:100%;background:${colorVar};border-radius:999px;transition:width 0.3s ease"></div>
          </div>
          <span class="mono" style="font-size:12px;font-weight:600;min-width:32px;text-align:right">${pct}%</span>
        </div>
        <div style="font-size:10px;color:var(--muted);margin-top:2px">${totalTasks > 0 ? `${doneTasks}/${totalTasks} tasks` : 'No tasks'}</div>
      </td>
      <td>${escHtml(p.priority || 'Medium')}</td>
      <td class="mono" style="color:var(--muted)">${escHtml(p.deadline || '—')}</td>
      <td class="mono">${fmt(p.budget)}</td>
      <td>${actionsHtml}</td>
    </tr>`;
  }).join('');
}

async function ensureProjects() {
  const body = document.getElementById('projectsBody');
  if (body && (!JMOS_STATE.projects || !JMOS_STATE.projects.length)) {
    body.innerHTML = '<tr><td colspan="11" style="padding:26px;text-align:center;color:var(--muted)">Loading projects from database…</td></tr>';
  }
  try {
    const res = await JMOS_API.get('/projects');
    const pList = Array.isArray(res) ? res : (Array.isArray(res?.data) ? res.data : (Array.isArray(res?.projects) ? res.projects : null));
    if (pList) {
      JMOS_STATE.projects = pList;
      renderProjectsTable();
      if (typeof window.populateTaskProjectOptions === 'function') {
        window.populateTaskProjectOptions();
      }
      if (typeof window.populateExpenseProjectOptions === 'function') {
        window.populateExpenseProjectOptions();
      }
    }
  } catch (err) {
    console.error('Error fetching projects:', err);
    renderProjectsTable();
  }
}

// Event Delegation for Delete Client & Delete Project with Custom Modal
document.addEventListener('click', async (e) => {
  const delClientBtn = e.target.closest('[data-del-client]');
  if (delClientBtn) {
    const id = delClientBtn.getAttribute('data-del-client');
    const name = delClientBtn.getAttribute('data-client-name') || 'this client';
    
    const confirmed = typeof window.showConfirmDialog === 'function' 
      ? await window.showConfirmDialog({
          title: 'Delete Client?',
          message: `Are you sure you want to permanently delete <b>${name}</b> from the database? This action cannot be undone.`,
          confirmText: 'Delete Client',
          isDanger: true
        })
      : confirm(`Delete client "${name}" from database?`);

    if (confirmed) {
      try {
        await JMOS_API.delete('/clients/' + id);
        showToast('Client deleted', `"${name}" removed from database`);
        await ensureClients();
      } catch (err) {
        showToast('Error', err.message, true);
      }
    }
  }

  const delProjectBtn = e.target.closest('[data-del-project]');
  if (delProjectBtn) {
    const id = delProjectBtn.getAttribute('data-del-project');
    const name = delProjectBtn.getAttribute('data-project-name') || 'this project';

    const confirmed = typeof window.showConfirmDialog === 'function'
      ? await window.showConfirmDialog({
          title: 'Delete Project?',
          message: `Are you sure you want to delete <b>${name}</b> and archive its records from the database?`,
          confirmText: 'Delete Project',
          isDanger: true
        })
      : confirm(`Delete project "${name}" from database?`);

    if (confirmed) {
      try {
        await JMOS_API.delete('/projects/' + id);
        showToast('Project deleted', `"${name}" removed from database`);
        await ensureProjects();
      } catch (err) {
        showToast('Error', err.message, true);
      }
    }
  }

  const refreshClientsBtn = e.target.closest('#refreshClientsBtn');
  if (refreshClientsBtn) {
    await ensureClients();
    showToast('Clients refreshed', 'Fetched latest from database');
  }

  const refreshProjectsBtn = e.target.closest('#refreshProjectsBtn');
  if (refreshProjectsBtn) {
    await ensureProjects();
    showToast('Projects refreshed', 'Fetched latest from database');
  }

  // Click on project row -> open workspace
  const projRow = e.target.closest('tr[data-project-id]');
  if (projRow && !e.target.closest('.row-actions-wrap') && !e.target.closest('button')) {
    const projId = projRow.getAttribute('data-project-id');
    window.openProjectWorkspace(projId);
  }

  // Click on client row -> open client workspace
  const clientRow = e.target.closest('tr[data-client-id]');
  if (clientRow && !e.target.closest('.row-actions-wrap') && !e.target.closest('button') && !e.target.closest('a')) {
    const clientId = clientRow.getAttribute('data-client-id');
    window.openClientDetailModal(clientId);
  }
});

// Notion-style Project Workspace Controller
window.openProjectWorkspace = async function(projectId) {
  try {
    const project = await JMOS_API.get('/projects/' + projectId);
    if (!project) return;

    document.getElementById('pdmProjectId').value = project.id;
    const nameInput = document.getElementById('pdmNameInput');
    if (nameInput) nameInput.value = project.project_name || '';
    const titleEl = document.getElementById('pdmTitle');
    if (titleEl) titleEl.textContent = project.project_name || 'Project Workspace';

    document.getElementById('pdmClientBadge').textContent = project.client || 'Client';
    document.getElementById('pdmTypeBadge').textContent = project.project_type || 'Production';

    const budgetBadge = document.getElementById('pdmBudgetBadge');
    const budgetInput = document.getElementById('pdmBudgetInput');
    const headerBudgetInput = document.getElementById('pdmHeaderBudgetInput');

    const setBudgetValue = (val) => {
      const numVal = parseFloat(val) || 0;
      if (budgetInput) budgetInput.value = val;
      if (headerBudgetInput) headerBudgetInput.value = val;
      if (budgetBadge) budgetBadge.textContent = fmt(numVal);
    };

    setBudgetValue(project.budget ?? 0);

    if (budgetInput) {
      budgetInput.oninput = () => {
        const val = budgetInput.value;
        if (headerBudgetInput) headerBudgetInput.value = val;
        if (budgetBadge) budgetBadge.textContent = fmt(parseFloat(val) || 0);
      };
    }
    if (headerBudgetInput) {
      headerBudgetInput.oninput = () => {
        const val = headerBudgetInput.value;
        if (budgetInput) budgetInput.value = val;
        if (budgetBadge) budgetBadge.textContent = fmt(parseFloat(val) || 0);
      };
    }

    const isInternal = isProjectInternal(project);
    const cat = project.category || (isInternal ? 'internal' : 'video_production');
    const catBadge = document.getElementById('pdmCategoryBadge');
    if (catBadge) {
      if (cat === 'development') {
        catBadge.textContent = 'Development & Systems';
        catBadge.style.background = 'rgba(59,130,246,0.15)';
        catBadge.style.color = '#3B82F6';
      } else if (cat === 'graphic_design') {
        catBadge.textContent = 'Graphic Design';
        catBadge.style.background = 'rgba(236,72,153,0.15)';
        catBadge.style.color = '#EC4899';
      } else if (cat === 'content_calendar') {
        catBadge.textContent = 'Content Calendar';
        catBadge.style.background = 'rgba(16,185,129,0.15)';
        catBadge.style.color = '#10B981';
      } else if (cat === 'internal') {
        catBadge.textContent = 'Internal & R&D';
        catBadge.style.background = 'rgba(110,43,138,0.15)';
        catBadge.style.color = '#8A2BE2';
      } else if (cat === 'video_production') {
        catBadge.textContent = 'Video Production';
        catBadge.style.background = 'rgba(224,40,38,0.15)';
        catBadge.style.color = 'var(--red)';
      } else {
        catBadge.textContent = cat;
        catBadge.style.background = 'var(--panel-2)';
        catBadge.style.color = 'var(--ink)';
      }
    }

    const catSelect = document.getElementById('pdmCategorySelect');
    const catWrap = document.getElementById('pdmCustomCategoryWrap');
    const catInput = document.getElementById('pdmCustomCategoryInput');
    if (catSelect) {
      const exists = Array.from(catSelect.options).some(o => o.value === cat);
      if (exists) {
        catSelect.value = cat;
        if (catWrap) catWrap.style.display = 'none';
      } else {
        catSelect.value = 'custom';
        if (catWrap) catWrap.style.display = 'block';
        if (catInput) catInput.value = cat;
      }
    }

    // Type Setup
    const pType = project.project_type || '';
    const typeSelect = document.getElementById('pdmTypeSelect');
    const typeWrap = document.getElementById('pdmCustomTypeWrap');
    const typeInput = document.getElementById('pdmCustomTypeInput');
    if (typeSelect) {
      const exists = Array.from(typeSelect.options).some(o => o.value === pType);
      if (exists) {
        typeSelect.value = pType;
        if (typeWrap) typeWrap.style.display = 'none';
      } else if (pType) {
        typeSelect.value = 'custom';
        if (typeWrap) typeWrap.style.display = 'block';
        if (typeInput) typeInput.value = pType;
      } else {
        if (typeWrap) typeWrap.style.display = 'none';
      }
    }

    // Stage Setup
    const pStage = project.stage || 'Brief';
    const stageSelect = document.getElementById('pdmStageSelect');
    const stageWrap = document.getElementById('pdmCustomStageWrap');
    const stageInput = document.getElementById('pdmCustomStageInput');
    if (stageSelect) {
      const exists = Array.from(stageSelect.options).some(o => o.value.toLowerCase() === pStage.toLowerCase());
      if (exists) {
        for (let opt of stageSelect.options) {
          if (opt.value.toLowerCase() === pStage.toLowerCase()) {
            stageSelect.value = opt.value;
            break;
          }
        }
        if (stageWrap) stageWrap.style.display = 'none';
      } else {
        stageSelect.value = 'custom';
        if (stageWrap) stageWrap.style.display = 'block';
        if (stageInput) stageInput.value = pStage;
      }
    }

    // Status Setup
    const pStatus = project.status || 'On track';
    const statusSelect = document.getElementById('pdmStatusSelect');
    const statusWrap = document.getElementById('pdmCustomStatusWrap');
    const statusInput = document.getElementById('pdmCustomStatusInput');
    if (statusSelect) {
      const exists = Array.from(statusSelect.options).some(o => o.value.toLowerCase() === pStatus.toLowerCase());
      if (exists) {
        for (let opt of statusSelect.options) {
          if (opt.value.toLowerCase() === pStatus.toLowerCase()) {
            statusSelect.value = opt.value;
            break;
          }
        }
        if (statusWrap) statusWrap.style.display = 'none';
      } else {
        statusSelect.value = 'custom';
        if (statusWrap) statusWrap.style.display = 'block';
        if (statusInput) statusInput.value = pStatus;
      }
    }

    let dVal = '';
    if (project.deadline) {
      try {
        const dt = new Date(project.deadline);
        dVal = !isNaN(dt.getTime()) ? dt.toISOString().slice(0, 10) : project.deadline;
      } catch (_) {
        dVal = project.deadline;
      }
    }
    const deadlineInput = document.getElementById('pdmDeadlineInput');
    if (deadlineInput) deadlineInput.value = dVal;

    document.getElementById('pdmManagerSelect').value = project.project_manager || 'Barny Kiome';
    document.getElementById('pdmPrioritySelect').value = project.priority || 'Medium';

    // Progress
    const pct = project.progress_pct || 0;
    document.getElementById('pdmProgressPct').textContent = pct + '%';
    document.getElementById('pdmProgressBar').style.width = pct + '%';

    // Links
    const driveLink = project.drive_link || '';
    const briefLink = project.brief_link || '';
    const treatmentLink = project.treatment_link || '';
    const playbookLink = project.playbook_link || '';

    document.getElementById('pdmDriveLink').value = driveLink;
    const driveBtn = document.getElementById('pdmDriveOpenBtn');
    if (driveBtn) {
      if (driveLink) { driveBtn.href = driveLink; driveBtn.style.display = 'inline-flex'; }
      else { driveBtn.style.display = 'none'; }
    }

    document.getElementById('pdmBriefLink').value = briefLink;
    const briefBtn = document.getElementById('pdmBriefOpenBtn');
    if (briefBtn) {
      if (briefLink) { briefBtn.href = briefLink; briefBtn.style.display = 'inline-flex'; }
      else { briefBtn.style.display = 'none'; }
    }

    document.getElementById('pdmTreatmentLink').value = treatmentLink;
    const treatBtn = document.getElementById('pdmTreatmentOpenBtn');
    if (treatBtn) {
      if (treatmentLink) { treatBtn.href = treatmentLink; treatBtn.style.display = 'inline-flex'; }
      else { treatBtn.style.display = 'none'; }
    }

    document.getElementById('pdmPlaybookLink').value = playbookLink;
    const playBtn = document.getElementById('pdmPlaybookOpenBtn');
    if (playBtn) {
      if (playbookLink) { playBtn.href = playbookLink; playBtn.style.display = 'inline-flex'; }
      else { playBtn.style.display = 'none'; }
    }

    document.getElementById('pdmNotes').value = project.notes || '';

    // Render Tasks
    renderProjectTasks(project);

    // Render Comments
    renderProjectComments(project);

    openModal('projectDetailModal');
  } catch (err) {
    showToast('Error opening project', err.message, true);
  }
};

function renderProjectTasks(project) {
  const container = document.getElementById('pdmTasksList');
  const countBadge = document.getElementById('pdmTasksCount');
  if (!container) return;

  const tasks = project.tasks || [];
  if (countBadge) countBadge.textContent = tasks.length;

  if (!tasks.length) {
    container.innerHTML = '<div style="padding:18px;text-align:center;font-size:12px;color:var(--muted)">No tasks attached to this project yet. Click "+ Add Task" above.</div>';
    return;
  }

  const stageLabels = {
    todo: 'To Do',
    in_progress: 'In Progress',
    review_internal: 'Internal Review',
    review_client: 'Client Review',
    done: 'Done'
  };

  const user = JMOS_STATE.currentUser;
  const isOwner = user && (user.role === 'owner' || (Array.isArray(user.permissions) && user.permissions.includes('*')));

  container.innerHTML = tasks.map(t => {
    const isDone = t.stage === 'done';
    const statusBg = isDone ? 'var(--green-soft)' : (t.stage === 'in_progress' ? 'rgba(197,37,35,0.08)' : 'var(--panel-2)');
    const statusCol = isDone ? 'var(--green)' : (t.stage === 'in_progress' ? 'var(--red)' : 'var(--muted)');
    const ini = t.assigned_initials || (t.assigned_to ? t.assigned_to.charAt(0) : 'T');
    const color = t.assigned_color || '#C52523';

    const isAssigner = user && (t.assigned_by_id && (Number(t.assigned_by_id) === Number(user.id)));
    const isAssignee = user && (
      (t.assigned_to_id && Number(t.assigned_to_id) === Number(user.id)) ||
      (t.assigned_to && user.name && (t.assigned_to.toLowerCase().includes(user.name.split(' ')[0].toLowerCase())))
    );
    const isManager = user && (user.role === 'manager' || user.role === 'admin' || (Array.isArray(user.permissions) && user.permissions.includes('tasks.manage')));
    const canToggle = isOwner || isAssigner || isAssignee || isManager;

    const toggleBtn = canToggle
      ? `<button type="button" class="icon-btn-sm" onclick="toggleTaskDoneFromWorkspace(${t.id}, '${t.stage}', ${project.id})" style="border-radius:50%;width:20px;height:20px;padding:0;display:grid;place-items:center;border:1px solid ${isDone ? 'var(--green)' : 'var(--line-strong)'};background:${isDone ? 'var(--green)' : 'transparent'};color:#fff;cursor:pointer" title="${isDone ? 'Mark to do' : 'Mark done'}">
          ${isDone ? '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
        </button>`
      : `<span style="border-radius:50%;width:20px;height:20px;display:grid;place-items:center;border:1px solid var(--line);background:${isDone ? 'var(--green-soft)' : 'var(--panel-2)'};color:${isDone ? 'var(--green)' : 'var(--muted)'};font-size:10px" title="Assigned to ${escHtml(t.assigned_to || 'Team')}">${isDone ? '✓' : '○'}</span>`;

    return `
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--line-soft);gap:10px;background:var(--surface)">
        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
          ${toggleBtn}
          <span onclick="openTaskFromProjectWorkspace(${t.id})" style="font-size:13px;font-weight:500;color:${isDone ? 'var(--muted)' : 'var(--ink)'};${isDone ? 'text-decoration:line-through;' : ''}cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="Click to view task details">${escHtml(t.title)}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
          <span class="badge" style="background:${statusBg};color:${statusCol};font-size:10.5px">${stageLabels[t.stage] || t.stage}</span>
          ${t.due_date ? `<span class="mono" style="font-size:10.5px;color:var(--muted)">${escHtml(t.due_date)}</span>` : ''}
          <span class="av" style="background:${color};width:22px;height:22px;font-size:9.5px" title="${escHtml(t.assigned_to || 'Unassigned')}">${escHtml(ini)}</span>
          <button type="button" class="btn" onclick="openTaskFromProjectWorkspace(${t.id})" style="padding:4px 9px;font-size:11px;gap:4px;background:var(--panel-2);border:1px solid var(--line-strong);border-radius:6px;color:var(--ink);cursor:pointer" title="View task details, deliverable links & discussion">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View
          </button>
        </div>
      </div>
    `;
  }).join('');
}

window.openTaskFromProjectWorkspace = function(taskId) {
  closeModal('projectDetailModal');
  if (typeof window.openTaskDetailModal === 'function') {
    window.openTaskDetailModal(taskId);
  }
};

window.toggleTaskDoneFromWorkspace = async function(taskId, currentStage, projectId) {
  const nextStage = currentStage === 'done' ? 'todo' : 'done';
  try {
    await JMOS_API.put('/tasks/' + taskId, { stage: nextStage });
    showToast('Task updated', nextStage === 'done' ? 'Task marked complete ✓' : 'Task marked to do');
    window.openProjectWorkspace(projectId);
    ensureProjects();
  } catch (err) {
    showToast('Error', err.message, true);
  }
};

function renderProjectComments(project) {
  const stream = document.getElementById('pdmCommentsStream');
  if (!stream) return;

  const comments = project.comments || [];
  if (!comments.length) {
    stream.innerHTML = '<div style="font-size:12px;color:var(--muted);font-style:italic">No comments yet. Start a discussion below.</div>';
    return;
  }

  stream.innerHTML = comments.map(c => `
    <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 10px;background:var(--panel-2);border-radius:8px;border:1px solid var(--line-soft)">
      <div class="av" style="background:var(--red);width:26px;height:26px;font-size:10px;flex-shrink:0">${(c.user_name || 'U').substring(0,2).toUpperCase()}</div>
      <div style="flex:1">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2px">
          <b style="font-size:12px;color:var(--ink)">${escHtml(c.user_name || 'Team Member')}</b>
          <span style="font-size:10.5px;color:var(--muted)">${escHtml(c.created_at || 'Just now')}</span>
        </div>
        <div style="font-size:12.5px;color:var(--ink);line-height:1.4">${escHtml(c.text || '')}</div>
      </div>
    </div>
  `).join('');
}

// Post Comment in Workspace
document.getElementById('pdmPostCommentBtn')?.addEventListener('click', async () => {
  const projId = document.getElementById('pdmProjectId')?.value;
  const input = document.getElementById('pdmCommentInput');
  const text = input ? input.value.trim() : '';
  if (!projId || !text) return;

  try {
    const user = JMOS_STATE.currentUser || { name: 'Ian Aluda' };
    const project = (JMOS_STATE.projects || []).find(p => String(p.id) === String(projId)) || {};
    const comments = Array.isArray(project.comments) ? [...project.comments] : [];

    const newComment = {
      id: Date.now(),
      user_name: user.name || 'Team Member',
      text: text,
      created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };
    comments.push(newComment);

    await JMOS_API.put('/projects/' + projId, { comments });
    input.value = '';
    showToast('Comment posted', 'Saved to project log');
    window.openProjectWorkspace(projId);
  } catch (err) {
    showToast('Error', err.message, true);
  }
});

// Save Project Changes from Workspace
document.getElementById('pdmSaveBtn')?.addEventListener('click', async () => {
  const projId = document.getElementById('pdmProjectId')?.value;
  if (!projId) return;

  const project_name = document.getElementById('pdmNameInput')?.value.trim() || document.getElementById('pdmTitle')?.textContent;
  const budget = parseFloat(document.getElementById('pdmHeaderBudgetInput')?.value || document.getElementById('pdmBudgetInput')?.value) || 0;
  
  const rawCat = document.getElementById('pdmCategorySelect')?.value || 'video_production';
  const category = rawCat === 'custom'
    ? (document.getElementById('pdmCustomCategoryInput')?.value.trim() || 'Custom')
    : rawCat;

  const rawType = document.getElementById('pdmTypeSelect')?.value || 'Brand film';
  const project_type = rawType === 'custom'
    ? (document.getElementById('pdmCustomTypeInput')?.value.trim() || 'Custom Project')
    : rawType;

  const rawStage = document.getElementById('pdmStageSelect')?.value || 'Brief';
  const stage = rawStage === 'custom'
    ? (document.getElementById('pdmCustomStageInput')?.value.trim() || 'Planning')
    : rawStage;

  const rawStatus = document.getElementById('pdmStatusSelect')?.value || 'On track';
  const status = rawStatus === 'custom'
    ? (document.getElementById('pdmCustomStatusInput')?.value.trim() || 'In Progress')
    : rawStatus;

  const deadline = document.getElementById('pdmDeadlineInput')?.value.trim();
  const project_manager = document.getElementById('pdmManagerSelect')?.value;
  const priority = document.getElementById('pdmPrioritySelect')?.value;
  const drive_link = document.getElementById('pdmDriveLink')?.value.trim();
  const brief_link = document.getElementById('pdmBriefLink')?.value.trim();
  const treatment_link = document.getElementById('pdmTreatmentLink')?.value.trim();
  const playbook_link = document.getElementById('pdmPlaybookLink')?.value.trim();
  const notes = document.getElementById('pdmNotes')?.value.trim();

  try {
    const saveBtn = document.getElementById('pdmSaveBtn');
    if (saveBtn) saveBtn.disabled = true;

    await JMOS_API.put('/projects/' + projId, {
      project_name,
      budget,
      category,
      project_type,
      stage,
      deadline,
      status,
      project_manager,
      priority,
      drive_link,
      brief_link,
      treatment_link,
      playbook_link,
      notes
    });

    showToast('Workspace Saved', `Project details & budget (${fmt(budget)}) updated`);
    closeModal('projectDetailModal');
    await ensureProjects();
    if (typeof ensureClients === 'function') {
      await ensureClients();
    }
    if (typeof JMOS_API.fetchAll === 'function') {
      await JMOS_API.fetchAll();
    }
    if (typeof renderAllViews === 'function') {
      renderAllViews();
    }
  } catch (err) {
    showToast('Error saving project', err.message, true);
  } finally {
    const saveBtn = document.getElementById('pdmSaveBtn');
    if (saveBtn) saveBtn.disabled = false;
  }
});

// Delete Project from Workspace
document.getElementById('pdmDeleteBtn')?.addEventListener('click', async () => {
  const projId = document.getElementById('pdmProjectId')?.value;
  const title = document.getElementById('pdmTitle')?.textContent || 'this project';

  const confirmed = typeof window.showConfirmDialog === 'function'
    ? await window.showConfirmDialog({
        title: 'Delete Project?',
        message: `Permanently delete <b>${title}</b> and its tasks from the database?`,
        confirmText: 'Delete Project',
        isDanger: true
      })
    : confirm(`Delete project "${title}"?`);

  if (confirmed) {
    try {
      await JMOS_API.delete('/projects/' + projId);
      showToast('Project deleted', `"${title}" removed`);
      closeModal('projectDetailModal');
      await ensureProjects();
    } catch (err) {
      showToast('Error', err.message, true);
    }
  }
});

// Add Task from Workspace
document.getElementById('pdmAddTaskBtn')?.addEventListener('click', () => {
  const projId = document.getElementById('pdmProjectId')?.value;
  closeModal('projectDetailModal');
  if (typeof openTaskModal === 'function') {
    openTaskModal(projId);
  } else {
    openModal('taskModal');
    const ntProj = document.getElementById('ntProject');
    if (ntProj && projId) ntProj.value = projId;
  }
});

// Click Client Badge inside Project Workspace -> Open Client Workspace
document.getElementById('pdmClientBadge')?.addEventListener('click', () => {
  const clientName = document.getElementById('pdmClientBadge')?.textContent?.trim();
  if (!clientName) return;
  const found = (JMOS_STATE.clients || []).find(c => (c.client_name || '').toLowerCase() === clientName.toLowerCase());
  if (found) {
    closeModal('projectDetailModal');
    window.openClientDetailModal(found.id);
  }
});

// Quick Launcher for Internal System Development Projects
window.openInternalProjectModal = function() {
  openModal('projectModal');
  setTimeout(() => {
    const cat = document.getElementById('npCategory');
    if (cat) {
      cat.value = 'development';
      if (typeof window.onProjectCategoryChange === 'function') {
        window.onProjectCategoryChange('development', 'np');
      }
    }
    const typeSelect = document.getElementById('npType');
    if (typeSelect) {
      typeSelect.value = 'Internal System Development (JMOS / Tech)';
    }
    const clientSel = document.getElementById('npClientSelect');
    const clientHid = document.getElementById('npClient');
    if (clientSel) {
      let found = false;
      for (let opt of clientSel.options) {
        if (opt.value.toLowerCase().includes('internal')) {
          clientSel.value = opt.value;
          if (clientHid) clientHid.value = opt.value;
          found = true;
          break;
        }
      }
      if (!found && clientHid) {
        clientHid.value = 'Jeota Media (Internal)';
      }
    }
    const mgrSelect = document.getElementById('npManager');
    if (mgrSelect) {
      for (let opt of mgrSelect.options) {
        if (opt.value.includes('Ian')) {
          mgrSelect.value = opt.value;
          break;
        }
      }
    }
    const nameInput = document.getElementById('npName');
    if (nameInput) {
      nameInput.placeholder = 'e.g. JMOS Operating System Upgrade';
      nameInput.focus();
    }
  }, 60);
};

// Projects Category Tab Switching
document.addEventListener('click', (e) => {
  const filterBtn = e.target.closest('.project-tab-btn[data-proj-filter]');
  if (filterBtn) {
    const filter = filterBtn.getAttribute('data-proj-filter');
    JMOS_STATE.projectCategoryFilter = filter;

    document.querySelectorAll('.project-tab-btn').forEach(b => {
      if (b === filterBtn) b.classList.add('active');
      else b.classList.remove('active');
    });

    renderProjectsTable();
  }
});

/* ==========================================================================
   Client Workspace & Detail Modal Controller
   ========================================================================== */

function switchCdmTab(tabName) {
  const tabBtns = document.querySelectorAll('.client-tab-btn');
  tabBtns.forEach(btn => {
    if (btn.getAttribute('data-cdm-tab') === tabName) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });

  const panes = {
    projects: document.getElementById('cdmTabPaneProjects'),
    info: document.getElementById('cdmTabPaneInfo'),
    quotes: document.getElementById('cdmTabPaneQuotes'),
    invoices: document.getElementById('cdmTabPaneInvoices'),
    shoots: document.getElementById('cdmTabPaneShoots'),
    statement: document.getElementById('cdmTabPaneStatement'),
  };

  Object.keys(panes).forEach(k => {
    if (panes[k]) {
      panes[k].style.display = (k === tabName) ? 'block' : 'none';
    }
  });
}

// Tab Switch Click Delegation
document.addEventListener('click', (e) => {
  const tabBtn = e.target.closest('.client-tab-btn[data-cdm-tab]');
  if (tabBtn) {
    const tabName = tabBtn.getAttribute('data-cdm-tab');
    switchCdmTab(tabName);
  }
});

function populateClientDetailHeader(client, stats = {}) {
  const cId = document.getElementById('cdmClientId');
  if (cId) cId.value = client.id || '';

  const nm = (client.client_name || '').toString();
  const ini = nm.trim().split(/\s+/).map(w => w[0] || '').slice(0, 2).join('').toUpperCase() || 'CL';

  const avatar = document.getElementById('cdmAvatar');
  if (avatar) avatar.textContent = ini;

  const title = document.getElementById('cdmTitle');
  if (title) title.textContent = nm || 'Client Profile';

  const typeBadge = document.getElementById('cdmTypeBadge');
  if (typeBadge) typeBadge.textContent = client.client_type || 'Corporate';

  const statusBadge = document.getElementById('cdmStatusBadge');
  if (statusBadge) {
    statusBadge.textContent = client.project_status || 'Active';
    const s = (client.project_status || '').toLowerCase();
    statusBadge.className = 'pill ' + (s === 'active' ? 'tint-green' : (s.includes('hold') ? 'tint-amber' : 'tint-blue'));
  }

  const serviceBadge = document.getElementById('cdmServiceBadge');
  if (serviceBadge) serviceBadge.textContent = client.service || 'Creative Production';

  const subtitle = document.getElementById('cdmSubtitle');
  if (subtitle) {
    subtitle.textContent = `Managed by ${client.owner || 'Barny Kiome'} • Contact: ${client.contact_person || '—'}`;
  }

  // Header quick buttons
  const newProjBtn = document.getElementById('cdmNewProjectBtn');
  if (newProjBtn) {
    newProjBtn.onclick = () => {
      closeModal('clientDetailModal');
      openProjectForClient(nm);
    };
  }

  const newQuoteBtn = document.getElementById('cdmNewQuoteBtn');
  if (newQuoteBtn) {
    newQuoteBtn.onclick = () => {
      closeModal('clientDetailModal');
      if (typeof window.openCreateQuoteModal === 'function') {
        window.openCreateQuoteModal(null, client.id);
      } else if (typeof window.JMOS_QUOTES !== 'undefined' && typeof window.JMOS_QUOTES.openCreateModal === 'function') {
        window.JMOS_QUOTES.openCreateModal(null, client.id);
      } else {
        openModal('quoteModal');
      }
    };
  }

  const newInvBtn = document.getElementById('cdmNewInvoiceBtn');
  if (newInvBtn) {
    newInvBtn.onclick = () => {
      closeModal('clientDetailModal');
      openInvoiceForClient(nm);
    };
  }

  const shootBtn = document.getElementById('cdmScheduleShootBtn');
  if (shootBtn) {
    shootBtn.onclick = () => {
      closeModal('clientDetailModal');
      if (typeof openScheduleShootModal === 'function') {
        openScheduleShootModal('', nm);
      }
    };
  }

  const tabProjBtn = document.getElementById('cdmAddProjectFromTabBtn');
  if (tabProjBtn) {
    tabProjBtn.onclick = () => {
      closeModal('clientDetailModal');
      openProjectForClient(nm);
    };
  }

  const tabQuoteBtn = document.getElementById('cdmAddQuoteFromTabBtn');
  if (tabQuoteBtn) {
    tabQuoteBtn.onclick = () => {
      closeModal('clientDetailModal');
      if (typeof window.openCreateQuoteModal === 'function') {
        window.openCreateQuoteModal(null, client.id);
      } else if (typeof window.JMOS_QUOTES !== 'undefined' && typeof window.JMOS_QUOTES.openCreateModal === 'function') {
        window.JMOS_QUOTES.openCreateModal(null, client.id);
      } else {
        openModal('quoteModal');
      }
    };
  }

  const tabInvBtn = document.getElementById('cdmAddInvoiceFromTabBtn');
  if (tabInvBtn) {
    tabInvBtn.onclick = () => {
      closeModal('clientDetailModal');
      openInvoiceForClient(nm);
    };
  }

  const tabShootBtn = document.getElementById('cdmAddShootFromTabBtn');
  if (tabShootBtn) {
    tabShootBtn.onclick = () => {
      closeModal('clientDetailModal');
      if (typeof openScheduleShootModal === 'function') {
        openScheduleShootModal('', nm);
      }
    };
  }

  // Key stats
  const statAct = document.getElementById('cdmStatActiveProjects');
  if (statAct) statAct.textContent = stats.active_projects ?? (client.projects || 0);

  const statTot = document.getElementById('cdmStatTotalProjects');
  if (statTot) statTot.textContent = `active / ${stats.total_projects ?? (client.projects || 0)} total`;

  const statVal = document.getElementById('cdmStatTotalValue');
  if (statVal) statVal.textContent = fmt(stats.total_project_value ?? (client.project_value || 0));

  const statInv = document.getElementById('cdmStatInvoiced');
  if (statInv) statInv.textContent = fmt(stats.total_invoiced ?? 0);

  const statPaid = document.getElementById('cdmStatPaid');
  if (statPaid) statPaid.textContent = `${fmt(stats.total_paid ?? 0)} collected`;

  const statOwner = document.getElementById('cdmStatOwner');
  if (statOwner) statOwner.textContent = client.owner || 'Barny Kiome';
}

function populateClientProjectsTab(client, projects = []) {
  const countBadge = document.getElementById('cdmTabProjectsCount');
  if (countBadge) countBadge.textContent = projects.length;

  const listEl = document.getElementById('cdmProjectsList');
  if (!listEl) return;

  if (!projects.length) {
    listEl.innerHTML = `
      <div style="background:var(--panel-2);border:1px dashed var(--line);border-radius:10px;padding:32px 20px;text-align:center">
        <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">No projects recorded yet</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Spin up a commercial, brand film, documentary or campaign for ${escHtml(client.client_name || 'this client')}.</p>
        <button type="button" class="btn primary" onclick="closeModal('clientDetailModal');openProjectForClient('${escHtml(client.client_name || '')}')">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 5v14M5 12h14"/></svg>Add First Live Project
        </button>
      </div>
    `;
    return;
  }

  listEl.innerHTML = projects.map(p => {
    const st = p.status || 'On track';
    const pTasks = p.tasks || [];
    const totalTasks = pTasks.length;
    const doneTasks = pTasks.filter(t => t.stage === 'done').length;
    const pct = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : (p.progress_pct || 0);

    const isGreen = pct === 100 || (p.status || '').toLowerCase().includes('delivering') || (p.status || '').toLowerCase().includes('completed');
    const colorVar = isGreen ? 'var(--green)' : (pct > 0 ? 'var(--amber)' : 'var(--muted)');

    return `
      <div class="client-card-item" style="cursor:pointer" onclick="closeModal('clientDetailModal');openProjectWorkspace(${p.id})" title="Click to open project workspace">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div style="flex:1;min-width:220px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
              <span class="pill ${getProjectStatusClass(p.status)}" style="font-size:10.5px">${escHtml(st)}</span>
              <span class="badge" style="font-size:10.5px;background:var(--surface);color:var(--muted)">${escHtml(p.project_type || 'Production')}</span>
              <span style="font-size:11px;color:var(--muted)">Lead: ${escHtml(p.project_manager || '—')}</span>
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:6px">
              <span style="color:var(--red);font-size:12px">▶</span> ${escHtml(p.project_name)}
            </div>
          </div>

          <div style="text-align:right">
            <div class="mono" style="font-size:14px;font-weight:700;color:var(--ink)">${fmt(p.budget || 0)}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:2px">Deadline: <span class="mono">${escHtml(p.deadline || 'Flexible')}</span></div>
          </div>
        </div>

        <div style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:14px;border-top:1px solid var(--line-soft);padding-top:8px">
          <div style="flex:1;display:flex;align-items:center;gap:8px">
            <div style="flex:1;max-width:220px;height:6px;background:var(--surface);border-radius:999px;overflow:hidden">
              <div style="width:${pct}%;height:100%;background:${colorVar};border-radius:999px;transition:width 0.3s ease"></div>
            </div>
            <span class="mono" style="font-size:11.5px;font-weight:600;color:var(--ink)">${pct}%</span>
            <span style="font-size:11px;color:var(--muted)">(${totalTasks > 0 ? `${doneTasks}/${totalTasks} tasks` : 'No tasks'})</span>
          </div>

          <button type="button" class="btn" style="padding:3px 8px;font-size:11px" onclick="event.stopPropagation();closeModal('clientDetailModal');openProjectWorkspace(${p.id})">
            Open Workspace ↗
          </button>
        </div>
      </div>
    `;
  }).join('');
}

function populateClientInfoTab(client) {
  const nameInput = document.getElementById('cdmInputName');
  if (nameInput) nameInput.value = client.client_name || '';

  const typeSelect = document.getElementById('cdmSelectType');
  if (typeSelect) typeSelect.value = client.client_type || 'Corporate';

  const contactInput = document.getElementById('cdmInputContact');
  if (contactInput) contactInput.value = client.contact_person || '';

  const ownerSelect = document.getElementById('cdmSelectOwner');
  if (ownerSelect) ownerSelect.value = client.owner || 'Barny Kiome';

  const emailInput = document.getElementById('cdmInputEmail');
  if (emailInput) emailInput.value = client.email || '';

  const emailLink = document.getElementById('cdmEmailLink');
  if (emailLink) {
    if (client.email) {
      emailLink.href = 'mailto:' + client.email;
      emailLink.style.display = 'inline-flex';
    } else {
      emailLink.style.display = 'none';
    }
  }

  const phoneInput = document.getElementById('cdmInputPhone');
  if (phoneInput) phoneInput.value = client.phone || '';

  const phoneLink = document.getElementById('cdmPhoneLink');
  if (phoneLink) {
    if (client.phone) {
      phoneLink.href = 'tel:' + client.phone;
      phoneLink.style.display = 'inline-flex';
    } else {
      phoneLink.style.display = 'none';
    }
  }

  const addressInput = document.getElementById('cdmInputAddress');
  if (addressInput) addressInput.value = client.address || '';

  const websiteInput = document.getElementById('cdmInputWebsite');
  if (websiteInput) websiteInput.value = client.website || '';

  const websiteLink = document.getElementById('cdmWebsiteLink');
  if (websiteLink) {
    if (client.website) {
      websiteLink.href = client.website.startsWith('http') ? client.website : 'https://' + client.website;
      websiteLink.style.display = 'inline-flex';
    } else {
      websiteLink.style.display = 'none';
    }
  }

  const serviceInput = document.getElementById('cdmInputService');
  if (serviceInput) serviceInput.value = client.service || '';

  const statusSelect = document.getElementById('cdmSelectStatus');
  if (statusSelect) statusSelect.value = client.project_status || 'Active';

  const notesInput = document.getElementById('cdmTextareaNotes');
  if (notesInput) notesInput.value = client.notes || '';
}

function populateClientQuotesTab(client, quotes = []) {
  const countBadge = document.getElementById('cdmTabQuotesCount');
  if (countBadge) countBadge.textContent = quotes.length;

  const listEl = document.getElementById('cdmQuotesList');
  if (!listEl) return;

  if (!quotes.length) {
    listEl.innerHTML = `
      <div style="background:var(--panel-2);border:1px dashed var(--line);border-radius:10px;padding:32px 20px;text-align:center">
        <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">No commercial quotations created yet</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Draft a detailed proposal with itemized pricing for ${escHtml(client.client_name || 'this client')}.</p>
        <button type="button" class="btn primary" onclick="closeModal('clientDetailModal');if(typeof window.openCreateQuoteModal==='function'){window.openCreateQuoteModal(null, ${client.id});}else{openModal('quoteModal');}">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>Draft First Quotation
        </button>
      </div>
    `;
    return;
  }

  listEl.innerHTML = quotes.map(q => {
    const st = (q.status || 'Draft').toLowerCase();
    const pillClass = st === 'accepted' || st === 'invoiced' ? 'tint-green' : (st === 'sent' ? 'tint-blue' : (st === 'declined' ? 'tint-alert' : 'tint-amber'));
    const isConverted = st === 'invoiced' || q.converted_invoice_id;

    return `
      <div class="client-card-item">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div style="flex:1;min-width:220px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap">
              <span class="mono" style="font-size:12.5px;font-weight:700;color:var(--ink)">${escHtml(q.quote_number || ('QT-' + q.id))}</span>
              <span class="pill ${pillClass}" style="font-size:10px">${escHtml(q.status || 'Draft')}</span>
              <span style="font-size:11px;color:var(--muted)">Valid ${escHtml(q.validity_days || 14)} days</span>
            </div>
            <div style="font-size:14px;font-weight:700;color:var(--ink)">${escHtml(q.title || 'Production Proposal')}</div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Created ${q.created_at ? new Date(q.created_at).toLocaleDateString() : 'Recently'}</div>
          </div>

          <div style="text-align:right">
            <div class="mono" style="font-size:16px;font-weight:700;color:var(--ink)">${fmt(q.total_amount || 0)}</div>
            ${q.discount_amount > 0 ? `<div style="font-size:10.5px;color:var(--green)">-${fmt(q.discount_amount)} discount</div>` : ''}
          </div>
        </div>

        <div style="margin-top:10px;display:flex;align-items:center;justify-content:flex-end;gap:8px;border-top:1px solid var(--line-soft);padding-top:8px;flex-wrap:wrap">
          <button type="button" class="btn" style="padding:4px 9px;font-size:11px" onclick="closeModal('clientDetailModal');if(typeof window.viewQuoteDetail==='function'){window.viewQuoteDetail(${q.id});}">
            View Proposal ↗
          </button>
          ${!isConverted ? `
            <button type="button" class="btn primary" style="padding:4px 9px;font-size:11px" onclick="closeModal('clientDetailModal');if(typeof window.openUpgradeQuoteModalFromRow==='function'){window.openUpgradeQuoteModalFromRow(${q.id});}">
              Convert to Invoice ➔
            </button>
          ` : `
            <span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-size:10.5px;font-weight:600">Converted to Invoice</span>
          `}
        </div>
      </div>
    `;
  }).join('');
}

function populateClientInvoicesTab(client, invoices = []) {
  const countBadge = document.getElementById('cdmTabInvoicesCount');
  if (countBadge) countBadge.textContent = invoices.length;

  const listEl = document.getElementById('cdmInvoicesList');
  if (!listEl) return;

  if (!invoices.length) {
    listEl.innerHTML = `
      <div style="background:var(--panel-2);border:1px dashed var(--line);border-radius:10px;padding:32px 20px;text-align:center">
        <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">No invoices on record</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Generate a retainer or milestone invoice for ${escHtml(client.client_name || 'this client')}.</p>
        <button type="button" class="btn primary" onclick="closeModal('clientDetailModal');openInvoiceForClient('${escHtml(client.client_name || '')}')">
          <svg viewBox="0 0 24 24" width="13" height="13"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>Draft First Invoice
        </button>
      </div>
    `;
    return;
  }

  listEl.innerHTML = invoices.map(inv => {
    const isPaid = (inv.status || '').toLowerCase() === 'paid';
    const isOverdue = (inv.status || '').toLowerCase().includes('overdue');
    const pillClass = isPaid ? 'tint-green' : (isOverdue ? 'tint-alert' : 'tint-amber');

    return `
      <div class="client-card-item">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
              <span class="mono" style="font-size:13px;font-weight:700;color:var(--ink)">${escHtml(inv.invoice_no || 'Invoice')}</span>
              <span class="pill ${pillClass}" style="font-size:10.5px">${escHtml(inv.status || 'Pending')}</span>
              ${inv.etims ? '<span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-size:10px;font-weight:600">eTIMS Compliant</span>' : ''}
            </div>
            <div style="font-size:12px;color:var(--muted)">${escHtml(inv.type || 'Invoice')} • Due: <span class="mono">${escHtml(inv.due_date || '—')}</span></div>
          </div>

          <div style="text-align:right">
            <div class="mono" style="font-size:15px;font-weight:700;color:var(--ink)">${fmt(inv.amount || 0)}</div>
            <div style="font-size:11px;color:var(--muted);margin-top:2px">${escHtml(inv.method || 'Direct Transfer')}</div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function populateClientShootsTab(client, events = []) {
  const countBadge = document.getElementById('cdmTabShootsCount');
  if (countBadge) countBadge.textContent = events.length;

  const listEl = document.getElementById('cdmShootsList');
  if (!listEl) return;

  if (!events.length) {
    listEl.innerHTML = `
      <div style="background:var(--panel-2);border:1px dashed var(--line);border-radius:10px;padding:32px 20px;text-align:center">
        <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">No shoots scheduled yet</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Reserve camera crew, locations, and studio sessions for ${escHtml(client.client_name || 'this client')}.</p>
        <button type="button" class="btn primary" onclick="closeModal('clientDetailModal');openScheduleShootModal('', '${escHtml(client.client_name || '')}')">
          <svg viewBox="0 0 24 24" width="13" height="13"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Schedule First Shoot
        </button>
      </div>
    `;
    return;
  }

  listEl.innerHTML = events.map(ev => {
    const startStr = ev.start_time ? new Date(ev.start_time).toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Flexible Time';
    return `
      <div class="client-card-item">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
              <span class="badge" style="font-size:10.5px;background:var(--red-soft);color:var(--red);font-weight:600">${escHtml(ev.event_type || 'Shoot')}</span>
              <span style="font-size:14px;font-weight:700;color:var(--ink)">${escHtml(ev.title)}</span>
            </div>
            <div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:8px;margin-top:2px">
              <span>📅 ${escHtml(startStr)}</span>
              ${ev.location ? `<span>📍 ${escHtml(ev.location)}</span>` : ''}
            </div>
          </div>

          <div>
            ${ev.meet_link ? `
              <a href="${escHtml(ev.meet_link)}" target="_blank" class="btn primary" style="font-size:11.5px;padding:5px 10px;text-decoration:none">
                Video Call ↗
              </a>
            ` : ''}
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function populateClientStatementTab(client, ledger = [], stats = {}, quotes = []) {
  const invoiced = stats.total_invoiced ?? 0;
  const paid = stats.total_paid ?? 0;
  const balance = stats.closing_balance ?? stats.total_unpaid ?? (invoiced - paid);
  const quotesTotal = stats.total_quotes ?? (Array.isArray(quotes) ? quotes.reduce((acc, q) => acc + (parseFloat(q.total_amount) || 0), 0) : 0);
  const quotesCount = stats.quotes_count ?? (Array.isArray(quotes) ? quotes.length : 0);

  const invoicedEl = document.getElementById('cdmStmtInvoiced');
  if (invoicedEl) invoicedEl.textContent = fmt(invoiced);

  const invCountEl = document.getElementById('cdmStmtInvoicesCount');
  if (invCountEl) invCountEl.textContent = `${stats.total_projects ? stats.total_projects + ' projects' : 'Live Ledger'}`;

  const collectedEl = document.getElementById('cdmStmtCollected');
  if (collectedEl) collectedEl.textContent = fmt(paid);

  const collectedPctEl = document.getElementById('cdmStmtCollectedPct');
  if (collectedPctEl) {
    const pct = invoiced > 0 ? Math.round((paid / invoiced) * 100) : (paid > 0 ? 100 : 0);
    collectedPctEl.textContent = `${pct}% settled`;
  }

  const balanceEl = document.getElementById('cdmStmtBalance');
  if (balanceEl) {
    balanceEl.textContent = fmt(balance);
    balanceEl.style.color = balance > 0 ? 'var(--red)' : 'var(--ink)';
  }

  const balanceStatusEl = document.getElementById('cdmStmtBalanceStatus');
  if (balanceStatusEl) {
    if (balance <= 0) {
      balanceStatusEl.innerHTML = `<span class="pill tint-green" style="font-size:9.5px">Settled (No Balance)</span>`;
    } else {
      balanceStatusEl.innerHTML = `<span class="pill tint-alert" style="font-size:9.5px">Payment Pending</span>`;
    }
  }

  const quotesEl = document.getElementById('cdmStmtQuotes');
  if (quotesEl) quotesEl.textContent = fmt(quotesTotal);

  const quotesCountEl = document.getElementById('cdmStmtQuotesCount');
  if (quotesCountEl) quotesCountEl.textContent = `${quotesCount} active proposal${quotesCount === 1 ? '' : 's'}`;

  const closingSummaryEl = document.getElementById('cdmStatementClosingSummary');
  if (closingSummaryEl) {
    closingSummaryEl.textContent = `Closing Account Balance: ${fmt(balance)}`;
  }

  // Wires Print Statement
  const printBtn = document.getElementById('cdmPrintStatementBtn');
  if (printBtn) {
    printBtn.onclick = () => {
      window.printClientStatement(client, ledger, { invoiced, paid, balance, quotesTotal });
    };
  }

  // Wires Deep Link to Main Financial Statements
  const openMainBtn = document.getElementById('cdmOpenMainStatementsBtn');
  if (openMainBtn) {
    openMainBtn.onclick = () => {
      closeModal('clientDetailModal');
      if (typeof showView === 'function') {
        showView('statements');
      }
      if (typeof window.switchFinanceTab === 'function') {
        window.switchFinanceTab('accounts');
      }
      const finSearch = document.getElementById('finSearchInput');
      if (finSearch) {
        finSearch.value = client.client_name || '';
        if (typeof window.onFinanceSearchChange === 'function') {
          window.onFinanceSearchChange(client.client_name || '');
        }
      }
    };
  }

  // Render Statement Ledger Table
  const tbody = document.getElementById('cdmStatementTableBody');
  if (!tbody) return;

  if (!ledger || !ledger.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" style="padding:28px 16px;text-align:center;color:var(--muted)">
          No financial ledger entries recorded yet. Generate an invoice or quotation for ${escHtml(client.client_name || 'this client')} to populate the statement.
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = ledger.map(row => {
    const isPayment = (row.type || '').toLowerCase() === 'payment';
    const isInvoice = (row.type || '').toLowerCase() === 'invoice';
    const typePill = isPayment 
      ? '<span class="pill tint-green" style="font-size:10px">Payment</span>'
      : (isInvoice ? '<span class="pill tint-blue" style="font-size:10px">Invoice</span>' : `<span class="pill tint-amber" style="font-size:10px">${escHtml(row.type)}</span>`);

    return `
      <tr style="border-bottom:1px solid var(--line-soft)">
        <td style="padding:9px 12px;color:var(--muted);white-space:nowrap">${escHtml(row.date || '—')}</td>
        <td style="padding:9px 12px;font-weight:600;color:var(--ink)" class="mono">${escHtml(row.ref_no || '—')}</td>
        <td style="padding:9px 12px">${typePill}</td>
        <td style="padding:9px 12px;color:var(--ink);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(row.description || '—')}</td>
        <td style="padding:9px 12px;text-align:right;font-weight:600;color:${row.debit > 0 ? 'var(--ink)' : 'var(--muted)'}" class="mono">${row.debit > 0 ? fmt(row.debit) : '—'}</td>
        <td style="padding:9px 12px;text-align:right;font-weight:600;color:${row.credit > 0 ? 'var(--green)' : 'var(--muted)'}" class="mono">${row.credit > 0 ? fmt(row.credit) : '—'}</td>
        <td style="padding:9px 12px;text-align:right;font-weight:700;color:var(--ink)" class="mono">${fmt(row.balance || 0)}</td>
        <td style="padding:9px 12px;text-align:center">
          <span class="badge" style="font-size:10px;background:var(--panel-2);color:var(--ink);font-weight:600">${escHtml(row.status || 'Posted')}</span>
        </td>
      </tr>
    `;
  }).join('');
}

// Statement Print / Export Generator
window.printClientStatement = function(client, ledger = [], stats = {}) {
  const cName = client.client_name || 'Client';
  const printWindow = window.open('', '_blank', 'width=900,height=700');
  if (!printWindow) {
    if (window.showToast) window.showToast('Popup Blocked', 'Please allow popups to export printable statement', true);
    return;
  }

  const rowsHtml = (ledger && ledger.length) ? ledger.map(r => `
    <tr>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0">${r.date || '—'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;font-family:monospace;font-weight:600">${r.ref_no || '—'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0">${r.type || 'Entry'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0">${r.description || '—'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;font-family:monospace">${r.debit > 0 ? 'KES ' + Number(r.debit).toLocaleString() : '—'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;font-family:monospace;color:#16a34a">${r.credit > 0 ? 'KES ' + Number(r.credit).toLocaleString() : '—'}</td>
      <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;font-family:monospace;font-weight:700">KES ${Number(r.balance || 0).toLocaleString()}</td>
    </tr>
  `).join('') : `<tr><td colspan="7" style="padding:20px;text-align:center;color:#64748b">No statement transactions recorded.</td></tr>`;

  const dateGenerated = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Financial Statement — ${cName} — JMOS</title>
        <style>
          body { font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; color: #0f172a; margin: 40px; }
          .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #C52523; padding-bottom: 20px; margin-bottom: 24px; }
          .logo { font-size: 24px; font-weight: 800; color: #C52523; letter-spacing: -0.5px; }
          .meta-box { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
          .stat-summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
          .stat-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; background: #ffffff; }
          table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 30px; }
          th { background: #f1f5f9; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #475569; }
          .footer { border-top: 1px solid #e2e8f0; padding-top: 16px; font-size: 11.5px; color: #64748b; text-align: center; }
          @media print { body { margin: 0; } }
        </style>
      </head>
      <body>
        <div class="header">
          <div>
            <div class="logo">JEOTA MEDIA</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px">Official Client Financial Statement</div>
          </div>
          <div style="text-align:right;font-size:12px;color:#64748b">
            <div>Date: <b>${dateGenerated}</b></div>
            <div>Ref: <b>STMT-${client.id || 'LIVE'}</b></div>
          </div>
        </div>

        <div class="meta-box">
          <div>
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700">Account Profile</div>
            <div style="font-size:16px;font-weight:700;color:#0f172a;margin-top:2px">${cName}</div>
            <div style="font-size:12.5px;color:#475569;margin-top:2px">${client.contact_person ? 'Contact: ' + client.contact_person : ''} ${client.email ? '• ' + client.email : ''}</div>
            <div style="font-size:12px;color:#64748b">${client.address || 'Nairobi, Kenya'}</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700">Account Lead</div>
            <div style="font-size:14px;font-weight:600;color:#0f172a;margin-top:2px">${client.owner || 'Barny Kiome'}</div>
            <div style="font-size:12px;color:#64748b">Jeota Media Operating System</div>
          </div>
        </div>

        <div class="stat-summary">
          <div class="stat-card">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase">Total Invoiced</div>
            <div style="font-size:18px;font-weight:700;font-family:monospace;margin-top:2px">KES ${Number(stats.invoiced || 0).toLocaleString()}</div>
          </div>
          <div class="stat-card">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase">Total Collected</div>
            <div style="font-size:18px;font-weight:700;font-family:monospace;color:#16a34a;margin-top:2px">KES ${Number(stats.paid || 0).toLocaleString()}</div>
          </div>
          <div class="stat-card">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase">Outstanding Balance</div>
            <div style="font-size:18px;font-weight:700;font-family:monospace;color:#C52523;margin-top:2px">KES ${Number(stats.balance || 0).toLocaleString()}</div>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Ref #</th>
              <th>Type</th>
              <th>Description</th>
              <th style="text-align:right">Debit</th>
              <th style="text-align:right">Credit</th>
              <th style="text-align:right">Balance</th>
            </tr>
          </thead>
          <tbody>
            ${rowsHtml}
          </tbody>
        </table>

        <div class="footer">
          This statement is an official computer-generated record from Jeota Media Operating System (JMOS).<br>
          For queries or invoice reconciliations, contact finance@jeotamedia.com or account lead.
        </div>

        <script>
          window.onload = function() {
            window.print();
          };
        </script>
      </body>
    </html>
  `);
  printWindow.document.close();
};

// Master Client Modal Opener
window.openClientDetailModal = async function(clientId) {
  try {
    switchCdmTab('projects');
    openModal('clientDetailModal');

    // Quick fill from memory while network request completes
    const cachedClient = (JMOS_STATE.clients || []).find(c => String(c.id) === String(clientId));
    if (cachedClient) {
      populateClientDetailHeader(cachedClient);
      populateClientInfoTab(cachedClient);
    }

    const data = await JMOS_API.get('/clients/' + clientId);
    if (!data) return;

    const client = data.client || data;
    const projects = data.projects || [];
    const invoices = data.invoices || [];
    const quotes = data.quotes || [];
    const events = data.events || [];
    const stats = data.stats || {};
    const statementLedger = data.statement_ledger || [];

    populateClientDetailHeader(client, stats);
    populateClientProjectsTab(client, projects);
    populateClientInfoTab(client);
    populateClientQuotesTab(client, quotes);
    populateClientInvoicesTab(client, invoices);
    populateClientShootsTab(client, events);
    populateClientStatementTab(client, statementLedger, stats, quotes);

  } catch (err) {
    console.error('Error opening client detail modal:', err);
    showToast('Failed to load client details', err.message || 'Error', true);
  }
};

// Save Client Profile Changes
document.getElementById('cdmSaveBtn')?.addEventListener('click', async () => {
  const clientId = document.getElementById('cdmClientId')?.value;
  if (!clientId) return;

  const name = document.getElementById('cdmInputName')?.value.trim();
  if (!name) return showToast('Client name required', 'Please specify client name', true);

  const saveBtn = document.getElementById('cdmSaveBtn');
  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving…';
  }

  try {
    const payload = {
      client_name: name,
      client_type: document.getElementById('cdmSelectType')?.value,
      contact_person: document.getElementById('cdmInputContact')?.value.trim(),
      owner: document.getElementById('cdmSelectOwner')?.value,
      email: document.getElementById('cdmInputEmail')?.value.trim() || null,
      phone: document.getElementById('cdmInputPhone')?.value.trim() || null,
      address: document.getElementById('cdmInputAddress')?.value.trim() || null,
      website: document.getElementById('cdmInputWebsite')?.value.trim() || null,
      service: document.getElementById('cdmInputService')?.value.trim() || null,
      project_status: document.getElementById('cdmSelectStatus')?.value,
      notes: document.getElementById('cdmTextareaNotes')?.value.trim() || null,
    };

    const res = await JMOS_API.put('/clients/' + clientId, payload);
    const updated = res.data || res;

    showToast('Client Profile Saved', `"${name}" details updated successfully`);

    // Refresh memory and tables
    await ensureClients();
    if (typeof ensureProjects === 'function') {
      await ensureProjects();
    }

    // Refresh modal header
    populateClientDetailHeader(updated);

  } catch (err) {
    console.error('Error updating client:', err);
    showToast('Failed to save client', err.message, true);
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Changes';
    }
  }
});

// Delete Client from Workspace
document.getElementById('cdmDeleteBtn')?.addEventListener('click', async () => {
  const clientId = document.getElementById('cdmClientId')?.value;
  const name = document.getElementById('cdmTitle')?.textContent || 'this client';
  if (!clientId) return;

  const confirmed = typeof window.showConfirmDialog === 'function'
    ? await window.showConfirmDialog({
        title: 'Delete Client?',
        message: `Are you sure you want to delete <b>${escHtml(name)}</b> from the database? This action cannot be undone.`,
        confirmText: 'Delete Client',
        isDanger: true
      })
    : confirm(`Delete client "${name}"?`);

  if (confirmed) {
    try {
      await JMOS_API.delete('/clients/' + clientId);
      showToast('Client deleted', `"${name}" removed from database`);
      closeModal('clientDetailModal');
      await ensureClients();
    } catch (err) {
      showToast('Error', err.message, true);
    }
  }
});

