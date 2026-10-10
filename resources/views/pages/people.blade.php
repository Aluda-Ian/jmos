<!-- ==========================================================================
     JMOS — View: People (Team & Access Management)
     ========================================================================== -->
<section class="view" data-view="people" hidden data-perm="owner finance manager">
  <div class="page-head">
    <div>
      <h1 class="pt">People</h1>
      <p>Your team, their access level, and how they're paid.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addUserBtn" onclick="openModal('userModal')" data-modal-open="userModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add person
      </button>
    </div>
  </div>

  <!-- Account activation tracker -->
  <div class="kpis" style="margin-bottom:14px" id="peopleInviteKpis">
    <div class="kpi" style="cursor:pointer" onclick="window.filterPeopleStatus('all')">
      <div class="lbl">Team members</div>
      <div class="val mono" id="pplKpiTotal">0</div>
      <div class="sub">Click a card to filter</div>
    </div>
    <div class="kpi" style="cursor:pointer" onclick="window.filterPeopleStatus('active')">
      <div class="lbl">Active</div>
      <div class="val mono" id="pplKpiActive" style="color:var(--green)">0</div>
      <div class="sub">Set a password &amp; signed in</div>
    </div>
    <div class="kpi" style="cursor:pointer" onclick="window.filterPeopleStatus('invited')">
      <div class="lbl">Invite sent</div>
      <div class="val mono" id="pplKpiInvited" style="color:var(--amber)">0</div>
      <div class="sub">Waiting for them to activate</div>
    </div>
    <div class="kpi" style="cursor:pointer" onclick="window.filterPeopleStatus('not_invited')">
      <div class="lbl">Not invited</div>
      <div class="val mono" id="pplKpiNotInvited" style="color:var(--red)">0</div>
      <div class="sub">No invite email delivered yet</div>
    </div>
  </div>

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Member</th>
            <th>Role</th>
            <th>Department</th>
            <th>Access</th>
            <th>Account</th>
            <th>Type</th>
            <th>Pay / Salary</th>
            <th>Login &amp; Contact</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody id="peopleBody"></tbody>
      </table>
    </div>
  </div>
</section>
