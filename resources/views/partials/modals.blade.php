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
  <div class="mbox workspace-modal">
    <!-- Notion-style Cover Banner -->
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg, #050507 0%, #121316 28%, #5a0c0b 68%, #C52523 100%)">
      <button class="mclose" data-close="clientDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      
      <!-- Dynamic Client Initials Avatar -->
      <div id="cdmAvatar" style="position:absolute;bottom:-24px;left:32px;width:60px;height:60px;border-radius:14px;background:var(--red);box-shadow:0 4px 16px rgba(0,0,0,0.25);display:grid;place-items:center;color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:22px;border:3px solid var(--surface)">
        CL
      </div>
    </div>

    <div class="workspace-modal-body">
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
          <button type="button" class="btn" id="cdmNewQuoteBtn" style="font-size:12px;padding:6px 12px" title="Draft new quotation for this client">
            <svg viewBox="0 0 24 24" width="13" height="13"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>+ Quotation
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
        <button type="button" class="client-tab-btn" data-cdm-tab="quotes">
          <svg viewBox="0 0 24 24" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <span>Quotations</span>
          <span class="client-tab-badge" id="cdmTabQuotesCount">0</span>
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
        <button type="button" class="client-tab-btn" data-cdm-tab="statement">
          <svg viewBox="0 0 24 24" width="14" height="14"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          <span>Financial Statement</span>
          <span class="badge" style="font-size:10px;padding:2px 6px;background:rgba(197,37,35,0.15);color:var(--red);font-weight:700">Ledger</span>
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

      <!-- Tab 3: Quotations & Proposals -->
      <div id="cdmTabPaneQuotes" class="cdm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--ink)">Commercial Quotations &amp; Proposals</div>
            <div style="font-size:11.5px;color:var(--muted)">Proposals created directly for this client profile</div>
          </div>
          <button type="button" class="btn primary" id="cdmAddQuoteFromTabBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ New Quotation
          </button>
        </div>
        <div id="cdmQuotesList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Tab 4: Invoices & Billing -->
      <div id="cdmTabPaneInvoices" class="cdm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--ink)">Invoices &amp; Retainer Records</div>
            <div style="font-size:11.5px;color:var(--muted)">Invoices issued and payment statuses</div>
          </div>
          <button type="button" class="btn primary" id="cdmAddInvoiceFromTabBtn" style="padding:4px 10px;font-size:11.5px">
            <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Issue Invoice
          </button>
        </div>
        <div id="cdmInvoicesList" style="display:flex;flex-direction:column;gap:8px">
          <!-- Populated dynamically -->
        </div>
      </div>

      <!-- Tab 5: Shoots & Calendar -->
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

      <!-- Tab 6: Client Financial Statement & Ledger -->
      <div id="cdmTabPaneStatement" class="cdm-tab-pane" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:6px">
              <span>Client Financial Statement</span>
              <span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-size:10px;font-weight:600">LIVE LEDGER</span>
            </div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px">Chronological accounting statement &amp; balances for this client profile</div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <button type="button" class="btn" id="cdmPrintStatementBtn" style="font-size:11.5px;padding:5px 11px">
              <svg viewBox="0 0 24 24" width="13" height="13"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>Print Statement
            </button>
            <button type="button" class="btn primary" id="cdmOpenMainStatementsBtn" style="font-size:11.5px;padding:5px 11px">
              <svg viewBox="0 0 24 24" width="13" height="13"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Open in Main Statements ↗
            </button>
          </div>
        </div>

        <!-- Statement Metrics Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:10px;margin-bottom:18px">
          <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Total Invoiced</div>
            <div class="mono" id="cdmStmtInvoiced" style="font-size:16px;font-weight:700;color:var(--ink);margin-top:3px">KES 0</div>
            <div style="font-size:10.5px;color:var(--muted);margin-top:2px" id="cdmStmtInvoicesCount">0 invoices billed</div>
          </div>
          <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Total Collected</div>
            <div class="mono" id="cdmStmtCollected" style="font-size:16px;font-weight:700;color:var(--green);margin-top:3px">KES 0</div>
            <div style="font-size:10.5px;color:var(--green);margin-top:2px" id="cdmStmtCollectedPct">0% recovered</div>
          </div>
          <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Outstanding Balance</div>
            <div class="mono" id="cdmStmtBalance" style="font-size:16px;font-weight:700;color:var(--ink);margin-top:3px">KES 0</div>
            <div style="font-size:10.5px;margin-top:2px" id="cdmStmtBalanceStatus"><span class="pill tint-green" style="font-size:9.5px">Settled</span></div>
          </div>
          <div style="background:var(--panel-2);border:1px solid var(--line);border-radius:10px;padding:12px 14px">
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Quotation Pipeline</div>
            <div class="mono" id="cdmStmtQuotes" style="font-size:16px;font-weight:700;color:var(--ink);margin-top:3px">KES 0</div>
            <div style="font-size:10.5px;color:var(--muted);margin-top:2px" id="cdmStmtQuotesCount">0 active proposals</div>
          </div>
        </div>

        <!-- Statement Table -->
        <div class="tablewrap" style="border:1px solid var(--line);border-radius:8px;max-height:380px;overflow-y:auto;background:var(--surface)">
          <table class="dtable" style="font-size:12px;width:100%;border-collapse:collapse">
            <thead>
              <tr style="position:sticky;top:0;background:var(--panel-2);z-index:2;box-shadow:0 1px 0 var(--line)">
                <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Date</th>
                <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Ref #</th>
                <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Type</th>
                <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Description</th>
                <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Debit (+)</th>
                <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Credit (-)</th>
                <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Balance</th>
                <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase">Status</th>
              </tr>
            </thead>
            <tbody id="cdmStatementTableBody">
              <!-- Populated dynamically -->
            </tbody>
          </table>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;font-size:11px;color:var(--muted);flex-wrap:wrap;gap:8px">
          <div>* Linked dynamically to JMOS Main Statement Hub and eTIMS accounting records.</div>
          <div id="cdmStatementClosingSummary" style="font-weight:600;color:var(--ink)">Closing Balance: KES 0</div>
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
    <div class="grid2">
      <div class="field">
        <label for="npCategory">Category</label>
        <select id="npCategory" onchange="window.onProjectCategoryChange(this.value, 'np')">
          <option value="video_production">Video &amp; Film Production (Brand Film, Commercial, Doc)</option>
          <option value="development">Development &amp; Software (JMOS, Web, Mobile, Systems)</option>
          <option value="graphic_design">Graphic Design &amp; Branding (Identity, Posters, UI/UX)</option>
          <option value="content_calendar">Content Calendar &amp; Social Media (Monthly, Reels, Copy)</option>
          <option value="internal">Internal Operations, Studio &amp; R&amp;D</option>
          <option value="custom">Other / Custom Category…</option>
        </select>
        <div id="npCustomCategoryWrap" style="display:none;margin-top:6px">
          <input id="npCustomCategoryInput" placeholder="Enter custom project category name" autocomplete="off" style="font-size:12.5px;padding:6px 10px">
        </div>
      </div>
      <div class="field">
        <label for="npType">Project type</label>
        <select id="npType" onchange="window.onProjectTypeChange(this.value, 'np')">
          <optgroup label="Development &amp; Software Engineering">
            <option value="Internal System Development (JMOS / Tech)">Internal System Development (JMOS / Tech)</option>
            <option value="Web Application Development">Web Application Development</option>
            <option value="Mobile App Development (iOS / Android)">Mobile App Development (iOS / Android)</option>
            <option value="API &amp; Database Engineering">API &amp; Database Engineering</option>
            <option value="DevOps, Cloud &amp; Hosting Infra">DevOps, Cloud &amp; Hosting Infra</option>
            <option value="Automation &amp; Internal Tooling">Automation &amp; Internal Tooling</option>
          </optgroup>
          <optgroup label="Graphic Design &amp; Creative Branding">
            <option value="Brand Identity &amp; Logo Design">Brand Identity &amp; Logo Design</option>
            <option value="Social Media Graphics &amp; Posters">Social Media Graphics &amp; Posters</option>
            <option value="UI/UX Design &amp; Prototyping (Figma)">UI/UX Design &amp; Prototyping (Figma)</option>
            <option value="Motion Graphics &amp; 2D/3D Animation">Motion Graphics &amp; 2D/3D Animation</option>
            <option value="Pitch Decks &amp; Print Collateral">Pitch Decks &amp; Print Collateral</option>
            <option value="Merchandise &amp; Marketing Design">Merchandise &amp; Marketing Design</option>
          </optgroup>
          <optgroup label="Content Calendar &amp; Digital Media">
            <option value="Monthly Content Calendar &amp; Production">Monthly Content Calendar &amp; Production</option>
            <option value="Social Media Reels &amp; TikTok Series">Social Media Reels &amp; TikTok Series</option>
            <option value="Copywriting &amp; Editorial Series">Copywriting &amp; Editorial Series</option>
            <option value="Influencer &amp; Distribution Campaign">Influencer &amp; Distribution Campaign</option>
            <option value="Newsletter &amp; Email Marketing Campaign">Newsletter &amp; Email Marketing Campaign</option>
          </optgroup>
          <optgroup label="Video &amp; Film Production">
            <option value="Brand film">Brand film</option>
            <option value="Commercial &amp; Advert">Commercial &amp; Advert</option>
            <option value="Documentary">Documentary</option>
            <option value="Social media reels">Social media reels</option>
            <option value="Podcast production">Podcast production</option>
            <option value="Event coverage">Event coverage</option>
            <option value="Livestream Broadcasting">Livestream Broadcasting</option>
            <option value="Corporate photography">Corporate photography</option>
          </optgroup>
          <optgroup label="Internal Studio Operations">
            <option value="Internal Operations &amp; Studio R&amp;D">Internal Operations &amp; Studio R&amp;D</option>
            <option value="Equipment &amp; Studio Engineering">Equipment &amp; Studio Engineering</option>
            <option value="Business Development &amp; Partnerships">Business Development &amp; Partnerships</option>
          </optgroup>
          <option value="custom">Other / Custom Project Type…</option>
        </select>
        <div id="npCustomTypeWrap" style="display:none;margin-top:6px">
          <input id="npCustomTypeInput" placeholder="Enter custom project type" autocomplete="off" style="font-size:12.5px;padding:6px 10px">
        </div>
      </div>
    </div>
    <div class="field">
      <label for="npManager">Project manager / Lead</label>
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
    <div class="grid2">
      <div class="field">
        <label for="npStage">Current stage / Workflow</label>
        <select id="npStage" onchange="window.onProjectStageChange(this.value, 'np')">
          <optgroup label="Video &amp; Film Production">
            <option value="Brief">Brief</option>
            <option value="Concept">Concept</option>
            <option value="Pre-production">Pre-production</option>
            <option value="Shoot">Shoot / Production</option>
            <option value="Edit">Post-Production / Edit</option>
            <option value="Client review">Client review</option>
            <option value="Delivery">Delivery</option>
          </optgroup>
          <optgroup label="Development &amp; Systems">
            <option value="Backlog / Requirements">Backlog / Requirements</option>
            <option value="UI/UX Design &amp; Spec">UI/UX Design &amp; Spec</option>
            <option value="In Development / Building">In Development / Building</option>
            <option value="Code Review &amp; Testing">Code Review &amp; Testing</option>
            <option value="Staging &amp; QA">Staging &amp; QA</option>
            <option value="Deployed / Live">Deployed / Live</option>
          </optgroup>
          <optgroup label="Graphic Design &amp; Creative">
            <option value="Creative Brief">Creative Brief</option>
            <option value="Moodboard &amp; Concept">Moodboard &amp; Concept</option>
            <option value="Design Drafting">Design Drafting</option>
            <option value="Internal Review">Internal Review</option>
            <option value="Client Revisions">Client Revisions</option>
            <option value="Final Assets &amp; Export">Final Assets &amp; Export</option>
          </optgroup>
          <optgroup label="Content Calendar &amp; Media">
            <option value="Content Strategy">Content Strategy</option>
            <option value="Drafting &amp; Copywriting">Drafting &amp; Copywriting</option>
            <option value="Asset Creation &amp; Design">Asset Creation &amp; Design</option>
            <option value="Review &amp; Approval">Review &amp; Approval</option>
            <option value="Scheduled &amp; Queued">Scheduled &amp; Queued</option>
            <option value="Published / Live">Published / Live</option>
          </optgroup>
          <optgroup label="General Workflow">
            <option value="Planning">Planning</option>
            <option value="In Progress">In Progress</option>
            <option value="Under Review">Under Review</option>
            <option value="Revisions">Revisions</option>
            <option value="Completed">Completed</option>
          </optgroup>
          <option value="custom">Custom Stage…</option>
        </select>
        <div id="npCustomStageWrap" style="display:none;margin-top:6px">
          <input id="npCustomStageInput" placeholder="Enter custom project stage" autocomplete="off" style="font-size:12.5px;padding:6px 10px">
        </div>
      </div>
      <div class="field">
        <label for="npStatus">Status</label>
        <select id="npStatus" onchange="window.onProjectStatusChange(this.value, 'np')">
          <option value="On track">On track</option>
          <option value="In Progress">In Progress</option>
          <option value="In Review">In Review</option>
          <option value="At risk">At risk</option>
          <option value="Delivering">Delivering</option>
          <option value="Blocked">Blocked</option>
          <option value="Completed">Completed</option>
          <option value="On Hold">On Hold</option>
          <option value="custom">Custom Status…</option>
        </select>
        <div id="npCustomStatusWrap" style="display:none;margin-top:6px">
          <input id="npCustomStatusInput" placeholder="Enter custom status" autocomplete="off" style="font-size:12.5px;padding:6px 10px">
        </div>
      </div>
    </div>
    <div class="grid2">
      <div class="field">
        <label for="npDeadline">Deadline Date *</label>
        <input id="npDeadline" type="date" class="date-input" required autocomplete="off" style="font-family:inherit;cursor:pointer" onclick="this.showPicker && this.showPicker()">
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
  <div class="mbox workspace-modal">
    <!-- Notion-style Cover Banner -->
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg, #E02826 0%, #C52523 25%, #6B0E0D 58%, #1F0505 82%, #080709 100%)">
      <button class="mclose" data-close="projectDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="position:absolute;bottom:-20px;left:32px;width:52px;height:52px;border-radius:12px;background:var(--surface);box-shadow:0 4px 16px rgba(0,0,0,0.2);display:grid;place-items:center;color:var(--ink);border:3px solid var(--surface)">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
      </div>
    </div>

    <div class="workspace-modal-body">
      <!-- Title & Client Header -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div style="flex:1;min-width:280px">
          <input type="hidden" id="pdmProjectId">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
            <span class="badge" id="pdmCategoryBadge" style="background:rgba(110,43,138,0.15);color:#8A2BE2;font-size:11px;font-weight:600">Internal</span>
            <span class="badge" id="pdmClientBadge" style="background:var(--red-soft);color:var(--red);font-size:11px;cursor:pointer" title="Click to open client workspace">Client</span>
            <span class="badge" id="pdmTypeBadge" style="background:var(--panel-2);color:var(--muted);font-size:11px">Brand Film</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <input id="pdmNameInput" type="text" placeholder="Project Name" style="font-family:'Poppins',sans-serif;font-size:22px;font-weight:700;margin:0;color:var(--ink);line-height:1.2;border:1px solid transparent;background:transparent;border-radius:6px;padding:2px 6px;width:100%;transition:border 0.2s ease" onfocus="this.style.borderColor='var(--line)';" onblur="this.style.borderColor='transparent';">
            <h2 id="pdmTitle" style="display:none">Project Name</h2>
          </div>
        </div>
        <div style="text-align:right">
          <label for="pdmHeaderBudgetInput" style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;font-weight:600;display:block;margin-bottom:3px;cursor:pointer">Project Budget</label>
          <div style="display:inline-flex;align-items:center;gap:6px;background:var(--panel-2);border:1px solid var(--line);border-radius:8px;padding:3px 10px;transition:border-color 0.15s ease">
            <span style="font-size:12px;font-weight:700;color:var(--muted)">KES</span>
            <input type="number" id="pdmHeaderBudgetInput" placeholder="0" min="0" step="any" style="width:130px;font-family:monospace;font-size:18px;font-weight:700;color:var(--ink);background:transparent;border:none;text-align:right;padding:0;outline:none" title="Click to edit project budget">
          </div>
          <div class="mono" id="pdmBudgetBadge" style="display:none">KES 0</div>
        </div>
      </div>

      <!-- Properties Grid (Notion Style 2-Column Responsive) -->
      <div class="project-props-grid">
        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Budget (KES):</span>
          </div>
          <div class="project-prop-control" style="display:flex;align-items:center;gap:8px">
            <input type="number" id="pdmBudgetInput" placeholder="e.g. 350000" style="padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface);flex:1;font-family:monospace;font-weight:600">
            <span style="font-size:11px;color:var(--muted);white-space:nowrap">KES</span>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
            <span>Category:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmCategorySelect" onchange="window.onProjectCategoryChange(this.value, 'pdm')" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
              <option value="video_production">Video &amp; Film Production</option>
              <option value="development">Development &amp; Software (JMOS, Web, Apps)</option>
              <option value="graphic_design">Graphic Design &amp; Branding (Identity, UI/UX)</option>
              <option value="content_calendar">Content Calendar &amp; Social Media</option>
              <option value="internal">Internal Operations, Studio &amp; R&amp;D</option>
              <option value="client">General Client Deliverable</option>
              <option value="custom">Other / Custom Category…</option>
            </select>
            <div id="pdmCustomCategoryWrap" style="display:none;margin-top:6px">
              <input id="pdmCustomCategoryInput" placeholder="Enter custom category name" autocomplete="off" style="width:100%;font-size:12.5px;padding:6px 10px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
            </div>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            <span>Project Type:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmTypeSelect" onchange="window.onProjectTypeChange(this.value, 'pdm')" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
              <optgroup label="Development &amp; Software Engineering">
                <option value="Internal System Development (JMOS / Tech)">Internal System Development (JMOS / Tech)</option>
                <option value="Web Application Development">Web Application Development</option>
                <option value="Mobile App Development (iOS / Android)">Mobile App Development (iOS / Android)</option>
                <option value="API &amp; Database Engineering">API &amp; Database Engineering</option>
                <option value="DevOps, Cloud &amp; Hosting Infra">DevOps, Cloud &amp; Hosting Infra</option>
                <option value="Automation &amp; Internal Tooling">Automation &amp; Internal Tooling</option>
              </optgroup>
              <optgroup label="Graphic Design &amp; Creative Branding">
                <option value="Brand Identity &amp; Logo Design">Brand Identity &amp; Logo Design</option>
                <option value="Social Media Graphics &amp; Posters">Social Media Graphics &amp; Posters</option>
                <option value="UI/UX Design &amp; Prototyping (Figma)">UI/UX Design &amp; Prototyping (Figma)</option>
                <option value="Motion Graphics &amp; 2D/3D Animation">Motion Graphics &amp; 2D/3D Animation</option>
                <option value="Pitch Decks &amp; Print Collateral">Pitch Decks &amp; Print Collateral</option>
                <option value="Merchandise &amp; Marketing Design">Merchandise &amp; Marketing Design</option>
              </optgroup>
              <optgroup label="Content Calendar &amp; Digital Media">
                <option value="Monthly Content Calendar &amp; Production">Monthly Content Calendar &amp; Production</option>
                <option value="Social Media Reels &amp; TikTok Series">Social Media Reels &amp; TikTok Series</option>
                <option value="Copywriting &amp; Editorial Series">Copywriting &amp; Editorial Series</option>
                <option value="Influencer &amp; Distribution Campaign">Influencer &amp; Distribution Campaign</option>
                <option value="Newsletter &amp; Email Marketing Campaign">Newsletter &amp; Email Marketing Campaign</option>
              </optgroup>
              <optgroup label="Video &amp; Film Production">
                <option value="Brand film">Brand film</option>
                <option value="Commercial &amp; Advert">Commercial &amp; Advert</option>
                <option value="Documentary">Documentary</option>
                <option value="Social media reels">Social media reels</option>
                <option value="Podcast production">Podcast production</option>
                <option value="Event coverage">Event coverage</option>
                <option value="Livestream Broadcasting">Livestream Broadcasting</option>
                <option value="Corporate photography">Corporate photography</option>
              </optgroup>
              <optgroup label="Internal Studio Operations">
                <option value="Internal Operations &amp; Studio R&amp;D">Internal Operations &amp; Studio R&amp;D</option>
                <option value="Equipment &amp; Studio Engineering">Equipment &amp; Studio Engineering</option>
                <option value="Business Development &amp; Partnerships">Business Development &amp; Partnerships</option>
              </optgroup>
              <option value="custom">Other / Custom Project Type…</option>
            </select>
            <div id="pdmCustomTypeWrap" style="display:none;margin-top:6px">
              <input id="pdmCustomTypeInput" placeholder="Enter custom project type" autocomplete="off" style="width:100%;font-size:12.5px;padding:6px 10px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
            </div>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            <span>Workflow Stage:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmStageSelect" onchange="window.onProjectStageChange(this.value, 'pdm')" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
              <optgroup label="Development &amp; Systems">
                <option value="Backlog / Requirements">Backlog / Requirements</option>
                <option value="UI/UX Design &amp; Spec">UI/UX Design &amp; Spec</option>
                <option value="In Development / Building">In Development / Building</option>
                <option value="Code Review &amp; Testing">Code Review &amp; Testing</option>
                <option value="Staging &amp; QA">Staging &amp; QA</option>
                <option value="Deployed / Live">Deployed / Live</option>
              </optgroup>
              <optgroup label="Graphic Design &amp; Creative">
                <option value="Creative Brief">Creative Brief</option>
                <option value="Moodboard &amp; Concept">Moodboard &amp; Concept</option>
                <option value="Design Drafting">Design Drafting</option>
                <option value="Internal Review">Internal Review</option>
                <option value="Client Revisions">Client Revisions</option>
                <option value="Final Assets &amp; Export">Final Assets &amp; Export</option>
              </optgroup>
              <optgroup label="Content Calendar &amp; Media">
                <option value="Content Strategy">Content Strategy</option>
                <option value="Drafting &amp; Copywriting">Drafting &amp; Copywriting</option>
                <option value="Asset Creation &amp; Design">Asset Creation &amp; Design</option>
                <option value="Review &amp; Approval">Review &amp; Approval</option>
                <option value="Scheduled &amp; Queued">Scheduled &amp; Queued</option>
                <option value="Published / Live">Published / Live</option>
              </optgroup>
              <optgroup label="Video &amp; Film Production">
                <option value="Brief">Brief</option>
                <option value="Concept">Concept</option>
                <option value="Pre-production">Pre-production</option>
                <option value="Shoot">Shoot / Production</option>
                <option value="Edit">Post-Production / Edit</option>
                <option value="Client review">Client review</option>
                <option value="Delivery">Delivery</option>
              </optgroup>
              <optgroup label="General Workflow">
                <option value="Planning">Planning</option>
                <option value="In Progress">In Progress</option>
                <option value="Under Review">Under Review</option>
                <option value="Revisions">Revisions</option>
                <option value="Completed">Completed</option>
              </optgroup>
              <option value="custom">Custom Stage…</option>
            </select>
            <div id="pdmCustomStageWrap" style="display:none;margin-top:6px">
              <input id="pdmCustomStageInput" placeholder="Enter custom stage" autocomplete="off" style="width:100%;font-size:12.5px;padding:6px 10px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
            </div>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Status:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmStatusSelect" onchange="window.onProjectStatusChange(this.value, 'pdm')" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
              <option value="On track">On track</option>
              <option value="In Progress">In Progress</option>
              <option value="In Review">In Review</option>
              <option value="At risk">At risk</option>
              <option value="Delivering">Delivering</option>
              <option value="Blocked">Blocked</option>
              <option value="Completed">Completed</option>
              <option value="On Hold">On Hold</option>
              <option value="custom">Custom Status…</option>
            </select>
            <div id="pdmCustomStatusWrap" style="display:none;margin-top:6px">
              <input id="pdmCustomStatusInput" placeholder="Enter custom status" autocomplete="off" style="width:100%;font-size:12.5px;padding:6px 10px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
            </div>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Owner / Lead:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmManagerSelect" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
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

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            <span>Priority:</span>
          </div>
          <div class="project-prop-control">
            <select id="pdmPrioritySelect" style="width:100%;padding:6px 10px;font-size:12.5px;border:1px solid var(--line);border-radius:8px;background:var(--surface)">
              <option value="High">High</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>
        </div>

        <div class="project-prop-row">
          <div class="project-prop-label">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Completion:</span>
          </div>
          <div class="project-prop-control" style="display:flex;align-items:center;gap:12px">
            <div style="flex:1;height:8px;background:var(--surface);border-radius:999px;overflow:hidden;border:1px solid var(--line)">
              <div id="pdmProgressBar" style="height:100%;background:var(--green);width:0%;border-radius:999px;transition:width 0.3s ease"></div>
            </div>
            <span class="mono" id="pdmProgressPct" style="font-size:13px;font-weight:700;color:var(--ink)">0%</span>
          </div>
        </div>

        <!-- Files & Media Storage Links -->
        <div class="project-prop-row full-width" style="display:flex;align-items:flex-start;padding:12px 0 4px">
          <div class="project-prop-label" style="margin-top:6px">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            <span>Storage Drives:</span>
          </div>
          <div class="project-prop-control" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:8px 14px">
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:75px;color:var(--muted)">Google Drive:</span>
              <input id="pdmDriveLink" placeholder="https://drive.google.com/..." style="flex:1;padding:5px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmDriveOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Drive Folder">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:75px;color:var(--muted)">Client Brief:</span>
              <input id="pdmBriefLink" placeholder="Link to project brief" style="flex:1;padding:5px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmBriefOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Brief">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:75px;color:var(--muted)">Treatment:</span>
              <input id="pdmTreatmentLink" placeholder="Director's treatment URL" style="flex:1;padding:5px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmTreatmentOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Treatment">Open ↗</a>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:11.5px;min-width:75px;color:var(--muted)">Playbook:</span>
              <input id="pdmPlaybookLink" placeholder="Production playbook URL" style="flex:1;padding:5px 8px;font-size:12px;border:1px solid var(--line);border-radius:6px;background:var(--surface)">
              <a id="pdmPlaybookOpenBtn" href="#" target="_blank" class="btn" style="padding:4px 8px;font-size:11px;display:none" title="Open Playbook">Open ↗</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Split Grid: Tasks & Notes (Left) + Comments & Discussion (Right) -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:24px;margin-bottom:20px">
        <!-- Left: Scope & Tasks -->
        <div>
          <!-- About this project (Description / Notes) -->
          <div style="margin-bottom:24px">
            <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--ink);display:flex;align-items:center;gap:6px">
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              <span>About this project</span>
            </h4>
            <textarea id="pdmNotes" rows="3" placeholder="Add scope details, shoot locations, deliverable specs, client contacts..." style="width:100%;border:1px solid var(--line);border-radius:10px;padding:12px;font-size:13px;background:var(--surface);resize:vertical"></textarea>
          </div>

          <!-- Project Tasks Section -->
          <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
              <h4 style="font-size:14px;font-weight:600;margin:0;color:var(--ink);display:flex;align-items:center;gap:6px">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Project Tasks</span> <span class="count" id="pdmTasksCount" style="font-size:11px;padding:1px 7px;background:var(--panel-2);border:1px solid var(--line);border-radius:12px">0</span>
              </h4>
              <button type="button" class="btn primary" id="pdmAddTaskBtn" style="padding:4px 10px;font-size:11.5px">
                <svg viewBox="0 0 24 24" width="12" height="12"><path d="M12 5v14M5 12h14"/></svg>+ Add Task
              </button>
            </div>

            <div id="pdmTasksList" style="border:1px solid var(--line);border-radius:10px;overflow:hidden;background:var(--surface);max-height:280px;overflow-y:auto">
              <!-- Populated dynamically with tasks -->
              <div style="padding:18px;text-align:center;font-size:12px;color:var(--muted)">No tasks attached to this project yet.</div>
            </div>
          </div>
        </div>

        <!-- Right: Comments Thread & Activity -->
        <div style="display:flex;flex-direction:column">
          <h4 style="font-size:14px;font-weight:600;margin-bottom:10px;color:var(--ink);display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Comments &amp; Activity Stream</span>
          </h4>

          <!-- Comments Stream -->
          <div id="pdmCommentsStream" style="flex:1;min-height:180px;max-height:280px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;margin-bottom:12px;padding:12px;background:var(--panel-2);border:1px solid var(--line);border-radius:10px">
            <div style="font-size:12px;color:var(--muted);font-style:italic">No comments yet. Start a discussion below.</div>
          </div>

          <!-- Add Comment Input -->
          <div style="display:flex;gap:8px;align-items:center">
            <input id="pdmCommentInput" placeholder="Add a comment, note, or update..." style="flex:1;border:1px solid var(--line);border-radius:8px;padding:8px 12px;font-size:12.5px;background:var(--surface)">
            <button type="button" class="btn primary" id="pdmPostCommentBtn" style="padding:8px 16px;font-size:12px">Post</button>
          </div>
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
  <div class="mbox" style="max-width:680px">
    <button class="mclose" data-close="taskModal" title="Close" aria-label="Close modal">&times;</button>
    <h3 id="taskModalTitle">Create a task</h3>
    <p class="msub">Assign a task to team members across project workflows with sticky-note styling & deliverables tracking.</p>
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
    <div class="field">
      <label for="ntDescription">Description / Brief Instructions</label>
      <textarea id="ntDescription" rows="2" placeholder="Specific deliverables, technical notes, or reference links…" style="resize:vertical"></textarea>
    </div>
    <div class="grid3">
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
        <label for="ntPriority">Priority</label>
        <select id="ntPriority">
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
          <option value="low">Low</option>
        </select>
      </div>
      <div class="field">
        <label for="ntDueDate">Due Date</label>
        <input type="date" id="ntDueDate" style="cursor:pointer" onclick="this.showPicker && this.showPicker()">
      </div>
    </div>
    <div class="grid2">
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
      <div class="field">
        <label>Sticky Note Theme</label>
        <input type="hidden" id="ntStickyColor" value="#FFFBEB">
        <div class="sticky-color-swatches" id="taskStickyColorSwatches" style="display:flex;align-items:center;gap:8px;padding-top:6px">
          <button type="button" class="color-swatch-btn active" data-color="#FFFBEB" style="width:26px;height:26px;border-radius:50%;background:#FFFBEB;border:2px solid #F59E0B;cursor:pointer" title="Sunny Amber"></button>
          <button type="button" class="color-swatch-btn" data-color="#FDF2F8" style="width:26px;height:26px;border-radius:50%;background:#FDF2F8;border:2px solid #EC4899;cursor:pointer" title="Rose Blush"></button>
          <button type="button" class="color-swatch-btn" data-color="#ECFDF5" style="width:26px;height:26px;border-radius:50%;background:#ECFDF5;border:2px solid #10B981;cursor:pointer" title="Mint Fresh"></button>
          <button type="button" class="color-swatch-btn" data-color="#F0F9FF" style="width:26px;height:26px;border-radius:50%;background:#F0F9FF;border:2px solid #0284C7;cursor:pointer" title="Sky Azure"></button>
          <button type="button" class="color-swatch-btn" data-color="#F5F3FF" style="width:26px;height:26px;border-radius:50%;background:#F5F3FF;border:2px solid #8B5CF6;cursor:pointer" title="Lavender Soft"></button>
          <button type="button" class="color-swatch-btn" data-color="#FFF7ED" style="width:26px;height:26px;border-radius:50%;background:#FFF7ED;border:2px solid #F97316;cursor:pointer" title="Warm Peach"></button>
          <button type="button" class="color-swatch-btn" data-color="#FEF9C3" style="width:26px;height:26px;border-radius:50%;background:#FEF9C3;border:2px solid #CA8A04;cursor:pointer" title="Lemon Yellow"></button>
        </div>
      </div>
    </div>
    <div class="mfoot">
      <button type="button" class="btn" data-close="taskModal">Cancel</button>
      <button type="button" class="btn primary" id="saveTaskBtn">Create task</button>
    </div>
  </div>
