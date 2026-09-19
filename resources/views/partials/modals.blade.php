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
        <label for="ncEmail">Contact email</label>
        <input id="ncEmail" type="email" placeholder="e.g. client@brand.co.ke" autocomplete="off">
      </div>
      <div class="field">
        <label for="ncPhone">Contact phone</label>
        <input id="ncPhone" type="tel" placeholder="e.g. +254 712 345 678" autocomplete="off">
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

<!-- 1b. Client Detailed Workspace & Overview Modal -->
<div class="modal" id="clientDetailModal" role="dialog" aria-modal="true" aria-labelledby="cdmTitle">
  <div class="mbg" data-close="clientDetailModal"></div>
  <div class="mbox" style="max-height:92vh;overflow-y:auto;padding:0;overflow-x:hidden;max-width:880px;width:95%">
    <!-- Notion-style Cover Banner -->
    <div style="height:120px;background:linear-gradient(135deg, #182848 0%, #2B6E8A 55%, #C52523 100%);position:relative;border-radius:16px 16px 0 0">
      <button class="mclose" data-close="clientDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      
      <!-- Dynamic Client Initials Avatar -->
      <div id="cdmAvatar" style="position:absolute;bottom:-24px;left:28px;width:56px;height:56px;border-radius:14px;background:var(--red);box-shadow:0 4px 16px rgba(0,0,0,0.25);display:grid;place-items:center;color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;border:3px solid var(--surface)">
        CL
      </div>
    </div>

    <div style="padding:36px 28px 24px">
      <!-- Title & Header Actions -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div style="flex:1;min-width:260px">
          <input type="hidden" id="cdmClientId">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
            <span class="badge" id="cdmTypeBadge" style="background:rgba(43,110,138,0.15);color:#2B6E8A;font-size:11px;font-weight:600">Corporate</span>
            <span class="pill tint-green" id="cdmStatusBadge">Active</span>
            <span class="badge" id="cdmServiceBadge" style="background:var(--panel-2);color:var(--muted);font-size:11px">Brand Film</span>
          </div>
          <h2 id="cdmTitle" style="font-family:'Poppins',sans-serif;font-size:24px;font-weight:700;margin:0;color:var(--ink);line-height:1.2">Client Name</h2>
          <div style="font-size:12px;color:var(--muted);margin-top:4px" id="cdmSubtitle">Managed by Barny Kiome • Primary Contact: Peter Karanja</div>
        </div>

        <!-- Quick Header Action Buttons -->
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn" id="cdmNewProjectBtn" style="font-size:12px;padding:6px 12px" title="Create new live project for this client">
            <svg viewBox="0 0 24 24" width="13" height="13"><polygon points="5 3 19 12 5 21 5 3"/></svg>+ Project
          </button>
          <button type="button" class="btn" id="cdmNewInvoiceBtn" style="font-size:12px;padding:6px 12px" title="Draft new invoice for this client">
            <svg viewBox="0 0 24 24" width="13" height="13"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>+ Invoice
          </button>
          <button type="button" class="btn" id="cdmScheduleShootBtn" style="font-size:12px;padding:6px 12px" title="Schedule production shoot for this client">
            <svg viewBox="0 0 24 24" width="13" height="13"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Shoot
          </button>
        </div>
      </div>

      <!-- Quick Stats Row -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:12px;margin-bottom:24px">
        <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Projects</div>
          <div style="display:flex;align-items:baseline;gap:6px;margin-top:2px">
            <span id="cdmStatActiveProjects" style="font-size:18px;font-weight:700;color:var(--ink)">0</span>
            <span id="cdmStatTotalProjects" style="font-size:11.5px;color:var(--muted)">active / 0 total</span>
          </div>
        </div>
        <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Total Value</div>
          <div class="mono" id="cdmStatTotalValue" style="font-size:17px;font-weight:700;color:var(--ink);margin-top:2px">KES 0</div>
        </div>
        <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Invoiced / Paid</div>
          <div style="margin-top:2px">
            <div class="mono" id="cdmStatInvoiced" style="font-size:15px;font-weight:700;color:var(--ink)">KES 0</div>
            <div id="cdmStatPaid" style="font-size:11px;color:var(--green);font-weight:600">KES 0 collected</div>
          </div>
        </div>
        <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Lead Producer</div>
          <div id="cdmStatOwner" style="font-size:14px;font-weight:600;color:var(--ink);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Barny Kiome</div>
        </div>
      </div>

      <!-- Tab Navigation Bar -->
      <div class="client-tab-nav">
        <button type="button" class="client-tab-btn active" data-cdm-tab="projects">
          <svg viewBox="0 0 24 24" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          <span>Projects</span>
          <span class="client-tab-badge" id="cdmTabProjectsCount">0</span>
        </button>
        <button type="button" class="client-tab-btn" data-cdm-tab="info">
          <svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>Information &amp; Contacts</span>
        </button>
        <button type="button" class="client-tab-btn" data-cdm-tab="invoices">
          <svg viewBox="0 0 24 24" width="14" height="14"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          <span>Invoices</span>
          <span class="client-tab-badge" id="cdmTabInvoicesCount">0</span>
        </button>
        <button type="button" class="client-tab-btn" data-cdm-tab="shoots">
          <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span>Shoots &amp; Dates</span>
          <span class="client-tab-badge" id="cdmTabShootsCount">0</span>
        </button>
      </div>

      <!-- Tab 1: Projects List -->
      <div id="cdmTabPaneProjects" class="cdm-tab-pane">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="font-size:13px;font-weight:600;color:var(--ink)">Active &amp; Delivered Projects</div>
          <button type="button" class="btn primary" id="cdmAddProjectFromTabBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Add Project
          </button>
        </div>
        <div id="cdmProjectsList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Tab 2: Information & Contacts -->
      <div id="cdmTabPaneInfo" class="cdm-tab-pane" style="display:none">
        <div class="grid2">
          <div class="field">
            <label for="cdmInputName">Client / Brand Name *</label>
            <input id="cdmInputName" placeholder="Client Name" required>
          </div>
          <div class="field">
            <label for="cdmSelectType">Client Type / Industry</label>
            <select id="cdmSelectType">
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
            <label for="cdmInputContact">Primary Contact Person</label>
            <input id="cdmInputContact" placeholder="e.g. Peter Karanja">
          </div>
          <div class="field">
            <label for="cdmSelectOwner">Account Lead / Owner</label>
            <select id="cdmSelectOwner">
              <option value="Barny Kiome">Barny Kiome (Executive Producer)</option>
              <option value="Ian Aluda">Ian Aluda (IT &amp; Systems)</option>
              <option value="Lesley Chacha">Lesley Chacha (Client Relations)</option>
              <option value="Patrick Mwendwa">Patrick Mwendwa (Sales)</option>
              <option value="Matthew Muange">Matthew Muange (Finance)</option>
            </select>
          </div>
        </div>

        <div class="grid2">
          <div class="field">
            <label for="cdmInputEmail">Contact Email</label>
            <div style="display:flex;gap:6px">
              <input id="cdmInputEmail" type="email" placeholder="client@brand.co.ke" style="flex:1">
              <a id="cdmEmailLink" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Send Email">✉ Email</a>
            </div>
          </div>
          <div class="field">
            <label for="cdmInputPhone">Contact Phone</label>
            <div style="display:flex;gap:6px">
              <input id="cdmInputPhone" type="tel" placeholder="+254 700 000 000" style="flex:1">
              <a id="cdmPhoneLink" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Call / Contact">📞 Call</a>
            </div>
          </div>
        </div>

        <div class="grid2">
          <div class="field">
            <label for="cdmInputAddress">Physical Address / Office Location</label>
            <input id="cdmInputAddress" placeholder="e.g. Westlands, Nairobi, Kenya">
          </div>
          <div class="field">
            <label for="cdmInputWebsite">Website</label>
            <div style="display:flex;gap:6px">
              <input id="cdmInputWebsite" placeholder="https://example.com" style="flex:1">
              <a id="cdmWebsiteLink" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Website">↗ Visit</a>
            </div>
          </div>
        </div>

        <div class="grid2">
          <div class="field">
            <label for="cdmInputService">Primary Service</label>
            <input id="cdmInputService" placeholder="e.g. Brand Film, Social Media">
          </div>
          <div class="field">
            <label for="cdmSelectStatus">Relationship Status</label>
            <select id="cdmSelectStatus">
              <option value="Active">Active</option>
              <option value="On hold">On hold</option>
              <option value="Lead">Lead</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div class="field" style="margin-top:8px">
          <label for="cdmTextareaNotes">Internal Client Notes &amp; Relationship History</label>
          <textarea id="cdmTextareaNotes" rows="3" placeholder="Key preferences, communication style, special contract terms, shoot requirements..." style="width:100%;border:1px solid var(--line);border-radius:8px;padding:10px;font-size:13px;background:var(--surface);resize:vertical"></textarea>
        </div>
      </div>

      <!-- Tab 3: Invoices & Billing -->
      <div id="cdmTabPaneInvoices" class="cdm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="font-size:13px;font-weight:600;color:var(--ink)">Invoices &amp; Retainer Records</div>
          <button type="button" class="btn primary" id="cdmAddInvoiceFromTabBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Issue Invoice
          </button>
        </div>
        <div id="cdmInvoicesList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Tab 4: Shoots & Calendar -->
      <div id="cdmTabPaneShoots" class="cdm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="font-size:13px;font-weight:600;color:var(--ink)">Production Shoots &amp; Scheduled Meetings</div>
          <button type="button" class="btn primary" id="cdmAddShootFromTabBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Schedule Shoot
          </button>
        </div>
        <div id="cdmShootsList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Action Footer -->
      <div class="mfoot" style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--line);margin-top:24px">
        <button type="button" class="btn" id="cdmDeleteBtn" style="color:var(--red);border-color:var(--line)">
          <svg viewBox="0 0 24 24" width="13" height="13"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete Client
        </button>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn" data-close="clientDetailModal">Close</button>
          <button type="button" class="btn primary" id="cdmSaveBtn">Save Changes</button>
        </div>
      </div>
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
      <div class="field">
        <label for="npCategory">Category</label>
        <select id="npCategory">
          <option value="client">Client Deliverable</option>
          <option value="internal">Internal &amp; Systems (e.g. JMOS Development)</option>
        </select>
      </div>
      <div class="field">
        <label for="npType">Project type</label>
        <select id="npType">
          <option value="Internal System Development">Internal System Development (JMOS / Tech)</option>
          <option value="Internal Operations &amp; Studio R&amp;D">Internal Operations &amp; Studio R&amp;D</option>
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
    </div>
    <div class="grid2">
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
        <label for="npDeadline">Deadline Date *</label>
        <input id="npDeadline" type="date" class="date-input" required autocomplete="off" style="font-family:inherit">
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
    <div style="height:110px;background:linear-gradient(135deg, #E02826 0%, #C52523 25%, #6B0E0D 58%, #1F0505 82%, #080709 100%);position:relative;border-radius:16px 16px 0 0">
      <button class="mclose" data-close="projectDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="position:absolute;bottom:-20px;left:28px;width:44px;height:44px;border-radius:10px;background:var(--surface);box-shadow:0 4px 14px rgba(0,0,0,0.15);display:grid;place-items:center;color:var(--ink)">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
      </div>
    </div>

    <div style="padding:32px 28px 24px">
      <!-- Title & Client Header -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px">
        <div style="flex:1">
          <input type="hidden" id="pdmProjectId">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap">
            <span class="badge" id="pdmCategoryBadge" style="background:rgba(110,43,138,0.15);color:#8A2BE2;font-size:11px;font-weight:600">Internal</span>
            <span class="badge" id="pdmClientBadge" style="background:var(--red-soft);color:var(--red);font-size:11px;cursor:pointer" title="Click to open client workspace">Client</span>
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
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
            <span>Category:</span>
          </div>
          <div style="flex:1">
            <select id="pdmCategorySelect" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <option value="client">Client Deliverable</option>
              <option value="internal">Internal &amp; Systems (e.g. JMOS Development)</option>
            </select>
          </div>
        </div>

        <div class="project-prop-row" style="display:flex;align-items:center;padding:7px 0;border-bottom:1px solid var(--line-soft)">
          <div style="width:140px;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);font-weight:500">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <span>Project Type:</span>
          </div>
          <div style="flex:1">
            <select id="pdmTypeSelect" style="padding:4px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <option value="Internal System Development">Internal System Development (JMOS / Tech)</option>
              <option value="Internal Operations &amp; Studio R&amp;D">Internal Operations &amp; Studio R&amp;D</option>
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
        </div>

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
    <div class="field" id="taskProjectPickerField">
      <label for="taskProjectTrigger">Attach to Project</label>
      <input type="hidden" id="ntProject" name="project_id" value="">
      <div class="searchable-select-wrap" id="taskProjectSelectWrap">
        <div class="searchable-select-trigger" id="taskProjectTrigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
          <span class="searchable-select-label" id="taskProjectSelectedLabel">
            <span style="color:var(--muted)">— Select a project —</span>
          </span>
          <div class="searchable-select-trigger-actions">
            <button type="button" class="searchable-select-clear" id="taskProjectClearBtn" title="Clear project selection" style="display:none" aria-label="Clear project selection">&times;</button>
            <svg class="searchable-select-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </div>
        </div>

        <div class="searchable-select-dropdown" id="taskProjectDropdown" style="display:none">
          <div class="searchable-select-search-wrap">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="taskProjectSearchInput" placeholder="Search projects by name or client…" autocomplete="off">
            <button type="button" class="searchable-select-input-clear" id="taskProjectSearchClear" style="display:none" title="Clear search">&times;</button>
          </div>
          <div class="searchable-select-list" id="taskProjectOptionsList" role="listbox">
            <!-- Populated dynamically with search options -->
          </div>
        </div>
      </div>
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
    <p class="msub" id="invoiceModalSub">Record an outgoing client invoice in JMOS.</p>
    <input type="hidden" id="editInvoiceId" value="">
    <div class="grid2">
      <div class="field">
        <label for="niNo">Invoice No <span style="font-size:11px;color:var(--muted);font-weight:normal">(Auto-assigned ascending)</span></label>
        <input id="niNo" placeholder="Auto-assigned (e.g. JM-0146)" autocomplete="off">
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
    <div class="grid2" id="invoiceStatusFields">
      <div class="field">
        <label for="niStatus">Invoice Status</label>
        <select id="niStatus">
          <option value="Sent">Sent</option>
          <option value="Paid">Paid</option>
          <option value="Overdue">Overdue</option>
        </select>
      </div>
      <div class="field">
        <label for="niMethod">Payment Method</label>
        <input id="niMethod" placeholder="e.g. M-Pesa, Bank Transfer, Cheque" autocomplete="off">
      </div>
    </div>
    <div class="mfoot" style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <button type="button" class="btn danger" id="deleteInvoiceModalBtn" style="display:none;align-items:center;gap:4px">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>Delete
        </button>
      </div>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn" data-close="invoiceModal">Cancel</button>
        <button type="button" class="btn primary" id="saveInvoiceBtn">Issue invoice</button>
      </div>
    </div>
  </div>
