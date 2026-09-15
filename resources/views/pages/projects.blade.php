<!-- ==========================================================================
     JMOS — View: Projects
     ========================================================================== -->
<section class="view" data-view="projects" hidden>
  <div class="page-head">
    <div>
      <h1 class="pt">Projects</h1>
      <p>Live from your database. What’s active, what stage it’s in, and what needs attention.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn" id="refreshProjectsBtn" onclick="ensureProjects()">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn primary" id="addProjectBtn" onclick="openModal('projectModal')" data-modal-open="projectModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add project
      </button>
    </div>
  </div>

  <!-- Quick Action Cards Grid -->
  <div class="action-cards-grid">
    <div class="action-card" onclick="openModal('projectModal')" role="button" tabindex="0" title="Create a new live project in database">
      <div class="action-card-icon tint-red">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Add Project <span class="action-card-badge" style="background:rgba(197,37,35,0.15);color:var(--red)">New</span></div>
        <p class="action-card-sub">Start production or commercial deliverable</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="openInternalProjectModal()" role="button" tabindex="0" title="Start internal system engineering, tooling or R&D project">
      <div class="action-card-icon tint-purple" style="background:rgba(110,43,138,0.12);color:#8A2BE2">
        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Internal Project <span class="action-card-badge" style="background:rgba(110,43,138,0.15);color:#8A2BE2">System</span></div>
        <p class="action-card-sub">JMOS development, infra, tools or R&amp;D</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="openModal('clientModal')" role="button" tabindex="0" title="Add a corporate client or partner">
      <div class="action-card-icon tint-blue">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Add Client</div>
        <p class="action-card-sub">Register brand, stakeholder, or agency</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="openScheduleShootModal()" role="button" tabindex="0" title="Schedule shoot, client review, or Google Meet">
      <div class="action-card-icon tint-green">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><circle cx="12" cy="15" r="2"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Schedule Shoot</div>
        <p class="action-card-sub">Book crew shoot or client review meet</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>
  </div>

  <!-- Projects Category Filter Tabs -->
  <div class="project-tab-nav" style="display:flex;align-items:center;gap:6px;margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:2px">
    <button type="button" class="project-tab-btn active" data-proj-filter="all">
      <span>All Projects</span>
      <span class="project-tab-badge" id="projFilterAllCount">0</span>
    </button>
    <button type="button" class="project-tab-btn" data-proj-filter="client">
      <svg viewBox="0 0 24 24" width="13" height="13"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      <span>Client Deliverables</span>
      <span class="project-tab-badge" id="projFilterClientCount">0</span>
    </button>
    <button type="button" class="project-tab-btn" data-proj-filter="internal">
      <svg viewBox="0 0 24 24" width="13" height="13"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
      <span>Internal &amp; Systems</span>
      <span class="project-tab-badge" id="projFilterInternalCount">0</span>
    </button>
  </div>

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Project</th>
            <th>Client</th>
            <th>Type</th>
            <th>Manager</th>
            <th>Stage</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Budget (KES)</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="projectsBody">
          <tr>
            <td colspan="11" style="padding:26px;text-align:center;color:var(--muted)">Loading your projects…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