</div>

<!-- 4.1 Sticky Note Task Detail & Review Workspace Modal -->
<div class="modal" id="taskDetailModal" role="dialog" aria-modal="true" aria-labelledby="tdTaskTitle">
  <div class="mbg" data-close="taskDetailModal"></div>
  <div class="mbox workspace-modal sticky-note-modal" id="tdModalBox">
    
    <!-- Notion-style Task Workspace Cover Banner -->
    <div class="workspace-cover-banner sticky-note-banner" id="tdBanner">
      <button class="mclose" data-close="taskDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.35);color:#fff;border:none">&times;</button>
      
      <!-- Dynamic Task Avatar Badge -->
      <div id="tdTaskAvatar" style="position:absolute;bottom:-24px;left:32px;width:60px;height:60px;border-radius:14px;background:var(--red);box-shadow:0 4px 16px rgba(0,0,0,0.25);display:grid;place-items:center;color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:20px;border:3px solid var(--surface)">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>

      <!-- Quick Color Theme Palette (Top Right in Banner) -->
      <div class="sticky-color-picker-mini" id="tdColorPickerMini" title="Change sticky note color" style="position:absolute;top:12px;right:56px">
        <button type="button" class="swatch-mini" data-color="#FFFBEB" style="background:#FFFBEB;border-color:#F59E0B" title="Sunny Amber"></button>
        <button type="button" class="swatch-mini" data-color="#FDF2F8" style="background:#FDF2F8;border-color:#EC4899" title="Rose Blush"></button>
        <button type="button" class="swatch-mini" data-color="#ECFDF5" style="background:#ECFDF5;border-color:#10B981" title="Mint Fresh"></button>
        <button type="button" class="swatch-mini" data-color="#F0F9FF" style="background:#F0F9FF;border-color:#0284C7" title="Sky Azure"></button>
        <button type="button" class="swatch-mini" data-color="#F5F3FF" style="background:#F5F3FF;border-color:#8B5CF6" title="Lavender Soft"></button>
        <button type="button" class="swatch-mini" data-color="#FFF7ED" style="background:#FFF7ED;border-color:#F97316" title="Warm Peach"></button>
        <button type="button" class="swatch-mini" data-color="#FEF9C3" style="background:#FEF9C3;border-color:#CA8A04" title="Lemon Yellow"></button>
      </div>
    </div>

    <!-- Sticky Note Workspace Body -->
    <div class="workspace-modal-body sticky-modal-body">
      <input type="hidden" id="tdTaskId" value="">

      <!-- Title & Assignee Header -->
      <div class="sticky-header-row" style="margin-left:72px">
        <div class="sticky-title-wrap">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
            <span class="badge sticky-badge-project" id="tdProjectBadge">No Project</span>
            <span class="badge" id="tdPriorityBadge">Medium Priority</span>
            <span class="badge" id="tdStageBadge">To do</span>
          </div>
          <h2 id="tdTaskTitle" class="sticky-title">Task Title</h2>
          <div class="sticky-meta-chips">
            <span class="sticky-chip" id="tdAssigneeChip">
              <span class="av-chip" id="tdAssigneeAvatar" style="background:#C52523">BK</span>
              <span id="tdAssigneeName">Barny Kiome</span>
            </span>
            <span class="sticky-chip" id="tdAssignerChip">
              <span style="color:var(--muted)">Assigned by:</span> <b id="tdAssignerName">Production Lead</b>
            </span>
            <span class="sticky-chip" id="tdDueDateChip">
              <span style="color:var(--muted)">Due:</span> <b id="tdDueDateText">Flexible</b>
            </span>
          </div>
        </div>
      </div>

      <!-- Main 2-Column Split: Deliverables/Details & Review/Comments -->
      <div class="sticky-workspace-grid">
        
        <!-- Left Pane: Details & Deliverable Review Links -->
        <div class="sticky-col-left">
          
          <!-- Task Description / Brief Card -->
          <div class="sticky-card-section">
            <div class="sticky-section-head">
              <h4>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Task Brief & Deliverable Notes
              </h4>
            </div>
            <div class="sticky-description-box" id="tdDescriptionBox">
              No specific instructions recorded.
            </div>
          </div>

          <!-- Deliverables & Review Links (Requirement 2) -->
          <div class="sticky-card-section">
            <div class="sticky-section-head">
              <h4>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                Deliverable Review Links
              </h4>
              <span class="badge" id="tdLinksCountBadge" style="font-size:11px;background:var(--panel-2)">0 links</span>
            </div>
            <p class="section-subtext">Add Google Drive, Frame.io, Dropbox, Playbook, Figma, or report links for review by the Owner & Project Manager.</p>

            <!-- Links List -->
            <div class="sticky-links-list" id="tdLinksList">
              <!-- Rendered dynamically -->
            </div>

            <!-- Add Review Link Form -->
            <div class="sticky-add-link-box">
              <div class="grid2" style="gap:10px;margin-bottom:8px">
                <input type="text" id="tdNewLinkTitle" placeholder="Link label (e.g. Rough Cut v2 for Review)" autocomplete="off">
                <div style="position:relative">
                  <input type="url" id="tdNewLinkUrl" placeholder="Paste link (Google Drive, Frame.io, Figma…)" autocomplete="off">
                  <span id="tdProviderDetectBadge" class="provider-detect-badge" style="display:none">Cloud Link</span>
                </div>
              </div>
              <div style="display:flex;justify-content:flex-end">
                <button type="button" class="btn primary" id="tdAddLinkBtn" style="font-size:12px;padding:6px 14px">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg> Attach Review Link
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Pane: PM Workflow Controls & Recommendations / Comments -->
        <div class="sticky-col-right">
          
          <!-- Project Manager / Reviewer Workflow Control Bar (Requirement 3) -->
          <div class="sticky-card-section pm-workflow-card">
            <div class="sticky-section-head">
              <h4>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Stage Stepper & Workflow Review
              </h4>
              <span class="badge" id="tdCurrentStagePill" style="font-weight:700">Stage</span>
            </div>

            <!-- 5-Stage Stepper -->
            <div class="task-stage-stepper" id="tdStageStepper">
              <button type="button" class="stage-step-btn" data-stage="todo"><span>1</span> To do</button>
              <button type="button" class="stage-step-btn" data-stage="in_progress"><span>2</span> In progress</button>
              <button type="button" class="stage-step-btn" data-stage="review_internal"><span>3</span> Internal Review</button>
              <button type="button" class="stage-step-btn" data-stage="review_client"><span>4</span> Client Review</button>
              <button type="button" class="stage-step-btn" data-stage="done"><span>5</span> Done ✓</button>
            </div>

            <!-- Workflow Action Buttons -->
            <div class="workflow-action-buttons" id="tdWorkflowActions">
              <button type="button" class="btn btn-revert" id="tdSendBackBtn" title="Request revisions and revert to In Progress">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Send Back for Changes
              </button>
              <button type="button" class="btn primary btn-advance" id="tdProceedBtn" title="Approve and move to next stage">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                Proceed to Next Level →
              </button>
            </div>

            <!-- Expandable Send Back Revision Note Box -->
            <div class="revision-note-box" id="tdRevisionNoteBox" style="display:none">
              <label for="tdRevisionText"><b>Revision Recommendation & Required Changes *</b></label>
              <textarea id="tdRevisionText" rows="2" placeholder="Specify what adjustments are needed (e.g. fix color cast at 01:20, adjust audio ducking)..."></textarea>
              <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:6px">
                <button type="button" class="btn" id="tdCancelRevisionBtn">Cancel</button>
                <button type="button" class="btn primary" id="tdConfirmSendBackBtn" style="background:#DC2626">Submit Revisions & Revert</button>
              </div>
            </div>
          </div>

          <!-- Discussion, Recommendations & Activity Stream (Requirement 3) -->
          <div class="sticky-card-section activity-stream-card">
            <div class="sticky-section-head">
              <h4>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Discussion & Recommendations
              </h4>
              <span class="badge" id="tdCommentsCountBadge" style="font-size:11px;background:var(--panel-2)">0 entries</span>
            </div>

            <!-- Comments Feed -->
            <div class="sticky-comments-feed" id="tdCommentsFeed">
              <!-- Populated dynamically -->
            </div>

            <!-- Add Comment Form -->
            <div class="sticky-comment-form">
              <div class="comment-input-header">
                <select id="tdCommentTypeSelect" style="font-size:12px;padding:4px 8px;width:auto">
                  <option value="comment">💬 General Comment</option>
                  <option value="recommendation">💡 Recommendation</option>
                  <option value="change_request">⚠️ Revision Note</option>
                </select>
                <span style="font-size:11px;color:var(--muted)">Visible to project team & managers</span>
              </div>
              <textarea id="tdCommentMessage" rows="2" placeholder="Write a comment or recommendation for this task…" style="margin-bottom:8px;resize:vertical"></textarea>
              <div style="display:flex;justify-content:flex-end">
                <button type="button" class="btn primary" id="tdPostCommentBtn" style="font-size:12px;padding:6px 14px">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                  Post Comment
                </button>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>

    <!-- Workspace Modal Footer -->
    <div class="workspace-modal-footer">
      <div class="workspace-modal-footer-left">
        <button type="button" class="btn btn-outline-danger" id="tdDeleteTaskBtn">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Remove Task
        </button>
      </div>
      <div class="workspace-modal-footer-right">
        <button type="button" class="btn" data-close="taskDetailModal">Close</button>
      </div>
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
        <label for="niDue">Due Date *</label>
        <input id="niDue" type="date" required autocomplete="off" style="cursor:pointer" onclick="this.showPicker && this.showPicker()">
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
        <label for="neDate">Expense Date *</label>
        <input id="neDate" type="date" required autocomplete="off" style="cursor:pointer" onclick="this.showPicker && this.showPicker()">
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

    <!-- Email Setup Notification Option -->
    <div style="margin-top:4px;margin-bottom:14px;padding:12px 14px;background:var(--panel-2);border:1px solid var(--line);border-radius:10px">
      <label style="display:flex;align-items:center;gap:10px;font-size:12.5px;color:var(--ink);cursor:pointer;margin:0">
        <input type="checkbox" id="nuSendInviteEmail" checked style="width:16px;height:16px;accent-color:var(--red);cursor:pointer">
        <span><b>Send email invitation &amp; password setup link</b> to user's inbox</span>
      </label>
      <div style="font-size:11px;color:var(--muted);margin-top:4px;margin-left:26px">The user will receive an onboarding email with a direct link and 6-digit verification code to choose their personal password.</div>
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
        <svg viewBox="0 0 48 48" width="42" height="42">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        </svg>
      </div>
      <h3 id="googleAuthTitle" style="font-family:'Google Sans',Roboto,Segoe UI,sans-serif;font-size:21px;font-weight:500;color:#202124;margin:0 0 6px">Connect Personal Google Calendar</h3>
      <p style="font-size:13px;color:#5f6368;margin:0">Authenticate directly via Google to mirror your shoots, deadlines &amp; video meetings</p>
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
        <div style="font-size:11px;color:#137333;margin-top:2px">Clicking the button below opens Google's authorization page to re-authenticate or switch accounts.</div>
      </div>

      <!-- Personal Google Account Email (Optional Pre-fill) -->
      <div style="margin-bottom:16px">
        <label for="personalGoogleEmailInput" style="display:block;font-size:12.5px;font-weight:600;color:#3c4043;margin-bottom:6px">
          Personal Google Account Email (Optional Hint)
        </label>
        <div style="display:flex;gap:8px">
          <input type="email" id="personalGoogleEmailInput" placeholder="e.g. yourname@gmail.com" style="flex:1;padding:9px 12px;border:1.5px solid #dadce0;border-radius:8px;font-size:13px;color:#202124;outline:none;background:#fff" autocomplete="email">
          <button type="button" class="btn" id="autofillMyEmailBtn" style="white-space:nowrap;font-size:11.5px;border:1px solid #dadce0;background:#f8f9fa;color:#1a73e8;padding:8px 12px" title="Use your logged-in email">
            Use my email
          </button>
        </div>
        <span style="font-size:11px;color:#5f6368;margin-top:4px;display:block">Opening the Google Auth page allows you to pick any Google account on your device.</span>
      </div>

      <!-- Permissions & Scopes Disclosure -->
      <div style="background:#f8f9fa;border:1px solid #e8eaed;border-radius:12px;padding:14px">
        <div style="font-size:12px;font-weight:700;color:#202124;margin-bottom:8px;display:flex;align-items:center;gap:6px">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#1a73e8" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Google Authorized Access:
        </div>
        <div style="display:flex;flex-direction:column;gap:7px;font-size:11.5px;color:#3c4043">
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Google OAuth Consent:</b> Secure authorization provided directly by Google (accounts.google.com).</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Bi-directional Calendar Sync:</b> Shoots, meetings, and milestones mirror to your Google Calendar.</span>
          </div>
          <div style="display:flex;align-items:flex-start;gap:8px">
            <span style="color:#34a853;font-weight:bold">✓</span>
            <span><b>Google Meet Integration:</b> Auto-generate video conferencing links for all scheduled client events.</span>
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
      <button type="button" class="btn primary" id="confirmGoogleAuthBtn" style="background:#1a73e8;border-color:#1a73e8;color:#fff;font-size:13px;padding:9px 20px;border-radius:8px;font-weight:600;display:inline-flex;align-items:center;gap:8px;cursor:pointer">
        <svg viewBox="0 0 48 48" width="18" height="18" style="background:#fff;border-radius:50%;padding:1px">
          <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
          <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
          <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        </svg>
        <span>Open Google Auth Page</span>
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
  <div class="mbox workspace-modal">
    <!-- Notion / Zoho Banner Header -->
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg, #050507 0%, #121316 28%, #5a0c0b 68%, #C52523 100%)">
      <button class="mclose" data-close="leadDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      
      <!-- Dynamic Lead Initials Avatar -->
      <div id="ldmAvatar" style="position:absolute;bottom:-24px;left:32px;width:60px;height:60px;border-radius:14px;background:var(--red);box-shadow:0 4px 16px rgba(0,0,0,0.25);display:grid;place-items:center;color:#fff;font-family:'Poppins',sans-serif;font-weight:700;font-size:22px;border:3px solid var(--surface)">
        IA
      </div>
    </div>

    <div class="workspace-modal-body">
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

    <!-- Client / Lead System Selector -->
    <div class="field" style="margin-bottom:14px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:12px">
      <label for="quoteSelectClientOrLead" style="font-weight:700;color:var(--ink);display:flex;align-items:center;gap:6px;font-size:12px;margin-bottom:6px">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Select Client or Lead in JMOS (Auto-fills details)
      </label>
      <select id="quoteSelectClientOrLead" onchange="window.onSelectQuoteClientOrLead(this.value)">
        <option value="">— Choose Client or Lead from System (or enter new below) —</option>
      </select>
    </div>

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
      <button type="button" class="btn" onclick="window.submitQuoteForm('save')" style="font-weight:600" id="saveOnlyQuoteBtn">
        Save Quotation
      </button>
      <button type="button" class="btn" onclick="window.submitQuoteForm('whatsapp')" style="background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
        Save &amp; WhatsApp
      </button>
      <button type="button" class="btn" id="saveQuoteBtn" onclick="window.submitQuoteForm('email')" style="font-weight:600">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Save &amp; Email
      </button>
      <button type="button" class="btn primary" id="saveAndUpgradeQuoteBtn" onclick="window.submitQuoteForm('upgrade')" style="background:var(--red);border-color:var(--red);font-weight:700">
        <svg viewBox="0 0 24 24" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
        Save &amp; Generate Invoice ➔
      </button>
    </div>
  </div>
