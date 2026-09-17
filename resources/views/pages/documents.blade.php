<!-- ==========================================================================
     JMOS — View: Documents Repository (Zoho CRM Workflow)
     ========================================================================== -->
<section class="view" data-view="documents" hidden>
  <div class="page-head" style="margin-bottom:16px">
    <div>
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
        <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:11px;font-weight:700">CENTRAL REPOSITORY</span>
        <span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:11px">CONTRACTS, PROPOSALS &amp; ASSETS</span>
      </div>
      <h1 class="pt">Documents &amp; Files</h1>
      <p>Contracts, commercial proposals, brand guidelines, production briefs, and grant application files.</p>
    </div>
    <div class="head-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" onclick="window.refreshDocuments()" title="Refresh documents">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Refresh
      </button>
      <button type="button" class="btn" onclick="window.openUploadDocumentModal('link')" title="Add Google Drive, Dropbox or Notion Link">
        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>Add Cloud Link
      </button>
      <button type="button" class="btn primary" onclick="window.openUploadDocumentModal('file')" title="Upload file attachment">
        <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>Upload Document
      </button>
    </div>
  </div>

  <div class="zoho-crm-container" style="display:grid;grid-template-columns:260px 1fr;gap:18px;align-items:start">
    <!-- Zoho-style Left Folder Sidebar -->
    <aside class="zoho-filter-sidebar" style="background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:16px;box-shadow:var(--shadow-sm)">
      <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">Document Folders</div>

      <div class="doc-folder-list" style="display:flex;flex-direction:column;gap:3px;text-align:left">
        <button type="button" class="doc-folder-btn active" data-doc-folder="all" onclick="window.selectDocFolder('all')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          <span style="flex:1;text-align:left">All Documents</span>
          <span class="crm-tab-badge" id="docBadgeAll">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="contracts" onclick="window.selectDocFolder('contracts')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
          <span style="flex:1;text-align:left">Contracts &amp; Legal</span>
          <span class="crm-tab-badge" id="docBadgeContracts">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="proposals" onclick="window.selectDocFolder('proposals')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
          <span style="flex:1;text-align:left">Proposals &amp; Quotes</span>
          <span class="crm-tab-badge" id="docBadgeProposals">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="brand_guides" onclick="window.selectDocFolder('brand_guides')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
          <span style="flex:1;text-align:left">Brand Guides &amp; Logos</span>
          <span class="crm-tab-badge" id="docBadgeBrandGuides">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="briefs" onclick="window.selectDocFolder('briefs')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
          <span style="flex:1;text-align:left">Production Briefs</span>
          <span class="crm-tab-badge" id="docBadgeBriefs">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="grants" onclick="window.selectDocFolder('grants')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span style="flex:1;text-align:left">Grant Applications</span>
          <span class="crm-tab-badge" id="docBadgeGrants">0</span>
        </button>
        <button type="button" class="doc-folder-btn" data-doc-folder="general" onclick="window.selectDocFolder('general')">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span style="flex:1;text-align:left">General &amp; Assets</span>
          <span class="crm-tab-badge" id="docBadgeGeneral">0</span>
        </button>
      </div>

      <div style="margin-top:20px;padding-top:14px;border-top:1px solid var(--line);font-size:11.5px;color:var(--muted)">
        <p style="margin:0 0 6px"><b>Tip:</b> Attach documents directly to Clients, Live Projects, or Leads for fast access.</p>
      </div>
    </aside>

    <!-- Main Documents Table / View -->
    <div class="zoho-main-content">
      <div class="tablecard" style="box-shadow:var(--shadow-sm);border:1px solid var(--line);border-radius:12px;overflow:hidden">
        
        <!-- Table Toolbar & Breadcrumb -->
        <div style="padding:12px 16px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
          <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted)">
            <span>JMOS</span>
            <span>&rsaquo;</span>
            <span style="font-weight:700;color:var(--ink)" id="docBreadcrumbActive">All Documents</span>
            <span class="badge" id="docCountPill" style="font-size:11px;background:var(--panel-2);color:var(--muted);margin-left:6px">0 Files</span>
          </div>

          <div style="display:flex;align-items:center;gap:10px">
            <div style="position:relative">
              <input type="text" id="docSearchInput" placeholder="Search files &amp; contracts..." style="font-size:12px;padding:5px 8px 5px 26px;border-radius:6px;border:1px solid var(--line);background:var(--panel-2);color:var(--ink);width:210px" oninput="window.onDocSearchChange(this.value)">
              <svg viewBox="0 0 24 24" width="13" height="13" style="position:absolute;left:8px;top:8px;color:var(--muted)" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            </div>
            <button type="button" class="btn primary" onclick="window.openUploadDocumentModal('file')" style="font-size:11.5px;padding:4px 12px">
              <svg viewBox="0 0 24 24" width="13" height="13"><path d="M12 5v14M5 12h14"/></svg>Upload
            </button>
          </div>
        </div>

        <!-- Table Container -->
        <div class="tablewrap" style="max-height:650px;overflow-y:auto">
          <table>
            <thead>
              <tr>
                <th>Document Title</th>
                <th>Category</th>
                <th>Associated Record</th>
                <th>File Format</th>
                <th>Size</th>
                <th>Uploaded By</th>
                <th>Date Added</th>
                <th style="text-align:right">Action</th>
              </tr>
            </thead>
            <tbody id="documentsTableBody">
              <tr>
                <td colspan="8" style="padding:40px;text-align:center;color:var(--muted)">Loading documents repository…</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</section>