</div>

<!-- 6. Log / Edit Expense Modal -->
<div class="modal" id="expenseModal" role="dialog" aria-modal="true" aria-labelledby="expenseModalTitle">
  <div class="mbg" data-close="expenseModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="expenseModal" title="Close" aria-label="Close modal">&times;</button>
    <input type="hidden" id="editExpenseId" value="">
    <input type="hidden" id="neReceiptUrl" value="">
    <input type="hidden" id="neReceiptName" value="">
    <h3 id="expenseModalTitle">Log an expense</h3>
    <p class="msub" id="expenseModalSub">Track project costs and operational expenses with ETR &amp; eTIMS compliance.</p>
    
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
      <div class="field" id="expenseProjectPickerField">
        <label for="expenseProjectTrigger">Project / Cost Allocation</label>
        <input type="hidden" id="neProject" name="project" value="overhead">
        <div class="searchable-select-wrap" id="expenseProjectSelectWrap">
          <div class="searchable-select-trigger" id="expenseProjectTrigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
            <span class="searchable-select-label" id="expenseProjectSelectedLabel">
              <span style="color:var(--muted)">🏢 General Overhead / Operations</span>
            </span>
            <div class="searchable-select-trigger-actions">
              <button type="button" class="searchable-select-clear" id="expenseProjectClearBtn" title="Reset to overhead" style="display:none" aria-label="Reset to overhead">&times;</button>
              <svg class="searchable-select-arrow" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>

          <div class="searchable-select-dropdown" id="expenseProjectDropdown" style="display:none">
            <div class="searchable-select-search-wrap">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" id="expenseProjectSearchInput" placeholder="Search active projects or client…" autocomplete="off">
              <button type="button" class="searchable-select-input-clear" id="expenseProjectSearchClear" style="display:none" title="Clear search">&times;</button>
            </div>
            <div class="searchable-select-list" id="expenseProjectOptionsList" role="listbox">
              <!-- Dynamically populated from active database projects -->
            </div>
          </div>
        </div>
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

    <div class="grid2">
      <div class="field">
        <label for="neEtimsNumber">eTIMS / KRA CU Invoice No.</label>
        <input id="neEtimsNumber" placeholder="e.g. KRA-ETIMS-002194 or CU01-08123" autocomplete="off">
      </div>
      <div class="field">
        <label for="neDate">Expense Date</label>
        <input id="neDate" placeholder="e.g. Aug 15 or 2026-09-14" autocomplete="off">
      </div>
    </div>

    <!-- Support Documents: Receipt or eTIMS Upload -->
    <div class="field" style="margin-top:4px">
      <label>Support Document (Receipt / eTIMS / Invoice)</label>
      <div class="expense-upload-box" id="expenseDropzone">
        <input type="file" id="neReceiptFile" accept=".pdf,.jpg,.jpeg,.png,.webp,.heic,.docx,.doc">
        <div class="expense-upload-icon">
          <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <div class="expense-upload-label" id="expenseUploadText">Click or drag &amp; drop receipt / eTIMS file</div>
        <div class="expense-upload-hint">PDF, PNG, JPG, or DOCX up to 25MB</div>
      </div>

      <!-- Uploaded Document Preview Card -->
      <div class="expense-receipt-preview" id="neReceiptPreview" style="display:none">
        <div class="expense-receipt-meta">
          <div class="expense-receipt-icon" id="neReceiptIcon">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div>
            <div class="expense-receipt-name" id="neReceiptDisplayName">document.pdf</div>
            <div style="font-size:11px;color:var(--muted)" id="neReceiptMeta">Attached Document</div>
          </div>
        </div>
        <div class="expense-receipt-actions">
          <a href="#" target="_blank" class="btn" id="neReceiptViewBtn" style="padding:4px 10px;font-size:11.5px;text-decoration:none" rel="noopener">View</a>
          <button type="button" class="btn danger" id="neReceiptRemoveBtn" style="padding:4px 10px;font-size:11.5px">Remove</button>
        </div>
      </div>
    </div>

    <div class="field" style="margin-top:6px">
      <label for="neNotes">Notes / Vendor Remarks (Optional)</label>
      <input id="neNotes" placeholder="e.g. Paid via M-Pesa to vendor till; ETR received via WhatsApp" autocomplete="off">
    </div>

    <div class="mfoot" style="display:flex;align-items:center;justify-content:space-between">
      <button type="button" class="btn danger" id="deleteExpenseModalBtn" style="display:none">Delete expense</button>
      <div style="display:flex;align-items:center;gap:8px;margin-left:auto">
        <button type="button" class="btn" data-close="expenseModal">Cancel</button>
        <button type="button" class="btn primary" id="saveExpenseBtn">Log expense</button>
      </div>
    </div>
  </div>
