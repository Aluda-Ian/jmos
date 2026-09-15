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
  const internalCount = allList.filter(isProjectInternal).length;
  const clientCount = allList.length - internalCount;

  // Update Category Tab Counters
  const countAll = document.getElementById('projFilterAllCount');
  if (countAll) countAll.textContent = allList.length;
  const countClient = document.getElementById('projFilterClientCount');
  if (countClient) countClient.textContent = clientCount;
  const countInternal = document.getElementById('projFilterInternalCount');
  if (countInternal) countInternal.textContent = internalCount;

  // Filter list by selected category tab
  const currentFilter = JMOS_STATE.projectCategoryFilter || 'all';
  const list = allList.filter(p => {
    if (currentFilter === 'client') return !isProjectInternal(p);
    if (currentFilter === 'internal') return isProjectInternal(p);
    return true;
  });

  if (!list.length) {
    const emptyMsg = currentFilter === 'internal'
      ? 'No internal system projects found. Click “Internal Project” to spin up JMOS or infrastructure development.'
      : (currentFilter === 'client' ? 'No client deliverable projects found in database.' : 'No active projects in database.');
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

    const catBadge = isInternal
      ? `<span class="badge" style="background:rgba(110,43,138,0.15);color:#8A2BE2;font-size:10px;font-weight:600;margin-left:5px">Internal</span>`
      : '';

    return `<tr data-project-id="${p.id}" class="clickable-project-row" style="cursor:pointer" title="Click to open project workspace">
      <td style="font-weight:600">
        <span style="color:var(--ink);font-weight:600;display:inline-flex;align-items:center;gap:6px">
          <span style="color:${isInternal ? '#8A2BE2' : 'var(--red)'}">▶</span> ${escHtml(p.project_name)} ${catBadge}
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
    document.getElementById('pdmTitle').textContent = project.project_name || 'Project Workspace';
    document.getElementById('pdmClientBadge').textContent = project.client || 'Client';
    document.getElementById('pdmTypeBadge').textContent = project.project_type || 'Production';
    document.getElementById('pdmBudgetBadge').textContent = fmt(project.budget || 0);

    const isInternal = isProjectInternal(project);
    const catBadge = document.getElementById('pdmCategoryBadge');
    if (catBadge) {
      catBadge.textContent = isInternal ? 'Internal & Systems' : 'Client Deliverable';
      catBadge.style.background = isInternal ? 'rgba(110,43,138,0.15)' : 'rgba(43,110,138,0.15)';
      catBadge.style.color = isInternal ? '#8A2BE2' : '#2B6E8A';
    }

    const catSelect = document.getElementById('pdmCategorySelect');
    if (catSelect) {
      catSelect.value = project.category || (isInternal ? 'internal' : 'client');
    }

    const typeSelect = document.getElementById('pdmTypeSelect');
    if (typeSelect) {
      typeSelect.value = project.project_type || (isInternal ? 'Internal System Development' : 'Brand film');
    }

    document.getElementById('pdmDeadlineInput').value = project.deadline || '';
    document.getElementById('pdmStatusSelect').value = project.status || 'On track';
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

  container.innerHTML = tasks.map(t => {
    const isDone = t.stage === 'done';
    const statusBg = isDone ? 'var(--green-soft)' : (t.stage === 'in_progress' ? 'rgba(197,37,35,0.08)' : 'var(--panel-2)');
    const statusCol = isDone ? 'var(--green)' : (t.stage === 'in_progress' ? 'var(--red)' : 'var(--muted)');
    const ini = t.assigned_initials || (t.assigned_to ? t.assigned_to.charAt(0) : 'T');
    const color = t.assigned_color || '#C52523';

    return `
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--line-soft);gap:10px;background:var(--surface)">
        <div style="display:flex;align-items:center;gap:10px;flex:1">
          <button type="button" class="icon-btn-sm" onclick="toggleTaskDoneFromWorkspace(${t.id}, '${t.stage}', ${project.id})" style="border-radius:50%;width:20px;height:20px;padding:0;display:grid;place-items:center;border:1px solid ${isDone ? 'var(--green)' : 'var(--line-strong)'};background:${isDone ? 'var(--green)' : 'transparent'};color:#fff;cursor:pointer" title="${isDone ? 'Mark to do' : 'Mark done'}">
            ${isDone ? '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>' : ''}
          </button>
          <span style="font-size:13px;font-weight:500;color:${isDone ? 'var(--muted)' : 'var(--ink)'};${isDone ? 'text-decoration:line-through' : ''}">${escHtml(t.title)}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge" style="background:${statusBg};color:${statusCol};font-size:10.5px">${stageLabels[t.stage] || t.stage}</span>
          ${t.due_date ? `<span class="mono" style="font-size:10.5px;color:var(--muted)">${escHtml(t.due_date)}</span>` : ''}
          <span class="av" style="background:${color};width:22px;height:22px;font-size:9.5px" title="${escHtml(t.assigned_to || 'Unassigned')}">${escHtml(ini)}</span>
        </div>
      </div>
    `;
  }).join('');
}

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

  const category = document.getElementById('pdmCategorySelect')?.value || 'client';
  const project_type = document.getElementById('pdmTypeSelect')?.value;
  const deadline = document.getElementById('pdmDeadlineInput')?.value.trim();
  const status = document.getElementById('pdmStatusSelect')?.value;
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
      category,
      project_type,
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

    showToast('Workspace Saved', 'Project details and drive links updated');
    closeModal('projectDetailModal');
    await ensureProjects();
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
      cat.value = 'internal';
      cat.dispatchEvent(new Event('change'));
    }
    const typeSelect = document.getElementById('npType');
    if (typeSelect) {
      typeSelect.value = 'Internal System Development';
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
    invoices: document.getElementById('cdmTabPaneInvoices'),
    shoots: document.getElementById('cdmTabPaneShoots'),
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
    const events = data.events || [];
    const stats = data.stats || {};

    populateClientDetailHeader(client, stats);
    populateClientProjectsTab(client, projects);
    populateClientInfoTab(client);
    populateClientInvoicesTab(client, invoices);
    populateClientShootsTab(client, events);

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