</div>

<!-- H. Quotation Detailed Preview & Dispatch Modal -->
<div class="modal" id="quoteDetailModal" role="dialog" aria-modal="true" aria-labelledby="qdmTitle">
  <div class="mbg" data-close="quoteDetailModal"></div>
  <div class="mbox workspace-modal" style="max-width:820px">
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg, #050507 0%, #121316 28%, #5a0c0b 68%, #C52523 100%);padding:24px 32px;color:#fff;display:flex;flex-direction:column;justify-content:center">
      <button class="mclose" data-close="quoteDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:0.85;font-weight:700">JEOTA MEDIA · COMMERCIAL PROPOSAL &amp; QUOTATION</div>
      <h3 id="qdmTitle" style="margin:4px 0 0;font-size:24px;color:#fff;font-family:'Poppins',sans-serif">Quotation Preview</h3>
      <div style="font-size:12.5px;opacity:0.9;margin-top:4px" id="qdmSubtitle">Quote #QT-2026-001 · Prepared for Client</div>
    </div>

    <div class="workspace-modal-body" style="padding:28px 36px">
      <!-- Quick Dispatch Action Bar -->
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-bottom:16px;border-bottom:1px solid var(--line);margin-bottom:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:6px">
          <span class="badge" id="qdmStatusBadge" style="background:var(--panel-2);color:var(--muted);font-weight:700">Draft</span>
          <span class="mono" id="qdmTotalBadge" style="font-size:16px;font-weight:700;color:var(--ink)">KES 0</span>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn" id="qdmEditBtn" onclick="window.openEditQuoteModalFromDetail()" style="font-size:11.5px;padding:6px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px" title="Edit quotation line items and rates">
            <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Edit Quote
          </button>
          <button type="button" class="btn" id="qdmApproveBtn" onclick="window.approveActiveQuote()" style="font-size:11.5px;padding:6px 12px;background:var(--green-soft);color:var(--green);border-color:rgba(19,115,51,0.25);font-weight:600;display:inline-flex;align-items:center;gap:5px" title="Approve quotation and mark ready for invoicing">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Approve Quote
          </button>
          <button type="button" class="btn primary" onclick="window.printQuotePdf()" style="font-size:11.5px;padding:6px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>Print / Save PDF
          </button>
          <button type="button" class="btn" id="qdmSendEmailBtn" onclick="window.dispatchQuoteEmail()" style="font-size:11.5px;padding:6px 12px;font-weight:600;display:inline-flex;align-items:center;gap:5px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Send Email
          </button>
          <button type="button" class="btn" id="qdmSendWhatsAppBtn" onclick="window.dispatchQuoteWhatsApp()" style="font-size:11.5px;padding:6px 12px;background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600;display:inline-flex;align-items:center;gap:5px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>Send WhatsApp
          </button>
          <button type="button" class="btn primary" id="qdmUpgradeInvoiceBtn" onclick="window.openUpgradeQuoteModal()" style="font-size:11.5px;padding:6px 14px;background:var(--red);border-color:var(--red);font-weight:700;display:inline-flex;align-items:center;gap:5px">
            <svg viewBox="0 0 24 24" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>Generate Invoice ➔
          </button>
        </div>
      </div>

      <input type="hidden" id="qdmQuoteId">

      <!-- Printable Commercial Quotation Document Preview -->
      <div id="printableQuoteDoc" class="printable-invoice-container" style="background:#fff;color:#1a1a1a;border:1px solid var(--line);border-radius:10px;padding:24px;margin-bottom:16px">
        <!-- Document Header -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:18px;border-bottom:2px solid #1a1a1a;margin-bottom:18px">
          <div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-weight:900;font-size:22px;letter-spacing:-0.5px;color:#C52523;font-family:'Poppins',sans-serif">JEOTA MEDIA</span>
            </div>
            <div style="font-size:11.5px;color:#555;margin-top:4px;line-height:1.4">
              Jeota Media Limited · Creative Agency &amp; Production House<br>
              Nairobi, Kenya · info@jeotamedia.co.ke · +254 712 345 678<br>
              <b>KRA PIN:</b> P052209707D · Tax Exempt (0% Professional Creative Services)
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-size:20px;font-weight:900;color:#1a1a1a;letter-spacing:1px;font-family:'Poppins',sans-serif">COMMERCIAL QUOTE</div>
            <div style="font-size:13px;font-weight:700;color:#C52523;font-family:'IBM Plex Mono',monospace;margin-top:2px" id="qdmDocQuoteNo">QT-2026-001</div>
            <div style="font-size:11.5px;color:#666;margin-top:4px">
              <b>Date:</b> <span id="qdmDocDate">—</span><br>
              <b>Validity:</b> <span id="qdmDocValidity">14 Days</span>
            </div>
          </div>
        </div>

        <!-- Bill To / Scope Summary -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid #eee;font-size:12px">
          <div>
            <div style="font-size:10.5px;text-transform:uppercase;color:#888;font-weight:700;letter-spacing:0.5px;margin-bottom:3px">PREPARED FOR</div>
            <div style="font-weight:700;font-size:14px;color:#1a1a1a" id="qdmDocRecipientName">Client Name</div>
            <div style="color:#555;margin-top:2px" id="qdmDocRecipientContact">info@client.co.ke</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:10.5px;text-transform:uppercase;color:#888;font-weight:700;letter-spacing:0.5px;margin-bottom:3px">PROPOSAL TITLE / SCOPE</div>
            <div style="font-weight:700;font-size:13px;color:#1a1a1a" id="qdmDocScopeTitle">Video Production Proposal</div>
            <div style="color:#666;margin-top:2px;font-size:11px">Production, Editing, Color Grading &amp; Deliverables</div>
          </div>
        </div>

        <!-- Line Items Table -->
        <div id="qdmItemsContainer" style="margin-bottom:16px">
          <!-- Rendered dynamically -->
        </div>

        <!-- Summary & Scope Notes -->
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;padding-top:12px;border-top:1px solid #eee;align-items:start">
          <div>
            <div style="font-size:10.5px;text-transform:uppercase;color:#888;font-weight:700;letter-spacing:0.5px;margin-bottom:4px">SCOPE BREAKDOWN &amp; PRODUCTION NOTES</div>
            <div id="qdmNotesBox" style="font-size:11.5px;color:#444;line-height:1.5;background:#f9f9fa;border:1px solid #eee;border-radius:6px;padding:10px">
              Scope notes: Full production includes filming gear, lighting kit, sound recording, editing &amp; color grading.
            </div>
          </div>
          <div style="text-align:right;font-size:12.5px;line-height:1.8">
            <div style="display:flex;justify-content:space-between;color:#666">
              <span>Subtotal:</span>
              <span class="mono" id="qdmDocSubtotal">KES 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;color:#666" id="qdmDocDiscountRow">
              <span>Discount:</span>
              <span class="mono" id="qdmDocDiscount" style="color:#C52523">- KES 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;color:#666">
              <span>VAT (0% Exempt):</span>
              <span class="mono">KES 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;color:#1a1a1a;border-top:2px solid #1a1a1a;padding-top:6px;margin-top:4px">
              <span>Total Quotation:</span>
              <span class="mono" id="qdmDocTotal" style="color:#C52523">KES 0</span>
            </div>
          </div>
        </div>

        <!-- Terms Footer -->
        <div style="margin-top:20px;padding-top:12px;border-top:1px dashed #ddd;font-size:10.5px;color:#777;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
          <div>
            <b>Payment Milestone:</b> 60% Deposit on Project Kickoff · 40% upon Final Master Delivery.
          </div>
          <div style="font-style:italic">
            Authorized by Jeota Media Production Department
          </div>
        </div>
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
  <div class="mbox" style="max-width:500px">
    <button class="mclose" data-close="upgradeQuoteModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:rgba(197,37,35,0.12);color:var(--red);font-weight:700">INVOICE GENERATION</span>
    </div>
    <h3 id="upgQuoteTitle">Generate Invoice from Quotation</h3>
    <p class="msub">Convert this approved quote into an official invoice. You can preview the invoice PDF before dispatching it to the client.</p>

    <input type="hidden" id="upgQuoteId">
    
    <div class="field">
      <label for="upgInvoiceType">Invoice Milestone / Billing Type</label>
      <select id="upgInvoiceType" onchange="window.onUpgTypeChange(this.value)">
        <option value="Deposit 60%">Deposit 60% (Standard Project Kickoff)</option>
        <option value="Full Payment 100%">Full Payment 100% (Complete Settlement)</option>
        <option value="Milestone 50%">Milestone 50% (Midway Progress)</option>
        <option value="Final Balance 40%">Final Balance 40% (Project Completion)</option>
        <option value="Custom">Custom Negotiated Amount</option>
      </select>
    </div>

    <div class="field">
      <label for="upgAmount">Invoice Amount (KES) *</label>
      <input id="upgAmount" type="number" required placeholder="KES amount">
    </div>

    <div class="field">
      <label for="upgDueDate">Payment Due Date *</label>
      <input id="upgDueDate" type="date" required autocomplete="off" style="cursor:pointer" onclick="this.showPicker && this.showPicker()">
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="upgradeQuoteModal">Cancel</button>
      <button type="button" class="btn primary" id="upgSubmitBtn" onclick="window.submitUpgradeQuoteToInvoice()" style="background:var(--red);border-color:var(--red);font-weight:700">
        Generate Invoice &amp; Preview PDF ➔
      </button>
    </div>
  </div>