</div>

<!-- 7. Add / Edit Person Modal -->
<div class="modal" id="userModal" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
  <div class="mbg" data-close="userModal"></div>
  <div class="mbox">
    <button class="mclose" data-close="userModal" title="Close" aria-label="Close modal">&times;</button>
    <input type="hidden" id="editUserId" value="">
    <h3 id="userModalTitle">Add a person</h3>
    <p class="msub" id="userModalSub">They'll get their own login and only see what their access level allows.</p>

    <!-- Avatar Preview & Upload -->
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;padding:12px 14px;background:var(--paper);border:1px solid var(--line);border-radius:10px">
      <div id="nuAvatarBox" style="width:48px;height:48px;border-radius:50%;background:var(--red);color:#fff;display:grid;place-items:center;font-weight:700;font-size:16px;overflow:hidden;flex-shrink:0;border:2px solid var(--line)">
        <span id="nuAvatarInitials">TM</span>
      </div>
      <div style="flex:1">
        <div style="font-size:12px;font-weight:600;margin-bottom:3px">Profile Picture (Optional)</div>
        <div style="display:flex;gap:8px;align-items:center">
          <input type="file" id="nuAvatarFile" accept="image/*" style="display:none">
          <button type="button" class="btn sm" onclick="document.getElementById('nuAvatarFile').click()">Choose photo</button>
          <span id="nuAvatarFileName" style="font-size:11px;color:var(--muted)">No file chosen</span>
        </div>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="nuName">Full name *</label>
        <input id="nuName" placeholder="e.g. Grace Wanjiru" required autocomplete="off">
      </div>
      <div class="field">
        <label for="nuTitle">Role / title</label>
        <input id="nuTitle" placeholder="e.g. Lead Photographer" autocomplete="off">
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nuDept">Department</label>
        <input id="nuDept" list="departmentSuggestions" placeholder="e.g. Production, Creative, Video" autocomplete="off">
        <datalist id="departmentSuggestions">
          <option value="Production">
          <option value="Creative">
          <option value="Video &amp; Cinematography">
          <option value="Photography">
          <option value="Audio &amp; Sound">
          <option value="Post-Production &amp; 3D">
          <option value="Sales &amp; Marketing">
          <option value="Finance &amp; Operations">
          <option value="Executive &amp; Management">
        </datalist>
      </div>
      <div class="field">
        <label for="nuPhone">Phone / WhatsApp</label>
        <input id="nuPhone" placeholder="e.g. +254 700 000 000" autocomplete="off">
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
          <option value="manager">Manager (Operations &amp; IT)</option>
          <option value="owner">Owner (full access)</option>
        </select>
      </div>
      <div class="field">
        <label for="nuType">Employment type</label>
        <select id="nuType">
          <option value="Full-time">Full-time</option>
          <option value="Per-project">Per-project</option>
          <option value="Part-time">Part-time</option>
          <option value="Contractor">Contractor</option>
        </select>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="nuPay">Salary / Wages (Pay)</label>
        <input id="nuPay" placeholder="e.g. 65,000/mo or 5,000/day" autocomplete="off">
      </div>
      <div class="field">
        <label for="nuPass" id="nuPassLabel">Temporary password</label>
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
          <option value="status_meeting">Status Meeting</option>
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

<!-- 11. Branded Confirmation & Action Modal -->
<div class="modal" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
  <div class="mbg" data-close="confirmModal"></div>
  <div class="mbox confirm-mbox" style="max-width:480px;padding:26px 28px;position:relative;border-radius:18px">
    <button class="mclose" data-close="confirmModal" title="Close" aria-label="Close modal">&times;</button>
    
    <div class="confirm-head-wrap" style="display:flex;align-items:flex-start;gap:14px;margin-bottom:14px">
      <div id="confirmIconWrap" class="confirm-icon-wrap danger">
        <svg id="confirmIconSvg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <div style="flex:1;min-width:0">
        <h3 id="confirmTitle" style="font-family:'Poppins',sans-serif;font-size:17.5px;font-weight:700;margin:0 0 3px;color:var(--ink);line-height:1.25">Confirm Action</h3>
        <p id="confirmSubtitle" class="msub" style="margin:0;font-size:12px;color:var(--muted);line-height:1.4">Please verify before proceeding.</p>
      </div>
    </div>

    <div id="confirmBodyWrap" style="margin-bottom:20px">
      <div id="confirmMsg" style="font-size:13.5px;color:var(--ink);line-height:1.55">
        Are you sure you want to proceed?
      </div>
      <div id="confirmBullets" style="margin-top:12px;display:none;flex-direction:column;gap:8px" class="confirm-bullets"></div>
    </div>

    <div class="mfoot" style="margin-top:0;padding-top:16px;border-top:1px solid var(--line,#ece6e4);display:flex;justify-content:flex-end;gap:10px">
      <button type="button" class="btn" id="confirmCancelBtn" data-close="confirmModal" style="padding:8px 16px;font-size:13px;font-weight:500">Cancel</button>
      <button type="button" class="btn primary" id="confirmYes" style="padding:8px 18px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
        <span id="confirmYesText">Confirm &amp; Proceed</span>
      </button>
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

<!-- 12. PWA App Installation & Download Modal (Unified 2-Step Flow: Notifications -> Download) -->
<div class="modal" id="pwaInstallModal" role="dialog" aria-modal="true" aria-labelledby="pwaInstallTitle">
  <div class="mbg" data-close="pwaInstallModal"></div>
  <div class="mbox" style="max-width:520px;padding:24px">
    <button class="mclose" data-close="pwaInstallModal" title="Close" aria-label="Close modal">&times;</button>
    
    <!-- Modal Header -->
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;border-bottom:1px solid var(--line);padding-bottom:14px">
      <img src="{{ asset('assets/img/jeota-logo.png') }}" alt="JMOS App" style="width:46px;height:46px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.18)">
      <div style="flex:1">
        <h3 id="pwaInstallTitle" style="margin:0;font-size:17px;font-weight:700;letter-spacing:-0.2px">JMOS Application Setup</h3>
        <p style="margin:2px 0 0;font-size:12px;color:var(--muted)">Operational Push Alerts &amp; Standalone Device App</p>
      </div>
    </div>

    <!-- Step Progress Indicator Tabs -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:18px">
      <button type="button" id="pwaTabStep1" onclick="window.JMOS_PWA.setStep(1)" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid var(--red);background:var(--red-soft);color:var(--red);font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s ease">
        <span style="display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:currentColor;color:var(--surface,#1e1715);font-size:10.5px;font-weight:700">1</span>
        <span>Push Alerts</span>
      </button>
      <button type="button" id="pwaTabStep2" onclick="window.JMOS_PWA.setStep(2)" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--muted);font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s ease">
        <span style="display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:var(--line);color:var(--ink);font-size:10.5px;font-weight:700">2</span>
        <span>Download App</span>
      </button>
    </div>

    <!-- ================= STEP 1: Push Notifications ================= -->
    <div id="pwaStep1Notifications" style="display:block">
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:14px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <div style="font-weight:700;font-size:13.5px;color:var(--ink);display:flex;align-items:center;gap:8px">
            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--red)"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Step 1: Real-Time Operational Alerts
          </div>
          <span id="pwaNotifStatusBadge" class="badge" style="font-size:10.5px;padding:2px 8px;border-radius:6px;background:rgba(217,119,6,0.15);color:var(--amber);font-weight:600">Action Required</span>
        </div>
        <p style="font-size:12px;color:var(--muted);line-height:1.55;margin:0 0 12px">
          Stay continuously informed on your mobile device &amp; PC even when JMOS is minimized or closed.
        </p>

        <!-- Feature Points -->
        <div style="display:flex;flex-direction:column;gap:8px;font-size:11.5px;color:var(--ink);margin-bottom:14px">
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:var(--red);font-size:13px;line-height:1">⚡</span>
            <span><b>Production &amp; Shoots:</b> Instant call sheets, schedule sync &amp; crew assignments.</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:var(--blue);font-size:13px;line-height:1">💬</span>
            <span><b>Live Team Chat:</b> Direct alerts when clients or team members send messages.</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:var(--green);font-size:13px;line-height:1">💳</span>
            <span><b>Finance &amp; Deals:</b> Live notifications when quotes are approved &amp; invoices paid.</span>
          </div>
        </div>

        <button type="button" class="btn primary" id="pwaAllowNotifsBtn" onclick="window.JMOS_PWA.enableNotificationsAndProceed()" style="width:100%;font-size:12.5px;padding:10px 14px;display:flex;align-items:center;justify-content:center;gap:8px;font-weight:600">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Allow Live Alerts &amp; Continue
        </button>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <button type="button" class="btn secondary" data-close="pwaInstallModal" style="font-size:11.5px;padding:6px 12px">Cancel</button>
        <button type="button" class="linkbtn" onclick="window.JMOS_PWA.setStep(2)" style="font-size:12px;color:var(--muted);text-decoration:underline;cursor:pointer">Skip to Download &rarr;</button>
      </div>
    </div>

    <!-- ================= STEP 2: Download & Install App ================= -->
    <div id="pwaStep2Download" style="display:none">
      <!-- Quick instructions for Android/Chrome/Edge/PC -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px;margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
          <div style="font-weight:700;font-size:13px;color:var(--ink);display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--blue)"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            PC / Mac / Android (Direct Install)
          </div>
          <span class="badge pwa-status-badge" style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(2,132,199,0.15);color:var(--blue);font-weight:600">Ready</span>
        </div>
        <p style="font-size:11.5px;color:var(--muted);margin:0 0 10px;line-height:1.45">Install JMOS as a standalone application on Windows, macOS, or Android for full-screen offline productivity:</p>
        <button type="button" class="btn primary pwa-install-btn" onclick="window.JMOS_PWA.triggerInstall()" style="width:100%;font-size:12.5px;padding:9px 14px;display:flex;align-items:center;justify-content:center;gap:8px;font-weight:600">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
          Download &amp; Install on this Device
        </button>
      </div>

      <!-- Instructions for iPhone / iPad (Safari iOS) -->
      <div style="background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px;margin-bottom:14px">
        <div style="font-weight:700;font-size:13px;color:var(--ink);display:flex;align-items:center;gap:6px;margin-bottom:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--purple,#a855f7)"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
          iPhone &amp; iPad (Apple Safari)
        </div>
        <ol style="margin:0;padding-left:18px;font-size:11.5px;color:var(--muted);line-height:1.6">
          <li>Tap the <b>Share</b> button <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> in Safari's bottom toolbar.</li>
          <li>Scroll down and tap <b>Add to Home Screen</b>.</li>
          <li>Tap <b>Add</b> in the top right. JMOS will launch in full-screen standalone mode!</li>
        </ol>
      </div>

      <!-- Modal Footer -->
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <button type="button" class="btn" onclick="window.JMOS_PWA.setStep(1)" style="font-size:11.5px;padding:6px 12px">← Back to Alerts</button>
        <button type="button" class="btn secondary" data-close="pwaInstallModal" style="font-size:11.5px;padding:6px 12px">Done</button>
      </div>
    </div>

  </div>
