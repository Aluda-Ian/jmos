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
