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

    return `<tr>
      <td><div class="cn"><span class="lg" style="background:var(--red)">${escHtml(ini)}</span>${escHtml(nm)}</div></td>
      <td>${escHtml(c.client_type || 'Corporate')}</td>
      <td>${escHtml(c.contact_person || '—')}</td>
      <td>${escHtml(c.owner || '—')}</td>
      <td>${escHtml(c.projects || 0)}</td>
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

// 2. Projects View
function renderProjectsTable() {
  const body = document.getElementById('projectsBody');
  if (!body) return;

  const list = JMOS_STATE.projects || [];
  if (!list.length) {
    body.innerHTML = '<tr><td colspan="11" style="padding:30px;text-align:center;color:var(--muted)"><b style="color:var(--ink)">No active projects in database.</b><br>Click “Add project” or win a pipeline deal.</td></tr>';
    return;
  }

  const canDelete = JMOS_STATE.currentUser && (JMOS_STATE.currentUser.role === 'owner');

  body.innerHTML = list.map(p => {
    const st = p.status || 'On track';
    const pName = p.project_name || 'Project';

    // Calculate task completion ratio
    const pTasks = (p.tasks && Array.isArray(p.tasks) && p.tasks.length)
      ? p.tasks
      : ((JMOS_STATE.tasks && Array.isArray(JMOS_STATE.tasks)) ? JMOS_STATE.tasks.filter(t => t.project_id === p.id) : []);
    const totalTasks = pTasks.length;
    const doneTasks = pTasks.filter(t => t.stage === 'done').length;
    const pct = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : (p.progress_pct || 0);

    const isGreen = pct === 100 || (p.status || '').toLowerCase().includes('delivering');
    const colorVar = isGreen ? 'var(--green)' : (pct > 0 ? 'var(--amber)' : 'var(--muted)');
    
    // Action buttons inside row
    let actionsHtml = `
      <div class="row-actions-wrap">
        <button type="button" class="row-action-btn" title="Add shoot or event to calendar" onclick="openModal('scheduleModal')">
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

    return `<tr data-project-id="${p.id}" class="clickable-project-row" style="cursor:pointer" title="Click to open project workspace">
      <td style="font-weight:600"><span style="color:var(--ink);font-weight:600;display:inline-flex;align-items:center;gap:6px"><span style="color:var(--red)">▶</span> ${escHtml(p.project_name)}</span></td>
      <td>${escHtml(p.client)}</td>
      <td>${escHtml(p.project_type || 'Production')}</td>
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