</div>

<!-- ==========================================================================
     ZOHO CRM LEAD GENERATION & JOURNEY MODALS
     ========================================================================== -->

<!-- A. Create / Edit Lead Modal -->
<div class="modal" id="leadModal" role="dialog" aria-modal="true" aria-labelledby="leadModalTitle">
  <div class="mbg" data-close="leadModal"></div>
  <div class="mbox" style="max-width:640px">
    <button class="mclose" data-close="leadModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="leadModalTitle">Create Lead</h3>
    <p class="msub">Capture a new prospective client into your sales pipeline.</p>
    
    <input type="hidden" id="leadFormId">
    <div class="grid2">
      <div class="field">
        <label for="leadFirstName">First name</label>
        <input id="leadFirstName" placeholder="e.g. Ian" autocomplete="off">
      </div>
      <div class="field">
        <label for="leadLastName">Last name *</label>
        <input id="leadLastName" placeholder="e.g. Aluda" required autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="leadCompany">Company / Brand *</label>
        <input id="leadCompany" placeholder="e.g. Venda Technologies" required autocomplete="off">
      </div>
      <div class="field">
        <label for="leadTitle">Job title / Designation</label>
        <input id="leadTitle" placeholder="e.g. Head of Marketing" autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="leadEmail">Email address</label>
        <input id="leadEmail" type="email" placeholder="e.g. client@brand.co.ke" autocomplete="off">
      </div>
      <div class="field">
        <label for="leadPhone">Phone number</label>
        <input id="leadPhone" type="tel" placeholder="e.g. +254 712 345 678" autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="leadSource">Lead source</label>
        <select id="leadSource">
          <option value="Web Research">Web Research</option>
          <option value="LinkedIn">LinkedIn</option>
          <option value="Referral">Referral</option>
          <option value="Cold Outreach">Cold Outreach</option>
          <option value="Website">Website</option>
          <option value="Campaign">Campaign</option>
          <option value="Partner">Partner</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="field">
        <label for="leadOwner">Lead owner</label>
        <select id="leadOwner">
          <option value="Jeota Media">Jeota Media</option>
          <option value="Barny Kiome">Barny Kiome (Owner)</option>
          <option value="Patrick Mwendwa">Patrick Mwendwa (Sales)</option>
          <option value="Lesley Chacha">Lesley Chacha (Client Relations)</option>
          <option value="Amos Muthama">Amos Muthama</option>
        </select>
      </div>
    </div>

    <div class="grid3">
      <div class="field">
        <label for="leadRating">Lead rating</label>
        <select id="leadRating">
          <option value="Hot">Hot 🔥</option>
          <option value="Warm" selected>Warm ⚡</option>
          <option value="Cold">Cold ❄️</option>
        </select>
      </div>
      <div class="field">
        <label for="leadStatus">Lead status</label>
        <select id="leadStatus">
          <option value="New">New</option>
          <option value="Attempted to Contact">Attempted to Contact</option>
          <option value="Contacted">Contacted</option>
          <option value="In Discussion">In Discussion</option>
          <option value="Qualified">Qualified</option>
          <option value="Junk/Lost">Junk/Lost</option>
        </select>
      </div>
      <div class="field">
        <label for="leadRevenue">Est. Value (KES)</label>
        <input id="leadRevenue" type="number" placeholder="e.g. 450000" autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="leadIndustry">Industry</label>
        <select id="leadIndustry">
          <option value="Corporate">Corporate</option>
          <option value="Tech">Tech</option>
          <option value="Fintech">Fintech</option>
          <option value="Agency">Agency</option>
          <option value="Hospitality">Hospitality</option>
          <option value="Agritech">Agritech</option>
          <option value="Direct">Direct</option>
        </select>
      </div>
      <div class="field">
        <label for="leadCity">City / Location</label>
        <input id="leadCity" placeholder="e.g. Nairobi, Kenya" autocomplete="off">
      </div>
    </div>

    <div class="field">
      <label for="leadNotes">Notes &amp; Scope Summary</label>
      <textarea id="leadNotes" rows="3" placeholder="Add any background, project scope, or notes from initial outreach..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="leadModal">Cancel</button>
      <button type="button" class="btn primary" id="saveLeadBtn" onclick="window.submitLeadForm()">Save Lead</button>
    </div>
  </div>
</div>

