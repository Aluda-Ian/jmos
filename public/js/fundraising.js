/* ==========================================================================
   JMOS — Fundraising Master File (Grants & Open Calls + Partnership Exploration)
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_FUNDRAISING = {
    opportunities: [],
    currentCategory: 'open_calls',
    searchQuery: '',
    statusFilter: 'all',

    // Partnership Exploration Filters
    partSectionFilter: 'all',
    partNatureFilter: 'all',
    partStatusFilter: 'all',
    partSearchQuery: '',

    init: function () {
      this.bindEvents();
      this.loadOpportunities();
    },

    bindEvents: function () {
      // Grants Search input
      const searchInput = document.getElementById('frSearchInput');
      if (searchInput) {
        searchInput.addEventListener('input', (e) => {
          this.searchQuery = e.target.value.toLowerCase().trim();
          this.renderList();
        });
      }

      // Partnerships Search input
      const partSearchInput = document.getElementById('partSearchInput');
      if (partSearchInput) {
        partSearchInput.addEventListener('input', (e) => {
          this.partSearchQuery = e.target.value.toLowerCase().trim();
          this.renderPartnerships();
        });
      }
    },

    loadOpportunities: async function () {
      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch('/api/fundraising', {
          headers: {
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });
        const data = await res.json();
        if (res.ok && data.data) {
          this.opportunities = data.data;
          this.updateMetrics(data.stats);
          this.renderList();
          this.renderPartnerships();
        }
      } catch (err) {
        console.error('Error fetching fundraising master data:', err);
      }
    },

    updateMetrics: function (stats) {
      let totalVal = 0;
      let openCallsCount = 0;
      let submittedCount = 0;
      let partCount = 0;
      let cboCount = 0;
      let corporateCount = 0;
      let assocAcademiaCount = 0;

      this.opportunities.forEach(o => {
        totalVal += (parseFloat(o.amount_kes) || 0);
        const cat = o.category || 'open_calls';
        const st = (o.status || '').toLowerCase();
        const ent = o.partnership_entity_type || '';

        if (cat === 'open_calls' || cat === 'fellowships') {
          if (st === 'identified' || st === 'in progress' || st === 'open') openCallsCount++;
          if (st === 'submitted' || st === 'applied') submittedCount++;
        }

        if (cat === 'partnerships') {
          partCount++;
          if (ent.includes('Grantmakers') || ent.includes('CBO')) cboCount++;
          if (ent.includes('Corporate')) corporateCount++;
          if (ent.includes('Associations') || ent.includes('Cooperatives') || ent.includes('Academia') || ent.includes('Educational')) assocAcademiaCount++;
        }
      });

      // Grants Page KPIs
      const totalPipeEl = document.getElementById('frKpiTotalPipeline');
      if (totalPipeEl) totalPipeEl.textContent = 'KES ' + Number(totalVal || 0).toLocaleString();

      const openEl = document.getElementById('frKpiOpenCallsCount');
      if (openEl) openEl.textContent = openCallsCount;

      const subEl = document.getElementById('frKpiSubmittedCount');
      if (subEl) subEl.textContent = submittedCount;

      const partEl = document.getElementById('frKpiPartnershipsCount');
      if (partEl) partEl.textContent = partCount;

      // Tab badges on Grants Page
      const tabBadgeOpen = document.getElementById('frTabBadgeOpen');
      if (tabBadgeOpen) tabBadgeOpen.textContent = this.opportunities.filter(o => o.category === 'open_calls').length;

      const tabBadgePart = document.getElementById('frTabBadgePartnerships');
      if (tabBadgePart) tabBadgePart.textContent = partCount;

      const tabBadgeAll = document.getElementById('frTabBadgeAll');
      if (tabBadgeAll) tabBadgeAll.textContent = this.opportunities.length;

      // Partnership Page KPIs
      const pTotalEl = document.getElementById('partKpiTotalCount');
      if (pTotalEl) pTotalEl.textContent = partCount;

      const pCboEl = document.getElementById('partKpiCboCount');
      if (pCboEl) pCboEl.textContent = cboCount;

      const pCorpEl = document.getElementById('partKpiCorporateCount');
      if (pCorpEl) pCorpEl.textContent = corporateCount;

      const pAssocEl = document.getElementById('partKpiAssocAcademiaCount');
      if (pAssocEl) pAssocEl.textContent = assocAcademiaCount;

      // Tab badges on Partnership Page
      const pBadgeAll = document.getElementById('partTabBadgeAll');
      if (pBadgeAll) pBadgeAll.textContent = partCount;

      const pBadgeCbo = document.getElementById('partTabBadgeCbo');
      if (pBadgeCbo) pBadgeCbo.textContent = cboCount;

      const pBadgeAssoc = document.getElementById('partTabBadgeAssoc');
      if (pBadgeAssoc) pBadgeAssoc.textContent = this.opportunities.filter(o => o.category === 'partnerships' && (o.partnership_entity_type || '').includes('Associations')).length;

      const pBadgeCorp = document.getElementById('partTabBadgeCorporate');
      if (pBadgeCorp) pBadgeCorp.textContent = corporateCount;

      const pBadgeAcad = document.getElementById('partTabBadgeAcademia');
      if (pBadgeAcad) pBadgeAcad.textContent = this.opportunities.filter(o => o.category === 'partnerships' && (o.partnership_entity_type || '').includes('Academia')).length;
    },

    // -------------------------------------------------------------
    // RENDER: GRANTS & OPEN CALLS VIEW
    // -------------------------------------------------------------
    renderList: function () {
      const tbody = document.getElementById('fundraisingTableBody');
      if (!tbody) return;

      let filtered = this.opportunities;

      if (this.currentCategory !== 'all') {
        filtered = filtered.filter(o => (o.category || 'open_calls') === this.currentCategory);
      }

      if (this.statusFilter !== 'all') {
        filtered = filtered.filter(o => (o.status || '').toLowerCase() === this.statusFilter.toLowerCase());
      }

      if (this.searchQuery) {
        filtered = filtered.filter(o =>
          (o.organization && o.organization.toLowerCase().includes(this.searchQuery)) ||
          (o.program_title && o.program_title.toLowerCase().includes(this.searchQuery)) ||
          (o.partner_organization && o.partner_organization.toLowerCase().includes(this.searchQuery)) ||
          (o.notes && o.notes.toLowerCase().includes(this.searchQuery))
        );
      }

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center;padding:48px 20px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" fill="none" stroke-width="1.5" style="margin-bottom:10px;opacity:0.4"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
              <div style="font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:4px">No grant calls match this filter</div>
              <div style="font-size:12px;margin-bottom:14px">Log open grant calls, fellowships, or impact consortium opportunities.</div>
              <button type="button" class="btn primary" onclick="window.openAddFundraisingModal()" style="font-size:11.5px;padding:5px 14px">+ Add Grant / Call</button>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(item => {
        const amt = item.amount_kes && Number(item.amount_kes) > 0 ? `KES ${Number(item.amount_kes).toLocaleString()}` : (item.amount_display || 'Undisclosed');
        const st = (item.status || 'Identified').toLowerCase();
        let pillClass = 'tint-blue';
        if (st.includes('won') || st.includes('awarded')) pillClass = 'tint-green';
        if (st.includes('progress') || st.includes('submitted')) pillClass = 'tint-amber';
        if (st.includes('missed') || st.includes('closed') || st.includes('rejected')) pillClass = 'tint-red';

        const linkBtn = item.application_link 
          ? `<a href="${item.application_link.startsWith('http') ? item.application_link : 'https://' + item.application_link}" target="_blank" rel="noopener noreferrer" class="btn" style="margin-right:6px;font-size:11px;padding:3px 8px" title="Open Application Portal">Portal ↗</a>`
          : '';

        return `
          <tr>
            <td>
              <div style="font-weight:700;color:var(--ink);font-size:13px">${escHtml(item.organization)}</div>
              <div style="font-size:11px;color:var(--muted)">${item.funding_type ? escHtml(item.funding_type) : 'Grant Opportunity'}</div>
            </td>
            <td>
              <div style="font-weight:600;color:var(--ink);font-size:12.5px">${escHtml(item.program_title || 'General Call')}</div>
              <div style="font-size:11px;color:var(--muted)">${item.partner_organization ? 'Partner: ' + escHtml(item.partner_organization) : ''}</div>
            </td>
            <td class="mono" style="font-weight:700;color:var(--ink);font-size:12px">${amt}</td>
            <td style="font-size:12px;color:var(--muted)">${escHtml(item.deadline || 'Rolling')}</td>
            <td><span class="pill ${pillClass}">${escHtml(item.status || 'Identified')}</span></td>
            <td style="font-size:11.5px;color:var(--muted);max-width:240px">${escHtml(item.notes || '—')}</td>
            <td style="text-align:right;white-space:nowrap">
              ${linkBtn}
              <button type="button" class="btn" onclick="window.editFundraisingOpportunity(${item.id})" style="margin-right:4px;font-size:11px;padding:3px 8px">Edit</button>
              <button type="button" class="btn" onclick="window.deleteFundraisingOpportunity(${item.id})" style="padding:3px 8px;font-size:11px;color:var(--red)" title="Delete">&times;</button>
            </td>
          </tr>
        `;
      }).join('');
    },

    // -------------------------------------------------------------
    // RENDER: PARTNERSHIP EXPLORATION VIEW (MATCHING MASTER FILE)
    // -------------------------------------------------------------
    renderPartnerships: function () {
      const tbody = document.getElementById('partnershipsTableBody');
      if (!tbody) return;

      // Filter to only partnership items
      let list = this.opportunities.filter(o => o.category === 'partnerships');

      if (this.partSectionFilter !== 'all') {
        list = list.filter(o => (o.partnership_entity_type || '').toLowerCase().includes(this.partSectionFilter.toLowerCase()));
      }

      if (this.partNatureFilter !== 'all') {
        list = list.filter(o => (o.funding_type || '').toLowerCase() === this.partNatureFilter.toLowerCase());
      }

      if (this.partStatusFilter !== 'all') {
        list = list.filter(o => (o.status || '').toLowerCase() === this.partStatusFilter.toLowerCase());
      }

      if (this.partSearchQuery) {
        list = list.filter(o =>
          (o.organization && o.organization.toLowerCase().includes(this.partSearchQuery)) ||
          (o.program_title && o.program_title.toLowerCase().includes(this.partSearchQuery)) ||
          (o.funding_type && o.funding_type.toLowerCase().includes(this.partSearchQuery)) ||
          (o.partnership_entity_type && o.partnership_entity_type.toLowerCase().includes(this.partSearchQuery)) ||
          (o.application_link && o.application_link.toLowerCase().includes(this.partSearchQuery)) ||
          (o.notes && o.notes.toLowerCase().includes(this.partSearchQuery))
        );
      }

      if (list.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align:center;padding:48px 20px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" fill="none" stroke-width="1.5" style="margin-bottom:10px;opacity:0.4"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              <div style="font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:4px">No partnership entities found</div>
              <div style="font-size:12px;margin-bottom:14px">Log foundations, CBOs, cooperatives, corporate institutions, or universities.</div>
              <button type="button" class="btn primary" onclick="window.openAddPartnershipModal()" style="font-size:11.5px;padding:5px 14px">+ Add Partner / Entity</button>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = list.map((item, idx) => {
        const st = (item.status || 'Pending').toLowerCase();
        let pillClass = 'tint-amber';
        if (st.includes('won') || st.includes('awarded') || st.includes('submitted')) pillClass = 'tint-green';
        if (st.includes('closed') || st.includes('rejected')) pillClass = 'tint-red';
        if (st === 'pending') pillClass = 'tint-blue';

        const naturePill = (item.funding_type === 'Grants Application')
          ? `<span class="badge" style="background:rgba(43,138,90,0.12);color:#2B8A5A;font-weight:600">Grants Application</span>`
          : `<span class="badge" style="background:rgba(124,58,237,0.12);color:#7C3AED;font-weight:600">Partnership Invitation</span>`;

        const sectionPill = `<span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">${escHtml(item.partnership_entity_type || 'General Partner')}</span>`;

        let webLink = '—';
        if (item.application_link) {
          const rawUrl = item.application_link;
          const hrefUrl = rawUrl.startsWith('http') ? rawUrl : 'https://' + rawUrl;
          const displayUrl = rawUrl.replace(/^https?:\/\//, '').replace(/\/$/, '');
          const shortUrl = displayUrl.length > 35 ? displayUrl.substring(0, 35) + '…' : displayUrl;
          webLink = `<a href="${hrefUrl}" target="_blank" rel="noopener noreferrer" style="color:var(--red);text-decoration:none;display:inline-flex;align-items:center;gap:4px;font-size:11.5px;word-break:break-all" title="${hrefUrl}">
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            ${escHtml(shortUrl)}
          </a>`;
        }

        return `
          <tr>
            <td style="text-align:center;color:var(--muted);font-weight:600;font-size:11px">${idx + 1}</td>
            <td>
              <div style="font-weight:700;color:var(--ink);font-size:13px">${escHtml(item.organization)}</div>
              ${item.program_title ? `<div style="font-size:11px;color:var(--muted)">${escHtml(item.program_title)}</div>` : ''}
            </td>
            <td>${sectionPill}</td>
            <td>${naturePill}</td>
            <td>${webLink}</td>
            <td><span class="pill ${pillClass}">${escHtml(item.status || 'Pending')}</span></td>
            <td style="font-size:11.5px;color:var(--ink);max-width:280px;line-height:1.4">${escHtml(item.notes || '—')}</td>
            <td style="text-align:right;white-space:nowrap">
              <button type="button" class="btn" onclick="window.editFundraisingOpportunity(${item.id})" style="margin-right:4px;font-size:11px;padding:3px 8px">Edit</button>
              <button type="button" class="btn" onclick="window.deleteFundraisingOpportunity(${item.id})" style="padding:3px 8px;font-size:11px;color:var(--red)" title="Delete">&times;</button>
            </td>
          </tr>
        `;
      }).join('');
    },

    // -------------------------------------------------------------
    // MODAL HANDLERS (CREATE / EDIT)
    // -------------------------------------------------------------
    openCreateModal: function (defaultCategory = 'open_calls', defaultSection = 'Grantmakers / CBOs') {
      const formId = document.getElementById('frFormId');
      if (formId) formId.value = '';

      const title = document.getElementById('frModalTitle');
      const badge = document.getElementById('frModalBadge');
      const sub = document.getElementById('frModalSub');
      const catSelect = document.getElementById('frCategory');
      const natureSelect = document.getElementById('frNature');
      const entityWrap = document.getElementById('frEntityTypeWrap');
      const entitySelect = document.getElementById('frEntityType');

      if (catSelect) catSelect.value = defaultCategory;
      if (entitySelect) entitySelect.value = defaultSection;

      if (defaultCategory === 'partnerships') {
        if (title) title.textContent = 'Add Partnership Entity';
        if (badge) { badge.textContent = 'PARTNERSHIP EXPLORATION'; badge.style.color = '#7C3AED'; badge.style.background = 'rgba(124,58,237,0.15)'; }
        if (sub) sub.textContent = 'Log institutional partner, CBO, cooperative, corporate fund, or university entity.';
        if (natureSelect) natureSelect.value = 'Partnership Invitation';
        if (entityWrap) entityWrap.style.display = 'block';
      } else {
        if (title) title.textContent = 'Add Grant / Call Opportunity';
        if (badge) { badge.textContent = 'GRANTS & OPEN CALLS'; badge.style.color = '#2B8A5A'; badge.style.background = 'rgba(43,138,90,0.15)'; }
        if (sub) sub.textContent = 'Log open funding calls, fellowships, and philanthropic grants.';
        if (natureSelect) natureSelect.value = 'Grants Application';
      }

      document.getElementById('frOrg').value = '';
      document.getElementById('frProgram').value = '';
      document.getElementById('frLink').value = '';
      document.getElementById('frAmount').value = '';
      document.getElementById('frDeadline').value = '';
      document.getElementById('frStatus').value = defaultCategory === 'partnerships' ? 'Pending' : 'Identified';
      document.getElementById('frNotes').value = '';

      window.openModal('fundraisingModal');
    },

    editOpportunity: function (id) {
      const item = this.opportunities.find(o => o.id === id);
      if (!item) return;

      document.getElementById('frFormId').value = item.id;
      const isPart = item.category === 'partnerships';

      const title = document.getElementById('frModalTitle');
      const badge = document.getElementById('frModalBadge');
      const sub = document.getElementById('frModalSub');
      if (title) title.textContent = isPart ? 'Edit Partnership Entity' : 'Edit Grant / Call Opportunity';
      if (badge) {
        badge.textContent = isPart ? 'PARTNERSHIP EXPLORATION' : 'GRANTS & OPEN CALLS';
        badge.style.color = isPart ? '#7C3AED' : '#2B8A5A';
        badge.style.background = isPart ? 'rgba(124,58,237,0.15)' : 'rgba(43,138,90,0.15)';
      }
      if (sub) sub.textContent = `Update record for ${item.organization}.`;

      document.getElementById('frCategory').value = item.category || (isPart ? 'partnerships' : 'open_calls');
      document.getElementById('frNature').value = item.funding_type || (isPart ? 'Partnership Invitation' : 'Grants Application');
      document.getElementById('frOrg').value = item.organization || '';
      if (document.getElementById('frEntityType')) {
        document.getElementById('frEntityType').value = item.partnership_entity_type || 'Grantmakers / CBOs';
      }
      document.getElementById('frProgram').value = item.program_title || '';
      document.getElementById('frLink').value = item.application_link || '';
      document.getElementById('frAmount').value = item.amount_kes || '';
      document.getElementById('frDeadline').value = item.deadline || '';
      document.getElementById('frStatus').value = item.status || 'Pending';
      document.getElementById('frNotes').value = item.notes || '';

      window.openModal('fundraisingModal');
    },

    submitFundraisingForm: async function () {
      const id = document.getElementById('frFormId')?.value;
      const org = document.getElementById('frOrg')?.value.trim();
      const category = document.getElementById('frCategory')?.value || 'open_calls';

      if (!org) {
        if (window.showToast) window.showToast('Validation Error', 'Please enter the Organization / Entity Name', true);
        return;
      }

      const payload = {
        organization: org,
        program_title: document.getElementById('frProgram')?.value.trim() || '',
        application_link: document.getElementById('frLink')?.value.trim() || '',
        amount_kes: parseFloat(document.getElementById('frAmount')?.value) || 0,
        funding_type: document.getElementById('frNature')?.value || (category === 'partnerships' ? 'Partnership Invitation' : 'Grants Application'),
        deadline: document.getElementById('frDeadline')?.value.trim() || null,
        status: document.getElementById('frStatus')?.value || (category === 'partnerships' ? 'Pending' : 'Identified'),
        category: category,
        partnership_entity_type: document.getElementById('frEntityType')?.value || 'Grantmakers / CBOs',
        notes: document.getElementById('frNotes')?.value.trim() || ''
      };

      const saveBtn = document.getElementById('saveFrBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
      }

      try {
        const token = localStorage.getItem('jmos_api_token');
        const url = id ? `/api/fundraising/${id}` : '/api/fundraising';
        const method = id ? 'PUT' : 'POST';

        const res = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to save record');

        if (window.showToast) {
          window.showToast('Record Saved', category === 'partnerships' ? 'Partnership entity saved successfully' : 'Grant opportunity saved successfully');
        }

        window.closeModal('fundraisingModal');
        this.loadOpportunities();
      } catch (err) {
        if (window.showToast) window.showToast('Save Error', err.message, true);
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save Record';
        }
      }
    },

    deleteOpportunity: async function (id) {
      const opp = this.opportunities.find(o => String(o.id) === String(id));
      const orgName = opp ? opp.organization : 'this record';
      const isPart = opp && opp.category === 'partnerships';
      
      const confirmed = await window.showConfirmDialog({
        title: isPart ? 'Delete Partnership Entity?' : 'Delete Grant Opportunity?',
        subtitle: 'Fundraising Master File',
        type: 'danger',
        confirmText: 'Delete Record',
        message: `Are you sure you want to delete <b>${escHtml(orgName)}</b> from the master directory?`,
        bullets: [
          'This record will be removed from the master file.',
          'This action cannot be undone.'
        ]
      });
      if (!confirmed) return;

      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch(`/api/fundraising/${id}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          }
        });

        if (!res.ok) {
          const data = await res.json();
          throw new Error(data.message || 'Failed to delete record');
        }

        if (window.showToast) {
          window.showToast('Record Deleted', 'Entity removed from master directory');
        }
        this.loadOpportunities();
      } catch (err) {
        if (window.showToast) window.showToast('Delete Error', err.message, true);
      }
    },

    exportGrantsCsv: function () {
      const grants = this.opportunities.filter(o => o.category !== 'partnerships');
      if (grants.length === 0) {
        if (window.showToast) window.showToast('Export Notice', 'No grants data available to export', true);
        return;
      }

      const headers = ['Organization', 'Program Title', 'Category', 'Status', 'Amount KES', 'Deadline', 'Application Link', 'Notes'];
      const rows = grants.map(o => [
        `"${(o.organization || '').replace(/"/g, '""')}"`,
        `"${(o.program_title || '').replace(/"/g, '""')}"`,
        `"${o.category}"`,
        `"${o.status}"`,
        o.amount_kes || '',
        `"${(o.deadline || '').replace(/"/g, '""')}"`,
        `"${(o.application_link || '').replace(/"/g, '""')}"`,
        `"${(o.notes || '').replace(/"/g, '""')}"`
      ]);

      this._downloadCsv(headers, rows, `JMOS_Grants_Master_${new Date().toISOString().split('T')[0]}.csv`);
    },

    exportPartnershipsCsv: function () {
      const parts = this.opportunities.filter(o => o.category === 'partnerships');
      if (parts.length === 0) {
        if (window.showToast) window.showToast('Export Notice', 'No partnership data available to export', true);
        return;
      }

      const headers = ['ENTITY NAME', 'SECTION / CLASSIFICATION', 'NATURE', 'WEBSITE', 'STATUS', 'COMMENTS'];
      const rows = parts.map(o => [
        `"${(o.organization || '').replace(/"/g, '""')}"`,
        `"${(o.partnership_entity_type || '').replace(/"/g, '""')}"`,
        `"${(o.funding_type || '').replace(/"/g, '""')}"`,
        `"${(o.application_link || '').replace(/"/g, '""')}"`,
        `"${(o.status || 'Pending').replace(/"/g, '""')}"`,
        `"${(o.notes || '').replace(/"/g, '""')}"`
      ]);

      this._downloadCsv(headers, rows, `JMOS_Partnership_Exploration_${new Date().toISOString().split('T')[0]}.csv`);
    },

    _downloadCsv: function (headers, rows, filename) {
      const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },

    // -------------------------------------------------------------
    // CSV / EXCEL MASTER FILE IMPORT
    // -------------------------------------------------------------
    selectedImportFile: null,

    openImportModal: function (defaultCategory = 'partnerships') {
      this.selectedImportFile = null;
      const catSelect = document.getElementById('importFrTargetCategory');
      if (catSelect) catSelect.value = defaultCategory;

      const fileInput = document.getElementById('importFrFileInput');
      if (fileInput) fileInput.value = '';

      const nameDisplay = document.getElementById('importFrFileNameDisplay');
      if (nameDisplay) nameDisplay.textContent = 'Click to browse or drag and drop CSV file';

      const previewBox = document.getElementById('importFrPreviewBox');
      if (previewBox) previewBox.style.display = 'none';

      const previewList = document.getElementById('importFrPreviewList');
      if (previewList) previewList.innerHTML = '';

      const submitBtn = document.getElementById('importFrSubmitBtn');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Import Records';
      }

      this.setupDropzone();
      window.openModal('importFundraisingModal');
    },

    setupDropzone: function () {
      const dropzone = document.getElementById('importFrDropzone');
      if (!dropzone || dropzone.dataset.bound) return;
      dropzone.dataset.bound = 'true';

      ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.style.borderColor = 'var(--red)';
          dropzone.style.background = 'var(--panel)';
        }, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.style.borderColor = 'var(--line-strong)';
          dropzone.style.background = 'var(--panel-2)';
        }, false);
      });

      dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
          this.processSelectedFile(dt.files[0]);
        }
      }, false);
    },

    handleFileSelect: function (event) {
      if (event.target && event.target.files && event.target.files[0]) {
        this.processSelectedFile(event.target.files[0]);
      }
    },

    processSelectedFile: function (file) {
      if (!file) return;
      this.selectedImportFile = file;

      const nameDisplay = document.getElementById('importFrFileNameDisplay');
      if (nameDisplay) {
        const sizeKb = (file.size / 1024).toFixed(1);
        nameDisplay.innerHTML = `<span style="color:var(--ink);font-weight:700">${escHtml(file.name)}</span> <span style="color:var(--muted);font-size:12px">(${sizeKb} KB)</span>`;
      }

      // Read for live preview
      const reader = new FileReader();
      reader.onload = (e) => {
        const content = e.target.result;
        const lines = content.split(/\r\n|\r|\n/).filter(l => l.trim() !== '');
        if (lines.length > 1) {
          const header = lines[0];
          const dataRows = lines.slice(1);
          const previewBox = document.getElementById('importFrPreviewBox');
          const summaryEl = document.getElementById('importFrPreviewSummary');
          const listEl = document.getElementById('importFrPreviewList');

          if (summaryEl) summaryEl.textContent = `${dataRows.length} record(s) detected in CSV`;

          if (listEl) {
            const previewItems = dataRows.slice(0, 6).map((row, idx) => {
              const cells = row.split(',').map(c => c.replace(/^["']|["']$/g, '').trim());
              const name = cells[0] || cells[1] || `Row #${idx + 1}`;
              const extra = cells.slice(1, 3).filter(Boolean).join(' • ');
              return `<div style="padding:4px 0;border-bottom:1px dashed var(--line);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <span style="font-weight:600;color:var(--ink)">${escHtml(name)}</span> ${extra ? `<span style="color:var(--muted);font-size:11px">(${escHtml(extra)})</span>` : ''}
              </div>`;
            }).join('');

            listEl.innerHTML = previewItems + (dataRows.length > 6 ? `<div style="padding-top:4px;font-size:11px;color:var(--muted);font-style:italic">+ ${dataRows.length - 6} more rows...</div>` : '');
          }

          if (previewBox) previewBox.style.display = 'block';
        }
      };
      reader.readAsText(file);
    },

    submitCsvImport: async function () {
      if (!this.selectedImportFile) {
        if (window.showToast) window.showToast('Validation Error', 'Please choose a CSV file to import.', true);
        return;
      }

      const targetCategory = document.getElementById('importFrTargetCategory')?.value || 'partnerships';
      const submitBtn = document.getElementById('importFrSubmitBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Importing...';
      }

      const formData = new FormData();
      formData.append('file', this.selectedImportFile);
      formData.append('default_category', targetCategory);

      try {
        const token = localStorage.getItem('jmos_api_token');
        const res = await fetch('/api/fundraising/import', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Authorization': token ? `Bearer ${token}` : ''
          },
          body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to import CSV records.');

        if (window.showToast) {
          window.showToast('Import Succeeded', data.message || `Processed ${data.total_processed || 0} records.`);
        }

        window.closeModal('importFundraisingModal');
        this.loadOpportunities();
      } catch (err) {
        if (window.showToast) window.showToast('Import Error', err.message, true);
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Import Records';
        }
      }
    },

    downloadSampleCsv: function (type) {
      if (type === 'partnerships') {
        const headers = ['ENTITY NAME', 'SECTION / CLASSIFICATION', 'NATURE', 'WEBSITE', 'STATUS', 'COMMENTS'];
        const sampleRows = [
          ['"Ford Foundation (East Africa)"', '"Grantmakers / CBOs"', '"Partnership Invitation"', '"https://www.fordfoundation.org"', '"Identified"', '"Regional civil society and media expression funding."'],
          ['"Kenya Community Media Network (KCOMNET)"', '"Associations / Cooperatives"', '"Partnership Invitation"', '"https://kcomnet.org"', '"In Conversation"', '"Grassroots broadcasting & community radio consortium."'],
          ['"Safaricom Foundation"', '"Corporate Institutions"', '"Partnership Invitation"', '"https://www.safaricomfoundation.org"', '"Identified"', '"Education, health and environmental CSR media partners."'],
          ['"Daystar University School of Communication"', '"Academia / Educational Institutions"', '"Partnership Invitation"', '"https://www.daystar.ac.ke"', '"Identified"', '"Student fellowship placement, media research and workshops."']
        ];
        this._downloadCsv(headers, sampleRows, 'Sample_Partnership_Master.csv');
      } else {
        const headers = ['Organization', 'Program Title', 'Category', 'Status', 'Amount KES', 'Deadline', 'Application Link', 'Notes'];
        const sampleRows = [
          ['"USAID Kenya"', '"Civil Society & Media Resilience"', '"open_calls"', '"In Progress"', '15000000', '"2026-11-30"', '"https://www.usaid.gov/kenya"', '"Strengthening independent regional journalism."'],
          ['"Mozilla Foundation"', '"Creative Media Fellowship"', '"fellowships"', '"Identified"', '4500000', '"2026-12-15"', '"https://foundation.mozilla.org"', '"Digital rights, tech accountability and open media."']
        ];
        this._downloadCsv(headers, sampleRows, 'Sample_Grants_Open_Calls.csv');
      }
    }
  };

  // -------------------------------------------------------------
  // GLOBAL WINDOW BINDINGS
  // -------------------------------------------------------------
  window.openAddFundraisingModal = function () {
    window.JMOS_FUNDRAISING.openCreateModal('open_calls');
  };

  window.openAddPartnershipModal = function () {
    window.JMOS_FUNDRAISING.openCreateModal('partnerships', 'Grantmakers / CBOs');
  };

  window.openImportFundraisingModal = function (targetCategory = 'partnerships') {
    window.JMOS_FUNDRAISING.openImportModal(targetCategory);
  };

  window.onImportFrFileSelected = function (event) {
    window.JMOS_FUNDRAISING.handleFileSelect(event);
  };

  window.submitFundraisingCsvImport = function () {
    window.JMOS_FUNDRAISING.submitCsvImport();
  };

  window.downloadSampleCsv = function (type) {
    window.JMOS_FUNDRAISING.downloadSampleCsv(type);
  };

  window.editFundraisingOpportunity = function (id) {
    window.JMOS_FUNDRAISING.editOpportunity(id);
  };

  window.deleteFundraisingOpportunity = function (id) {
    window.JMOS_FUNDRAISING.deleteOpportunity(id);
  };

  window.submitFundraisingForm = function () {
    window.JMOS_FUNDRAISING.submitFundraisingForm();
  };

  window.exportFundraisingCsv = function () {
    window.JMOS_FUNDRAISING.exportGrantsCsv();
  };

  window.exportPartnershipsCsv = function () {
    window.JMOS_FUNDRAISING.exportPartnershipsCsv();
  };

  window.refreshFundraising = function () {
    window.JMOS_FUNDRAISING.loadOpportunities();
  };

  window.refreshPartnerships = function () {
    window.JMOS_FUNDRAISING.loadOpportunities();
  };

  window.switchFundraisingTab = function (tab) {
    document.querySelectorAll('[data-fr-tab]').forEach(b => b.classList.toggle('active', b.getAttribute('data-fr-tab') === tab));
    window.JMOS_FUNDRAISING.currentCategory = tab;
    window.JMOS_FUNDRAISING.renderList();
  };

  window.switchPartnershipTab = function (section) {
    document.querySelectorAll('[data-part-tab]').forEach(b => b.classList.toggle('active', b.getAttribute('data-part-tab') === section));
    window.JMOS_FUNDRAISING.partSectionFilter = section;
    window.JMOS_FUNDRAISING.renderPartnerships();
  };

  window.applyFundraisingFilters = function () {
    const statusSelect = document.getElementById('frStatusFilter');
    if (statusSelect) window.JMOS_FUNDRAISING.statusFilter = statusSelect.value;
    window.JMOS_FUNDRAISING.renderList();
  };

  window.applyPartnershipFilters = function () {
    const natureSelect = document.getElementById('partNatureFilter');
    if (natureSelect) window.JMOS_FUNDRAISING.partNatureFilter = natureSelect.value;
    const statusSelect = document.getElementById('partStatusFilter');
    if (statusSelect) window.JMOS_FUNDRAISING.partStatusFilter = statusSelect.value;
    window.JMOS_FUNDRAISING.renderPartnerships();
  };

  window.onFrSearchChange = function (val) {
    window.JMOS_FUNDRAISING.searchQuery = (val || '').toLowerCase().trim();
    window.JMOS_FUNDRAISING.renderList();
  };

  window.onPartSearchChange = function (val) {
    window.JMOS_FUNDRAISING.partSearchQuery = (val || '').toLowerCase().trim();
    window.JMOS_FUNDRAISING.renderPartnerships();
  };

  window.onFrCategoryChange = function (category) {
    const natureSelect = document.getElementById('frNature');
    const entityWrap = document.getElementById('frEntityTypeWrap');
    if (category === 'partnerships') {
      if (natureSelect) natureSelect.value = 'Partnership Invitation';
      if (entityWrap) entityWrap.style.display = 'block';
    } else {
      if (natureSelect) natureSelect.value = 'Grants Application';
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_FUNDRAISING.init();
  });
})();

