<!-- ==========================================================================
     JMOS — View: Dedicated Operations & Schedule Calendar
     ========================================================================== -->
<section class="view" data-view="calendar" hidden>
  <!-- Page Header -->
  <div class="page-head">
    <div>
      <h1 class="pt">Operations Calendar</h1>
      <p>Schedule client meetings, production shoots, editing milestones, and project delivery deadlines synced with Google Calendar.</p>
    </div>
    <div class="head-actions">
      <button type="button" class="btn" id="fullCalSyncBtn" onclick="syncCalendarWithGoogle()">
        <svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Sync Google Calendar
      </button>
      <button type="button" class="btn primary" id="addCalEventBtn" onclick="openScheduleModal()" data-modal-open="eventModal">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Schedule Event
      </button>
    </div>
  </div>

  <!-- Event Type Filter Chips -->
  <div class="cal-filter-bar" style="display:flex;align-items:center;gap:8px;margin-bottom:18px;flex-wrap:wrap">
    <button type="button" class="cal-pill active" data-cal-filter="all">All Operations</button>
    <button type="button" class="cal-pill" data-cal-filter="meeting"><span class="dot-indicator" style="background:var(--red)"></span>Client Meetings</button>
    <button type="button" class="cal-pill" data-cal-filter="shoot"><span class="dot-indicator" style="background:var(--amber)"></span>Production Shoots</button>
    <button type="button" class="cal-pill" data-cal-filter="deadline"><span class="dot-indicator" style="background:var(--green)"></span>Project Deliveries</button>
    <button type="button" class="cal-pill" data-cal-filter="invoice"><span class="dot-indicator" style="background:var(--blue, #2B6E8A)"></span>Invoice Due Dates</button>
  </div>

  <!-- 2-Column Calendar & Operations Workspace -->
  <div class="full-cal-layout" style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start">
    <!-- Left: Full Month Calendar Grid -->
    <div class="card" style="padding:22px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div style="display:flex;align-items:center;gap:12px">
          <h2 id="fullCalMonthLabel" style="font-family:'Poppins',sans-serif;font-size:18px;font-weight:700;margin:0">September 2026</h2>
          <button type="button" class="linkbtn" id="fullCalTodayBtn" style="font-size:12px">Today</button>
        </div>
        <div style="display:flex;align-items:center;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:3px 8px;gap:8px">
          <button type="button" class="icon-btn-sm" id="fullCalPrevBtn" title="Previous Month" style="cursor:pointer">&lsaquo; Prev</button>
          <span style="color:var(--line-strong)">|</span>
          <button type="button" class="icon-btn-sm" id="fullCalNextBtn" title="Next Month" style="cursor:pointer">Next &rsaquo;</button>
        </div>
      </div>

      <!-- Month Grid Container -->
      <div class="full-cal-grid" id="fullCalendarGrid">
        <!-- Dynamically rendered by calendar.js -->
      </div>
    </div>

    <!-- Right: Selected Day Schedule & 7-Day Operations Brief -->
    <div style="display:flex;flex-direction:column;gap:16px">
      <!-- Selected Day Schedule Card -->
      <div class="card" style="padding:20px">
        <div class="card-h" style="margin-bottom:12px">
          <div>
            <h3 style="font-family:'Poppins',sans-serif;font-size:14px;font-weight:600">Selected Schedule</h3>
            <span id="fullCalSelectedDate" style="font-size:12px;color:var(--muted)">Today</span>
          </div>
          <button type="button" class="btn" style="padding:5px 10px;font-size:11.5px" onclick="openScheduleModal()">+ Book</button>
        </div>
        <div class="agenda" id="fullDayAgendaList">
          <div style="padding:16px;text-align:center;color:var(--muted);font-size:12px">Select a date on the calendar to view scheduled items.</div>
        </div>
      </div>

      <!-- Upcoming 7-Day Operations Card -->
      <div class="card" style="padding:20px">
        <div class="card-h" style="margin-bottom:12px">
          <h3 style="font-family:'Poppins',sans-serif;font-size:14px;font-weight:600">Upcoming This Week</h3>
          <span class="badge" style="background:var(--green-soft);color:var(--green);font-size:10.5px">Next 7 Days</span>
        </div>
        <div class="agenda" id="upcomingOpsList">
          <div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">Loading operations brief…</div>
        </div>
      </div>

      <!-- Google Calendar Sync Status Card -->
      <div class="card" style="padding:16px;background:var(--paper)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          <div style="width:28px;height:28px;border-radius:6px;background:var(--amber-soft);color:var(--amber);display:grid;place-items:center">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </div>
          <div>
            <b style="font-size:12.5px;color:var(--ink)">Google Calendar Sync</b>
            <p style="font-size:11px;color:var(--muted);margin:0">Connected &amp; Active</p>
          </div>
        </div>
        <p style="font-size:11.5px;color:var(--muted);margin-top:6px;line-height:1.4">
          All client meetings, shoot locations, and task deadlines created in JMOS are automatically reflected in your team Google Calendar.
        </p>
      </div>
    </div>
  </div>
</section>
