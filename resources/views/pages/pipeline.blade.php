<!-- ==========================================================================
     JMOS — View: Sales Pipeline
     ========================================================================== -->
<section class="view" data-view="pipeline" hidden>
  <div class="page-head">
    <div>
      <h1 class="pt">Pipeline</h1>
      <p>Lead → Meeting → Proposal sent → Negotiation → Won. Drag or click to advance deals.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addDealBtn" onclick="openModal('dealModal')" data-modal-open="dealModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New deal
      </button>
    </div>
  </div>

  <div class="board" id="pipelineBoard" style="grid-template-columns:repeat(5, 1fr)">
    <div style="grid-column:span 5;padding:40px;text-align:center;color:var(--muted)">Loading pipeline from database…</div>
  </div>
</section>