</div>

<!-- I2. Commercial & Tax Invoice Detailed Preview & PDF Generation Modal -->
<div class="modal" id="invoiceDetailModal" role="dialog" aria-modal="true" aria-labelledby="idmTitle">
  <div class="mbg" data-close="invoiceDetailModal"></div>
  <div class="mbox workspace-modal" style="max-width:820px">
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg, #050507 0%, #121316 28%, #5a0c0b 68%, #C52523 100%);padding:24px 32px;color:#fff;display:flex;flex-direction:column;justify-content:center">
      <button class="mclose" data-close="invoiceDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:0.85;font-weight:700">JEOTA MEDIA · COMMERCIAL &amp; TAX INVOICE</div>
      <h3 id="idmTitle" style="margin:4px 0 0;font-size:24px;color:#fff;font-family:'Poppins',sans-serif">Invoice JM-0146</h3>
      <div style="font-size:12.5px;opacity:0.9;margin-top:4px" id="idmSubtitle">Billed to Aquila · Deposit 60%</div>
    </div>

    <div class="workspace-modal-body" style="padding:28px 36px">
      <!-- Quick Dispatch & Action Bar -->
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-bottom:16px;border-bottom:1px solid var(--line);margin-bottom:20px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="badge" id="idmStatusBadge" style="background:var(--panel-2);color:var(--muted);font-weight:700">Sent</span>
          <span class="badge" id="idmEtimsBadge" style="display:none;background:var(--green-soft);color:var(--green);font-weight:700">✓ eTIMS Compliant</span>
          <span class="mono" id="idmTotalBadge" style="font-size:16px;font-weight:700;color:var(--ink)">KES 0</span>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn primary" onclick="window.printInvoicePdf()" style="font-size:11.5px;padding:6px 13px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
            Print / Save PDF
          </button>
          <button type="button" class="btn" id="idmSendEmailBtn" onclick="window.dispatchInvoiceEmail()" style="font-size:11.5px;padding:6px 13px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Send Email
          </button>
          <button type="button" class="btn" id="idmSendWhatsAppBtn" onclick="window.dispatchInvoiceWhatsApp()" style="font-size:11.5px;padding:6px 13px;background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
            Send WhatsApp
          </button>
          <button type="button" class="btn" id="idmPayBtn" onclick="window.recordInvoicePaymentFromModal()" style="font-size:11.5px;padding:6px 12px;background:var(--green-soft);color:var(--green);border-color:rgba(19,115,51,0.25);font-weight:600;display:inline-flex;align-items:center;gap:5px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Record Payment
          </button>
          <button type="button" class="btn" onclick="window.openEditInvoiceModalFromDetail()" style="font-size:11.5px;padding:6px 10px" title="Edit invoice details">
            <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>Edit
          </button>
        </div>
      </div>

      <input type="hidden" id="idmInvoiceId">

      <!-- Printable Invoice Document Container -->
      <div id="printableInvoiceDoc" class="printable-invoice-container" style="background:#fff;color:#1a1a1a;border:1px solid var(--line);border-radius:10px;padding:24px;margin-bottom:16px">
        <!-- Document Header -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:18px;border-bottom:2px solid #1a1a1a;margin-bottom:18px">
          <div>
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-weight:900;font-size:22px;letter-spacing:-0.5px;color:#C52523;font-family:'Poppins',sans-serif">JEOTA MEDIA</span>
            </div>
            <div style="font-size:11.5px;color:#555;margin-top:4px;line-height:1.4">
              Jeota Media Limited · Creative Agency &amp; Production House<br>
              Nairobi, Kenya · info@jeotamedia.co.ke · +254 712 345 678<br>
              <b>KRA PIN:</b> P052209707D · eTIMS Registered
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-size:20px;font-weight:800;color:#1a1a1a;letter-spacing:1px">INVOICE</div>
            <div class="mono" style="font-size:14px;font-weight:700;color:#C52523;margin-top:2px" id="idmDocInvoiceNo">JM-0146</div>
            <div style="font-size:11.5px;color:#555;margin-top:4px">
              <b>Date:</b> <span id="idmDocIssueDate">22 Sep 2026</span><br>
              <b>Due Date:</b> <span id="idmDocDueDate">15 Sep 2026</span>
            </div>
          </div>
        </div>

        <!-- Billed To & Remit Summary Box -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;background:#f8fafc;padding:14px 18px;border-radius:8px;border:1px solid #e2e8f0">
          <div>
            <div style="font-size:11px;text-transform:uppercase;font-weight:700;color:#64748b;letter-spacing:0.5px;margin-bottom:4px">Billed To:</div>
            <div style="font-size:14px;font-weight:700;color:#0f172a" id="idmDocClientName">Aquila</div>
            <div style="font-size:12px;color:#475569" id="idmDocClientContact">Client Account · Commercial Deliverables</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:11px;text-transform:uppercase;font-weight:700;color:#64748b;letter-spacing:0.5px;margin-bottom:4px">Payment Status:</div>
            <div style="font-size:13px;font-weight:700" id="idmDocPaymentStatus">Sent (Unpaid)</div>
            <div style="font-size:12px;color:#475569" id="idmDocPaymentMethod">Method: Bank Transfer / M-Pesa</div>
          </div>
        </div>

        <!-- Line Items Table -->
        <div style="margin-bottom:18px;overflow-x:auto">
          <table style="width:100%;border-collapse:collapse;font-size:12.5px">
            <thead>
              <tr style="background:#0f172a;color:#fff;text-align:left">
                <th style="padding:9px 12px;border-top-left-radius:6px">Item / Description</th>
                <th style="padding:9px 12px;text-align:center">Milestone / Scope</th>
                <th style="padding:9px 12px;text-align:right">Qty</th>
                <th style="padding:9px 12px;text-align:right;border-top-right-radius:6px">Amount (KES)</th>
              </tr>
            </thead>
            <tbody id="idmLineItemsTableBody">
              <tr style="border-bottom:1px solid #e2e8f0">
                <td style="padding:10px 12px;font-weight:600;color:#0f172a" id="idmItemDesc">Production Deposit 60% — Aquila</td>
                <td style="padding:10px 12px;text-align:center;color:#475569" id="idmItemMilestone">Deposit 60%</td>
                <td style="padding:10px 12px;text-align:right;color:#475569">1</td>
                <td class="mono" style="padding:10px 12px;text-align:right;font-weight:700;color:#0f172a" id="idmItemAmount">KES 667,788</td>
              </tr>
            </tbody>
            <tfoot>
              <tr style="border-top:2px solid #0f172a">
                <td colspan="3" style="padding:10px 12px;text-align:right;font-weight:600;font-size:13px;color:#475569">Subtotal:</td>
                <td class="mono" style="padding:10px 12px;text-align:right;font-weight:700;font-size:13px;color:#0f172a" id="idmDocSubtotal">KES 667,788</td>
              </tr>
              <tr>
                <td colspan="3" style="padding:6px 12px;text-align:right;font-size:12px;color:#64748b">eTIMS Tax / VAT:</td>
                <td class="mono" style="padding:6px 12px;text-align:right;font-size:12px;color:#64748b" id="idmDocTax">KES 0.00 (Exempt/Direct)</td>
              </tr>
              <tr style="background:#fef2f2;color:#C52523">
                <td colspan="3" style="padding:12px 12px;text-align:right;font-weight:800;font-size:15px">TOTAL DUE:</td>
                <td class="mono" style="padding:12px 12px;text-align:right;font-weight:900;font-size:16px" id="idmDocGrandTotal">KES 667,788</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Official Remittance & Banking Box -->
        <div style="background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:8px;padding:14px 18px;margin-bottom:16px">
          <div style="font-size:12px;font-weight:700;color:#0f172a;margin-bottom:8px;display:flex;align-items:center;gap:6px">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#C52523" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            HOW TO PAY (Official Remittance Details)
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:12px;color:#334155">
            <div>
              <div style="font-weight:700;color:#0f172a;margin-bottom:2px">Option 1: Lipa na M-Pesa (Paybill)</div>
              <div>• <b>Paybill Business Number:</b> <span class="mono" style="font-weight:700">880100</span></div>
              <div>• <b>Account Number:</b> <span class="mono" style="font-weight:700;color:#C52523" id="idmDocMpesaAcc">JM-0146</span></div>
            </div>
            <div>
              <div style="font-weight:700;color:#0f172a;margin-bottom:2px">Option 2: Electronic Bank Transfer (EFT / RTGS)</div>
              <div>• <b>Bank:</b> NCBA Bank Kenya · Branch: Upper Hill</div>
              <div>• <b>Account Name:</b> Jeota Media Limited</div>
              <div>• <b>Account No:</b> <span class="mono" style="font-weight:700">1002349871</span></div>
            </div>
          </div>
        </div>

        <!-- Terms & Verification Sign-off -->
        <div style="font-size:11px;color:#64748b;line-height:1.5;border-top:1px solid #e2e8f0;padding-top:10px;display:flex;justify-content:space-between;align-items:flex-end">
          <div>
            <b>Payment Terms:</b> Payment is due strictly upon the specified due date.<br>
            For billing inquiries or KRA eTIMS invoice certificates, contact <b>finance@jeotamedia.co.ke</b>.
          </div>
          <div style="text-align:right">
            <div style="font-size:10px;text-transform:uppercase;color:#94a3b8">Authorized Signatory</div>
            <div style="font-weight:700;color:#0f172a">Jeota Media Finance Operations</div>
          </div>
        </div>
      </div>

      <div style="font-size:11.5px;color:var(--muted);border-top:1px solid var(--line);padding-top:12px;display:flex;align-items:center;justify-content:space-between">
        <span id="idmFooterTimestamp">System generated commercial invoice</span>
        <button type="button" class="linkbtn" style="color:var(--red)" onclick="window.deleteActiveInvoiceFromDetail()">Delete Invoice</button>
      </div>
    </div>
  </div>
