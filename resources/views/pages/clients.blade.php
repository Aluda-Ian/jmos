<!-- ==========================================================================
     JMOS — View: Clients
     ========================================================================== -->
<section class="view" data-view="clients" hidden>
  <div class="page-head">
    <div>
      <h1 class="pt">Clients</h1>
      <p>Live from your database. Create and manage your client directory in real time.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn" id="refreshClientsBtn" onclick="ensureClients()">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn primary" id="addClientBtn" onclick="openModal('clientModal')" data-modal-open="clientModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add client
      </button>
    </div>
  </div>

  <!-- Quick Action Cards Grid -->
  <div class="action-cards-grid">
    <div class="action-card" onclick="openModal('clientModal')" role="button" tabindex="0" title="Register a new corporate client or agency">
      <div class="action-card-icon tint-blue">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Add Client <span class="action-card-badge" style="background:rgba(43,110,138,0.15);color:#2B6E8A">New</span></div>
        <p class="action-card-sub">Register brand, stakeholder, or partner</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="openModal('projectModal')" role="button" tabindex="0" title="Start a new live project for a client">
      <div class="action-card-icon tint-red">
        <svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">New Live Project</div>
        <p class="action-card-sub">Spin up commercial, video, or campaign</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="window.openCreateInvoiceInBudget()" role="button" tabindex="0" title="Draft deposit or milestone invoice via Production Budget">
      <div class="action-card-icon tint-green">
        <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Issue Invoice (Budget)</div>
        <p class="action-card-sub">Generate retainer or milestone invoice</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>

    <div class="action-card" onclick="triggerCleanupClients()" role="button" tabindex="0" title="Review inactive clients or prune directory">
      <div class="action-card-icon tint-danger">
        <svg viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
      </div>
      <div class="action-card-content">
        <div class="action-card-title">Directory Cleanup</div>
        <p class="action-card-sub">Audit inactive clients or delete records</p>
      </div>
      <div class="action-card-arr">→</div>
    </div>
  </div>

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Client</th>
            <th>Type</th>
            <th>Contact</th>
            <th>Owner</th>
            <th>Projects</th>
            <th>Service</th>
            <th>Status</th>
            <th>Value (KES)</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="clientsBody">
          <tr>
            <td colspan="9" style="padding:26px;text-align:center;color:var(--muted)">Loading your clients…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
