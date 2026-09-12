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
    const delBtn = canDelete ? `<button class="linkbtn" style="color:var(--red)" data-del-client="${c.id}">Delete</button>` : '';

    return `<tr>
      <td><div class="cn"><span class="lg" style="background:var(--red)">${escHtml(ini)}</span>${escHtml(nm)}</div></td>
      <td>${escHtml(c.client_type || 'Corporate')}</td>
      <td>${escHtml(c.contact_person || '—')}</td>
      <td>${escHtml(c.owner || '—')}</td>
      <td>${escHtml(c.projects || 0)}</td>
      <td>${escHtml(c.service || '—')}</td>
      <td><span class="pill tint-green">${escHtml(c.project_status || 'Active')}</span></td>
      <td class="mono">${fmt(c.project_value)}</td>
      <td>${delBtn}</td>
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

// 2. Projects View
function renderProjectsTable() {
  const body = document.getElementById('projectsBody');
  if (!body) return;

  const list = JMOS_STATE.projects || [];
  if (!list.length) {
    body.innerHTML = '<tr><td colspan="10" style="padding:30px;text-align:center;color:var(--muted)"><b style="color:var(--ink)">No active projects in database.</b><br>Click “Add project” or win a pipeline deal.</td></tr>';
    return;
  }

  const canDelete = JMOS_STATE.currentUser && (JMOS_STATE.currentUser.role === 'owner');

  body.innerHTML = list.map(p => {
    const st = p.status || 'On track';
    const delBtn = canDelete ? `<button class="linkbtn" style="color:var(--red)" data-del-project="${p.id}">Delete</button>` : '';

    return `<tr>
      <td style="font-weight:500">${escHtml(p.project_name)}</td>
      <td>${escHtml(p.client)}</td>
      <td>${escHtml(p.project_type || 'Production')}</td>
      <td>${escHtml(p.project_manager || '—')}</td>
      <td>${escHtml(p.stage || 'brief')}</td>
      <td><span class="pill ${getProjectStatusClass(p.status)}">${escHtml(st)}</span></td>
      <td>${escHtml(p.priority || 'Medium')}</td>
      <td class="mono" style="color:var(--muted)">${escHtml(p.deadline || '—')}</td>
      <td class="mono">${fmt(p.budget)}</td>
      <td>${delBtn}</td>
    </tr>`;
  }).join('');
}

async function ensureProjects() {
  const body = document.getElementById('projectsBody');
  if (body && (!JMOS_STATE.projects || !JMOS_STATE.projects.length)) {
    body.innerHTML = '<tr><td colspan="10" style="padding:26px;text-align:center;color:var(--muted)">Loading projects from database…</td></tr>';
  }
  try {
    const projects = await JMOS_API.get('/projects');
    if (Array.isArray(projects)) {
      JMOS_STATE.projects = projects;
      renderProjectsTable();
    }
  } catch (err) {
    console.error('Error fetching projects:', err);
    renderProjectsTable();
  }
}

// Event Delegation for Delete Client & Delete Project
document.addEventListener('click', async (e) => {
  const delClientBtn = e.target.closest('[data-del-client]');
  if (delClientBtn) {
    const id = delClientBtn.getAttribute('data-del-client');
    if (confirm('Delete this client from database?')) {
      try {
        await JMOS_API.delete('/clients/' + id);
        showToast('Client deleted', 'Database updated successfully');
        await ensureClients();
      } catch (err) {
        alert('Error: ' + err.message);
      }
    }
  }

  const delProjectBtn = e.target.closest('[data-del-project]');
  if (delProjectBtn) {
    const id = delProjectBtn.getAttribute('data-del-project');
    if (confirm('Delete this project from database?')) {
      try {
        await JMOS_API.delete('/projects/' + id);
        showToast('Project deleted', 'Database updated successfully');
        await ensureProjects();
      } catch (err) {
        alert('Error: ' + err.message);
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
});