</div>

<!-- J. Cloud Document Link Modal (Google Drive, Dropbox, Playbook, Notion) -->
<div class="modal" id="documentModal" role="dialog" aria-modal="true" aria-labelledby="docModalTitle">
  <div class="mbg" data-close="documentModal"></div>
  <div class="mbox" style="max-width:540px">
    <button class="mclose" data-close="documentModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:rgba(37,211,102,0.15);color:#128C7E;font-size:11px;font-weight:700">ZERO HOST STORAGE</span>
    </div>
    <h3 id="docModalTitle">Add Document Link</h3>
    <p class="msub">Attach Google Drive, Dropbox, Playbook, Notion, or Frame.io links directly to save hosting space and data traffic.</p>

    <!-- Cloud Provider Badges -->
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">
      <span class="badge" style="background:rgba(26,115,232,0.1);color:#1a73e8;font-size:11px;font-weight:600;padding:3px 8px">Google Drive</span>
      <span class="badge" style="background:rgba(0,97,255,0.1);color:#0061ff;font-size:11px;font-weight:600;padding:3px 8px">Dropbox</span>
      <span class="badge" style="background:rgba(124,58,237,0.1);color:#7c3aed;font-size:11px;font-weight:600;padding:3px 8px">Playbook</span>
      <span class="badge" style="background:rgba(99,102,241,0.1);color:#6366f1;font-size:11px;font-weight:600;padding:3px 8px">Frame.io</span>
      <span class="badge" style="background:rgba(17,24,39,0.1);color:var(--ink);font-size:11px;font-weight:600;padding:3px 8px">Notion</span>
      <span class="badge" style="background:rgba(234,88,12,0.1);color:#ea580c;font-size:11px;font-weight:600;padding:3px 8px">Figma / Other</span>
    </div>

    <input type="hidden" id="docUploadType" value="link">

    <div class="field">
      <label for="docUrlInput">Cloud Document / Storage Link *</label>
      <input type="url" id="docUrlInput" placeholder="https://drive.google.com/file/d/... or https://playbook.com/..." required autocomplete="off" oninput="window.onDocUrlInput(this.value)">
      <div id="docDetectedProvider" style="font-size:11.5px;color:var(--muted);margin-top:4px;display:none"></div>
    </div>

    <div class="field">
      <label for="docInputTitle">Document Title *</label>
      <input id="docInputTitle" placeholder="e.g. Moyo Honey — Master Production Treatment &amp; Storyboard" required autocomplete="off">
    </div>

    <div class="grid2">
      <div class="field">
        <label for="docFolderSelect">Folder Category *</label>
        <select id="docFolderSelect">
          <option value="contracts">Contracts &amp; Legal</option>
          <option value="proposals">Proposals &amp; Quotes</option>
          <option value="brand_guides">Brand Guides &amp; Logos</option>
          <option value="briefs">Production Briefs</option>
          <option value="grants">Grant Applications</option>
          <option value="general">General Assets &amp; Footage</option>
        </select>
      </div>
      <div class="field">
        <label for="docProviderSelect">Cloud Platform</label>
        <select id="docProviderSelect">
          <option value="auto">Auto-Detect from Link</option>
          <option value="Google Drive">Google Drive</option>
          <option value="Dropbox">Dropbox</option>
          <option value="Playbook">Playbook</option>
          <option value="Frame.io">Frame.io</option>
          <option value="Notion">Notion</option>
          <option value="Figma">Figma</option>
          <option value="OneDrive">OneDrive / SharePoint</option>
          <option value="Cloud Link">Other Cloud Link</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="docInputNotes">Description / Version Notes</label>
      <textarea id="docInputNotes" rows="2" placeholder="e.g. Final approved treatment with shot list, moodboard and references..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="documentModal">Cancel</button>
      <button type="button" class="btn primary" id="saveDocBtn" onclick="window.submitDocumentForm()">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>Save Document Link
      </button>
    </div>
  </div>