<!-- B. Zoho CRM Lead Workspace & Journey Drawer Modal -->
<div class="modal" id="leadDetailModal" role="dialog" aria-modal="true" aria-labelledby="ldmLeadName">
  <div class="mbg" data-close="leadDetailModal"></div>
  <div class="mbox" style="max-height:92vh;overflow-y:auto;padding:0;overflow-x:hidden;max-width:880px;width:95%">
    <!-- Notion / Zoho Banner Header -->
    <div style="height:110px;background:linear-gradient(135deg, #182848 0%, #2B6E8A 50%, #C52523 100%);position:relative;border-radius:16px 16px 0 0">
      <button class="mclose" data-close="leadDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      
      <!-- Dynamic Lead Initials Avatar -->
      <div id="ldmAvatar" style="position:absolute;bottom:-22px;left:28px;width:54px;height:54px;border-radius:14px;background:var(--red);box-shadow:0 4px 16px rgba(0,0,0,0.25);display:grid;place-items:center;color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;border:3px solid var(--surface)">
        IA
      </div>
    </div>

    <div style="padding:32px 28px 24px">
      <!-- Title & Header Actions -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:260px">
          <input type="hidden" id="ldmLeadId">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
            <span class="badge" id="ldmRatingBadge" style="background:rgba(197,37,35,0.12);color:var(--red);font-size:11px;font-weight:700">Hot 🔥</span>
            <span class="badge" id="ldmSourceBadge" style="background:var(--panel-2);color:var(--muted);font-size:11px">Web Research</span>
            <span class="pill tint-amber" id="ldmStatusBadge">Qualified</span>
          </div>
          <h2 id="ldmLeadName" style="font-family:'Poppins',sans-serif;font-size:24px;font-weight:700;margin:0;color:var(--ink);line-height:1.2">Ian Aluda</h2>
          <div style="font-size:12.5px;color:var(--muted);margin-top:4px" id="ldmSubtitle">Chief Technology Officer at Venda Technologies</div>
        </div>

        <!-- Zoho CRM Primary Actions -->
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn" onclick="window.JMOS_QUOTES.openCreateModal(document.getElementById('ldmLeadId').value)" style="font-size:12px;padding:6px 12px" title="Generate quotation for lead">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>Quote Lead
          </button>
          <button type="button" class="btn" onclick="window.openLogCallForActiveLead()" style="font-size:12px;padding:6px 12px" title="Log phone call with this lead">
            <svg viewBox="0 0 24 24" width="13" height="13"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Log Call
          </button>
          <button type="button" class="btn" onclick="window.openScheduleMeetingForActiveLead()" style="font-size:12px;padding:6px 12px" title="Schedule discovery meeting">
            <svg viewBox="0 0 24 24" width="13" height="13"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Schedule Meet
          </button>
          <button type="button" class="btn primary" id="ldmConvertBtn" onclick="window.openConvertLeadModal(document.getElementById('ldmLeadId').value)" style="font-size:12px;padding:6px 14px;background:var(--red);border-color:var(--red);font-weight:700" title="Convert qualified lead to Account, Contact & Deal">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Convert Lead
          </button>
        </div>
      </div>

      <!-- Zoho CRM Visual Status Lifecycle Stepper -->
      <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px;margin-bottom:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Lead Lifecycle Journey</span>
          <span id="ldmStepperLabel" style="font-size:11.5px;font-weight:600;color:var(--ink)">Stage: QUALIFIED</span>
        </div>
        <div class="lead-journey-stepper" id="ldmJourneyStepper">
          <button type="button" class="lead-step-btn" data-lead-stage="New" onclick="window.changeLeadStage('New')">
            <span>1. New</span>
          </button>
          <button type="button" class="lead-step-btn" data-lead-stage="Contacted" onclick="window.changeLeadStage('Contacted')">
            <span>2. Contacted</span>
          </button>
          <button type="button" class="lead-step-btn" data-lead-stage="In Discussion" onclick="window.changeLeadStage('In Discussion')">
            <span>3. In Discussion</span>
          </button>
          <button type="button" class="lead-step-btn" data-lead-stage="Qualified" onclick="window.changeLeadStage('Qualified')">
            <span>4. Qualified</span>
          </button>
          <button type="button" class="lead-step-btn convert" data-lead-stage="Converted" onclick="window.openConvertLeadModal(document.getElementById('ldmLeadId').value)">
            <span>5. Convert ➔</span>
          </button>
        </div>
      </div>

      <!-- Quick Metrics Summary -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:12px;margin-bottom:22px">
        <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:10px 12px">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">Est. Deal Value</div>
          <div class="mono" id="ldmStatValue" style="font-size:16px;font-weight:700;color:var(--ink);margin-top:2px">KES 0</div>
        </div>
        <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:10px 12px">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">Lead Owner</div>
          <div id="ldmStatOwner" style="font-size:13px;font-weight:600;color:var(--ink);margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Jeota Media</div>
        </div>
        <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:10px 12px">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">Calls Logged</div>
          <div id="ldmStatCalls" style="font-size:15px;font-weight:700;color:var(--ink);margin-top:2px">0 calls</div>
        </div>
        <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:10px 12px">
          <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase">Meetings</div>
          <div id="ldmStatMeetings" style="font-size:15px;font-weight:700;color:var(--ink);margin-top:2px">0 scheduled</div>
        </div>
      </div>

      <!-- Drawer Tabs -->
      <div class="client-tab-nav" style="margin-bottom:16px">
        <button type="button" class="client-tab-btn active" data-ldm-tab="info" onclick="window.switchLeadDrawerTab('info')">
          <svg viewBox="0 0 24 24" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>Lead Info &amp; Notes</span>
        </button>
        <button type="button" class="client-tab-btn" data-ldm-tab="calls" onclick="window.switchLeadDrawerTab('calls')">
          <svg viewBox="0 0 24 24" width="14" height="14"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <span>Call Logs</span>
          <span class="client-tab-badge" id="ldmTabCallsCount">0</span>
        </button>
        <button type="button" class="client-tab-btn" data-ldm-tab="meetings" onclick="window.switchLeadDrawerTab('meetings')">
          <svg viewBox="0 0 24 24" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span>Meetings &amp; Meets</span>
          <span class="client-tab-badge" id="ldmTabMeetingsCount">0</span>
        </button>
      </div>

      <!-- Tab Pane 1: Lead Information -->
      <div id="ldmTabPaneInfo" class="ldm-tab-pane">
        <div class="grid2">
          <div class="field">
            <label>Email Address</label>
            <div id="ldmInfoEmail" style="font-size:13px;color:var(--ink);padding:8px;background:var(--paper);border:1px solid var(--line);border-radius:6px">—</div>
          </div>
          <div class="field">
            <label>Phone Number</label>
            <div id="ldmInfoPhone" style="font-size:13px;color:var(--ink);padding:8px;background:var(--paper);border:1px solid var(--line);border-radius:6px">—</div>
          </div>
        </div>
        <div class="grid2">
          <div class="field">
            <label>Industry</label>
            <div id="ldmInfoIndustry" style="font-size:13px;color:var(--ink);padding:8px;background:var(--paper);border:1px solid var(--line);border-radius:6px">—</div>
          </div>
          <div class="field">
            <label>City / Location</label>
            <div id="ldmInfoCity" style="font-size:13px;color:var(--ink);padding:8px;background:var(--paper);border:1px solid var(--line);border-radius:6px">—</div>
          </div>
        </div>
        <div class="field">
          <label>Scope &amp; Qualification Notes</label>
          <div id="ldmInfoNotes" style="font-size:13px;color:var(--ink);padding:10px;background:var(--paper);border:1px solid var(--line);border-radius:6px;min-height:70px;white-space:pre-wrap">—</div>
        </div>
        
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:18px;border-top:1px solid var(--line);padding-top:14px">
          <button type="button" class="linkbtn" style="color:var(--red)" onclick="window.deleteActiveLead()">Delete Lead</button>
          <button type="button" class="btn" onclick="window.editActiveLead()">Edit Lead Information</button>
        </div>
      </div>

      <!-- Tab Pane 2: Calls Log -->
      <div id="ldmTabPaneCalls" class="ldm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="font-size:13px;font-weight:700;color:var(--ink)">Phone Call Interactions</div>
          <button type="button" class="btn primary" onclick="window.openLogCallForActiveLead()" style="padding:4px 10px;font-size:11.5px">+ Log Call</button>
        </div>
        <div id="ldmCallsList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Rendered dynamically -->
        </div>
      </div>

      <!-- Tab Pane 3: Meetings -->
      <div id="ldmTabPaneMeetings" class="ldm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div style="font-size:13px;font-weight:700;color:var(--ink)">Discovery &amp; Pitch Meetings</div>
          <button type="button" class="btn primary" onclick="window.openScheduleMeetingForActiveLead()" style="padding:4px 10px;font-size:11.5px">+ Schedule Meeting</button>
        </div>
        <div id="ldmMeetingsList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Rendered dynamically -->
        </div>
      </div>

    </div>
  </div>
</div>

<!-- C. Zoho CRM Lead Conversion Engine Dialog -->
<div class="modal" id="convertLeadModal" role="dialog" aria-modal="true" aria-labelledby="convertModalTitle">
  <div class="mbg" data-close="convertLeadModal"></div>
  <div class="mbox" style="max-width:580px">
    <button class="mclose" data-close="convertLeadModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:10px;font-weight:700">CONVERSION ENGINE</span>
    </div>
    <h3 id="convertModalTitle">Convert Lead</h3>
    <p class="msub">Converting this lead creates an <b>Account (Client)</b>, a <b>Contact person</b>, and a <b>Pipeline Deal</b>.</p>
    
    <input type="hidden" id="convLeadId">

    <!-- 1. Account (Company) Section -->
    <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
        <span class="badge" style="background:rgba(43,110,138,0.15);color:#2B6E8A;font-weight:700">1. ACCOUNT (COMPANY)</span>
      </div>
      <div class="field" style="margin-bottom:0">
        <label for="convAccountName">Account / Client Name *</label>
        <input id="convAccountName" placeholder="e.g. Venda Technologies" required>
      </div>
    </div>

    <!-- 2. Contact Person Section -->
    <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
        <span class="badge" style="background:rgba(217,119,6,0.15);color:#D97706;font-weight:700">2. CONTACT (PERSON)</span>
      </div>
      <div class="grid2">
        <div class="field" style="margin-bottom:0">
          <label for="convContactName">Contact Name *</label>
          <input id="convContactName" placeholder="e.g. Ian Aluda" required>
        </div>
        <div class="field" style="margin-bottom:0">
          <label for="convContactTitle">Job Title</label>
          <input id="convContactTitle" placeholder="e.g. CTO">
        </div>
      </div>
    </div>

    <!-- 3. Pipeline Deal Section -->
    <div style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
        <span class="badge" style="background:rgba(197,37,35,0.15);color:var(--red);font-weight:700">3. PIPELINE DEAL (OPPORTUNITY)</span>
      </div>
      <div class="field">
        <label for="convDealTitle">Deal Title *</label>
        <input id="convDealTitle" placeholder="e.g. Venda Technologies · Commercial Video" required>
      </div>
      <div class="grid2">
        <div class="field" style="margin-bottom:0">
          <label for="convDealValue">Deal Amount (KES) *</label>
          <input id="convDealValue" type="number" placeholder="e.g. 450000" required>
        </div>
        <div class="field" style="margin-bottom:0">
          <label for="convDealStage">Initial Stage</label>
          <select id="convDealStage">
            <option value="lead">1. Lead</option>
            <option value="meeting" selected>2. Meeting</option>
            <option value="proposal">3. Proposal</option>
            <option value="negotiation">4. Negotiation</option>
            <option value="won">5. Won (Cascade Project &amp; Invoice)</option>
          </select>
        </div>
      </div>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="convertLeadModal">Cancel</button>
      <button type="button" class="btn primary" id="convSubmitBtn" onclick="window.submitConvertLead()" style="background:var(--red);border-color:var(--red);font-weight:700">Convert Lead Now</button>
    </div>
  </div>
