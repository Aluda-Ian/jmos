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

  <div class="tablecard">
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>Member</th>
            <th>Role</th>
            <th>Department</th>
            <th>Access</th>
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
