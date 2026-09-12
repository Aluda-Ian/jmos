<!-- ==========================================================================
     JMOS — Modals & Interactive Overlays
     ========================================================================== -->

<!-- 1. Add Client Modal -->
<div class="modal" id="clientModal" role="dialog" aria-modal="true" aria-labelledby="clientModalTitle">
  <div class="mbg" data-close="clientModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="clientModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="clientModalTitle">Add a client</h3>
    <p class="msub">Create a new client record in your central database.</p>
    <div class="grid2">
      <div class="field">
        <label for="ncName">Client / Brand name *</label>
        <input id="ncName" placeholder="e.g. Safari Park Hotel" required autocomplete="off">
      </div>
      <div class="field">
        <label for="ncType">Industry / Type</label>
        <select id="ncType">
          <option value="Corporate">Corporate</option>
          <option value="Tech">Tech</option>
          <option value="Agency">Agency</option>
          <option value="Agritech">Agritech</option>
          <option value="Hospitality">Hospitality</option>
          <option value="Direct">Direct</option>
        </select>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="ncContact">Contact person</label>
        <input id="ncContact" placeholder="e.g. Peter Karanja" autocomplete="off">
      </div>
      <div class="field">
        <label for="ncOwner">Account owner</label>
        <input id="ncOwner" placeholder="e.g. Patrick M." autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="ncService">Primary service</label>
        <input id="ncService" placeholder="e.g. Brand Film" autocomplete="off">
      </div>
      <div class="field">
        <label for="ncValue">Project value (KES)</label>
        <input id="ncValue" type="number" placeholder="e.g. 250000" autocomplete="off">
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="clientModal">Cancel</button>
      <button type="button" class="btn primary" id="saveClientBtn">Save client</button>
    </div>
  </div>
</div>

<!-- 2. Add Project Modal -->
<div class="modal" id="projectModal" role="dialog" aria-modal="true" aria-labelledby="projectModalTitle">
  <div class="mbg" data-close="projectModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="projectModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="projectModalTitle">Add a live project</h3>
    <p class="msub">Spin up an active project on the delivery board.</p>
    <div class="grid2">
      <div class="field">
        <label for="npName">Project name *</label>
        <input id="npName" placeholder="e.g. Moyo Honey · Brand Film" required autocomplete="off">
      </div>
      <div class="field">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
          <label for="npClientSelect" style="margin:0">Client *</label>
          <button type="button" id="npToggleNewClientBtn" style="background:none;border:none;color:var(--red);font-size:11.5px;font-weight:600;cursor:pointer;padding:0">
            + New Client
          </button>
        </div>
        <!-- Client Dropdown Selection -->
        <div id="npClientSelectWrap">
          <select id="npClientSelect" required>
            <option value="">-- Choose existing client --</option>
            <option value="__new__">+ Add new client...</option>
          </select>
        </div>
        <!-- Quick Add New Client Inline Box -->
        <div id="npNewClientWrap" style="display:none;margin-top:6px">
          <div style="display:flex;gap:6px">
            <input id="npNewClientInput" placeholder="Enter new client name" autocomplete="off" style="flex:1">
            <button type="button" class="btn" id="npSaveNewClientBtn" style="padding:6px 12px;font-size:11px;white-space:nowrap;background:var(--red);color:#fff;border-color:var(--red)">Save &amp; Pick</button>
          </div>
        </div>
        <input type="hidden" id="npClient" name="client">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="npType">Project type</label>
        <select id="npType">
          <option value="Brand film">Brand film</option>
          <option value="Documentary">Documentary</option>
          <option value="Social media reels">Social media reels</option>
          <option value="Corporate photography">Corporate photography</option>
          <option value="Event coverage">Event coverage</option>
          <option value="Podcast production">Podcast production</option>
          <option value="Livestream">Livestream</option>
          <option value="Social media management">Social media management</option>
          <option value="Commercial">Commercial</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="field">
        <label for="npManager">Project manager</label>
        <select id="npManager">
          <option value="Barny Kiome">Barny Kiome (Executive Producer)</option>
          <option value="Ian Aluda">Ian Aluda (IT & Systems)</option>
          <option value="Stephen Otieno">Stephen Otieno (Video Editor)</option>
          <option value="Amos Muthama">Amos Muthama (Cinematographer)</option>
          <option value="Matthew Muange">Matthew Muange (Finance)</option>
          <option value="Patrick Mwendwa">Patrick Mwendwa (Sales)</option>
          <option value="Lesley Chacha">Lesley Chacha (Copywriter)</option>
        </select>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="npStage">Current stage</label>
        <select id="npStage">
          <option value="brief">Brief</option>
          <option value="concept">Concept</option>
          <option value="pre-pro">Pre-production</option>
          <option value="shoot">Shoot</option>
          <option value="edit">Edit</option>
          <option value="client review">Client review</option>
          <option value="delivery">Delivery</option>
        </select>
      </div>
      <div class="field">
        <label for="npStatus">Status</label>
        <select id="npStatus">
          <option value="On track">On track</option>
          <option value="At risk">At risk</option>
          <option value="Delivering">Delivering</option>
          <option value="Blocked">Blocked</option>
        </select>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="npDeadline">Deadline</label>
        <input id="npDeadline" placeholder="e.g. Sep 30" autocomplete="off">
      </div>
      <div class="field">
        <label for="npBudget">Budget (KES)</label>
        <input id="npBudget" type="number" placeholder="e.g. 320000" autocomplete="off">
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="projectModal">Cancel</button>
      <button type="button" class="btn primary" id="saveProjectBtn">Create project</button>
    </div>
  </div>
</div>

