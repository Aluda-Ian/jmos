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
      this.loadDocuments();
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
        if (res.ok && data.data) {
          this.documents = data.data;
          this.updateFolderCounts(data.counts);
          this.renderList();
        }
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
      const typeInput = document.getElementById('docUploadType');
      if (typeInput) typeInput.value = mode || 'file';

      const titleInput = document.getElementById('docInputTitle');
      if (titleInput) titleInput.value = '';

      const notesInput = document.getElementById('docInputNotes');
      if (notesInput) notesInput.value = '';

      const fileInput = document.getElementById('docFileInput');
      if (fileInput) fileInput.value = '';

      const urlInput = document.getElementById('docUrlInput');
      if (urlInput) urlInput.value = '';

      const folderSelect = document.getElementById('docFolderSelect');
      if (folderSelect && this.currentFolder !== 'all') {
        folderSelect.value = this.currentFolder;
      }

      const fileWrap = document.getElementById('docFileInputWrap');
      const urlWrap = document.getElementById('docUrlInputWrap');
      const modalTitle = document.getElementById('docModalTitle');

      if (mode === 'link') {
        if (fileWrap) fileWrap.style.display = 'none';
        if (urlWrap) urlWrap.style.display = 'block';
        if (modalTitle) modalTitle.textContent = 'Add Cloud Document Link';
      } else {
        if (fileWrap) fileWrap.style.display = 'block';
        if (urlWrap) urlWrap.style.display = 'none';
        if (modalTitle) modalTitle.textContent = 'Upload Document';
      }

      window.openModal('documentModal');
    },

    submitDocumentForm: async function () {
      const title = document.getElementById('docInputTitle')?.value.trim();
      const folder = document.getElementById('docFolderSelect')?.value || 'general';
      const type = document.getElementById('docUploadType')?.value || 'file';
      const notes = document.getElementById('docInputNotes')?.value.trim() || '';

      if (!title) {
        if (window.showToast) window.showToast('Title Required', 'Please enter a document title', true);
        return;
      }

      const formData = new FormData();
      formData.append('title', title);
      formData.append('folder', folder);
      formData.append('notes', notes);

      if (type === 'link') {
        const url = document.getElementById('docUrlInput')?.value.trim();
        if (!url) {
          if (window.showToast) window.showToast('Link Required', 'Please enter a valid cloud link URL', true);
          return;
        }
        formData.append('external_url', url);
      } else {
        const fileInput = document.getElementById('docFileInput');
        if (fileInput && fileInput.files && fileInput.files[0]) {
          formData.append('file', fileInput.files[0]);
        }
      }

      const saveBtn = document.getElementById('saveDocBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Uploading...';
      }

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
          window.showToast('Document Saved', 'File uploaded successfully');
        }

        window.closeModal('documentModal');
        this.loadDocuments();
      } catch (err) {
        if (window.showToast) window.showToast('Upload Error', err.message, true);
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save Document';
        }
      }
    },

    deleteDocument: async function (id) {
      const doc = this.documents.find(d => String(d.id) === String(id));
      const docTitle = doc ? doc.title : 'this document';

      const confirmed = await window.showConfirmDialog({
        title: 'Delete Document?',
        subtitle: 'Document Archive',
        type: 'danger',
        confirmText: 'Delete Document',
        message: `Permanently remove <b>${escHtml(docTitle)}</b> from file vault?`,
        bullets: [
          'The document file and all cloud links will be permanently deleted.',
          'This action cannot be undone.'
        ]
      });
      if (!confirmed) return;

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

        if (!res.ok) {
          const data = await res.json();
          throw new Error(data.message || 'Failed to delete document');
        }

        if (window.showToast) {
          window.showToast('Document Deleted', 'File removed from vault');
        }
        this.loadDocuments();
      } catch (err) {
        if (window.showToast) window.showToast('Delete Error', err.message, true);
      }
    }
  };

  // Global window aliases
  window.selectDocFolder = function (folder) {
    window.JMOS_DOCUMENTS.selectFolder(folder);
  };

  window.openUploadDocumentModal = function (mode) {
    window.JMOS_DOCUMENTS.openUploadModal(mode);
  };

  window.submitDocumentForm = function () {
    window.JMOS_DOCUMENTS.submitDocumentForm();
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
