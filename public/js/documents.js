/* ==========================================================================
   JMOS — Documents & Asset Management
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_DOCUMENTS = {
    documents: [],
    currentFolder: 'all',
    searchQuery: '',

    folderLabels: {
      all: 'All Documents',
      contracts: 'Contracts & Legal',
      proposals: 'Proposals & Quotes',
      brand_guides: 'Brand Guides & Logos',
      briefs: 'Production Briefs',
      grants: 'Grant Applications',
      general: 'General & Assets'
    },

    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Search input
      const searchInput = document.getElementById('docSearchInput');
      if (searchInput) {
        searchInput.addEventListener('input', (e) => {
          this.searchQuery = e.target.value.toLowerCase().trim();
          this.renderList();
        });
      }
    },

    selectFolder: function (folder) {
      this.currentFolder = folder || 'all';

      // Update active folder button state
      document.querySelectorAll('.doc-folder-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-doc-folder') === this.currentFolder);
      });

      // Update breadcrumb
      const bc = document.getElementById('docBreadcrumbActive');
      if (bc) bc.textContent = this.folderLabels[this.currentFolder] || 'All Documents';

      this.renderList();
    },

    loadDocuments: async function () {
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch('/api/documents', {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to fetch documents');

        this.documents = data.data || [];
        this.updateFolderCounts(data.counts);
        this.renderList();
      } catch (err) {
        console.error('Error loading documents:', err);
      }
    },

    updateFolderCounts: function (counts) {
      if (!counts) {
        counts = {
          all: this.documents.length,
          contracts: 0,
          proposals: 0,
          brand_guides: 0,
          briefs: 0,
          grants: 0,
          general: 0
        };

        this.documents.forEach(doc => {
          if (counts[doc.folder] !== undefined) {
            counts[doc.folder]++;
          }
        });
      }

      const map = {
        all: 'docBadgeAll',
        contracts: 'docBadgeContracts',
        proposals: 'docBadgeProposals',
        brand_guides: 'docBadgeBrandGuides',
        briefs: 'docBadgeBriefs',
        grants: 'docBadgeGrants',
        general: 'docBadgeGeneral'
      };

      Object.keys(map).forEach(k => {
        const el = document.getElementById(map[k]);
        if (el) el.textContent = counts[k] || 0;
      });
    },

    renderList: function () {
      const tbody = document.getElementById('documentsTableBody');
      const countPill = document.getElementById('docCountPill');
      if (!tbody) return;

      let filtered = this.documents;

      if (this.currentFolder !== 'all') {
        filtered = filtered.filter(d => d.folder === this.currentFolder);
      }

      if (this.searchQuery) {
        filtered = filtered.filter(d => 
          (d.title && d.title.toLowerCase().includes(this.searchQuery)) ||
          (d.notes && d.notes.toLowerCase().includes(this.searchQuery)) ||
          (d.file_name && d.file_name.toLowerCase().includes(this.searchQuery))
        );
      }

      if (countPill) countPill.textContent = `${filtered.length} Files`;

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align:center;padding:48px 20px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" fill="none" stroke-width="1.5" style="margin-bottom:10px;opacity:0.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              <div style="font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:4px">No documents in this folder</div>
              <div style="font-size:12px;margin-bottom:14px">Upload contracts, proposals, or paste cloud links (Canva, Drive, Dropbox).</div>
              <button type="button" class="btn primary" onclick="window.openUploadDocumentModal('file')" style="font-size:11.5px;padding:5px 14px">+ Upload Document</button>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(doc => {
        const isExternal = !!doc.external_url;
        const typeIcon = isExternal 
          ? `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>`
          : `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`;

        const folderBadge = `<span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">${(doc.folder || 'general').replace('_', ' ').toUpperCase()}</span>`;
        const sizeFormatted = doc.file_size ? `${(doc.file_size / 1024).toFixed(1)} KB` : 'Cloud Link';
        const updatedDate = doc.updated_at ? doc.updated_at.split('T')[0] : '—';
        const uploader = doc.uploaded_by || 'Team Member';
        const recordName = doc.client ? doc.client.client_name : (doc.lead ? doc.lead.lead_name : (doc.project ? doc.project.project_name : 'General'));

        const actionBtn = isExternal
          ? `<a href="${doc.external_url}" target="_blank" class="btn small" style="margin-right:6px;font-size:11.5px;padding:3px 10px">Open Link ↗</a>`
          : `<a href="/api/documents/${doc.id}/download" class="btn small" style="margin-right:6px;font-size:11.5px;padding:3px 10px">Download ↓</a>`;

        return `
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="color:var(--red);flex-shrink:0">${typeIcon}</div>
                <div>
                  <div style="font-weight:600;color:var(--ink);font-size:13px">${doc.title}</div>
                  <div style="font-size:11px;color:var(--muted)">${doc.notes || (isExternal ? doc.external_url : doc.file_name)}</div>
                </div>
              </div>
            </td>
            <td>${folderBadge}</td>
            <td style="font-size:12px;color:var(--ink)">${recordName}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:11.5px;text-transform:uppercase">${doc.file_type || 'PDF'}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:12px">${sizeFormatted}</td>
            <td style="font-size:12px">${uploader}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:12px">${updatedDate}</td>
            <td style="text-align:right">
              ${actionBtn}
              <button type="button" class="btn small danger" onclick="window.JMOS_DOCUMENTS.deleteDocument(${doc.id})" title="Delete" style="padding:3px 8px;font-size:12px">&times;</button>
            </td>
          </tr>
        `;
      }).join('');
    },

    openUploadModal: function (mode) {
      const modal = document.getElementById('documentModal');
      if (!modal) return;
      const form = document.getElementById('documentForm');
      if (form) form.reset();

      // Set default folder based on active view
      const folderSelect = document.getElementById('docFolderSelect');
      if (folderSelect && this.currentFolder !== 'all') {
        folderSelect.value = this.currentFolder;
      }

      modal.classList.add('active');
    },

    saveDocument: async function (e) {
      if (e) e.preventDefault();
      const form = document.getElementById('documentForm');
      const formData = new FormData(form);

      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch('/api/documents', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          },
          body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to upload document');

        if (window.showToast) {
          window.showToast('Document saved successfully', 'success');
        } else {
          alert('Document saved successfully');
        }

        document.getElementById('documentModal').classList.remove('active');
        this.loadDocuments();
      } catch (err) {
        alert('Error uploading document: ' + err.message);
      }
    },

    deleteDocument: async function (id) {
      if (!confirm('Are you sure you want to delete this document?')) return;
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/documents/${id}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to delete document');

        if (window.showToast) {
          window.showToast('Document deleted', 'success');
        }
        this.loadDocuments();
      } catch (err) {
        alert('Error deleting document: ' + err.message);
      }
    }
  };

  // Global window aliases for inline onclick bindings
  window.selectDocFolder = function (folder) {
    window.JMOS_DOCUMENTS.selectFolder(folder);
  };

  window.openUploadDocumentModal = function (mode) {
    window.JMOS_DOCUMENTS.openUploadModal(mode);
  };

  window.onDocSearchChange = function (val) {
    window.JMOS_DOCUMENTS.searchQuery = (val || '').toLowerCase().trim();
    window.JMOS_DOCUMENTS.renderList();
  };

  window.refreshDocuments = function () {
    window.JMOS_DOCUMENTS.loadDocuments();
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_DOCUMENTS.init();
  });
})();