<!-- 2b. Project Details & Notion-style Workspace Modal -->
<div class="modal" id="projectDetailModal" role="dialog" aria-modal="true" aria-labelledby="pdmTitle">
  <div class="mbg" data-close="projectDetailModal"></div>
  <div class="mbox" style="max-height:92vh;overflow-y:auto;padding:0;overflow-x:hidden">
    <!-- Notion-style Cover Banner -->
    <div style="height:110px;background:linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);position:relative;border-radius:16px 16px 0 0">
      <button class="mclose" data-close="projectDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.4);color:#fff;border:none">&times;</button>
      <div style="position:absolute;bottom:-20px;left:28px;width:44px;height:44px;border-radius:10px;background:var(--surface);box-shadow:0 4px 14px rgba(0,0,0,0.15);display:grid;place-items:center;color:var(--ink)">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
      </div>
    </div>

    <div style="padding:32px 28px 24px">
      <!-- Title & Client Header -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px">
        <div style="flex:1">
          <input type="hidden" id="pdmProjectId">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <span class="badge" id="pdmClientBadge" style="background:var(--red-soft);color:var(--red);font-size:11px">Client</span>
            <span class="badge" id="pdmTypeBadge" style="background:var(--panel-2);color:var(--muted);font-size:11px">Brand Film</span>
          </div>
          <h2 id="pdmTitle" style="font-family:'Poppins',sans-serif;font-size:24px;font-weight:700;margin:0;color:var(--ink);line-height:1.2">Project Name</h2>
        </div>
        <div style="text-align:right">
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Budget</div>
          <div class="mono" id="pdmBudgetBadge" style="font-size:16px;font-weight:700;color:var(--ink)">KES 0</div>
        </div>
      </div>

      <!-- Properties Grid (Notion Style) -->
      <div class="project-props-list" style="background:var(--panel-2);border:1px solid var(--line);border-radius:12px;padding:12px 16px;margin-bottom:22px">
        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Dates / Deadline:</span>
          </div>
          <div style="flex:1;display:flex;align-items:center;gap:8px">
            <input type="text" id="pdmDeadlineInput" placeholder="e.g. 2026-09-30 or Sep 30" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface);width:180px">
            <span style="font-size:11px;color:var(--muted)">Syncs to Operations Calendar</span>
          </div>
        </div>

        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Status:</span>
          </div>
          <div style="flex:1">
            <select id="pdmStatusSelect" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <option value="On track">On track</option>
              <option value="At risk">At risk</option>
              <option value="Delivering">Delivering</option>
              <option value="Blocked">Blocked</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>

        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Owner / Lead:</span>
          </div>
          <div style="flex:1">
            <select id="pdmManagerSelect" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <option value="Barny Kiome">Barny Kiome (Executive Producer)</option>
              <option value="Ian Aluda">Ian Aluda (IT &amp; Systems)</option>
              <option value="Lesley Chacha">Lesley Chacha (Client Relations)</option>
              <option value="Amos Muthama">Amos Muthama (Cinematography)</option>
              <option value="Stephen Otieno">Stephen Otieno (Post-Production)</option>
              <option value="Patrick Mwendwa">Patrick Mwendwa (Sales)</option>
              <option value="Matthew Muange">Matthew Muange (Finance)</option>
            </select>
          </div>
        </div>

        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Completion:</span>
          </div>
          <div style="flex:1;display:flex;align-items:center;gap:12px">
            <div style="flex:1;max-width:240px;height:8px;background:var(--surface);border-radius:999px;overflow:hidden;border:1px solid var(--line)">
              <div id="pdmProgressBar" style="height:100%;background:var(--green);width:0%;border-radius:999px;transition:width 0.3s ease"></div>
            </div>
            <span class="mono" id="pdmProgressPct" style="font-size:13px;font-weight:700;color:var(--ink)">0%</span>
          </div>
        </div>

        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            <span>Priority:</span>
          </div>
          <div style="flex:1">
            <select id="pdmPrioritySelect" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <option value="High">High</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
        </div>

        <!-- Files & Media Storage Links -->
        <div class="project-prop-row" style="display:flex;align-items:flex-start;padding:8px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500;margin-top:4px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            <span>Storage Drives:</span>
          </div>
          <div style="flex:1;display:flex;flex-direction:column;gap:6px">
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:90px;color:var(--muted)">Google Drive:</span>
              <input id="pdmDriveLink" placeholder="https://drive.google.com/drive/folders/..." style="flex:1;padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmDriveOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Drive Folder">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:90px;color:var(--muted)">Client Brief:</span>
              <input id="pdmBriefLink" placeholder="Link to project brief / creative doc" style="flex:1;padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmBriefOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Brief">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:90px;color:var(--muted)">Treatment:</span>
              <input id="pdmTreatmentLink" placeholder="Director's treatment / storyboard URL" style="flex:1;padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmTreatmentOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Treatment">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:90px;color:var(--muted)">Playbook:</span>
              <input id="pdmPlaybookLink" placeholder="Production playbook URL or guide" style="flex:1;padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmPlaybookOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Playbook">Open ↗</a>
            </div>
          </div>
        </div>
      </div>

      <!-- About this project (Description / Notes) -->
      <div style="margin-bottom:24px">
        <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--ink);display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          <span>About this project</span>
        </h4>
        <textarea id="pdmNotes" rows="3" placeholder="Add scope details, shoot locations, deliverable specs, client contacts..." style="width:100%;border:1px solid var(--line);border-radius:8px;padding:10px;font-size:13px;background:var(--surface);resize:vertical"></textarea>
      </div>

      <!-- Project Tasks Section -->
      <div style="margin-bottom:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <h4 style="font-size:14px;font-weight:600;margin:0;color:var(--ink);display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span>Project Tasks</span> <span class="count" id="pdmTasksCount" style="font-size:11px;padding:1px 7px;background:var(--panel-2);border:1px solid var(--line);border-radius:12px">0</span>
          </h4>
          <button type="button" class="btn primary" id="pdmAddTaskBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Add Task
          </button>
        </div>

        <div id="pdmTasksList" style="border:1px solid var(--line);border-radius:10px;overflow:hidden;background:var(--surface)">
          <!-- Populated dynamically with tasks -->
          <div style="padding:18px;text-align:center;font-size:12px;color:var(--muted)">No tasks attached to this project yet.</div>
        </div>
      </div>

      <!-- Comments Thread Section -->
      <div style="margin-bottom:16px">
        <h4 style="font-size:14px;font-weight:600;margin-bottom:10px;color:var(--ink);display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Comments &amp; Activity</span>
        </h4>

        <!-- Comments Stream -->
        <div id="pdmCommentsStream" style="max-height:180px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;margin-bottom:10px;padding:6px 0">
          <div style="font-size:12px;color:var(--muted);font-style:italic">No comments yet. Start a discussion below.</div>
        </div>

        <!-- Add Comment Input -->
        <div style="display:flex;gap:8px;align-items:center">
          <input id="pdmCommentInput" placeholder="Add a comment, note, or update..." style="flex:1;border:1px solid var(--line);border-radius:8px;padding:8px 12px;font-size:12.5px;background:var(--surface)">
          <button type="button" class="btn primary" id="pdmPostCommentBtn" style="padding:8px 14px;font-size:12px">Post</button>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="mfoot" style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--line);margin-top:20px">
        <button type="button" class="btn" id="pdmDeleteBtn" style="color:var(--red);border-color:var(--line)">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete Project
        </button>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn" data-close="projectDetailModal">Close</button>
          <button type="button" class="btn primary" id="pdmSaveBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 3. Add Pipeline Deal Modal -->
