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
            <th>Priority</th>
            <th>Deadline</th>
            <th>Budget (KES)</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="projectsBody">
          <tr>
            <td colspan="10" style="padding:26px;text-align:center;color:var(--muted)">Loading your projects…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
