<!-- ==========================================================================
     JMOS — View: Tasks
     ========================================================================== -->
<section class="view" data-view="tasks" hidden>
  <div class="page-head">
    <div>
      <h1 class="pt">Tasks</h1>
      <p>Live task boards across active projects — auto-generated from service recipes or manually created.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="addTaskBtn" onclick="if (typeof window.openCreateTaskModal === 'function') { window.openCreateTaskModal(); } else { openModal('taskModal'); }">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New task
      </button>
    </div>
  </div>

  <div class="board" id="tasksBoard">
    <div style="grid-column:span 5;padding:40px;text-align:center;color:var(--muted)">Loading tasks from database…</div>
  </div>
</section>