<div class="modal" id="dealModal" role="dialog" aria-modal="true" aria-labelledby="dealModalTitle">
  <div class="mbg" data-close="dealModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="dealModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="dealModalTitle">Add pipeline deal</h3>
    <p class="msub">Track a new opportunity in your sales funnel.</p>
    <div class="field">
      <label for="ndTitle">Deal title *</label>
      <input id="ndTitle" placeholder="e.g. Sanlam · Brand Film" required autocomplete="off">
    </div>
    <div class="grid2">
      <div class="field">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
          <label for="ndClientSelect" style="margin:0">Client / Prospect *</label>
          <button type="button" id="ndToggleNewClientBtn" style="background:none;border:none;color:var(--red);font-size:11.5px;font-weight:600;cursor:pointer;padding:0">
            + New Client
          </button>
        </div>
        <div id="ndClientSelectWrap">
          <select id="ndClientSelect" required>
            <option value="">-- Choose existing client --</option>
            <option value="__new__">+ Add new client...</option>
          </select>
        </div>
        <div id="ndNewClientWrap" style="display:none;margin-top:6px">
          <div style="display:flex;gap:6px">
            <input id="ndNewClientInput" placeholder="Enter client / prospect name" autocomplete="off" style="flex:1">
            <button type="button" class="btn" id="ndSaveNewClientBtn" style="padding:6px 12px;font-size:11px;white-space:nowrap;background:var(--red);color:#fff;border-color:var(--red)">Save &amp; Pick</button>
          </div>
        </div>
        <input type="hidden" id="ndClient" name="client">
      </div>
      <div class="field">
        <label for="ndValue">Deal value (KES) *</label>
        <input id="ndValue" type="number" placeholder="e.g. 500000" required autocomplete="off">
      </div>
    </div>
    <div class="field">
      <label for="ndStage">Initial stage</label>
      <select id="ndStage">
        <option value="lead">Lead</option>
        <option value="meeting">Meeting</option>
        <option value="proposal">Proposal</option>
        <option value="negotiation">Negotiation</option>
        <option value="won">Won</option>
      </select>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="dealModal">Cancel</button>
      <button type="button" class="btn primary" id="saveDealBtn">Add to pipeline</button>
    </div>
  </div>
</div>

<!-- 3b. Pipeline Deal Details & Stage Movement Modal -->
<div class="modal" id="dealDetailModal" role="dialog" aria-modal="true" aria-labelledby="dealDetailTitle">
  <div class="mbg" data-close="dealDetailModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="dealDetailModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="badge" id="ddCurrentStageBadge" style="background:var(--red-soft);color:var(--red);font-size:11px">Pipeline Deal</span>
      <span class="mono" id="ddValueBadge" style="font-size:13px;font-weight:700;color:var(--ink)">KES 0</span>
    </div>
    <h3 id="dealDetailTitle" style="font-size:20px;font-weight:700;margin:0 0 4px 0">Deal Details</h3>
    <p class="msub" style="margin-bottom:14px">Move between pipeline stages or update deal terms.</p>

    <!-- Interactive Stage Mover -->
    <div style="margin-bottom:6px">
      <label style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Move Pipeline Stage</label>
    </div>
    <div class="deal-stage-stepper" id="ddStageStepper">
      <button type="button" class="stage-step-btn" data-stage="lead">
        <span>1. Lead</span>
      </button>
      <button type="button" class="stage-step-btn" data-stage="meeting">
        <span>2. Meeting</span>
      </button>
      <button type="button" class="stage-step-btn" data-stage="proposal">
        <span>3. Proposal</span>
      </button>
      <button type="button" class="stage-step-btn" data-stage="negotiation">
        <span>4. Negotiation</span>
      </button>
      <button type="button" class="stage-step-btn won" data-stage="won">
        <span>5. Won <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-left:2px"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
      </button>
    </div>

    <input type="hidden" id="ddDealId">
    <input type="hidden" id="ddActiveStage">

    <div class="field">
      <label for="ddTitle">Deal Title *</label>
      <input id="ddTitle" placeholder="e.g. Sanlam · Brand Film" required autocomplete="off">
    </div>

    <div class="grid2">
      <div class="field">
        <label for="ddClient">Client / Prospect *</label>
        <input id="ddClient" placeholder="e.g. Sanlam Kenya" required autocomplete="off">
      </div>
      <div class="field">
        <label for="ddValue">Deal Value (KES) *</label>
        <input id="ddValue" type="number" placeholder="e.g. 500000" required autocomplete="off">
      </div>
    </div>

    <div class="field">
      <label for="ddNotes">Notes / Scope Summary</label>
      <textarea id="ddNotes" rows="3" placeholder="Add scope notes, next steps, client decision date..." style="width:100%;resize:vertical"></textarea>
    </div>

    <div class="mfoot" style="display:flex;align-items:center;justify-content:space-between">
      <div>
        <button type="button" class="btn" id="ddDeleteBtn" style="color:var(--red);border-color:var(--line)">
          <svg viewBox="0 0 24 24" width="14" height="14"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete Deal
        </button>
      </div>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn" data-close="dealDetailModal">Close</button>
        <button type="button" class="btn primary" id="ddSaveBtn">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- 4. Add Task Modal -->
