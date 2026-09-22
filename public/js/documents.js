/* ==========================================================================
   JMOS — Documents & Asset Management (Cloud Links & Drive Storage)
   ========================================================================== */

(function () {
  'use strict';

  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

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
      general: 'General Assets & Footage'
    },

    init: function () {
      this.loadDocuments();
    },

    detectProvider: function (url) {
      if (!url) return { name: 'Cloud Link', color: 'var(--ink)', bg: 'var(--panel-2)', icon: 'link' };

      const u = url.toLowerCase();
      if (u.includes('drive.google.com') || u.includes('docs.google.com')) {
        return { name: 'Google Drive', color: '#1a73e8', bg: 'rgba(26,115,232,0.12)', icon: 'drive' };
      }
      if (u.includes('dropbox.com') || u.includes('paper.dropbox.com')) {
        return { name: 'Dropbox', color: '#0061ff', bg: 'rgba(0,97,255,0.12)', icon: 'dropbox' };
      }
      if (u.includes('playbook.com')) {
        return { name: 'Playbook', color: '#7c3aed', bg: 'rgba(124,58,237,0.12)', icon: 'playbook' };
      }
      if (u.includes('frame.io')) {
        return { name: 'Frame.io', color: '#6366f1', bg: 'rgba(99,102,241,0.12)', icon: 'frame' };
      }
      if (u.includes('notion.so') || u.includes('notion.site')) {
        return { name: 'Notion', color: 'var(--ink)', bg: 'rgba(0,0,0,0.08)', icon: 'notion' };
      }
      if (u.includes('figma.com')) {
        return { name: 'Figma', color: '#ea580c', bg: 'rgba(234,88,12,0.12)', icon: 'figma' };
      }
      if (u.includes('onedrive') || u.includes('sharepoint.com')) {
        return { name: 'OneDrive', color: '#0284c7', bg: 'rgba(2,132,199,0.12)', icon: 'onedrive' };
      }
      if (u.includes('canva.com')) {
        return { name: 'Canva', color: '#00c4cc', bg: 'rgba(0,196,204,0.12)', icon: 'canva' };
      }
      if (u.includes('youtube.com') || u.includes('youtu.be') || u.includes('vimeo.com')) {
        return { name: 'Video Review', color: '#e11d48', bg: 'rgba(225,29,72,0.12)', icon: 'video' };
      }

      return { name: 'Cloud Link', color: 'var(--red)', bg: 'var(--red-soft)', icon: 'link' };
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
          (d.external_url && d.external_url.toLowerCase().includes(this.searchQuery)) ||
          (d.file_name && d.file_name.toLowerCase().includes(this.searchQuery))
        );
      }

      if (countPill) countPill.textContent = `${filtered.length} Links`;

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center;padding:48px 20px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" fill="none" stroke-width="1.5" style="margin-bottom:10px;opacity:0.4"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              <div style="font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:4px">No cloud documents in this folder</div>
              <div style="font-size:12px;margin-bottom:14px">Link contracts, proposals, treatments, Google Drive folders, Dropbox, or Playbook boards.</div>
              <button type="button" class="btn primary" onclick="window.openUploadDocumentModal('link')" style="font-size:11.5px;padding:5px 14px">+ Add Document Link</button>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(doc => {
        const url = doc.external_url || (doc.file_path ? `/api/documents/${doc.id}/download` : '#');
        const isExternal = !!doc.external_url;
        const provider = this.detectProvider(url);
        const displayProvider = doc.file_type && doc.file_type !== 'pdf' && doc.file_type !== 'url' ? doc.file_type : provider.name;

        const folderBadge = `<span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">${(doc.folder || 'general').replace('_', ' ').toUpperCase()}</span>`;
        const providerBadge = `<span class="badge" style="background:${provider.bg};color:${provider.color};font-weight:600;font-size:11px">${escHtml(displayProvider)}</span>`;
        const updatedDate = doc.updated_at ? doc.updated_at.split('T')[0] : '—';
        const uploader = doc.uploaded_by || 'Team Member';
        const recordName = doc.client ? doc.client.client_name : (doc.lead ? doc.lead.lead_name : (doc.project ? doc.project.project_name : 'General'));

        const actionBtn = `<a href="${escHtml(url)}" target="_blank" rel="noopener noreferrer" class="btn small" style="margin-right:6px;font-size:11.5px;padding:4px 10px;display:inline-flex;align-items:center;gap:4px">Open Link ↗</a>`;

        return `
          <tr>
            <td>
              <div style="display:flex;align-items:flex-start;gap:10px">
                <div style="color:var(--red);flex-shrink:0;margin-top:2px">
                  <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </div>
                <div>
                  <a href="${escHtml(url)}" target="_blank" rel="noopener noreferrer" style="font-weight:600;color:var(--ink);font-size:13px;text-decoration:none" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='var(--ink)'">${escHtml(doc.title)}</a>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(doc.notes || url)}</div>
                </div>
              </div>
            </td>
            <td>${folderBadge}</td>
            <td>${providerBadge}</td>
            <td style="font-size:12px;color:var(--ink)">${escHtml(recordName)}</td>
            <td style="font-size:12px">${escHtml(uploader)}</td>
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
      if (typeInput) typeInput.value = 'link';

      const titleInput = document.getElementById('docInputTitle');
      if (titleInput) titleInput.value = '';

      const notesInput = document.getElementById('docInputNotes');
      if (notesInput) notesInput.value = '';

      const urlInput = document.getElementById('docUrlInput');
      if (urlInput) urlInput.value = '';

      const providerSelect = document.getElementById('docProviderSelect');
      if (providerSelect) providerSelect.value = 'auto';

      const folderSelect = document.getElementById('docFolderSelect');
      if (folderSelect && this.currentFolder !== 'all') {
        folderSelect.value = this.currentFolder;
      }

      const detected = document.getElementById('docDetectedProvider');
      if (detected) {
        detected.style.display = 'none';
        detected.textContent = '';
      }

      window.openModal('documentModal');

      setTimeout(() => {
        if (urlInput) urlInput.focus();
      }, 100);
    },

    onUrlInput: function (val) {
      const detected = document.getElementById('docDetectedProvider');
      const providerSelect = document.getElementById('docProviderSelect');
      if (!val || !val.trim()) {
        if (detected) detected.style.display = 'none';
        return;
      }

      const provider = this.detectProvider(val.trim());
      if (detected) {
        detected.style.display = 'block';
        detected.innerHTML = `Detected Provider: <b style="color:${provider.color}">${escHtml(provider.name)}</b> (0 MB server disk used)`;
      }

      if (providerSelect && providerSelect.value === 'auto') {
        // keep auto
      }
    },

    submitDocumentForm: async function () {
      const url = document.getElementById('docUrlInput')?.value.trim();
      const title = document.getElementById('docInputTitle')?.value.trim();
      const folder = document.getElementById('docFolderSelect')?.value || 'general';
      const providerChoice = document.getElementById('docProviderSelect')?.value || 'auto';
      const notes = document.getElementById('docInputNotes')?.value.trim() || '';

      if (!url) {
        if (window.showToast) window.showToast('Link Required', 'Please enter a valid Google Drive, Dropbox, Playbook or cloud URL', true);
        return;
      }

      if (!title) {
        if (window.showToast) window.showToast('Title Required', 'Please enter a document title', true);
        return;
      }

      // Automatically format URL with https:// if missing
      let finalUrl = url;
      if (!finalUrl.startsWith('http://') && !finalUrl.startsWith('https://')) {
        finalUrl = 'https://' + finalUrl;
      }

      const detected = this.detectProvider(finalUrl);
      const providerName = providerChoice === 'auto' ? detected.name : providerChoice;

      const formData = new FormData();
      formData.append('title', title);
      formData.append('folder', folder);
      formData.append('notes', notes);
      formData.append('external_url', finalUrl);
      formData.append('file_type', providerName);

      const saveBtn = document.getElementById('saveDocBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving Link...';
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
        if (!res.ok) throw new Error(data.message || 'Failed to save document link');

        if (window.showToast) {
          window.showToast('Document Link Saved', `Linked ${providerName} asset successfully`);
        }

        window.closeModal('documentModal');
        this.loadDocuments();
      } catch (err) {
        if (window.showToast) window.showToast('Save Error', err.message, true);
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>Save Document Link`;
        }
      }
    },

    deleteDocument: async function (id) {
      const doc = this.documents.find(d => String(d.id) === String(id));
      const docTitle = doc ? doc.title : 'this document';

      const confirmed = await window.showConfirmDialog({
        title: 'Delete Document Link?',
        subtitle: 'Document Archive',
        type: 'danger',
        confirmText: 'Delete Link',
        message: `Permanently remove link to <b>${escHtml(docTitle)}</b> from file vault?`,
        bullets: [
          'The document record and cloud bookmark will be removed.',
          'Your original files on Google Drive / Dropbox / Playbook will remain safe.'
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
          window.showToast('Document Deleted', 'Link removed from vault');
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

  window.onDocUrlInput = function (val) {
    window.JMOS_DOCUMENTS.onUrlInput(val);
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