</div>

<!-- D. Log Call Modal -->
<div class="modal" id="logLeadCallModal" role="dialog" aria-modal="true" aria-labelledby="logCallTitle">
  <div class="mbg" data-close="logLeadCallModal"></div>
  <div class="mbox" style="max-width:520px">
    <button class="mclose" data-close="logLeadCallModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="logCallTitle">Log Phone Call</h3>
    <p class="msub">Record phone outreach, discovery details, or client discussion.</p>

    <input type="hidden" id="callLeadId">
    <input type="hidden" id="callClientId">

    <div class="grid2">
      <div class="field">
        <label for="callType">Call Type</label>
        <select id="callType">
          <option value="Outbound">Outbound (We called)</option>
          <option value="Inbound">Inbound (They called)</option>
        </select>
      </div>
      <div class="field">
        <label for="callPurpose">Purpose</label>
        <select id="callPurpose">
          <option value="Discovery">Discovery</option>
          <option value="Pitch">Pitch</option>
          <option value="Follow-up">Follow-up</option>
          <option value="Negotiation">Negotiation</option>
          <option value="General">General</option>
        </select>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="callOutcome">Outcome</label>
        <select id="callOutcome">
          <option value="Interested">Interested / Qualified</option>
          <option value="Meeting Scheduled">Meeting Scheduled</option>
          <option value="Follow-up Needed">Follow-up Needed</option>
          <option value="Left Voicemail">Left Voicemail / SMS</option>
          <option value="Busy">Busy / Call Later</option>
          <option value="Not Interested">Not Interested</option>
        </select>
      </div>
      <div class="field">
        <label for="callDuration">Duration (Minutes)</label>
        <input id="callDuration" type="number" placeholder="e.g. 15" value="10">
      </div>
    </div>

    <div class="field">
      <label for="callNotes">Call Summary &amp; Next Steps *</label>
      <textarea id="callNotes" rows="3" placeholder="Key points discussed, client needs, budget, or next scheduled touchpoint..." required></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="logLeadCallModal">Cancel</button>
      <button type="button" class="btn primary" id="saveCallBtn" onclick="window.submitLogCall()">Save Call Log</button>
    </div>
  </div>
</div>

<!-- E. Schedule Lead Meeting Modal -->
<div class="modal" id="scheduleLeadMeetingModal" role="dialog" aria-modal="true" aria-labelledby="schMeetingTitle">
  <div class="mbg" data-close="scheduleLeadMeetingModal"></div>
  <div class="mbox" style="max-width:540px">
    <button class="mclose" data-close="scheduleLeadMeetingModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="schMeetingTitle">Schedule Discovery Meeting</h3>
    <p class="msub">Book meeting with prospect and generate instant Google Meet video link.</p>

    <input type="hidden" id="schLeadId">
    
    <div class="field">
      <label for="schTitle">Meeting Title *</label>
      <input id="schTitle" placeholder="e.g. Discovery &amp; Commercial Proposal Session" required>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="schDate">Date *</label>
        <input id="schDate" type="date" required>
      </div>
      <div class="field">
        <label for="schTime">Time *</label>
        <input id="schTime" type="time" value="10:00" required>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="schDuration">Duration</label>
        <select id="schDuration">
          <option value="30">30 minutes</option>
          <option value="45">45 minutes</option>
          <option value="60" selected>1 hour</option>
          <option value="90">1.5 hours</option>
        </select>
      </div>
      <div class="field">
        <label for="schLocation">Location</label>
        <select id="schLocation">
          <option value="Google Meet">Google Meet (Auto-generated)</option>
          <option value="Jeota Studio">Jeota Studio</option>
          <option value="Client Office">Client Office</option>
          <option value="Phone Call">Phone Call</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="schAttendees">Attendees (Emails or names)</label>
      <input id="schAttendees" placeholder="e.g. client@brand.co.ke, barny@jeotamedia.co.ke">
    </div>

    <div class="field">
      <label for="schNotes">Meeting Agenda &amp; Scope</label>
      <textarea id="schNotes" rows="2" placeholder="Agenda topics, presentation deck, or discussion points..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="scheduleLeadMeetingModal">Cancel</button>
      <button type="button" class="btn primary" id="saveSchMeetingBtn" onclick="window.submitScheduleMeeting()">Schedule &amp; Generate Meet</button>
    </div>
  </div>
</div>

<!-- F. Create Contact Modal -->
<div class="modal" id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle">
  <div class="mbg" data-close="contactModal"></div>
  <div class="mbox" style="max-width:540px">
    <button class="mclose" data-close="contactModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="contactModalTitle">Add Contact</h3>
    <p class="msub">Add an individual stakeholder or representative.</p>

    <div class="grid2">
      <div class="field">
        <label for="ctName">Contact Name *</label>
        <input id="ctName" placeholder="e.g. Peter Karanja" required autocomplete="off">
      </div>
      <div class="field">
        <label for="ctCompany">Company / Account</label>
        <input id="ctCompany" placeholder="e.g. Safari Park Hotel" autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="ctTitle">Job Title</label>
        <input id="ctTitle" placeholder="e.g. Marketing Director" autocomplete="off">
      </div>
      <div class="field">
        <label for="ctOwner">Owner</label>
        <input id="ctOwner" placeholder="e.g. Patrick M." value="Jeota Media" autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="ctEmail">Email Address</label>
        <input id="ctEmail" type="email" placeholder="e.g. peter@safari.co.ke" autocomplete="off">
      </div>
      <div class="field">
        <label for="ctPhone">Phone Number</label>
        <input id="ctPhone" type="tel" placeholder="e.g. +254 712 345 678" autocomplete="off">
      </div>
    </div>

    <div class="field">
      <label for="ctNotes">Notes</label>
      <textarea id="ctNotes" rows="2" placeholder="Contact preferences, key responsibilities..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="contactModal">Cancel</button>
      <button type="button" class="btn primary" id="saveContactBtn" onclick="window.submitContactForm()">Save Contact</button>
    </div>
  </div>
</div>

<!-- G. Create / Edit Quotation Modal -->
<div class="modal" id="quoteModal" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">
  <div class="mbg" data-close="quoteModal"></div>
  <div class="mbox" style="max-width:720px;max-height:92vh;overflow-y:auto">
    <button class="mclose" data-close="quoteModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:10.5px;font-weight:700">COMMERCIAL PROPOSAL</span>
    </div>
    <h3 id="quoteModalTitle">Generate Quotation</h3>
    <p class="msub">Create an itemized quote with direct Email and WhatsApp dispatch.</p>

    <input type="hidden" id="quoteFormId">
    <input type="hidden" id="quoteLeadId">
    <input type="hidden" id="quoteClientId">

    <div class="grid2">
      <div class="field">
        <label for="quoteRecipient">Recipient / Client Name *</label>
        <input id="quoteRecipient" placeholder="e.g. Safari Park Hotel" required autocomplete="off">
      </div>
      <div class="field">
        <label for="quoteTitle">Quote Title / Scope *</label>
        <input id="quoteTitle" placeholder="e.g. Brand Commercial Video Production" required autocomplete="off">
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="quoteEmail">Recipient Email</label>
        <input id="quoteEmail" type="email" placeholder="e.g. client@brand.co.ke" autocomplete="off">
      </div>
      <div class="field">
        <label for="quotePhone">Recipient WhatsApp / Phone</label>
        <input id="quotePhone" type="tel" placeholder="e.g. +254 712 345 678" autocomplete="off">
      </div>
    </div>

    <!-- Line Items Table -->
    <div style="margin:16px 0 10px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <label style="font-size:12px;font-weight:700;color:var(--ink);text-transform:uppercase;letter-spacing:0.5px">Quote Deliverables &amp; Line Items</label>
        <button type="button" class="linkbtn" onclick="window.addQuoteItemRow()" style="font-size:11.5px">+ Add Deliverable</button>
      </div>
      <div class="tablewrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%">
        <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:540px">
          <thead>
            <tr style="background:var(--panel-2);color:var(--muted)">
              <th style="padding:6px;text-align:left">Description</th>
              <th style="padding:6px;width:70px;text-align:center">Qty</th>
              <th style="padding:6px;width:120px;text-align:right">Rate (KES)</th>
              <th style="padding:6px;width:120px;text-align:right">Amount</th>
              <th style="padding:6px;width:30px"></th>
            </tr>
          </thead>
          <tbody id="quoteItemsTableBody">
            <!-- Dynamic row -->
          </tbody>
        </table>
      </div>
    </div>

    <div class="grid3" style="align-items:end">
      <div class="field">
        <label for="quoteValidity">Validity (Days)</label>
        <input id="quoteValidity" type="number" value="14">
      </div>
      <div class="field">
        <label for="quoteDiscount">Discount (KES)</label>
        <input id="quoteDiscount" type="number" value="0" oninput="window.calcQuoteTotals()">
      </div>
      <div class="field">
        <label>Total Amount</label>
        <div class="mono" id="quoteTotalDisplay" style="font-size:16px;font-weight:700;color:var(--red);padding:8px 0">KES 0</div>
        <input type="hidden" id="quoteTotalAmount" value="0">
      </div>
    </div>

    <div class="field">
      <label for="quoteNotes">Deliverables Summary &amp; Production Notes</label>
      <textarea id="quoteNotes" rows="2" placeholder="Brief scope breakdown, gear included, delivery formats..."></textarea>
    </div>

    <div class="mfoot" style="display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" data-close="quoteModal">Cancel</button>
      <button type="button" class="btn" onclick="window.submitQuoteForm('whatsapp')" style="background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
        Save &amp; WhatsApp
      </button>
      <button type="button" class="btn primary" id="saveQuoteBtn" onclick="window.submitQuoteForm('email')" style="font-weight:600">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Save &amp; Send to Email
      </button>
    </div>
  </div>