<div class="modal" id="taskModal" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
  <div class="mbg" data-close="taskModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="taskModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="taskModalTitle">Create a task</h3>
    <p class="msub">Assign a task to team members across project workflows.</p>
    <div class="field">
      <label for="ntProject">Attach to Project</label>
      <select id="ntProject">
        <option value="">— Select a project —</option>
      </select>
    </div>
    <div class="field">
      <label for="ntTitle">Task title *</label>
      <input id="ntTitle" placeholder="e.g. Color grade & sound master" required autocomplete="off">
    </div>
    <div class="grid2">
      <div class="field">
        <label for="ntStage">Stage</label>
        <select id="ntStage">
          <option value="todo">To do</option>
          <option value="in_progress">In progress</option>
          <option value="review_internal">Review (internal)</option>
          <option value="review_client">Review (client)</option>
          <option value="done">Done</option>
        </select>
      </div>
      <div class="field">
        <label for="ntAssigned">Assign to</label>
        <select id="ntAssigned">
          <option value="Stephen Otieno">Stephen Otieno (SO)</option>
          <option value="Barny Kiome">Barny Kiome (BK)</option>
          <option value="Amos Muthama">Amos Muthama (AM)</option>
          <option value="Lesley Chacha">Lesley Chacha (LC)</option>
          <option value="Ian Aluda">Ian Aluda (IA)</option>
          <option value="Matthew Muange">Matthew Muange (MM)</option>
          <option value="Patrick Mwendwa">Patrick Mwendwa (PM)</option>
        </select>
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="taskModal">Cancel</button>
      <button type="button" class="btn primary" id="saveTaskBtn">Create task</button>
    </div>
  </div>
</div>

<!-- 5. Add Invoice Modal -->
<div class="modal" id="invoiceModal" role="dialog" aria-modal="true" aria-labelledby="invoiceModalTitle">
  <div class="mbg" data-close="invoiceModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="invoiceModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="invoiceModalTitle">Issue new invoice</h3>
    <p class="msub">Record an outgoing client invoice in JMOS.</p>
    <div class="grid2">
      <div class="field">
        <label for="niNo">Invoice No *</label>
        <input id="niNo" placeholder="e.g. JM-0146" required autocomplete="off">
      </div>
      <div class="field">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
          <label for="niClientSelect" style="margin:0">Client *</label>
          <button type="button" id="niToggleNewClientBtn" style="background:none;border:none;color:var(--red);font-size:11.5px;font-weight:600;cursor:pointer;padding:0">
            + New Client
          </button>
        </div>
        <div id="niClientSelectWrap">
          <select id="niClientSelect" required>
            <option value="">-- Choose existing client --</option>
            <option value="__new__">+ Add new client...</option>
          </select>
        </div>
        <div id="niNewClientWrap" style="display:none;margin-top:6px">
          <div style="display:flex;gap:6px">
            <input id="niNewClientInput" placeholder="Enter client / brand name" autocomplete="off" style="flex:1">
            <button type="button" class="btn" id="niSaveNewClientBtn" style="padding:6px 12px;font-size:11px;white-space:nowrap;background:var(--red);color:#fff;border-color:var(--red)">Save &amp; Pick</button>
          </div>
        </div>
        <input type="hidden" id="niClient" name="client">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="niType">Invoice Type</label>
        <select id="niType">
          <option value="Deposit 60%">Deposit 60%</option>
          <option value="Balance 40%">Balance 40%</option>
          <option value="Retainer">Monthly Retainer</option>
          <option value="Full 100%">Full 100%</option>
        </select>
      </div>
      <div class="field">
        <label for="niAmount">Amount (KES) *</label>
        <input id="niAmount" type="number" placeholder="e.g. 192000" required autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="niDue">Due Date</label>
        <input id="niDue" placeholder="e.g. Sep 15" autocomplete="off">
      </div>
      <div class="field">
        <label for="niEtims">eTIMS Compliant?</label>
        <select id="niEtims">
          <option value="1">Yes (KRA eTIMS)</option>
          <option value="0">No / Pending</option>
        </select>
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="invoiceModal">Cancel</button>
      <button type="button" class="btn primary" id="saveInvoiceBtn">Issue invoice</button>
    </div>
  </div>