</div>

<!-- K. Create / Edit Impact Grant & Partnership Exploration Modal -->
<div class="modal" id="fundraisingModal" role="dialog" aria-modal="true" aria-labelledby="frModalTitle">
  <div class="mbg" data-close="fundraisingModal"></div>
  <div class="mbox" style="max-width:620px">
    <button class="mclose" data-close="fundraisingModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" id="frModalBadge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-weight:700">JEOTA FUNDRAISING MASTER</span>
    </div>
    <h3 id="frModalTitle">Add Opportunity / Partner</h3>
    <p class="msub" id="frModalSub">Log open funding calls, grants application, or strategic partnership entities.</p>

    <input type="hidden" id="frFormId">

    <div class="grid2">
      <div class="field">
        <label for="frCategory">Master Module *</label>
        <select id="frCategory" onchange="window.onFrCategoryChange && window.onFrCategoryChange(this.value)">
          <option value="open_calls">Grants &amp; Open Calls</option>
          <option value="partnerships">Partnership Exploration</option>
          <option value="fellowships">Fellowships &amp; Residencies</option>
        </select>
      </div>
      <div class="field">
        <label for="frNature">Nature of Opportunity *</label>
        <select id="frNature">
          <option value="Grants Application">Grants Application</option>
          <option value="Partnership Invitation">Partnership Invitation</option>
          <option value="Grant">Grant</option>
          <option value="Fellowship">Fellowship</option>
          <option value="Competition">Competition</option>
        </select>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="frOrg">Entity / Organization Name *</label>
        <input id="frOrg" placeholder="e.g. Oak Foundation, Altérra, D-Prize" required autocomplete="off">
      </div>
      <div class="field" id="frEntityTypeWrap">
        <label for="frEntityType">Section / Classification</label>
        <select id="frEntityType">
          <option value="Grantmakers / CBOs">a. Grantmakers / CBOs</option>
          <option value="Associations / Cooperatives">b. Associations / Cooperatives</option>
          <option value="Corporate Institutions">c. Corporate Institutions</option>
          <option value="Academia / Educational Institutions">d. Academia / Educational Institutions</option>
        </select>
      </div>
    </div>

    <div class="grid2">
      <div class="field">
        <label for="frProgram">Program Title / Particulars</label>
        <input id="frProgram" placeholder="e.g. Grants &amp; Environment Programme" autocomplete="off">
      </div>
      <div class="field">
        <label for="frLink">Website / Portal URL</label>
        <input id="frLink" placeholder="https://..." autocomplete="off">
      </div>
    </div>

    <div class="grid3">
      <div class="field">
        <label for="frAmount">Funding Value (KES)</label>
        <input id="frAmount" type="number" placeholder="e.g. 15000000" autocomplete="off">
      </div>
      <div class="field">
        <label for="frDeadline">Deadline / Timeline</label>
        <input id="frDeadline" type="text" placeholder="e.g. Rolling basis or YYYY-MM-DD" autocomplete="off">
      </div>
      <div class="field">
        <label for="frStatus">Status</label>
        <select id="frStatus">
          <option value="Pending">Pending</option>
          <option value="Identified">Identified</option>
          <option value="In Progress">In Progress</option>
          <option value="Submitted">Submitted</option>
          <option value="Won / Awarded">Won / Awarded</option>
          <option value="Closed">Closed</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="frNotes">Comments, Criteria &amp; Strategy</label>
      <textarea id="frNotes" rows="2" placeholder="Notes on outreach angle, eligibility, co-production partners..."></textarea>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="fundraisingModal">Cancel</button>
      <button type="button" class="btn primary" id="saveFrBtn" onclick="window.submitFundraisingForm()">Save Record</button>
    </div>
  </div>
