/* ==========================================================================
   JMOS — Fundraising & Impact Grants (Leads for Impact Projects)
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_FUNDRAISING = {
    opportunities: [],
    currentCategory: 'all',
    searchQuery: '',

    init: function () {
      this.bindEvents();
      this.loadOpportunities();
    },

    bindEvents: function () {
      // Category tabs
      document.querySelectorAll('.fund-cat-tab').forEach(btn => {
        btn.addEventListener('click', (e) => {
          document.querySelectorAll('.fund-cat-tab').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          this.currentCategory = btn.getAttribute('data-fund-cat') || 'all';
          this.renderList();
        });
      });

      // Search input
      const searchInput = document.getElementById('frSearchInput');
      if (searchInput) {
        searchInput.addEventListener('input', (e) => {
          this.searchQuery = e.target.value.toLowerCase().trim();
          this.renderList();
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
          this.updateMetrics(data.metrics);
          this.renderList();
        }
      } catch (err) {
        console.error('Error fetching fundraising data:', err);
      }
    },

    updateMetrics: function (metrics) {
      if (!metrics) {
        let totalVal = 0;
        let openCount = 0;
        let subCount = 0;
        let partCount = 0;

        this.opportunities.forEach(o => {
          totalVal += (parseFloat(o.amount_kes) || 0);
          if (o.status === 'Identified' || o.status === 'In Progress') openCount++;
          if (o.status === 'Submitted') subCount++;
          if (o.category === 'partnerships') partCount++;
        });

        metrics = {
          total_pipeline_value: totalVal,
          open_calls_count: openCount,
          submitted_count: subCount,
          partnerships_count: partCount
        };
      }

      const totalPipeEl = document.getElementById('frKpiTotalPipeline');
      if (totalPipeEl) totalPipeEl.textContent = 'KES ' + Number(metrics.total_pipeline_value || 0).toLocaleString();

      const openEl = document.getElementById('frKpiOpenCallsCount');
      if (openEl) openEl.textContent = metrics.open_calls_count || 0;

      const subEl = document.getElementById('frKpiSubmittedCount');
      if (subEl) subEl.textContent = metrics.submitted_count || 0;

      const partEl = document.getElementById('frKpiPartnershipsCount');
      if (partEl) partEl.textContent = metrics.partnerships_count || 0;
    },

    renderList: function () {
      const tbody = document.getElementById('fundraisingTableBody');
      const countEl = document.getElementById('frCountBadge');
      if (!tbody) return;

      let filtered = this.opportunities;

      if (this.currentCategory !== 'all') {
        filtered = filtered.filter(o => o.category === this.currentCategory);
      }

      if (this.searchQuery) {
        filtered = filtered.filter(o =>
          (o.organization && o.organization.toLowerCase().includes(this.searchQuery)) ||
          (o.program_title && o.program_title.toLowerCase().includes(this.searchQuery)) ||
          (o.partner_organization && o.partner_organization.toLowerCase().includes(this.searchQuery)) ||
          (o.notes && o.notes.toLowerCase().includes(this.searchQuery))
        );
      }

      if (countEl) countEl.textContent = `${filtered.length} Opportunities`;

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align:center;padding:48px 20px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" fill="none" stroke-width="1.5" style="margin-bottom:10px;opacity:0.4"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
              <div style="font-size:13.5px;font-weight:600;color:var(--ink);margin-bottom:4px">No opportunities match this filter</div>
              <div style="font-size:12px;margin-bottom:14px">Log open grant calls, fellowships, or impact consortium partners.</div>
              <button type="button" class="btn primary" onclick="window.openAddFundraisingModal()" style="font-size:11.5px;padding:5px 14px">+ Add Opportunity</button>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(item => {
        const amt = item.amount_kes ? `KES ${Number(item.amount_kes).toLocaleString()}` : (item.amount_display || 'Undisclosed');
        const st = (item.status || 'Identified').toLowerCase();
        let pillClass = 'tint-blue';
        if (st.includes('won') || st.includes('awarded')) pillClass = 'tint-green';
        if (st.includes('progress') || st.includes('submitted')) pillClass = 'tint-amber';
        if (st.includes('missed') || st.includes('lost')) pillClass = 'tint-red';

        const catBadge = `<span class="badge" style="background:var(--panel-2);color:var(--muted);font-size:10.5px">${(item.category || 'open_calls').replace('_', ' ').toUpperCase()}</span>`;
        const linkBtn = item.application_link 
          ? `<a href="${item.application_link}" target="_blank" class="btn small" style="margin-right:6px;font-size:11px;padding:3px 8px">Portal ↗</a>`
          : '';

        return `
          <tr>
            <td>
              <div style="font-weight:700;color:var(--ink);font-size:13px">${item.organization}</div>
              <div style="font-size:11px;color:var(--muted)">${item.partner_organization ? 'Partner: ' + item.partner_organization : 'Direct Applicant'}</div>
            </td>
            <td>
              <div style="font-weight:600;color:var(--ink);font-size:12.5px">${item.program_title}</div>
              <div style="font-size:11px;color:var(--muted)">${item.notes ? (item.notes.length > 55 ? item.notes.substring(0, 55) + '…' : item.notes) : ''}</div>
            </td>
            <td>${catBadge}</td>
            <td class="mono" style="font-weight:700;color:var(--ink);font-size:12px">${amt}</td>
            <td style="font-size:12px;color:var(--muted)">${item.deadline || 'Rolling'}</td>
            <td><span class="pill ${pillClass}">${item.status || 'Identified'}</span></td>
            <td style="text-align:right">
              ${linkBtn}
              <button type="button" class="btn small" onclick="window.editFundraisingOpportunity(${item.id})" style="margin-right:6px;font-size:11px;padding:3px 8px">Edit</button>
              <button type="button" class="btn small danger" onclick="window.deleteFundraisingOpportunity(${item.id})" style="padding:3px 8px;font-size:11px">&times;</button>
            </td>
          </tr>
        `;
      }).join('');
    },

    openCreateModal: function () {
      document.getElementById('frFormId').value = '';
      document.getElementById('frModalTitle').textContent = 'Add Grant / Call Opportunity';
      document.getElementById('frOrg').value = '';
      document.getElementById('frProgram').value = '';
      document.getElementById('frLink').value = '';
      document.getElementById('frAmount').value = '';
      document.getElementById('frDeadline').value = '';
      document.getElementById('frStatus').value = 'Identified';
      document.getElementById('frCategory').value = this.currentCategory !== 'all' ? this.currentCategory : 'open_calls';
      document.getElementById('frPartner').value = '';
      document.getElementById('frNotes').value = '';

      window.openModal('fundraisingModal');
    },

    editOpportunity: function (id) {
      const item = this.opportunities.find(o => o.id === id);
      if (!item) return;

      document.getElementById('frFormId').value = item.id;
      document.getElementById('frModalTitle').textContent = 'Edit Grant / Call Opportunity';
      document.getElementById('frOrg').value = item.organization || '';
      document.getElementById('frProgram').value = item.program_title || '';
      document.getElementById('frLink').value = item.application_link || '';
      document.getElementById('frAmount').value = item.amount_kes || '';
      document.getElementById('frDeadline').value = item.deadline || '';
      document.getElementById('frStatus').value = item.status || 'Identified';
      document.getElementById('frCategory').value = item.category || 'open_calls';
      document.getElementById('frPartner').value = item.partner_organization || '';
      document.getElementById('frNotes').value = item.notes || '';

      window.openModal('fundraisingModal');
    },

    submitFundraisingForm: async function () {
      const id = document.getElementById('frFormId')?.value;
      const org = document.getElementById('frOrg')?.value.trim();
      const program = document.getElementById('frProgram')?.value.trim();

      if (!org || !program) {
        alert('Please fill in the Organization and Program Title.');
        return;
      }

      const payload = {
        organization: org,
        program_title: program,
        application_link: document.getElementById('frLink')?.value.trim() || '',
        amount_kes: parseFloat(document.getElementById('frAmount')?.value) || null,
        deadline: document.getElementById('frDeadline')?.value.trim() || null,
        status: document.getElementById('frStatus')?.value || 'Identified',
        category: document.getElementById('frCategory')?.value || 'open_calls',
        partner_organization: document.getElementById('frPartner')?.value.trim() || '',
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
        if (!res.ok) throw new Error(data.message || 'Failed to save opportunity');

        if (window.showToast) {
          window.showToast('Grant opportunity saved successfully', 'success');
        }

        window.closeModal('fundraisingModal');
        this.loadOpportunities();
      } catch (err) {
        alert('Error saving opportunity: ' + err.message);
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save Opportunity';
        }
      }
    },

    deleteOpportunity: async function (id) {
      if (!confirm('Are you sure you want to delete this fundraising opportunity?')) return;
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
          throw new Error(data.message || 'Failed to delete opportunity');
        }

        if (window.showToast) {
          window.showToast('Opportunity deleted', 'success');
        }
        this.loadOpportunities();
      } catch (err) {
        alert('Error deleting opportunity: ' + err.message);
      }
    },

    exportCsv: function () {
      if (this.opportunities.length === 0) {
        alert('No data to export.');
        return;
      }

      const headers = ['Organization', 'Program Title', 'Category', 'Status', 'Amount KES', 'Deadline', 'Partner Organization', 'Application Link'];
      const rows = this.opportunities.map(o => [
        `"${(o.organization || '').replace(/"/g, '""')}"`,
        `"${(o.program_title || '').replace(/"/g, '""')}"`,
        `"${o.category}"`,
        `"${o.status}"`,
        o.amount_kes || '',
        `"${(o.deadline || '').replace(/"/g, '""')}"`,
        `"${(o.partner_organization || '').replace(/"/g, '""')}"`,
        `"${(o.application_link || '').replace(/"/g, '""')}"`
      ]);

      const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', `JMOS_Fundraising_Master_${new Date().toISOString().split('T')[0]}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  };

  // Global window bindings
  window.openAddFundraisingModal = function () {
    window.JMOS_FUNDRAISING.openCreateModal();
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
    window.JMOS_FUNDRAISING.exportCsv();
  };

  window.refreshFundraising = function () {
    window.JMOS_FUNDRAISING.loadOpportunities();
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_FUNDRAISING.init();
  });
})();