</div>

<!-- H. Quotation Detailed Preview & Dispatch Modal -->
<div class="modal" id="quoteDetailModal" role="dialog" aria-modal="true" aria-labelledby="qdmTitle">
  <div class="mbg" data-close="quoteDetailModal"></div>
  <div class="mbox" style="max-width:680px;max-height:92vh;overflow-y:auto;padding:0">
    <div style="background:linear-gradient(135deg, #182848 0%, #C52523 100%);padding:22px 24px;color:#fff;border-radius:16px 16px 0 0;position:relative">
      <button class="mclose" data-close="quoteDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:0.85;font-weight:700">JEOTA MEDIA · COMMERCIAL QUOTATION</div>
      <h3 id="qdmTitle" style="margin:4px 0 0;font-size:22px;color:#fff;font-family:'Poppins',sans-serif">Quotation Preview</h3>
      <div style="font-size:12px;opacity:0.9;margin-top:4px" id="qdmSubtitle">Quote #QT-2026-001 · Prepared for Client</div>
    </div>

    <div style="padding:22px 26px">
      <!-- Quick Dispatch Action Bar -->
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-bottom:16px;border-bottom:1px solid var(--line);margin-bottom:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:6px">
          <span class="badge" id="qdmStatusBadge" style="background:var(--panel-2);color:var(--muted);font-weight:700">Draft</span>
          <span class="mono" id="qdmTotalBadge" style="font-size:15px;font-weight:700;color:var(--ink)">KES 0</span>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn primary" id="qdmSendEmailBtn" onclick="window.dispatchQuoteEmail()" style="font-size:11.5px;padding:6px 13px;font-weight:600">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Send to Email
          </button>
          <button type="button" class="btn" id="qdmSendWhatsAppBtn" onclick="window.dispatchQuoteWhatsApp()" style="font-size:11.5px;padding:6px 13px;background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>Send WhatsApp
          </button>
          <button type="button" class="btn" onclick="window.print()" style="font-size:11.5px;padding:6px 10px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>Print PDF
          </button>
          <button type="button" class="btn" id="qdmUpgradeInvoiceBtn" onclick="window.openUpgradeQuoteModal()" style="font-size:11.5px;padding:6px 12px;background:var(--paper);border-color:var(--line-strong);font-weight:600">
            <svg viewBox="0 0 24 24" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>Upgrade to Invoice ➔
          </button>
        </div>
      </div>

      <input type="hidden" id="qdmQuoteId">

      <!-- Quote Details Preview Table -->
      <div id="qdmItemsContainer" style="margin-bottom:16px">
        <!-- Rendered dynamically -->
      </div>

      <div id="qdmNotesBox" style="background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:12px;font-size:12px;color:var(--ink);margin-bottom:16px">
        <!-- Notes text -->
      </div>

      <div style="font-size:11.5px;color:var(--muted);border-top:1px solid var(--line);padding-top:12px;display:flex;align-items:center;justify-content:space-between">
        <span id="qdmValidityText">Valid for 14 days</span>
        <button type="button" class="linkbtn" style="color:var(--red)" onclick="window.deleteActiveQuote()">Delete Quote</button>
      </div>
    </div>
  </div>
</div>

<!-- I. Upgrade Quote to Official Invoice Dialog -->
<div class="modal" id="upgradeQuoteModal" role="dialog" aria-modal="true" aria-labelledby="upgQuoteTitle">
  <div class="mbg" data-close="upgradeQuoteModal"></div>
  <div class="mbox" style="max-width:480px">
    <button class="mclose" data-close="upgradeQuoteModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:rgba(197,37,35,0.12);color:var(--red);font-weight:700">NEGOTIATION WON</span>
    </div>
    <h3 id="upgQuoteTitle">Upgrade Quote to Invoice</h3>
    <p class="msub">Convert this accepted quotation into an official invoice in JMOS.</p>

    <input type="hidden" id="upgQuoteId">
    
    <div class="field">
      <label for="upgInvoiceType">Invoice Milestone / Type</label>
      <select id="upgInvoiceType" onchange="window.onUpgTypeChange(this.value)">
        <option value="Deposit 60%">Deposit 60% (Recommended kickoff)</option>
        <option value="Full Payment 100%">Full Payment 100%</option>
        <option value="Milestone 50%">Milestone 50%</option>
        <option value="Custom">Custom Negotiated Amount</option>
      </select>
    </div>

    <div class="field">
      <label for="upgAmount">Invoice Amount (KES) *</label>
      <input id="upgAmount" type="number" required>
    </div>

    <div class="field">
      <label for="upgDueDate">Due Date</label>
      <input id="upgDueDate" placeholder="e.g. Oct 15" value="7 days">
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="upgradeQuoteModal">Cancel</button>
      <button type="button" class="btn primary" id="upgSubmitBtn" onclick="window.submitUpgradeQuoteToInvoice()" style="background:var(--red);border-color:var(--red);font-weight:700">Convert to Invoice Now</button>
    </div>
  </div>
</div>

<!-- J. Upload Document / Cloud Link Modal -->
<div class="modal" id="documentModal" role="dialog" aria-modal="true" aria-labelledby="docModalTitle">
  <div class="mbg" data-close="documentModal"></div>
  <div class="mbox" style="max-width:540px">
    <button class="mclose" data-close="documentModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="docModalTitle">Upload Document</h3>
    <p class="msub">Add contracts, proposals, brand assets, or cloud drive links.</p>

    <input type="hidden" id="docUploadType" value="file">

    <div class="field">
      <label for="docInputTitle">Document Title *</label>
      <input id="docInputTitle" placeholder="e.g. Safari Park Hotel — Master Production Contract" required autocomplete="off">
    </div>

    <div class="field">
      <label for="docFolderSelect">Folder Category *</label>
      <select id="docFolderSelect">
        <option value="contracts">Contracts &amp; Legal</option>
        <option value="proposals">Proposals &amp; Quotes</option>
        <option value="brand_guides">Brand Guides &amp; Logos</option>
        <option value="briefs">Production Briefs</option>
        <option value="grants">Grant Applications</option>
        <option value="general">General Assets</option>
      </select>
    </div>

    <div class="field" id="docFileInputWrap">
      <label for="docFileInput">File Attachment (PDF, DOCX, XLSX, MP4, PNG, ZIP)</label>
      <input type="file" id="docFileInput">
    </div>

    <div class="field" id="docUrlInputWrap" style="display:none">
      <label for="docUrlInput">Cloud Storage Link (Google Drive / Dropbox / Notion)</label>
      <input type="url" id="docUrlInput" placeholder="https://drive.google.com/file/d/...">
    </div>

    <div class="field">
      <label for="docInputNotes">Description / Version Notes</label>
      <textarea id="docInputNotes" rows="2" placeholder="e.g. Final signed copy, v2.0 revision..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="documentModal">Cancel</button>
      <button type="button" class="btn primary" id="saveDocBtn" onclick="window.submitDocumentForm()">Save Document</button>
    </div>
  </div>
</div>

<!-- K. Create / Edit Impact Grant Opportunity Modal -->
<div class="modal" id="fundraisingModal" role="dialog" aria-modal="true" aria-labelledby="frModalTitle">
  <div class="mbg" data-close="fundraisingModal"></div>
  <div class="mbox" style="max-width:580px">
    <button class="mclose" data-close="fundraisingModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-weight:700">IMPACT FUNDING LEAD</span>
    </div>
    <h3 id="frModalTitle">Add Grant / Call Opportunity</h3>
    <p class="msub">Log open funding calls, fellowships, and philanthropic grants.</p>

    <input type="hidden" id="frFormId">

    <div class="grid2">
      <div class="field">
        <label for="frOrg">Organization / Grantmaker *</label>
        <input id="frOrg" placeholder="e.g. D-Prize" required autocomplete="off">
      </div>
      <div class="field">
        <label for="frProgram">Program / Particulars *</label>
        <input id="frProgram" placeholder="e.g. Custom Solution Challenge" required autocomplete="off">
      </div>
    </div>

    <div class="field">
      <label for="frLink">Application Portal Link</label>
      <input type="url" id="frLink" placeholder="https://..." autocomplete="off">
    </div>

    <div class="grid3">
      <div class="field">
        <label for="frAmount">Amount (KES)</label>
        <input id="frAmount" type="number" placeholder="e.g. 2500000" autocomplete="off">
      </div>
      <div class="field">
        <label for="frDeadline">Deadline</label>
        <input id="frDeadline" placeholder="e.g. Nov 15 or Rolling basis" autocomplete="off">
      </div>
      <div class="field">
        <label for="frStatus">Status</label>
        <select id="frStatus">
          <option value="Identified">Identified</option>
          <option value="In Progress">In Progress</option>
          <option value="Submitted">Submitted</option>
          <option value="Won / Awarded">Won / Awarded</option>
          <option value="Missed">Missed</option>
        </select>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="frCategory">Category</label>
        <select id="frCategory">
          <option value="open_calls">Open Calls &amp; Grants</option>
          <option value="partnerships">Partnership Exploration</option>
          <option value="fellowships">Fellowships &amp; Residencies</option>
        </select>
      </div>
      <div class="field">
        <label for="frPartner">Partner Organization</label>
        <input id="frPartner" placeholder="e.g. Pankaj Social Service, Kilimora" autocomplete="off">
      </div>
    </div>

    <div class="field">
      <label for="frNotes">Notes &amp; Criteria</label>
      <textarea id="frNotes" rows="2" placeholder="Eligibility criteria, consortium requirements, pitch angle..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="fundraisingModal">Cancel</button>
      <button type="button" class="btn primary" id="saveFrBtn" onclick="window.submitFundraisingForm()">Save Opportunity</button>
    </div>
  </div>