</div>

<!-- K2. Import Fundraising & Partnerships Master CSV Modal -->
<div class="modal" id="importFundraisingModal" role="dialog" aria-modal="true" aria-labelledby="importFrModalTitle">
  <div class="mbg" data-close="importFundraisingModal"></div>
  <div class="mbox" style="max-width:580px">
    <button class="mclose" data-close="importFundraisingModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:rgba(43,138,90,0.15);color:#2B8A5A;font-weight:700">MASTER SPREADSHEET IMPORT</span>
    </div>
    <h3 id="importFrModalTitle">Import CSV / Excel Records</h3>
    <p class="msub">Upload your exported CSV from the JEOTA FUNDRAISING MASTER FILE to sync all grants, open calls, and partnership entities.</p>

    <!-- Destination Category Selector -->
    <div class="field">
      <label for="importFrTargetCategory">Target Directory / Module *</label>
      <select id="importFrTargetCategory">
        <option value="partnerships">Partnership Exploration (Foundations, CBOs, Corporate, Academia)</option>
        <option value="open_calls">Grants &amp; Open Calls</option>
      </select>
    </div>

    <!-- Dropzone / File Picker -->
    <div class="field">
      <label for="importFrFileInput">Choose CSV File (.csv) *</label>
      <div id="importFrDropzone" style="border:2px dashed var(--line-strong);border-radius:12px;padding:24px;text-align:center;background:var(--panel-2);cursor:pointer;transition:all 0.2s ease" onclick="document.getElementById('importFrFileInput').click()">
        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom:8px;color:var(--muted)"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        <div style="font-weight:600;color:var(--ink);font-size:13px" id="importFrFileNameDisplay">Click to browse or drag and drop CSV file</div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:4px">Supports CSV exported from Google Sheets or Microsoft Excel</div>
      </div>
      <input type="file" id="importFrFileInput" accept=".csv,text/csv" style="display:none" onchange="window.onImportFrFileSelected(event)">
    </div>

    <!-- Live Preview Box -->
    <div id="importFrPreviewBox" style="display:none;margin-top:12px;background:var(--surface);border:1px solid var(--line);border-radius:8px;padding:12px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
        <span style="font-size:12px;font-weight:600;color:var(--ink)" id="importFrPreviewSummary">0 records ready to import</span>
        <span class="badge" style="background:var(--green-soft);color:var(--green);font-size:10px">Validated</span>
      </div>
      <div id="importFrPreviewList" style="max-height:140px;overflow-y:auto;font-size:11.5px;color:var(--muted)"></div>
    </div>

    <!-- Sample Templates Helper -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding:10px 14px;background:var(--panel-2);border-radius:8px;font-size:11.5px">
      <span style="color:var(--muted)">Need a format template?</span>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn" onclick="window.downloadSampleCsv('partnerships')" style="padding:3px 8px;font-size:11px">Partnership CSV</button>
        <button type="button" class="btn" onclick="window.downloadSampleCsv('grants')" style="padding:3px 8px;font-size:11px">Grants CSV</button>
      </div>
    </div>

    <div class="mfoot">
      <button type="button" class="btn" data-close="importFundraisingModal">Cancel</button>
      <button type="button" class="btn primary" id="importFrSubmitBtn" onclick="window.submitFundraisingCsvImport()">Import Records</button>
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
  <div class="mbox workspace-modal" style="padding:28px 36px">
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