</div>

<!-- 6. Log Expense Modal -->
<div class="modal" id="expenseModal" role="dialog" aria-modal="true" aria-labelledby="expenseModalTitle">
  <div class="mbg" data-close="expenseModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="expenseModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="expenseModalTitle">Log an expense</h3>
    <p class="msub">Track project costs and operational expenses with ETR compliance.</p>
    <div class="field">
      <label for="neName">Expense name / Description *</label>
      <input id="neName" placeholder="e.g. Drone hire for shoot" required autocomplete="off">
    </div>
    <div class="grid2">
      <div class="field">
        <label for="neCat">Category</label>
        <select id="neCat">
          <option value="Equipment">Equipment</option>
          <option value="Location fees">Location fees</option>
          <option value="Editor fees">Editor fees</option>
          <option value="Software">Software</option>
          <option value="Catering">Catering</option>
          <option value="Travel & Transport">Travel & Transport</option>
          <option value="Overhead">Overhead</option>
        </select>
      </div>
      <div class="field">
        <label for="neProject">Project / Allocation</label>
        <input id="neProject" placeholder="e.g. Pankaj Documentary or overhead" autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="neAmount">Amount (KES) *</label>
        <input id="neAmount" type="number" placeholder="e.g. 15000" required autocomplete="off">
      </div>
      <div class="field">
        <label for="neEtr">ETR Receipt received?</label>
        <select id="neEtr">
          <option value="yes">Yes (ETR on file)</option>
          <option value="no">No (Missing receipt)</option>
          <option value="na">N/A</option>
        </select>
      </div>
    </div>
    <div class="field">
      <label for="neDate">Date</label>
      <input id="neDate" placeholder="e.g. Aug 15" autocomplete="off">
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="expenseModal">Cancel</button>
      <button type="button" class="btn primary" id="saveExpenseBtn">Log expense</button>
    </div>
  </div>
</div>

<!-- 7. Add Person Modal -->
<div class="modal" id="userModal" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
  <div class="mbg" data-close="userModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="userModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="userModalTitle">Add a person</h3>
    <p class="msub">They'll get their own login and only see what their access level allows.</p>
    <div class="grid2">
      <div class="field">
        <label for="nuName">Full name *</label>
        <input id="nuName" placeholder="e.g. Grace Wanjiru" required autocomplete="off">
      </div>
      <div class="field">
        <label for="nuTitle">Role / title</label>
        <input id="nuTitle" placeholder="e.g. Photographer" autocomplete="off">
      </div>
    </div>
    <div class="field">
      <label for="nuEmail">Email (their login) *</label>
      <input id="nuEmail" type="email" placeholder="grace@jeotamedia.co.ke" required autocomplete="off">
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nuRole">Access level</label>
        <select id="nuRole">
          <option value="team">Team member</option>
          <option value="sales">Sales</option>
          <option value="finance">Finance</option>
          <option value="owner">Owner (full access)</option>
        </select>
      </div>
      <div class="field">
        <label for="nuType">Employment</label>
        <select id="nuType">
          <option value="Full-time">Full-time</option>
          <option value="Per-project">Per-project</option>
        </select>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nuPay">Pay</label>
        <input id="nuPay" placeholder="e.g. 5,000/day" autocomplete="off">
      </div>
      <div class="field">
        <label for="nuPass">Temporary password</label>
        <input id="nuPass" placeholder="they can change it later" value="jeota2024" autocomplete="off">
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="userModal">Cancel</button>
      <button type="button" class="btn primary" id="saveUserBtn">Add person &amp; create login</button>
    </div>
  </div>
</div>

<!-- 8. Add Service Modal -->
<div class="modal" id="serviceModal" role="dialog" aria-modal="true" aria-labelledby="serviceModalTitle">
  <div class="mbg" data-close="serviceModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="serviceModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="serviceModalTitle">Add a service recipe</h3>
    <p class="msub">Define standard workflow stages and deliverables for your production service.</p>
    <div class="grid2">
      <div class="field">
        <label for="nsName">Service name *</label>
        <input id="nsName" placeholder="e.g. TV Commercial / 3D Motion" required autocomplete="off">
      </div>
      <div class="field">
        <label for="nsCode">Service code *</label>
        <input id="nsCode" placeholder="e.g. TVC-01" required autocomplete="off">
      </div>
    </div>
    <div class="field">
      <label for="nsDeliverables">Standard deliverables</label>
      <input id="nsDeliverables" placeholder="e.g. 30s master, 15s cutdowns, 4K ProRes + H.264" autocomplete="off">
    </div>
    <div class="field">
      <label for="nsStages">Workflow stages (comma-separated)</label>
      <input id="nsStages" placeholder="brief, concept, storyboard, shoot, edit, color grade, final delivery" value="brief, concept, pre-pro, shoot, edit, review, delivery" autocomplete="off">
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="serviceModal">Cancel</button>
      <button type="button" class="btn primary" id="saveServiceBtn">Save service</button>
    </div>
  </div>
</div>