</div>

<!-- ==========================================================================
     USER ACCOUNT & PROFILE SETTINGS MODAL
     ========================================================================== -->
<div class="modal" id="myProfileModal" role="dialog" aria-modal="true" aria-labelledby="myProfileTitle">
  <div class="mbg" data-close="myProfileModal"></div>
  <div class="mbox" style="max-width:580px;width:95%;max-height:92vh;overflow-y:auto">
    <button class="mclose" data-close="myProfileModal" title="Close" aria-label="Close modal">&times;</button>
    
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px">
      <div style="width:38px;height:38px;border-radius:10px;background:var(--red-soft);color:var(--red);display:grid;place-items:center">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div>
        <h2 id="myProfileTitle" style="font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;margin:0">My Profile &amp; Account</h2>
        <p style="font-size:12px;color:var(--muted);margin:2px 0 0">Update your profile picture, personal details, and security credentials.</p>
      </div>
    </div>

    <!-- Avatar Upload Section -->
    <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:12px;padding:16px;margin-bottom:18px;display:flex;align-items:center;gap:18px">
      <div id="mpAvatarPreviewBox" style="width:68px;height:68px;border-radius:50%;overflow:hidden;border:2px solid var(--line-strong);background:var(--red);color:#fff;display:grid;place-items:center;font-size:22px;font-weight:700;flex-shrink:0;box-shadow:var(--shadow-sm)">
        <span id="mpAvatarInitials">BK</span>
      </div>
      <div style="flex:1">
        <div style="font-size:13px;font-weight:600;color:var(--ink);margin-bottom:4px">Profile Picture</div>
        <div style="font-size:11.5px;color:var(--muted);margin-bottom:10px">JPG, PNG, GIF or WEBP. Max 10MB.</div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <input type="file" id="mpAvatarFileInput" accept="image/*" style="display:none" onchange="window.onMyAvatarFileSelected(this)">
          <button type="button" class="btn small primary" onclick="document.getElementById('mpAvatarFileInput').click()" style="font-size:11.5px;padding:5px 12px">
            <svg viewBox="0 0 24 24" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Upload New Photo
          </button>
          <button type="button" class="btn small danger" id="mpRemoveAvatarBtn" onclick="window.removeMyAvatar()" style="display:none;font-size:11.5px;padding:5px 10px">
            Remove
          </button>
        </div>
      </div>
    </div>

    <!-- Personal & Professional Data Form -->
    <form id="myProfileForm" onsubmit="window.saveMyProfile(event)">
      <div class="grid2">
        <div class="field">
          <label for="mpName">Full Name</label>
          <input type="text" id="mpName" required placeholder="Your full name" autocomplete="name">
        </div>
        <div class="field">
          <label for="mpTitle">Professional Title</label>
          <input type="text" id="mpTitle" placeholder="e.g. Lead Cinematographer" autocomplete="organization-title">
        </div>
      </div>

      <div class="grid2">
        <div class="field">
          <label for="mpDepartment">Department</label>
          <input type="text" id="mpDepartment" placeholder="e.g. Production, IT, Sales" autocomplete="off">
        </div>
        <div class="field">
          <label for="mpPhone">Phone Number</label>
          <input type="tel" id="mpPhone" placeholder="+254 712 345 678" autocomplete="tel">
        </div>
      </div>

      <div class="grid2">
        <div class="field">
          <label for="mpEmail">Primary Login Email</label>
          <input type="email" id="mpEmail" readonly style="background:var(--paper);opacity:0.75;cursor:not-allowed" autocomplete="email">
        </div>
        <div class="field">
          <label for="mpSecondaryEmail">Secondary Alert Email</label>
          <input type="email" id="mpSecondaryEmail" placeholder="e.g. personal@gmail.com" autocomplete="email">
        </div>
      </div>

      <div class="field">
        <label for="mpBio">Bio / Specialization</label>
        <textarea id="mpBio" rows="2" placeholder="Brief professional overview, equipment proficiencies, or creative focus..."></textarea>
      </div>

      <!-- Change Password Accordion -->
      <details style="background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:12px 14px;margin-bottom:16px">
        <summary style="font-size:12.5px;font-weight:600;color:var(--ink);cursor:pointer;user-select:none;display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Change Account Password (Optional)
        </summary>
        <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:10px">
          <div class="field" style="margin-bottom:0">
            <label for="mpCurrentPass">Current Password</label>
            <input type="password" id="mpCurrentPass" placeholder="Enter current password" autocomplete="current-password">
          </div>
          <div class="grid2" style="margin-bottom:0">
            <div class="field" style="margin-bottom:0">
              <label for="mpNewPass">New Password</label>
              <input type="password" id="mpNewPass" placeholder="Min 4 characters" autocomplete="new-password">
            </div>
            <div class="field" style="margin-bottom:0">
              <label for="mpConfirmPass">Confirm New Password</label>
              <input type="password" id="mpConfirmPass" placeholder="Repeat new password" autocomplete="new-password">
            </div>
          </div>
        </div>
      </details>

      <div class="mfoot" style="margin-top:10px">
        <button type="button" class="btn" data-close="myProfileModal">Cancel</button>
        <button type="submit" class="btn primary" id="mpSaveBtn">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- 16. Custom Role & Permissions Modal -->
<div class="modal" id="roleModal" role="dialog" aria-modal="true" aria-labelledby="roleModalTitle">
  <div class="mbg" data-close="roleModal"></div>
  <div class="mbox" style="max-width:720px;max-height:90vh;display:flex;flex-direction:column;padding:24px">
    <button class="mclose" data-close="roleModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="flex-shrink:0;margin-bottom:12px">
      <h3 id="roleModalTitle" style="font-family:'Poppins',sans-serif;font-size:18px;font-weight:600;margin-bottom:4px">Create Custom Role</h3>
      <p class="msub" id="roleModalSub" style="margin-bottom:14px;color:var(--muted);font-size:12.5px">Configure custom role access rights and modular permissions across JMOS.</p>
      
      <input type="hidden" id="editRoleId" value="">

      <div class="grid2" style="margin-bottom:12px">
        <div class="field">
          <label for="roleName">Role Name *</label>
          <input id="roleName" placeholder="e.g. Lead Producer, Field Director" required autocomplete="off">
        </div>
        <div class="field">
          <label for="roleColor">Role Badge Color</label>
          <select id="roleColor">
            <option value="#C52523">Crimson Red (#C52523)</option>
            <option value="#2B8A5A">Forest Green (#2B8A5A)</option>
            <option value="#2B6E8A">Ocean Blue (#2B6E8A)</option>
            <option value="#8A5A2B">Amber Gold (#8A5A2B)</option>
            <option value="#6E2B8A">Purple (#6E2B8A)</option>
            <option value="#5A7A2B">Olive (#5A7A2B)</option>
            <option value="#473833">Charcoal (#473833)</option>
          </select>
        </div>
      </div>

      <div class="field" style="margin-bottom:14px">
        <label for="roleDescription">Description / Scope of Duties</label>
        <input id="roleDescription" placeholder="e.g. Oversees video deliverables, quotes, and client communications." autocomplete="off">
      </div>

      <!-- Quick select buttons -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--line)">
        <span style="font-size:12px;font-weight:600;color:var(--ink)">Access Rights &amp; Modular Permissions</span>
        <div style="display:flex;gap:8px">
          <button type="button" class="btn sm" onclick="toggleAllRolePermissions(true)" style="font-size:11px;padding:4px 9px">Select All</button>
          <button type="button" class="btn sm" onclick="toggleAllRolePermissions(false)" style="font-size:11px;padding:4px 9px">Clear All</button>
        </div>
      </div>
    </div>

    <!-- Permissions Category Grid (Scrollable) -->
    <div id="rolePermissionsContainer" style="flex:1;overflow-y:auto;padding-right:6px;display:flex;flex-direction:column;gap:14px">
      <!-- Populated dynamically via JS from permission catalog -->
    </div>

    <div class="modal-footer" style="margin-top:16px;padding-top:12px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px;flex-shrink:0">
      <button type="button" class="btn" data-close="roleModal">Cancel</button>
      <button type="button" class="btn primary" id="saveRoleBtn" onclick="saveRole()">
        <svg viewBox="0 0 24 24" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>Save Role
      </button>
    </div>
  </div>
</div>

<!-- Toast Notification Container -->
<div class="toasts" id="toasts"></div>


