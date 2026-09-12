<!-- ==========================================================================
     JMOS — View: Services (Recipe Book)
     ========================================================================== -->
<section class="view" data-view="services" hidden data-perm="owner manager">
  <div class="page-head">
    <div>
      <h1 class="pt">Services</h1>
      <p>Your recipe book. Each type carries its stages, deliverables &amp; task list.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addServiceBtn" onclick="openModal('serviceModal')" data-modal-open="serviceModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add service
      </button>
    </div>
  </div>

  <div class="svc-grid" id="svcGrid"></div>
</section>