<!-- 9. Schedule Meeting / Production Event Modal -->
<div class="modal" id="eventModal" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle">
  <div class="mbg" data-close="eventModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="eventModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="eventModalTitle">Schedule Meeting or Event</h3>
    <p class="msub">Book client meetings, production shoots, and project milestones synced with Google Calendar.</p>
    <div class="field">
      <label for="nevtTitle">Event Title *</label>
      <input id="nevtTitle" placeholder="e.g. Client Briefing — Nairobi Homes" required autocomplete="off">
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nevtType">Event Type</label>
        <select id="nevtType">
          <option value="meeting">Client Meeting</option>
          <option value="shoot">Production Shoot</option>
          <option value="deadline">Project Deadline</option>
          <option value="task">Internal Review / Task</option>
          <option value="other">Other Event</option>
        </select>
      </div>
      <div class="field">
        <label for="nevtDate">Date *</label>
        <input id="nevtDate" type="date" required autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nevtTime">Start Time</label>
        <input id="nevtTime" type="time" value="10:00" autocomplete="off">
      </div>
      <div class="field">
        <label for="nevtEndTime">End Time</label>
        <input id="nevtEndTime" type="time" value="11:30" autocomplete="off">
      </div>
    </div>
    <div class="field">
      <label for="nevtLocation">Location / Meeting Link</label>
      <input id="nevtLocation" placeholder="e.g. Google Meet or Westlands Studio" autocomplete="off">
    </div>
    <!-- Attendees Selection & Guest Integration -->
    <div class="field" style="margin-bottom:14px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <label style="margin:0;font-size:12.5px;font-weight:600">Attendees &amp; Participants</label>
        <span id="nevtAttendeeCountPill" style="font-size:11px;color:var(--muted)">0 selected</span>
      </div>

      <!-- Selected Chips Display -->
      <div id="nevtSelectedChipsWrap" class="attendee-chips-wrap">
        <span class="attendee-chips-empty">No attendees selected yet. Pick team members below or add a guest.</span>
      </div>
      <input type="hidden" id="nevtAttendees" name="attendees">

      <!-- Team Members Picker -->
      <div style="margin-top:10px">
        <div style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:6px;display:flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.4px">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Team Members
        </div>
        <div id="nevtTeamMembersList" class="attendee-team-list">
          <!-- Populated dynamically from JMOS_STATE.users -->
        </div>
      </div>

      <!-- Guest / Client Picker -->
      <div style="margin-top:10px;padding-top:10px;border-top:1px dashed var(--line)">
        <div style="font-size:11px;font-weight:600;color:var(--muted);margin-bottom:6px;display:flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.4px">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Add Client or External Guest
        </div>

        <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px">
          <select id="nevtClientSelect" style="flex:1;min-width:140px;font-size:12px;padding:7px 10px;border-radius:8px;background:var(--paper);border:1px solid var(--line);color:var(--ink)">
            <option value="">-- Choose from existing clients --</option>
          </select>
          <button type="button" class="btn" id="nevtAddClientBtn" style="padding:7px 12px;font-size:11.5px;white-space:nowrap">+ Add Client</button>
        </div>

        <div style="display:flex;gap:8px;align-items:center">
          <input type="text" id="nevtGuestInput" placeholder="Or type guest name or email (e.g. client@brand.com)" style="flex:1;font-size:12px;padding:7px 10px;border-radius:8px;background:var(--paper);border:1px solid var(--line);color:var(--ink)" autocomplete="off">
          <button type="button" class="btn" id="nevtAddGuestBtn" style="padding:7px 12px;font-size:11.5px;white-space:nowrap">+ Add Guest</button>
        </div>
      </div>
    </div>
    <div class="field">
      <label for="nevtDesc">Description / Notes</label>
      <input id="nevtDesc" placeholder="e.g. Discuss script storyboard and location permits" autocomplete="off">
    </div>
    <div style="margin-bottom:14px;background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.18);border-radius:8px;padding:10px 12px">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin:0;font-size:12.5px">
        <input type="checkbox" id="nevtAddMeet" checked style="accent-color:var(--red)">
        <span style="font-weight:600;display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          Add Google Meet video conferencing (Auto-generate Meet link)
        </span>
      </label>
      <p style="font-size:11px;color:var(--muted);margin:4px 0 0 24px">Creates an active Google Meet room and syncs with Google Calendar.</p>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="eventModal">Cancel</button>
      <button type="button" class="btn primary" id="saveEventBtn">Schedule event</button>
    </div>
  </div>
</div>

