@php
  $hour = (int) now()->format('H');
  if ($hour >= 4 && $hour < 12) {
      $greeting = 'Good morning';
      $briefTag = 'Your morning brief';
  } elseif ($hour >= 12 && $hour < 17) {
      $greeting = 'Good afternoon';
      $briefTag = 'Your afternoon brief';
  } elseif ($hour >= 17 && $hour < 22) {
      $greeting = 'Good evening';
      $briefTag = 'Your evening brief';
  } else {
      $greeting = 'Good evening';
      $briefTag = 'Your late-night brief';
  }
@endphp
<!-- ==========================================================================
     JMOS — View: Dashboard
     ========================================================================== -->
<section class="view" data-view="dashboard">
  <!-- Page Header -->
  <div class="page-head">
    <div>
      <h1 class="pt" id="greetName">{{ $greeting }}.</h1>
      <p id="dashSubtitle">Here's where Jeota stands today.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn primary" id="dashQuickActionBtn" onclick="openModal('dealModal')" data-modal-open="dealModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Quick Action
      </button>
    </div>
  </div>

  <!-- Operations Brief Hero -->
  <div class="brief">
    <div class="tag" id="dashBriefTag"><span class="d"></span><span id="briefTagText">{{ $briefTag }}</span></div>
    <h2 id="briefHeadline">Live system status &amp; operations</h2>
    <p id="briefBody">Loading your operations brief…</p>
    <div class="drip" style="left:56px;height:15px"></div>
    <div class="drip" style="left:70px;height:24px"></div>
    <div class="drip" style="left:83px;height:11px"></div>
    <div class="drip" style="left:210px;height:18px"></div>
    <div class="drip" style="left:223px;height:9px"></div>
  </div>

  <!-- Key Performance Indicators -->
  <div class="kpis">
    <div class="kpi" data-perm="owner finance">
      <div class="lbl">
        <span class="ic tint-green">
          <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/></svg>
        </span>
        Account balance
      </div>
      <div class="val" id="kpiBalance">KES 0</div>
      <div class="src"><span class="dotg"></span>calculated live in JMOS</div>
    </div>

    <div class="kpi" data-perm="owner finance">
      <div class="lbl">
        <span class="ic tint-red">
          <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M3 17l6-6 4 4 8-8"/></svg>
        </span>
        Revenue · received
      </div>
      <div class="val" id="kpiRevenue">KES 0</div>
      <div class="sub">from paid invoices</div>
    </div>

    <div class="kpi" data-perm="owner finance sales">
      <div class="lbl">
        <span class="ic tint-amber">
          <svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-7"/></svg>
        </span>
        Pipeline
      </div>
      <div class="val" id="kpiPipeline">KES 0</div>
      <div class="sub" id="kpiPipelineSub"><span class="mono">0</span> open deals</div>
    </div>

    <div class="kpi" data-perm="owner finance">
      <div class="lbl">
        <span class="ic tint-red">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        </span>
        Unpaid invoices
      </div>
      <div class="val" id="kpiUnpaid">KES 0</div>
      <div class="sub"><span class="delta" id="kpiOverdue">0 overdue</span></div>
    </div>
  </div>

  <!-- 2-Column: Triage & Active Projects -->
  <div class="cols">
    <div class="card" style="align-self:start">
      <div class="card-h">
        <h3>Needs you <span class="count" id="triageCount">0</span></h3>
      </div>
      <div class="triage" id="triageList">
        <div style="padding:20px;text-align:center;color:var(--muted)">Loading alerts…</div>
      </div>
    </div>

    <div class="card" style="align-self:start">
      <div class="card-h">
        <h3>Active projects <span class="count" id="dashProjectsCount">0</span></h3>
        <span class="link" data-view="projects">Open board</span>
      </div>
      <div class="plist" id="dashProjectsList">
        <div style="padding:20px;text-align:center;color:var(--muted)">Loading active projects…</div>
      </div>
    </div>
  </div>

  <!-- Sales Pipeline Trigger Card -->
  <div class="card" style="margin-top:17px" data-perm="owner finance sales">
    <div class="card-h">
      <h3>Sales pipeline <span class="count" id="dashPipelineTotal">KES 0</span></h3>
      <span class="link" data-view="pipeline">View pipeline</span>
    </div>
    <div class="pipe" id="dashPipeWidget">
      <!-- Dynamically filled -->
    </div>
    <div class="flow">
      <span class="lead">Jeota's engine</span>
      <span class="step">Win client</span><span class="arr">→</span>
      <span class="step">Create project</span><span class="arr">→</span>
      <span class="step">Generate tasks</span><span class="arr">→</span>
      <span class="step">Notify team</span><span class="arr">→</span>
      <span class="step">Schedule invoice</span><span class="arr">→</span>
      <span class="step">Update dashboard</span>
    </div>
  </div>

  <!-- Interactive Operations & Meetings Calendar + Team Workload -->
  <div class="cols" style="margin-top:17px">
    <!-- Operations & Schedule Calendar Widget -->
    <div class="card" style="align-self:start">
      <div class="card-h">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <h3 style="font-family:'Poppins',sans-serif;font-size:15px;font-weight:600;white-space:nowrap;margin:0">Operations Calendar</h3>
          <span class="badge" style="background:var(--amber-soft);color:var(--amber);font-size:11px;white-space:nowrap;flex-shrink:0">Google Calendar</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <div class="cal-nav-group" style="display:flex;align-items:center;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:2px 6px;white-space:nowrap;flex-shrink:0">
            <button type="button" class="icon-btn-sm" id="calPrevBtn" title="Previous Month" style="padding:3px 6px;cursor:pointer">&lsaquo;</button>
            <span id="calMonthLabel" style="font-size:12px;font-weight:600;padding:0 6px;min-width:110px;text-align:center;white-space:nowrap">September 2026</span>
            <button type="button" class="icon-btn-sm" id="calNextBtn" title="Next Month" style="padding:3px 6px;cursor:pointer">&rsaquo;</button>
          </div>
          <button type="button" class="btn primary" id="dashScheduleEventBtn" onclick="openModal('eventModal')" data-modal-open="eventModal" style="padding:6px 13px;font-size:12px;white-space:nowrap;flex-shrink:0">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Book Event
          </button>
        </div>
      </div>

      <!-- Month Grid -->
      <div class="cal-grid-wrapper" style="padding:14px 16px 10px">
        <div class="cal-grid" id="dashCalendarGrid">
          <!-- Dynamically populated by calendar.js -->
        </div>
      </div>

      <!-- Selected Day Agenda -->
      <div style="border-top:1px solid var(--line);padding:14px 16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span style="font-size:12px;font-weight:600;color:var(--muted)">Agenda for <span id="calSelectedDateLabel" style="color:var(--ink)">Today</span></span>
          <button type="button" class="linkbtn" style="font-size:11px" id="calTodayBtn">Jump to Today</button>
        </div>
        <div class="agenda" id="calAgendaList">
          <div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Loading events…</div>
        </div>
      </div>
    </div>

    <!-- Team Workload Widget -->
    <div class="card" style="align-self:start" data-perm="owner finance">
      <div class="card-h">
        <h3>Team workload <span class="count">this week</span></h3>
        <span class="link" data-view="people">People</span>
      </div>
      <div class="work" id="dashWorkloadList">
        <!-- Dynamically filled -->
      </div>
    </div>
  </div>

  <!-- 5. System Audit Trail & Activity Feed (Admin & IT Manager Exclusive) -->
  <div class="card" style="margin-top:17px" data-perm="owner manager admin" id="dashAuditTrailCard">
    <div class="card-h" style="flex-wrap:wrap;gap:10px">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--red)"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <h3 style="margin:0;font-family:'Poppins',sans-serif;font-size:15px;font-weight:600">Audit Trail &amp; Activity Log</h3>
        </div>
        <span class="badge" style="background:rgba(43,138,90,0.12);color:var(--green);font-size:11px;font-weight:600;display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:999px">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--green);display:inline-block"></span>
          Live Security Feed
        </span>
        <span class="count" id="dashAuditTotalBadge">0 events</span>
      </div>

      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <div class="audit-filter-group" style="display:flex;align-items:center;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:2px 4px;gap:2px">
          <button type="button" class="linkbtn active audit-filter-btn" data-audit-filter="all" onclick="filterAuditLogs('all')" style="font-size:11.5px;padding:3px 8px;border-radius:6px;font-weight:600">All</button>
          <button type="button" class="linkbtn audit-filter-btn" data-audit-filter="PROJECT" onclick="filterAuditLogs('PROJECT')" style="font-size:11.5px;padding:3px 8px;border-radius:6px">Projects</button>
          <button type="button" class="linkbtn audit-filter-btn" data-audit-filter="SHOOT" onclick="filterAuditLogs('SHOOT')" style="font-size:11.5px;padding:3px 8px;border-radius:6px">Shoots</button>
          <button type="button" class="linkbtn audit-filter-btn" data-audit-filter="AUTH" onclick="filterAuditLogs('AUTH')" style="font-size:11.5px;padding:3px 8px;border-radius:6px">Auth</button>
          <button type="button" class="linkbtn audit-filter-btn" data-audit-filter="SYSTEM" onclick="filterAuditLogs('SYSTEM')" style="font-size:11.5px;padding:3px 8px;border-radius:6px">System</button>
        </div>

        <button type="button" class="btn" id="dashAuditRefreshBtn" onclick="fetchAuditLogs()" title="Refresh live audit trail" style="padding:5px 10px;font-size:11.5px;display:flex;align-items:center;gap:5px">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Quick Stats Bar -->
    <div style="padding:10px 16px;background:var(--paper);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:12px">
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
        <span style="color:var(--muted)">Today: <b id="dashAuditStatToday" style="color:var(--ink)">0</b></span>
        <span style="color:var(--muted)">Created: <b id="dashAuditStatCreates" style="color:var(--green)">0</b></span>
        <span style="color:var(--muted)">Updated: <b id="dashAuditStatUpdates" style="color:var(--blue)">0</b></span>
        <span style="color:var(--muted)">Deleted: <b id="dashAuditStatDeletes" style="color:var(--red)">0</b></span>
        <span style="color:var(--muted)">Auth: <b id="dashAuditStatAuth" style="color:#8b5cf6">0</b></span>
        <span style="color:var(--muted)">System: <b id="dashAuditStatSystem" style="color:var(--amber)">0</b></span>
      </div>
      <div style="display:flex;align-items:center;gap:6px">
        <input type="text" id="dashAuditSearchInput" placeholder="Filter activity..." oninput="handleAuditSearch(this.value)" style="padding:4px 9px;font-size:11.5px;border-radius:6px;border:1px solid var(--line);background:var(--bg);color:var(--ink);width:160px" autocomplete="off">
      </div>
    </div>

    <!-- Audit Event Log Table -->
    <div class="tablewrap" style="max-height:360px;overflow-y:auto">
      <table style="width:100%;font-size:12px">
        <thead>
          <tr>
            <th style="width:110px">Action</th>
            <th style="width:170px">User</th>
            <th>Activity Details</th>
            <th style="width:110px">Entity</th>
            <th style="width:120px">IP / Host</th>
            <th style="width:110px;text-align:right">Time</th>
          </tr>
        </thead>
        <tbody id="dashAuditLogsBody">
          <tr>
            <td colspan="6" style="padding:24px;text-align:center;color:var(--muted)">Loading system audit records…</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Home Screen Footnote -->
  <footer class="dash-footnote" style="margin-top:28px;padding-top:14px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;font-size:11.5px;color:var(--muted);flex-wrap:wrap;gap:8px">
    <div style="display:flex;align-items:center;gap:8px">
      <span>JMOS &middot; Jeota Media Operating System</span>
      <span style="display:inline-block;width:3px;height:3px;border-radius:50%;background:var(--muted)"></span>
      <span>All systems operational</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <span class="badge" style="font-size:10.5px;padding:2px 7px;background:var(--paper);border:1px solid var(--line);color:var(--muted);font-family:'IBM Plex Mono',monospace">{{ config('app.version', 'v2.4.6') }}</span>
      <span>&copy; {{ date('Y') }} Jeota Media Ltd</span>
    </div>
  </footer>
</section>