<!-- AI Executive Intelligence & Reporting Modal -->
<div class="modal-overlay" id="aiReportModal" style="display:none;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(6px)">
  <div class="modal" style="max-width:720px;width:95%;max-height:88vh;display:flex;flex-direction:column;border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,0.3)">
    <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line)">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        </div>
        <div>
          <h3 id="aiReportModalTitle" style="margin:0;font-size:16px;font-weight:600">J- ai Executive Intelligence Briefing</h3>
          <div id="aiReportMeta" style="font-size:11px;color:var(--muted);font-family:'IBM Plex Mono',monospace;margin-top:2px">
            Role-Scoped Dataset &middot; Powered by J- ai (Gemini)
          </div>
        </div>
      </div>
      <button type="button" class="btn icon sm" onclick="document.getElementById('aiReportModal').style.display='none'" style="border:none;background:transparent;cursor:pointer">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div id="aiReportContent" style="flex:1;overflow-y:auto;padding:24px 20px;background:var(--paper);border-radius:0 0 0 0">
      <!-- Populated dynamically via JS -->
    </div>

    <div class="modal-footer" style="padding:12px 20px;border-top:1px solid var(--line);background:var(--panel);display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;gap:8px">
        <button type="button" class="btn sm" onclick="window.openAiReportModal('executive_digest', true)">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Refresh Live Analysis
        </button>
      </div>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn" onclick="document.getElementById('aiReportModal').style.display='none'">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification Container -->
<div class="toasts" id="toasts"></div>