<!-- 9B. Calendar Event Details & Join Meeting Modal -->
<div class="modal" id="eventDetailModal" role="dialog" aria-modal="true" aria-labelledby="eventDetailTitle">
  <div class="mbg" data-close="eventDetailModal"></div>
  <div class="mbox" style="max-width:500px">
    <button class="mclose" data-close="eventDetailModal" title="Close" aria-label="Close modal">&times;</button>
    
    <!-- Header with Type Badge and Title -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px">
      <div>
        <span id="eventDetailTypeBadge" class="type tint-red" style="font-size:11px;padding:3px 8px;font-weight:600;display:inline-block;margin-bottom:6px">Meeting</span>
        <h3 id="eventDetailTitle" style="font-size:18px;line-height:1.3;margin:0">Event Title</h3>
      </div>
    </div>

    <!-- Date & Time Row -->
    <div style="display:flex;align-items:center;gap:14px;padding:10px 14px;background:var(--paper);border:1px solid var(--line);border-radius:10px;margin-bottom:14px;font-size:12.5px">
      <div style="display:flex;align-items:center;gap:6px;color:var(--ink);font-weight:600">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <span id="eventDetailDate">Date</span>
      </div>
      <span style="color:var(--line-strong)">•</span>
      <div style="display:flex;align-items:center;gap:6px;color:var(--muted)">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <span id="eventDetailTime">Time</span>
      </div>
    </div>

    <!-- Google Meet Callout Box -->
    <div id="eventDetailMeetBox" style="background:linear-gradient(135deg, rgba(37,99,235,0.08) 0%, rgba(59,130,246,0.03) 100%);border:1.5px solid rgba(37,99,235,0.25);border-radius:12px;padding:14px;margin-bottom:14px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:30px;height:30px;border-radius:8px;background:#2563eb;color:#fff;display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          </div>
          <div>
            <b style="font-size:13px;color:var(--ink)">Google Meet Video Conference</b>
            <span style="display:block;font-size:11px;color:var(--muted)">HD video meeting, screen sharing &amp; audio</span>
          </div>
        </div>
        <span class="badge" style="background:var(--green-soft);color:var(--green);font-size:10.5px">Meet Ready</span>
      </div>

      <div style="display:flex;align-items:center;gap:8px;margin-top:10px">
        <a id="eventDetailJoinBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn primary" style="flex:1;justify-content:center;background:#2563eb;border-color:#2563eb;padding:9px 14px;font-size:13px;text-decoration:none">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
          Join with Google Meet
        </a>
        <button type="button" class="btn" id="eventDetailCopyMeetBtn" title="Copy meeting link" style="padding:9px 12px">
          <svg viewBox="0 0 24 24" width="15" height="15"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        </button>
      </div>

      <div style="margin-top:8px;font-size:11px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        Link: <a id="eventDetailMeetLinkText" href="#" target="_blank" rel="noopener noreferrer" style="color:var(--blue, #2563eb);text-decoration:underline">https://meet.google.com/...</a>
      </div>
    </div>

    <!-- Generate Meet Link Fallback (if event has no meet link yet) -->
    <div id="eventDetailNoMeetBox" style="display:none;background:var(--paper);border:1px dashed var(--line-strong);border-radius:10px;padding:12px;margin-bottom:14px;text-align:center">
      <span style="font-size:12px;color:var(--muted);display:block;margin-bottom:8px">No Google Meet link attached to this event.</span>
      <button type="button" class="btn" id="eventDetailGenerateMeetBtn" style="font-size:12px;margin:0 auto">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        Generate Google Meet Link
      </button>
    </div>

    <!-- Details Metadata List -->
    <div style="display:flex;flex-direction:column;gap:10px;font-size:12.5px;margin-bottom:16px;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:12px">
      <div id="eventDetailLocRow" style="display:flex;gap:10px">
        <span style="color:var(--muted);min-width:80px">Location:</span>
        <span id="eventDetailLocation" style="font-weight:600;color:var(--ink)">—</span>
      </div>
      <div id="eventDetailAttRow" style="display:flex;gap:10px">
        <span style="color:var(--muted);min-width:80px">Attendees:</span>
        <span id="eventDetailAttendees" style="color:var(--ink)">—</span>
      </div>
      <div id="eventDetailDescRow" style="display:flex;gap:10px">
        <span style="color:var(--muted);min-width:80px">Notes:</span>
        <span id="eventDetailDesc" style="color:var(--muted);line-height:1.4">—</span>
      </div>
    </div>

    <!-- Modal Footer Actions -->
    <div class="mfoot" style="justify-content:space-between">
      <div>
        <button type="button" class="btn danger" id="eventDetailDeleteBtn" style="color:var(--red);border-color:rgba(197,37,35,0.3)">
          <svg viewBox="0 0 24 24" width="14" height="14"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Delete Event
        </button>
      </div>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn" data-close="eventDetailModal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- 9C. Personal Google Calendar Authorization Modal -->
