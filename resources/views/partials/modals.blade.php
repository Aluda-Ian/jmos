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
        <label for="npClient">Client *</label>
        <input id="npClient" placeholder="e.g. Moyo Honey Ltd" required autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="npType">Project type</label>
        <input id="npType" placeholder="e.g. Brand Film / Documentary" autocomplete="off">
      </div>
      <div class="field">
        <label for="npManager">Project manager</label>
        <input id="npManager" placeholder="e.g. Barny Kiome" autocomplete="off">
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
        <label for="ndClient">Client / Prospect name *</label>
        <input id="ndClient" placeholder="e.g. Sanlam Kenya" required autocomplete="off">
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

<!-- 4. Add Task Modal -->
<div class="modal" id="taskModal" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
  <div class="mbg" data-close="taskModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="taskModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="taskModalTitle">Create a task</h3>
    <p class="msub">Assign a task to team members across project workflows.</p>
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
        <label for="niClient">Client *</label>
        <input id="niClient" placeholder="e.g. Acre Insights" required autocomplete="off">
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
    <div class="field">
      <label for="nevtAttendees">Attendees / Team</label>
      <input id="nevtAttendees" placeholder="e.g. Barny Kiome, Amos Muthama, Client Team" autocomplete="off">
    </div>
    <div class="field">
      <label for="nevtDesc">Description / Notes</label>
      <input id="nevtDesc" placeholder="e.g. Discuss script storyboard and location permits" autocomplete="off">
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="eventModal">Cancel</button>
      <button type="button" class="btn primary" id="saveEventBtn">Schedule event</button>
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
