/* ==========================================================================
   JMOS — Fundraiser & Impact Projects Pipeline
   ========================================================================== */

(function () {
  'use strict';

  window.JMOS_FUNDRAISING = {
    opportunities: [],
    currentCategory: 'all',
    currentStatus: 'all',
    searchQuery: '',

    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Category tab switching
      document.querySelectorAll('.fund-cat-tab').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          document.querySelectorAll('.fund-cat-tab').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          this.currentCategory = btn.getAttribute('data-cat') || 'all';
          this.render();
        });
      });

      // Status filter dropdown
      const statusSelect = document.getElementById('fundStatusFilter');
      if (statusSelect) {
        statusSelect.addEventListener('change', (e) => {
          this.currentStatus = e.target.value;
          this.render();
        });
      }

      // Search input
      const searchInput = document.getElementById('fundSearchInput');
      if (searchInput) {
        searchInput.addEventListener('input', (e) => {
          this.searchQuery = e.target.value.toLowerCase().trim();
          this.render();
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
        if (!res.ok) throw new Error(data.message || 'Failed to fetch fundraising opportunities');

        this.opportunities = data.data || [];
        this.renderMetrics(data.stats);
        this.render();
      } catch (err) {
        console.error('Error loading fundraising opportunities:', err);
      }
    },

    renderMetrics: function (stats) {
      if (!stats) {
        const totalKes = this.opportunities.reduce((acc, o) => acc + (parseFloat(o.amount_kes) || 0), 0);
        const submitted = this.opportunities.filter(o => o.status === 'Submitted' || o.status === 'Applied' || o.status === '1').length;
        const awarded = this.opportunities.filter(o => o.status === 'Won / Awarded').length;

        if (document.getElementById('fundMetricPipeline')) {
          document.getElementById('fundMetricPipeline').textContent = 'KES ' + (totalKes / 1000000).toFixed(1) + 'M';
        }
        if (document.getElementById('fundMetricActiveCount')) {
          document.getElementById('fundMetricActiveCount').textContent = this.opportunities.length;
        }
        if (document.getElementById('fundMetricSubmittedCount')) {
          document.getElementById('fundMetricSubmittedCount').textContent = submitted;
        }
        if (document.getElementById('fundMetricAwardedCount')) {
          document.getElementById('fundMetricAwardedCount').textContent = awarded;
        }
        return;
      }

      if (document.getElementById('fundMetricPipeline')) {
        document.getElementById('fundMetricPipeline').textContent = 'KES ' + (Number(stats.total_pipeline_kes) / 1000000).toFixed(1) + 'M';
      }
      if (document.getElementById('fundMetricActiveCount')) {
        document.getElementById('fundMetricActiveCount').textContent = stats.total_opportunities;
      }
      if (document.getElementById('fundMetricSubmittedCount')) {
        document.getElementById('fundMetricSubmittedCount').textContent = stats.submitted_count;
      }
      if (document.getElementById('fundMetricAwardedCount')) {
        document.getElementById('fundMetricAwardedCount').textContent = stats.won_count;
      }
    },

    render: function () {
      const tbody = document.getElementById('fundraisingTableBody');
      if (!tbody) return;

      let filtered = this.opportunities;

      if (this.currentCategory !== 'all') {
        filtered = filtered.filter(o => o.category === this.currentCategory);
      }

      if (this.currentStatus !== 'all') {
        filtered = filtered.filter(o => o.status === this.currentStatus);
      }

      if (this.searchQuery) {
        filtered = filtered.filter(o => 
          (o.program_title && o.program_title.toLowerCase().includes(this.searchQuery)) ||
          (o.organization && o.organization.toLowerCase().includes(this.searchQuery)) ||
          (o.partner_organization && o.partner_organization.toLowerCase().includes(this.searchQuery)) ||
          (o.notes && o.notes.toLowerCase().includes(this.searchQuery))
        );
      }

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
              <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" fill="none" style="margin-bottom:8px;opacity:0.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <div>No impact opportunities match your current filters.</div>
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(item => {
        const amountDisplay = item.amount_display || (item.amount_kes ? `KES ${Number(item.amount_kes).toLocaleString()}` : '—');
        const deadlineDisplay = item.deadline || 'Rolling / TBD';
        const linkBtn = item.application_link
          ? `<a href="${item.application_link}" target="_blank" class="btn small" style="margin-right:6px">Apply / Portal</a>`
          : '';

        const statusLabel = item.status || 'Identified';
        const statusClass = `badge ${statusLabel.toLowerCase().replace(/[^a-z0-9]/g, '_')}`;

        return `
          <tr>
            <td>
              <div style="font-weight:600;color:var(--ink)">${item.program_title || item.organization}</div>
              <div style="font-size:11px;color:var(--muted);display:flex;gap:8px;align-items:center;margin-top:2px">
                <span class="badge ${item.category}">${item.category === 'open_calls' ? 'Open Call' : 'Partnership'}</span>
                <span>${item.organization}</span>
              </div>
            </td>
            <td>
              <div style="font-size:12px;color:var(--ink)">${item.partner_organization || 'Jeota Media Direct'}</div>
              <div style="font-size:11px;color:var(--muted)">${item.funding_type || item.partnership_entity_type || 'Grant'}</div>
            </td>
            <td style="font-family:'IBM Plex Mono',monospace;font-weight:600;color:var(--green)">${amountDisplay}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:12px">${deadlineDisplay}</td>
            <td><span class="${statusClass}">${statusLabel}</span></td>
            <td style="font-size:12px;color:var(--muted)">${item.lead_owner || 'Barny / Ian'}</td>
            <td style="text-align:right">
              ${linkBtn}
              <button type="button" class="btn small" onclick="window.JMOS_FUNDRAISING.editOpportunity(${item.id})" style="margin-right:4px">Edit</button>
              <button type="button" class="btn small danger" onclick="window.JMOS_FUNDRAISING.deleteOpportunity(${item.id})">&times;</button>
            </td>
          </tr>
        `;
      }).join('');
    },

    openCreateModal: function () {
      const modal = document.getElementById('fundraisingModal');
      if (!modal) return;
      const form = document.getElementById('fundraisingForm');
      if (form) form.reset();
      document.getElementById('fundId').value = '';
      document.getElementById('fundModalTitle').textContent = 'Add Fundraising Opportunity';
      modal.classList.add('active');
    },

    editOpportunity: function (id) {
      const item = this.opportunities.find(o => o.id === id);
      if (!item) return;

      const modal = document.getElementById('fundraisingModal');
      if (!modal) return;

      document.getElementById('fundId').value = item.id;
      document.getElementById('fundModalTitle').textContent = 'Edit Fundraising Opportunity';
      if (document.getElementById('fundOrganization')) document.getElementById('fundOrganization').value = item.organization || '';
      if (document.getElementById('fundProgramTitle')) document.getElementById('fundProgramTitle').value = item.program_title || '';
      if (document.getElementById('fundCategory')) document.getElementById('fundCategory').value = item.category || 'open_calls';
      if (document.getElementById('fundStatus')) document.getElementById('fundStatus').value = item.status || 'Identified';
      if (document.getElementById('fundAmountKes')) document.getElementById('fundAmountKes').value = item.amount_kes || '';
      if (document.getElementById('fundAmountDisplay')) document.getElementById('fundAmountDisplay').value = item.amount_display || '';
      if (document.getElementById('fundDeadline')) document.getElementById('fundDeadline').value = item.deadline || '';
      if (document.getElementById('fundPartnerOrg')) document.getElementById('fundPartnerOrg').value = item.partner_organization || '';
      if (document.getElementById('fundFundingType')) document.getElementById('fundFundingType').value = item.funding_type || '';
      if (document.getElementById('fundLeadOwner')) document.getElementById('fundLeadOwner').value = item.lead_owner || '';
      if (document.getElementById('fundAppUrl')) document.getElementById('fundAppUrl').value = item.application_link || '';
      if (document.getElementById('fundNotes')) document.getElementById('fundNotes').value = item.notes || '';

      modal.classList.add('active');
    },

    saveOpportunity: async function (e) {
      if (e) e.preventDefault();
      const id = document.getElementById('fundId')?.value;
      const payload = {
        organization: document.getElementById('fundOrganization')?.value || '',
        program_title: document.getElementById('fundProgramTitle')?.value || document.getElementById('fundTitle')?.value || '',
        category: document.getElementById('fundCategory')?.value || 'open_calls',
        status: document.getElementById('fundStatus')?.value || 'Identified',
        amount_kes: parseFloat(document.getElementById('fundAmountKes')?.value) || null,
        amount_display: document.getElementById('fundAmountDisplay')?.value || null,
        deadline: document.getElementById('fundDeadline')?.value || null,
        partner_organization: document.getElementById('fundPartnerOrg')?.value || document.getElementById('fundLeadPartner')?.value || '',
        funding_type: document.getElementById('fundFundingType')?.value || document.getElementById('fundStrategicFocus')?.value || '',
        lead_owner: document.getElementById('fundLeadOwner')?.value || '',
        application_link: document.getElementById('fundAppUrl')?.value || '',
        notes: document.getElementById('fundNotes')?.value || ''
      };

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
          window.showToast('Opportunity saved successfully', 'success');
        } else {
          alert('Opportunity saved successfully');
        }

        document.getElementById('fundraisingModal').classList.remove('active');
        this.loadOpportunities();
      } catch (err) {
        alert('Error saving opportunity: ' + err.message);
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

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to delete opportunity');

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

      const headers = ['Organization', 'Program Title', 'Category', 'Status', 'Amount KES', 'Amount Display', 'Deadline', 'Funding Type', 'Partner Organization', 'Lead Owner', 'Application Link'];
      const rows = this.opportunities.map(o => [
        `"${(o.organization || '').replace(/"/g, '""')}"`,
        `"${(o.program_title || '').replace(/"/g, '""')}"`,
        `"${o.category}"`,
        `"${o.status}"`,
        o.amount_kes || '',
        `"${(o.amount_display || '').replace(/"/g, '""')}"`,
        `"${(o.deadline || '').replace(/"/g, '""')}"`,
        `"${(o.funding_type || '').replace(/"/g, '""')}"`,
        `"${(o.partner_organization || '').replace(/"/g, '""')}"`,
        `"${(o.lead_owner || '').replace(/"/g, '""')}"`,
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

  window.refreshFundraising = function () {
    window.JMOS_FUNDRAISING.loadOpportunities();
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.JMOS_FUNDRAISING.init();
  });
})();