<div class="modal" id="googleAuthModal" role="dialog" aria-modal="true" aria-labelledby="googleAuthTitle">
  <div class="mbg" data-close="googleAuthModal"></div>
  <div class="mbox" style="max-width:500px;border-radius:18px;padding:0;overflow:hidden;background:#fff;color:#202124;box-shadow:0 24px 48px rgba(0,0,0,0.25)">
    <!-- Google OAuth Header -->
    <div style="padding:26px 28px 16px;text-align:center;border-bottom:1px solid #e8eaed;background:#fff">
      <div style="display:flex;justify-content:center;margin-bottom:12px">
        <!-- Official Google 4-Color 'G' Logo -->
        <svg viewBox="0 0 48 48" width="40" height="40">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        </svg>
      </div>
      <h3 id="googleAuthTitle" style="font-family:'Google Sans',Roboto,Segoe UI,sans-serif;font-size:21px;font-weight:500;color:#202124;margin:0 0 6px">Connect Personal Google Calendar</h3>
      <p style="font-size:13px;color:#5f6368;margin:0">Sign in with your Google account to mirror your schedule &amp; video meetings</p>
    </div>

    <!-- Active User & Account Input -->
    <div style="padding:22px 28px;background:#fff">
      <!-- Active JMOS User Info Banner -->
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f8f9fa;border:1px solid #e8eaed;border-radius:10px;margin-bottom:16px">
        <div id="googleAuthUserAvatar" style="width:34px;height:34px;border-radius:50%;background:#1a73e8;color:#fff;display:grid;place-items:center;font-weight:700;font-size:13px;flex-shrink:0">TM</div>
        <div style="flex:1;overflow:hidden;text-overflow:ellipsis">
          <div style="font-size:12.5px;font-weight:600;color:#202124" id="googleAuthUserName">Team Member</div>
          <div style="font-size:11.5px;color:#5f6368" id="googleAuthUserEmail">user@jeotamedia.co.ke</div>
        </div>
      </div>

      <!-- Current Sync State Banner -->
      <div id="googleAuthConnectedAlert" style="display:none;background:#e6f4ea;border:1px solid #ceead6;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#137333">
        <div style="font-weight:600;display:flex;align-items:center;gap:6px">
          <span style="font-size:14px">✓</span> Currently connected to: <span id="googleAuthCurrentConnectedEmail" style="font-weight:700">user@gmail.com</span>
        </div>
        <div style="font-size:11px;color:#137333;margin-top:2px">You can enter a different Google account below to switch, or disconnect anytime.</div>
      </div>

      <!-- Personal Google Account Input -->
      <div style="margin-bottom:16px">
        <label for="personalGoogleEmailInput" style="display:block;font-size:12.5px;font-weight:600;color:#3c4043;margin-bottom:6px">
          Personal Google Account Email *
        </label>
        <div style="display:flex;gap:8px">
          <input type="email" id="personalGoogleEmailInput" placeholder="e.g. yourname@gmail.com" style="flex:1;padding:9px 12px;border:1.5px solid #dadce0;border-radius:8px;font-size:13px;color:#202124;outline:none;background:#fff" autocomplete="email" required>
          <button type="button" class="btn" id="autofillMyEmailBtn" style="white-space:nowrap;font-size:11.5px;border:1px solid #dadce0;background:#f8f9fa;color:#1a73e8;padding:8px 12px" title="Use your logged-in email">
            Use my email
          </button>
        </div>
        <span style="font-size:11px;color:#5f6368;margin-top:4px;display:block">Enter the personal Gmail or Google Workspace address you check on your phone or laptop.</span>
      </div>

      <!-- Permissions & Scopes Disclosure -->
      <div style="background:#f8f9fa;border:1px solid #e8eaed;border-radius:12px;padding:14px">
        <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:8px;display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#1a73e8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Permissions for your personal account:
        </div>
        <div style="display:flex;flex-direction:column;gap:7px;font-size:11.5px;color:#3c4043">
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Bi-directional Event Sync:</b> Shoots, meetings, and deadlines are mirrored to your personal calendar.</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Google Meet Integration:</b> Generates active video rooms for client reviews and production syncs.</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Privacy Protected:</b> Your Google password is never requested or stored. You can disconnect at any time.</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Google OAuth Footer -->
    <div style="padding:16px 28px;background:#f8f9fa;border-top:1px solid #e8eaed;display:flex;justify-content:space-between;align-items:center;gap:10px">
      <div style="display:flex;gap:8px;align-items:center">
        <button type="button" class="btn" data-close="googleAuthModal" style="background:#fff;border:1px solid #dadce0;color:#3c4043;font-size:12.5px;padding:8px 16px;border-radius:8px">Cancel</button>
        <button type="button" class="btn" id="modalDisconnectGoogleBtn" style="display:none;background:#fff;border:1px solid #fecaca;color:#dc2626;font-size:12px;padding:8px 14px;border-radius:8px" title="Disconnect personal calendar">Disconnect</button>
      </div>
      <button type="button" class="btn primary" id="confirmGoogleAuthBtn" style="background:#1a73e8;border-color:#1a73e8;color:#fff;font-size:13px;padding:9px 22px;border-radius:8px;font-weight:600;display:inline-flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Connect &amp; Sync Calendar
      </button>
    </div>
  </div>
</div>

<!-- 10. Add Channel Modal -->
<div class="modal" id="channelModal" role="dialog" aria-modal="true" aria-labelledby="channelModalTitle">
  <div class="mbg" data-close="channelModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="channelModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="channelModalTitle">Create Team Channel</h3>
    <p class="msub">Create a dedicated group discussion channel for shoots, projects or topics.</p>
    <div class="field">
      <label for="nchanName">Channel Name *</label>
      <input id="nchanName" placeholder="e.g. #post-production or #gear-maintenance" required autocomplete="off">
    </div>
    <div class="field">
      <label for="nchanDesc">Topic / Description</label>
      <input id="nchanDesc" placeholder="e.g. Color grading workflows, proxies, and render farm coordination" autocomplete="off">
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="channelModal">Cancel</button>
      <button type="button" class="btn primary" id="saveChannelBtn" onclick="saveNewChannel()">Create Channel</button>
    </div>
  </div>
</div>

<!-- 11. Confirm Removal Modal -->
<div class="modal" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
  <div class="mbg" data-close="confirmModal"></div>
  <div class="mbox" style="max-width:420px">
    <button class="mclose" data-close="confirmModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="confirmTitle">Remove item?</h3>
    <p class="msub" id="confirmMsg">Are you sure you want to remove this record?</p>
    <div class="mfoot">
      <button type="button" class="btn" data-close="confirmModal">Cancel</button>
      <button type="button" class="btn primary" id="confirmYes" style="background:var(--red);border-color:var(--red)">Remove &amp; delete</button>
    </div>
  </div>
</div>

<!-- 10. Deal-Won Cascade Sequence -->
<div class="cascade" id="cascade" role="dialog" aria-modal="true" aria-labelledby="cascadeTitle">
  <div class="cbg" id="cascadeBg"></div>
  <div class="cbox">
    <h3 id="cascadeTitle">Winning Deal…</h3>
    <p class="lede">One click. <b>This is the whole idea.</b></p>
    <div class="csteps">
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct" id="cstep1">Deal moved to <b>Won</b><small id="cstep1sub">Booked in pipeline</small></div></div>
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct" id="cstep2">Project created — <b>Brand Film</b><small>Added to the delivery board</small></div></div>
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct"><b>8 tasks</b> generated from the recipe<small>brief · concept · pre-pro · shoot · edit · color · review · deliver</small></div></div>
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct">Team assigned &amp; notified<small>Barny · Amos · Stephen</small></div></div>
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct" id="cstep5">Deposit invoice drafted<small id="cstep5sub">60% deposit</small></div></div>
      <div class="cstep"><span class="ck"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span><div class="ct">Finance updated<small>Balance &amp; pipeline refreshed</small></div></div>
    </div>
    <div class="cfoot"><button type="button" class="btn primary" id="cascadeDone">Done — that was one click</button></div>
  </div>
</div>

<!-- Toast Notification Container -->
<div class="toasts" id="toasts"></div>
