/* ==========================================================================
   JMOS — Operations & Google Calendar Controller (Dashboard & Dedicated View)
   ========================================================================== */

const CALENDAR_STATE = {
  currentDate: new Date(),
  selectedDate: new Date(),
  activeFilter: 'all',
  events: []
};

async function fetchCalendarEvents() {
  try {
    let endpoint = '/calendar/events';
    const params = [];
    if (JMOS_STATE.currentUser) {
      if (JMOS_STATE.currentUser.email) {
        params.push('user_email=' + encodeURIComponent(JMOS_STATE.currentUser.email));
      }
      if (JMOS_STATE.currentUser.id) {
        params.push('user_id=' + encodeURIComponent(JMOS_STATE.currentUser.id));
      }
      if (JMOS_STATE.currentUser.role) {
        params.push('user_role=' + encodeURIComponent(JMOS_STATE.currentUser.role));
      }
    }
    if (params.length) {
      endpoint += '?' + params.join('&');
    }
    const res = await JMOS_API.get(endpoint);
    if (res.status === 'success' && Array.isArray(res.data)) {
      CALENDAR_STATE.events = res.data;
    }
  } catch (err) {
    console.warn('Calendar events fetch note:', err.message);
  }
}
window.fetchCalendarEvents = fetchCalendarEvents;

// --------------------------------------------------------------------------
// 1. DASHBOARD COMPACT WIDGET RENDERER
// --------------------------------------------------------------------------
function renderDashboardCalendar() {
  const grid = document.getElementById('dashCalendarGrid');
  const monthLabel = document.getElementById('calMonthLabel');
  const agendaList = document.getElementById('calAgendaList');
  const selectedDateLabel = document.getElementById('calSelectedDateLabel');

  if (!grid) return;

  const year = CALENDAR_STATE.currentDate.getFullYear();
  const month = CALENDAR_STATE.currentDate.getMonth();

  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  if (monthLabel) {
    monthLabel.textContent = `${monthNames[month]} ${year}`;
  }

  const firstDayIndex = new Date(year, month, 1).getDay();
  const adjustedFirstDay = (firstDayIndex === 0 ? 6 : firstDayIndex - 1);
  const totalDays = new Date(year, month + 1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();

  const today = new Date();
  const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;
  const todayDate = today.getDate();

  let daysHtml = '';
  const weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  const headersHtml = weekDays.map(d => `<div class="cal-head-day">${d}</div>`).join('');

  for (let i = adjustedFirstDay - 1; i >= 0; i--) {
    daysHtml += `<div class="cal-day other-month"><span class="dnum">${prevMonthDays - i}</span></div>`;
  }

  for (let day = 1; day <= totalDays; day++) {
    const isToday = isCurrentMonth && day === todayDate;
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const isSelected = CALENDAR_STATE.selectedDate &&
      CALENDAR_STATE.selectedDate.getFullYear() === year &&
      CALENDAR_STATE.selectedDate.getMonth() === month &&
      CALENDAR_STATE.selectedDate.getDate() === day;

    const dayEvents = CALENDAR_STATE.events.filter(e => e.date === dateStr);

    let eventDots = '';
    if (dayEvents.length) {
      eventDots = `<div class="cal-dots">` + dayEvents.slice(0, 3).map(e => {
        let dotColor = '#C52523';
        if (e.event_type === 'status_meeting') dotColor = '#7C3AED';
        if (e.event_type === 'shoot') dotColor = '#B4780F';
        if (e.event_type === 'deadline') dotColor = '#1C7A4E';
        if (e.event_type === 'invoice') dotColor = '#2B6E8A';
        return `<span class="cal-dot" style="background:${dotColor}" title="${escHtml(e.title)}"></span>`;
      }).join('') + (dayEvents.length > 3 ? `<span class="cal-more">+${dayEvents.length - 3}</span>` : '') + `</div>`;
    }

    daysHtml += `
      <div class="cal-day ${isToday ? 'today' : ''} ${isSelected ? 'selected' : ''}" data-cal-date="${dateStr}">
        <span class="dnum">${day}</span>
        ${eventDots}
      </div>
    `;
  }

  const totalCells = adjustedFirstDay + totalDays;
  const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
  for (let i = 1; i <= remainingCells; i++) {
    daysHtml += `<div class="cal-day other-month"><span class="dnum">${i}</span></div>`;
  }

  grid.innerHTML = headersHtml + daysHtml;

  if (agendaList) {
    const selYear = CALENDAR_STATE.selectedDate.getFullYear();
    const selMonth = CALENDAR_STATE.selectedDate.getMonth();
    const selDay = CALENDAR_STATE.selectedDate.getDate();
    const selDateStr = `${selYear}-${String(selMonth + 1).padStart(2, '0')}-${String(selDay).padStart(2, '0')}`;

    if (selectedDateLabel) {
      selectedDateLabel.textContent = CALENDAR_STATE.selectedDate.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric'
      });
    }

    const selectedEvents = CALENDAR_STATE.events.filter(e => e.date === selDateStr);

    if (selectedEvents.length === 0) {
      agendaList.innerHTML = `
        <div style="padding:16px;text-align:center;color:var(--muted);font-size:12px">
          No events scheduled on this day.<br>
          <button type="button" class="linkbtn" style="margin-top:6px;font-size:12px" onclick="openScheduleModal('${selDateStr}')">+ Book Meeting / Shoot</button>
        </div>
      `;
    } else {
      agendaList.innerHTML = selectedEvents.map(e => {
        let badgeClass = 'tint-red';
        let typeName = 'Meeting';
        if (e.event_type === 'status_meeting') { badgeClass = 'tint-purple'; typeName = 'Status Meeting'; }
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice Due'; }

        const meetBtn = e.meet_link
          ? `<a href="${escHtml(e.meet_link)}" target="_blank" rel="noopener noreferrer" class="btn primary" style="padding:2px 7px;font-size:10px;background:#2563eb;border-color:#2563eb;text-decoration:none;gap:3px;white-space:nowrap" title="Join Google Meet" onclick="event.stopPropagation()"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>Join</a>`
          : '';

        const delBtn = e.db_id && e.source === 'calendar'
          ? `<button type="button" class="iconact danger" data-del-event="${e.db_id}" title="Delete event" style="width:22px;height:22px" onclick="event.stopPropagation()"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>`
          : '';

        return `
          <div class="arow" data-open-detail="${e.id}" style="display:flex;align-items:center;justify-content:space-between;gap:8px;cursor:pointer">
            <span class="time">${escHtml(e.time || 'All Day')}</span>
            <div class="txt" style="flex:1">
              ${escHtml(e.title)}
              <small>${escHtml(e.location ? `${e.location} · ` : '')}${escHtml(e.attendees || '')}</small>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
              ${meetBtn}
              <span class="type ${badgeClass}">${typeName}</span>
              ${delBtn}
            </div>
          </div>
        `;
      }).join('');
    }
  }
}

// --------------------------------------------------------------------------
// 2. DEDICATED FULL OPERATIONS CALENDAR RENDERER
// --------------------------------------------------------------------------
function renderFullCalendar() {
  const fullGrid = document.getElementById('fullCalendarGrid');
  const fullMonthLabel = document.getElementById('fullCalMonthLabel');
  const fullAgendaList = document.getElementById('fullDayAgendaList');
  const fullSelectedDateLabel = document.getElementById('fullCalSelectedDate');
  const upcomingList = document.getElementById('upcomingOpsList');

  if (!fullGrid) return;

  const year = CALENDAR_STATE.currentDate.getFullYear();
  const month = CALENDAR_STATE.currentDate.getMonth();

  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  if (fullMonthLabel) {
    fullMonthLabel.textContent = `${monthNames[month]} ${year}`;
  }

  const firstDayIndex = new Date(year, month, 1).getDay();
  const adjustedFirstDay = (firstDayIndex === 0 ? 6 : firstDayIndex - 1);
  const totalDays = new Date(year, month + 1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();

  const today = new Date();
  const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;
  const todayDate = today.getDate();

  let daysHtml = '';
  const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  const headersHtml = weekDays.map(d => `<div class="cal-head-day">${d}</div>`).join('');

  // Leading days from previous month
  for (let i = adjustedFirstDay - 1; i >= 0; i--) {
    daysHtml += `<div class="full-cal-day other-month"><span class="dnum">${prevMonthDays - i}</span></div>`;
  }

  // Active filter
  const filter = CALENDAR_STATE.activeFilter;

  // Days of current month
  for (let day = 1; day <= totalDays; day++) {
    const isToday = isCurrentMonth && day === todayDate;
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const isSelected = CALENDAR_STATE.selectedDate &&
      CALENDAR_STATE.selectedDate.getFullYear() === year &&
      CALENDAR_STATE.selectedDate.getMonth() === month &&
      CALENDAR_STATE.selectedDate.getDate() === day;

    let dayEvents = CALENDAR_STATE.events.filter(e => e.date === dateStr);
    if (filter !== 'all') {
      dayEvents = dayEvents.filter(e => e.event_type === filter);
    }

    let eventPills = '';
    if (dayEvents.length > 0) {
      eventPills = `<div class="full-cal-events-wrap">` +
        dayEvents.slice(0, 3).map(e => {
          let typeClass = 'meeting';
          if (e.event_type === 'status_meeting') typeClass = 'status_meeting';
          if (e.event_type === 'shoot') typeClass = 'shoot';
          if (e.event_type === 'deadline') typeClass = 'deadline';
          if (e.event_type === 'invoice') typeClass = 'invoice';

          const meetIcon = e.meet_link
            ? `<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:2px;flex-shrink:0"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>`
            : '';

          return `<div class="full-cal-event-pill ${typeClass}" data-open-detail="${e.id}" title="${escHtml(e.title)} (${escHtml(e.time || 'All Day')})">
            ${meetIcon}
            <span>${escHtml(e.time ? e.time.slice(0, 5) : '')}</span>
            <span style="overflow:hidden;text-overflow:ellipsis">${escHtml(e.title)}</span>
          </div>`;
        }).join('') +
        (dayEvents.length > 3 ? `<span class="full-cal-more">+${dayEvents.length - 3} more</span>` : '') +
        `</div>`;
    }

    daysHtml += `
      <div class="full-cal-day ${isToday ? 'today' : ''} ${isSelected ? 'selected' : ''}" data-cal-date="${dateStr}">
        <span class="dnum">${day}</span>
        ${eventPills}
      </div>
    `;
  }

  // Trailing days
  const totalCells = adjustedFirstDay + totalDays;
  const remainingCells = (totalCells % 7 === 0) ? 0 : 7 - (totalCells % 7);
  for (let i = 1; i <= remainingCells; i++) {
    daysHtml += `<div class="full-cal-day other-month"><span class="dnum">${i}</span></div>`;
  }

  fullGrid.innerHTML = headersHtml + daysHtml;

  // Selected Day Schedule
  if (fullAgendaList) {
    const selYear = CALENDAR_STATE.selectedDate.getFullYear();
    const selMonth = CALENDAR_STATE.selectedDate.getMonth();
    const selDay = CALENDAR_STATE.selectedDate.getDate();
    const selDateStr = `${selYear}-${String(selMonth + 1).padStart(2, '0')}-${String(selDay).padStart(2, '0')}`;

    if (fullSelectedDateLabel) {
      fullSelectedDateLabel.textContent = CALENDAR_STATE.selectedDate.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    }

    let selectedEvents = CALENDAR_STATE.events.filter(e => e.date === selDateStr);
    if (filter !== 'all') {
      selectedEvents = selectedEvents.filter(e => e.event_type === filter);
    }

    if (selectedEvents.length === 0) {
      fullAgendaList.innerHTML = `
        <div style="padding:18px;text-align:center;color:var(--muted);font-size:12px">
          No operations scheduled for this day.<br>
          <button type="button" class="btn primary" style="margin-top:10px;font-size:11.5px;padding:5px 12px" onclick="openScheduleModal('${selDateStr}')">+ Schedule Event</button>
        </div>
      `;
    } else {
      fullAgendaList.innerHTML = selectedEvents.map(e => {
        let badgeClass = 'tint-red';
        let typeName = 'Meeting';
        if (e.event_type === 'status_meeting') { badgeClass = 'tint-purple'; typeName = 'Status Meeting'; }
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice Due'; }

        const meetBtn = e.meet_link
          ? `<a href="${escHtml(e.meet_link)}" target="_blank" rel="noopener noreferrer" class="btn primary" style="padding:4px 9px;font-size:11px;background:#2563eb;border-color:#2563eb;text-decoration:none;gap:4px;white-space:nowrap" title="Join Google Meet" onclick="event.stopPropagation()"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>Join Meet</a>`
          : '';

        const delBtn = e.db_id && e.source === 'calendar'
          ? `<button type="button" class="iconact danger" data-del-event="${e.db_id}" title="Delete event" style="width:22px;height:22px" onclick="event.stopPropagation()"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>`
          : '';

        return `
          <div class="arow" data-open-detail="${e.id}" style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--line);cursor:pointer">
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                <span class="type ${badgeClass}" style="font-size:10px;padding:2px 6px">${typeName}</span>
                <span style="font-size:11.5px;font-weight:600;color:var(--ink)">${escHtml(e.time || 'All Day')}</span>
              </div>
              <div style="font-size:12.5px;font-weight:600;color:var(--ink);line-height:1.3">${escHtml(e.title)}</div>
              ${e.location ? `<div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg><span>${escHtml(e.location)}</span></div>` : ''}
              ${e.attendees ? `<div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;align-items:center;gap:4px"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>${escHtml(e.attendees)}</span></div>` : ''}
            </div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:2px">
              ${meetBtn}
              ${delBtn}
            </div>
          </div>
        `;
      }).join('');
    }
  }

  // 7-Day Operations Brief
  if (upcomingList) {
    const todayTimestamp = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
    const sevenDaysLater = todayTimestamp + 7 * 86400000;

    const upcomingEvents = CALENDAR_STATE.events.filter(e => {
      if (!e.date) return false;
      const [ey, em, ed] = e.date.split('-').map(Number);
      const evTimestamp = new Date(ey, em - 1, ed).getTime();
      return evTimestamp >= todayTimestamp && evTimestamp <= sevenDaysLater;
    }).sort((a, b) => a.date.localeCompare(b.date));

    if (upcomingEvents.length === 0) {
      upcomingList.innerHTML = `<div style="padding:12px;text-align:center;color:var(--muted);font-size:12px">No events in the next 7 days.</div>`;
    } else {
      upcomingList.innerHTML = upcomingEvents.slice(0, 6).map(e => {
        let badgeClass = 'tint-red';
        let typeName = 'Meeting';
        if (e.event_type === 'status_meeting') { badgeClass = 'tint-purple'; typeName = 'Status Meeting'; }
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice'; }

        return `
          <div class="arow" data-open-detail="${e.id}" style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 0;cursor:pointer">
            <span class="time" style="font-size:11px">${escHtml(e.date ? e.date.slice(5) : '')}</span>
            <div class="txt" style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <span style="font-weight:600">${escHtml(e.title)}</span>
            </div>
            <span class="type ${badgeClass}" style="font-size:10px">${typeName}</span>
          </div>
        `;
      }).join('');
    }
  }
}

window.renderFullCalendar = renderFullCalendar;
window.renderDashboardCalendar = renderDashboardCalendar;

// --------------------------------------------------------------------------
// 3. PERSONAL GOOGLE CALENDAR SYNC & OAUTH MODAL
// --------------------------------------------------------------------------
window.syncCalendarWithGoogle = async function() {
  const user = JMOS_STATE.currentUser;
  const nameEl = document.getElementById('googleAuthUserName');
  const emailEl = document.getElementById('googleAuthUserEmail');
  const avEl = document.getElementById('googleAuthUserAvatar');
  const alertEl = document.getElementById('googleAuthConnectedAlert');
  const currEmailEl = document.getElementById('googleAuthCurrentConnectedEmail');
  const inputEl = document.getElementById('personalGoogleEmailInput');
  const disconnectBtn = document.getElementById('modalDisconnectGoogleBtn');
  const confirmBtn = document.getElementById('confirmGoogleAuthBtn');

  if (nameEl && user) nameEl.textContent = user.name || 'Team Member';
  if (emailEl && user) emailEl.textContent = user.email || '';
  if (avEl && user) {
    avEl.textContent = user.initials || getInitials(user.name) || 'TM';
    if (user.color) avEl.style.background = user.color;
  }

  // Fetch latest personal sync status
  try {
    const statusRes = await JMOS_API.get('/calendar/sync-status');
    const isConnected = statusRes.connected && statusRes.account;
    const connectedAccount = statusRes.account;

    if (alertEl && currEmailEl && disconnectBtn && confirmBtn) {
      if (isConnected) {
        alertEl.style.display = 'block';
        currEmailEl.textContent = connectedAccount;
        disconnectBtn.style.display = 'inline-block';
        confirmBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Update &amp; Re-sync';
        if (inputEl) inputEl.value = connectedAccount;
      } else {
        alertEl.style.display = 'none';
        disconnectBtn.style.display = 'none';
        confirmBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Connect &amp; Sync Calendar';
        if (inputEl) inputEl.value = user?.email || '';
      }
    }
  } catch (_) {
    if (inputEl) inputEl.value = user?.email || '';
  }

  openModal('googleAuthModal');
};

window.openGoogleCalendarAuth = async function(accountEmail) {
  try {
    const query = accountEmail ? `?email=${encodeURIComponent(accountEmail)}` : '';
    const res = await JMOS_API.get(`/calendar/google-auth-url${query}`);
    if (res && res.url) {
      window.location.href = res.url;
    } else {
      window.location.href = `/calendar/google/redirect${query}`;
    }
  } catch (err) {
    const query = accountEmail ? `?email=${encodeURIComponent(accountEmail)}` : '';
    window.location.href = `/calendar/google/redirect${query}`;
  }
};

window.executeGoogleCalendarSync = async function(accountEmail) {
  if (!accountEmail || !accountEmail.includes('@')) {
    showToast('Valid Email Required', 'Please enter a valid personal Google email', true);
    return;
  }

  const syncBtn = document.getElementById('fullCalSyncBtn');
  if (syncBtn) {
    syncBtn.disabled = true;
    syncBtn.innerHTML = '<svg viewBox="0 0 24 24" class="spin"><path d="M23 4v6h-6M1 20v-6h6"/></svg> Syncing…';
  }

  try {
    const res = await JMOS_API.post('/calendar/sync', { account_email: accountEmail });
    if (res.status === 'success') {
      if (res.user && JMOS_STATE.currentUser) {
        JMOS_STATE.currentUser = { ...JMOS_STATE.currentUser, ...res.user };
      }
      showToast('Personal Calendar Synced', `Connected to ${res.account}`);
      window.updateCalendarSyncDisplay(res.account, true);
      await fetchCalendarEvents();
      renderDashboardCalendar();
      renderFullCalendar();
    } else {
      showToast('Sync Notice', res.message || 'Calendar refreshed');
    }
  } catch (err) {
    showToast('Sync Failed', err.message, true);
  } finally {
    if (syncBtn) {
      syncBtn.disabled = false;
    }
  }
};

window.disconnectPersonalGoogleCalendar = async function() {
  const confirmed = await window.showConfirmDialog({
    title: 'Disconnect Google Calendar?',
    subtitle: 'Google Workspace Integration',
    type: 'warning',
    confirmText: 'Disconnect Account',
    message: 'Are you sure you want to disconnect your personal Google Calendar account?',
    bullets: [
      'Scheduled meetings will no longer sync bidirectionally with your Google account.',
      'Existing system records in JMOS will remain safe and intact.'
    ]
  });
  if (!confirmed) return;

  try {
    const res = await JMOS_API.post('/calendar/disconnect', {});
    if (res.status === 'success') {
      if (JMOS_STATE.currentUser) {
        JMOS_STATE.currentUser.google_calendar_email = null;
        JMOS_STATE.currentUser.google_calendar_status = 'disconnected';
      }
      showToast('Calendar Disconnected', 'Personal Google Calendar unlinked');
      window.updateCalendarSyncDisplay(null, false);
      closeModal('googleAuthModal');
      await fetchCalendarEvents();
      renderDashboardCalendar();
      renderFullCalendar();
    }
  } catch (err) {
    showToast('Disconnect Failed', err.message, true);
  }
};

window.updateCalendarSyncDisplay = function(accountEmail, isConnected) {
  const syncLabel = document.getElementById('googleSyncAccountLabel');
  const actionBtn = document.getElementById('googleSyncActionBtn');
  const disconBtn = document.getElementById('googleDisconnectBtn');
  const fullCalBtn = document.getElementById('fullCalSyncBtn');

  if (syncLabel) {
    if (isConnected && accountEmail) {
      syncLabel.textContent = `Connected: ${accountEmail}`;
      syncLabel.style.color = 'var(--green)';
    } else {
      syncLabel.textContent = 'Not synced with personal calendar';
      syncLabel.style.color = 'var(--muted)';
    }
  }

  if (actionBtn) {
    actionBtn.textContent = (isConnected && accountEmail) ? 'Switch' : 'Connect';
  }

  if (disconBtn) {
    disconBtn.style.display = (isConnected && accountEmail) ? 'inline-block' : 'none';
  }

  if (fullCalBtn) {
    if (isConnected && accountEmail) {
      const shortEmail = accountEmail.length > 20 ? accountEmail.slice(0, 18) + '…' : accountEmail;
      fullCalBtn.innerHTML = `<svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Synced (${escHtml(shortEmail)})`;
      fullCalBtn.title = `Synced with personal account ${accountEmail}. Click to manage.`;
    } else {
      fullCalBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Sync Google Calendar';
      fullCalBtn.title = 'Connect personal Google Calendar';
    }
  }
};

async function loadCalendarSyncStatus() {
  try {
    const res = await JMOS_API.get('/calendar/sync-status');
    if (res.status === 'success') {
      window.updateCalendarSyncDisplay(res.account, res.connected);
    }
  } catch (_) {
    window.updateCalendarSyncDisplay(null, false);
  }
}
window.loadCalendarSyncStatus = loadCalendarSyncStatus;

// --------------------------------------------------------------------------
// 4. ATTENDEES PICKER (TEAM MEMBERS, CLIENTS & GUESTS)
// --------------------------------------------------------------------------
CALENDAR_STATE.selectedAttendees = [];

function initAttendeesPicker() {
  const teamContainer = document.getElementById('nevtTeamMembersList');
  const clientSelect = document.getElementById('nevtClientSelect');
  const addClientBtn = document.getElementById('nevtAddClientBtn');
  const addGuestBtn = document.getElementById('nevtAddGuestBtn');
  const guestInput = document.getElementById('nevtGuestInput');

  // Populate Team Members List
  if (teamContainer) {
    const users = JMOS_STATE.users || [];
    teamContainer.innerHTML = users.map(u => {
      const isSelected = CALENDAR_STATE.selectedAttendees.some(a => a.id === `user_${u.id}`);
      const ini = u.ini || getInitials(u.name);
      return `
        <div class="attendee-team-pill ${isSelected ? 'selected' : ''}" 
             data-team-id="${u.id}" 
             onclick="toggleTeamMemberAttendee(${u.id})"
             title="Add ${escHtml(u.name)} (${escHtml(u.title || u.role)})">
          <span class="attendee-team-pill-av" style="background:${u.color || 'var(--red)'}">
            ${escHtml(ini)}
          </span>
          <span>${escHtml(u.name)}</span>
          <span class="attendee-team-pill-check"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
        </div>
      `;
    }).join('');
  }

  // Populate Clients Dropdown
  if (clientSelect) {
    const clients = JMOS_STATE.clients || [];
    clientSelect.innerHTML = `
      <option value="">-- Choose from existing clients --</option>
      ${clients.map(c => `
        <option value="${c.id}">${escHtml(c.client_name)}${c.contact_person ? ` (${escHtml(c.contact_person)})` : ''}</option>
      `).join('')}
    `;
  }

  // Hook Add Client Button
  if (addClientBtn && !addClientBtn.dataset.bound) {
    addClientBtn.dataset.bound = 'true';
    addClientBtn.onclick = () => {
      const selId = clientSelect?.value;
      if (!selId) {
        if (typeof showToast === 'function') showToast('Select Client', 'Please choose a client from the dropdown', true);
        return;
      }
      const client = (JMOS_STATE.clients || []).find(c => String(c.id) === String(selId));
      if (!client) return;

      const attendeeId = `client_${client.id}`;
      if (CALENDAR_STATE.selectedAttendees.some(a => a.id === attendeeId)) {
        if (typeof showToast === 'function') showToast('Already Added', `${client.client_name} is already in attendees list`);
        return;
      }

      CALENDAR_STATE.selectedAttendees.push({
        id: attendeeId,
        name: client.client_name,
        type: 'client',
        color: '#f59e0b',
        ini: getInitials(client.client_name) || 'CL'
      });

      if (clientSelect) clientSelect.value = '';
      updateAttendeesDisplay();
    };
  }

  // Hook Add Guest Button & Enter Key
  if (addGuestBtn && !addGuestBtn.dataset.bound) {
    addGuestBtn.dataset.bound = 'true';
    const handleAddGuest = () => {
      const val = guestInput?.value.trim();
      if (!val) return;

      const attendeeId = `guest_${Date.now()}`;
      CALENDAR_STATE.selectedAttendees.push({
        id: attendeeId,
        name: val,
        type: 'guest',
        color: '#10b981',
        ini: getInitials(val) || 'G'
      });

      if (guestInput) guestInput.value = '';
      updateAttendeesDisplay();
    };

    addGuestBtn.onclick = handleAddGuest;
    if (guestInput) {
      guestInput.onkeydown = (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          handleAddGuest();
        }
      };
    }
  }

  updateAttendeesDisplay();
}

window.toggleTeamMemberAttendee = function(userId) {
  const user = (JMOS_STATE.users || []).find(u => u.id === userId);
  if (!user) return;

  const attendeeId = `user_${user.id}`;
  const existingIndex = CALENDAR_STATE.selectedAttendees.findIndex(a => a.id === attendeeId);

  if (existingIndex >= 0) {
    CALENDAR_STATE.selectedAttendees.splice(existingIndex, 1);
  } else {
    CALENDAR_STATE.selectedAttendees.push({
      id: attendeeId,
      name: user.name,
      type: 'team',
      color: user.color || 'var(--red)',
      ini: user.ini || getInitials(user.name)
    });
  }

  updateAttendeesDisplay();
};

window.removeAttendee = function(id) {
  CALENDAR_STATE.selectedAttendees = CALENDAR_STATE.selectedAttendees.filter(a => a.id !== id);
  updateAttendeesDisplay();
};

function updateAttendeesDisplay() {
  const chipsWrap = document.getElementById('nevtSelectedChipsWrap');
  const countPill = document.getElementById('nevtAttendeeCountPill');
  const hiddenInput = document.getElementById('nevtAttendees');
  const list = CALENDAR_STATE.selectedAttendees || [];

  if (countPill) {
    countPill.textContent = `${list.length} selected`;
  }

  if (hiddenInput) {
    hiddenInput.value = list.map(a => a.name).join(', ');
  }

  if (chipsWrap) {
    if (list.length === 0) {
      chipsWrap.innerHTML = '<span class="attendee-chips-empty">No attendees selected yet. Pick team members below or add a guest.</span>';
    } else {
      chipsWrap.innerHTML = list.map(att => `
        <div class="attendee-chip" id="attChip_${att.id}">
          <span class="attendee-chip-av" style="background:${att.color || 'var(--red)'}">${escHtml(att.ini || 'A')}</span>
          <span>${escHtml(att.name)}</span>
          <span class="attendee-chip-tag ${att.type}">${att.type}</span>
          <button type="button" class="attendee-chip-del" onclick="removeAttendee('${att.id}')" title="Remove">&times;</button>
        </div>
      `).join('');
    }
  }

  // Update .selected state on team member pills
  const pills = document.querySelectorAll('.attendee-team-pill');
  pills.forEach(pill => {
    const uid = pill.getAttribute('data-team-id');
    const isSel = list.some(a => a.id === `user_${uid}`);
    if (isSel) pill.classList.add('selected');
    else pill.classList.remove('selected');
  });
}

// --------------------------------------------------------------------------
// 5. EVENT MODAL TRIGGER
// --------------------------------------------------------------------------
window.openScheduleModal = function(prefillDate) {
  const dateInput = document.getElementById('nevtDate');
  if (dateInput) {
    dateInput.value = prefillDate || new Date().toISOString().split('T')[0];
  }
  ['nevtTitle', 'nevtLocation', 'nevtDesc', 'nevtGuestInput'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });

  // Reset & re-initialize attendees picker
  CALENDAR_STATE.selectedAttendees = [];
  initAttendeesPicker();

  openModal('eventModal');
};

window.openScheduleShootModal = function(projectName = '', clientName = '', prefillDate = null) {
  if (typeof window.openScheduleModal === 'function') {
    window.openScheduleModal(prefillDate);
  } else {
    window.openModal('eventModal');
  }

  const typeSelect = document.getElementById('nevtType');
  if (typeSelect) {
    typeSelect.value = 'shoot';
  }

  const titleInput = document.getElementById('nevtTitle');
  if (titleInput) {
    if (projectName) {
      titleInput.value = `Shoot: ${projectName}`;
    } else {
      titleInput.value = 'Production Shoot';
    }
  }

  const modalTitle = document.getElementById('eventModalTitle');
  if (modalTitle) {
    modalTitle.textContent = 'Schedule Production Shoot';
  }

  if (clientName) {
    const clientSelect = document.getElementById('nevtClientSelect');
    if (clientSelect && clientSelect.options) {
      for (let i = 0; i < clientSelect.options.length; i++) {
        if (clientSelect.options[i].text.toLowerCase().includes(clientName.toLowerCase())) {
          clientSelect.selectedIndex = i;
          break;
        }
      }
    }
  }

  window.openModal('eventModal');
};

window.openShootModal = window.openScheduleShootModal;

// --------------------------------------------------------------------------
// 6. EVENT LISTENERS & INITIALIZATION
// --------------------------------------------------------------------------
function initCalendar() {
  initAttendeesPicker();

  // Dashboard navigation buttons
  const prevBtn = document.getElementById('calPrevBtn');
  const nextBtn = document.getElementById('calNextBtn');
  const todayBtn = document.getElementById('calTodayBtn');

  if (prevBtn) {
    prevBtn.onclick = () => {
      CALENDAR_STATE.currentDate.setMonth(CALENDAR_STATE.currentDate.getMonth() - 1);
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  if (nextBtn) {
    nextBtn.onclick = () => {
      CALENDAR_STATE.currentDate.setMonth(CALENDAR_STATE.currentDate.getMonth() + 1);
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  if (todayBtn) {
    todayBtn.onclick = () => {
      CALENDAR_STATE.currentDate = new Date();
      CALENDAR_STATE.selectedDate = new Date();
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  // Full Calendar navigation buttons
  const fullPrevBtn = document.getElementById('fullCalPrevBtn');
  const fullNextBtn = document.getElementById('fullCalNextBtn');
  const fullTodayBtn = document.getElementById('fullCalTodayBtn');

  if (fullPrevBtn) {
    fullPrevBtn.onclick = () => {
      CALENDAR_STATE.currentDate.setMonth(CALENDAR_STATE.currentDate.getMonth() - 1);
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  if (fullNextBtn) {
    fullNextBtn.onclick = () => {
      CALENDAR_STATE.currentDate.setMonth(CALENDAR_STATE.currentDate.getMonth() + 1);
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  if (fullTodayBtn) {
    fullTodayBtn.onclick = () => {
      CALENDAR_STATE.currentDate = new Date();
      CALENDAR_STATE.selectedDate = new Date();
      renderDashboardCalendar();
      renderFullCalendar();
    };
  }

  // Filter Pill clicks
  document.addEventListener('click', (e) => {
    const pill = e.target.closest('[data-cal-filter]');
    if (pill) {
      document.querySelectorAll('[data-cal-filter]').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      CALENDAR_STATE.activeFilter = pill.getAttribute('data-cal-filter') || 'all';
      renderFullCalendar();
    }
  });

  // Date selection click handler on calendar grids
  document.addEventListener('click', (e) => {
    const dayCell = e.target.closest('[data-cal-date]');
    if (dayCell) {
      const dateStr = dayCell.getAttribute('data-cal-date');
      const [y, m, d] = dateStr.split('-').map(Number);
      CALENDAR_STATE.selectedDate = new Date(y, m - 1, d);
      renderDashboardCalendar();
      renderFullCalendar();
    }
  });

  // Save new calendar event submission
  document.addEventListener('click', async (e) => {
    if (e.target.closest('#saveEventBtn')) {
      const btn = e.target.closest('#saveEventBtn');
      const title = document.getElementById('nevtTitle')?.value.trim();
      const date = document.getElementById('nevtDate')?.value;
      const time = document.getElementById('nevtTime')?.value || '10:00';
      const endTime = document.getElementById('nevtEndTime')?.value;
      const type = document.getElementById('nevtType')?.value || 'meeting';
      const location = document.getElementById('nevtLocation')?.value.trim();
      const attendees = document.getElementById('nevtAttendees')?.value.trim();
      const desc = document.getElementById('nevtDesc')?.value.trim();
      const addMeet = document.getElementById('nevtAddMeet') ? document.getElementById('nevtAddMeet').checked : true;

      if (!title || !date) {
        showToast('Title and Date Required', 'Please enter event title and date', true);
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Scheduling…';

      try {
        const startDateTime = `${date}T${time}:00`;
        const endDateTime = endTime ? `${date}T${endTime}:00` : null;

        const res = await JMOS_API.post('/calendar/events', {
          title,
          event_type: type,
          start_time: startDateTime,
          end_time: endDateTime,
          location,
          attendees,
          description: desc,
          generate_meet: addMeet ? 1 : 0
        });

        closeModal('eventModal');
        const hasMeet = res.data && res.data.meet_link;
        showToast(
          'Event Scheduled',
          hasMeet ? `${title} scheduled with Google Meet video link` : `${title} added to calendar`
        );
        await fetchCalendarEvents();
        renderDashboardCalendar();
        renderFullCalendar();
      } catch (err) {
        showToast('Scheduling Failed', err.message, true);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Schedule event';
      }
    }
  });

  // Delegate clicks on calendar event pills & agenda rows to open details modal
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-open-detail]');
    if (trigger && !e.target.closest('a') && !e.target.closest('[data-del-event]') && !e.target.closest('button')) {
      const eventId = trigger.getAttribute('data-open-detail');
      if (eventId && typeof window.openEventDetailModal === 'function') {
        window.openEventDetailModal(eventId);
      }
    }
  });

  // Delete calendar event delegation
  document.addEventListener('click', async (e) => {
    const delEventBtn = e.target.closest('[data-del-event]');
    if (delEventBtn) {
      const dbId = delEventBtn.getAttribute('data-del-event');
      const confirmed = await window.showConfirmDialog({
        title: 'Delete Calendar Event?',
        subtitle: 'Schedule Modification',
        type: 'danger',
        confirmText: 'Remove Event',
        message: 'Remove this scheduled event from the central calendar?',
        bullets: [
          'All attendees will be notified of cancellation.',
          'Synchronized Google Calendar events will be removed.'
        ]
      });
      if (confirmed) {
        delEventBtn.disabled = true;
        try {
          await JMOS_API.delete(`/calendar/events/${dbId}`);
          showToast('Event Removed', 'Calendar updated');
          await fetchCalendarEvents();
          renderDashboardCalendar();
          renderFullCalendar();
        } catch (err) {
          showToast('Delete Failed', err.message, true);
        }
      }
    }
  });

  // Personal Google Calendar OAuth Handlers
  const autofillBtn = document.getElementById('autofillMyEmailBtn');
  if (autofillBtn) {
    autofillBtn.onclick = () => {
      const emailInput = document.getElementById('personalGoogleEmailInput');
      if (emailInput && JMOS_STATE.currentUser?.email) {
        emailInput.value = JMOS_STATE.currentUser.email;
        emailInput.focus();
      }
    };
  }

  const modalDisconBtn = document.getElementById('modalDisconnectGoogleBtn');
  if (modalDisconBtn) {
    modalDisconBtn.onclick = async () => {
      await window.disconnectPersonalGoogleCalendar();
    };
  }

  const confirmAuthBtn = document.getElementById('confirmGoogleAuthBtn');
  if (confirmAuthBtn) {
    confirmAuthBtn.onclick = async () => {
      const emailInput = document.getElementById('personalGoogleEmailInput');
      const email = emailInput?.value.trim() || '';

      confirmAuthBtn.disabled = true;
      const originalText = confirmAuthBtn.innerHTML;
      confirmAuthBtn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" class="spin"><path d="M23 4v6h-6M1 20v-6h6"/></svg> Opening Google Auth…';

      try {
        closeModal('googleAuthModal');
        await window.openGoogleCalendarAuth(email);
      } catch (err) {
        showToast('Google Auth Notice', err.message, true);
      } finally {
        confirmAuthBtn.disabled = false;
        confirmAuthBtn.innerHTML = originalText;
      }
    };
  }

  // Handle Google OAuth callback notification if redirected back with success
  try {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('google_sync') === 'success') {
      showToast('Google Calendar Connected', 'Your Google Calendar has been authenticated and connected successfully.');
      urlParams.delete('google_sync');
      const cleanUrl = window.location.pathname + (urlParams.toString() ? `?${urlParams.toString()}` : '');
      window.history.replaceState({}, document.title, cleanUrl);
    }
  } catch (_) {}

  loadCalendarSyncStatus();
  fetchCalendarEvents().then(() => {
    renderDashboardCalendar();
    renderFullCalendar();
  });
}

// --------------------------------------------------------------------------
// 6. EVENT DETAILS & GOOGLE MEET MODAL CONTROLLER
// --------------------------------------------------------------------------
window.openEventDetailModal = function(eventId) {
  const event = CALENDAR_STATE.events.find(x => x.id === eventId || String(x.db_id) === String(eventId));
  if (!event) return;

  const titleEl = document.getElementById('eventDetailTitle');
  const badgeEl = document.getElementById('eventDetailTypeBadge');
  const dateEl = document.getElementById('eventDetailDate');
  const timeEl = document.getElementById('eventDetailTime');
  const locRow = document.getElementById('eventDetailLocRow');
  const locEl = document.getElementById('eventDetailLocation');
  const attRow = document.getElementById('eventDetailAttRow');
  const attEl = document.getElementById('eventDetailAttendees');
  const descRow = document.getElementById('eventDetailDescRow');
  const descEl = document.getElementById('eventDetailDesc');
  const meetBox = document.getElementById('eventDetailMeetBox');
  const noMeetBox = document.getElementById('eventDetailNoMeetBox');
  const joinBtn = document.getElementById('eventDetailJoinBtn');
  const copyBtn = document.getElementById('eventDetailCopyMeetBtn');
  const meetLinkText = document.getElementById('eventDetailMeetLinkText');
  const genMeetBtn = document.getElementById('eventDetailGenerateMeetBtn');
  const delBtn = document.getElementById('eventDetailDeleteBtn');

  if (titleEl) titleEl.textContent = event.title;

  let badgeClass = 'tint-red';
  let typeName = 'Client Meeting';
  if (event.event_type === 'status_meeting') { badgeClass = 'tint-purple'; typeName = 'Status Meeting'; }
  if (event.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Production Shoot'; }
  if (event.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Project Deadline'; }
  if (event.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice Due Date'; }
  if (badgeEl) {
    badgeEl.className = `type ${badgeClass}`;
    badgeEl.textContent = typeName;
  }

  if (dateEl) {
    let dStr = event.date;
    try {
      const [y, m, d] = event.date.split('-').map(Number);
      dStr = new Date(y, m - 1, d).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    } catch (_) {}
    dateEl.textContent = dStr || 'Scheduled Date';
  }

  if (timeEl) {
    timeEl.textContent = event.time ? `${event.time} EAT` : 'All Day';
  }

  if (locEl && locRow) {
    if (event.location) {
      locRow.style.display = 'flex';
      locEl.textContent = event.location;
    } else {
      locRow.style.display = 'none';
    }
  }

  if (attEl && attRow) {
    if (event.attendees) {
      attRow.style.display = 'flex';
      attEl.textContent = event.attendees;
    } else {
      attRow.style.display = 'none';
    }
  }

  if (descEl && descRow) {
    if (event.description) {
      descRow.style.display = 'flex';
      descEl.textContent = event.description;
    } else {
      descRow.style.display = 'none';
    }
  }

  // Google Meet Link Box Setup
  if (event.meet_link) {
    if (meetBox) meetBox.style.display = 'block';
    if (noMeetBox) noMeetBox.style.display = 'none';
    if (joinBtn) joinBtn.href = event.meet_link;
    if (meetLinkText) {
      meetLinkText.href = event.meet_link;
      meetLinkText.textContent = event.meet_link;
    }
    if (copyBtn) {
      copyBtn.onclick = () => {
        navigator.clipboard.writeText(event.meet_link);
        showToast('Meet Link Copied', 'Google Meet URL copied to clipboard');
      };
    }
  } else {
    if (meetBox) meetBox.style.display = 'none';
    if (noMeetBox) {
      if (event.source === 'calendar' && event.db_id) {
        noMeetBox.style.display = 'block';
        if (genMeetBtn) {
          genMeetBtn.onclick = async () => {
            genMeetBtn.disabled = true;
            genMeetBtn.textContent = 'Generating…';
            try {
              const res = await JMOS_API.post(`/calendar/events/${event.db_id}/meet`, {});
              if (res.status === 'success' && res.data?.meet_link) {
                event.meet_link = res.data.meet_link;
                showToast('Google Meet Generated', 'Video conference room created');
                window.openEventDetailModal(event.id);
                renderDashboardCalendar();
                renderFullCalendar();
              }
            } catch (err) {
              showToast('Notice', err.message, true);
            } finally {
              genMeetBtn.disabled = false;
              genMeetBtn.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>Generate Google Meet Link';
            }
          };
        }
      } else {
        noMeetBox.style.display = 'none';
      }
    }
  }

  // Delete Action Setup
  if (delBtn) {
    if (event.source === 'calendar' && event.db_id) {
      delBtn.style.display = 'inline-flex';
      delBtn.onclick = async () => {
        const confirmed = await window.showConfirmDialog({
          title: 'Delete Calendar Event?',
          subtitle: 'Schedule Modification',
          type: 'danger',
          confirmText: 'Remove Event',
          message: `Remove "<b>${escHtml(event.title || 'Event')}</b>" from the central calendar?`,
          bullets: [
            'All attendees will be notified of cancellation.',
            'Synchronized Google Calendar events will be removed.'
          ]
        });
        if (confirmed) {
          delBtn.disabled = true;
          try {
            await JMOS_API.delete(`/calendar/events/${event.db_id}`);
            closeModal('eventDetailModal');
            showToast('Event Removed', 'Calendar updated');
            await fetchCalendarEvents();
            renderDashboardCalendar();
            renderFullCalendar();
          } catch (err) {
            showToast('Delete Failed', err.message, true);
          } finally {
            delBtn.disabled = false;
          }
        }
      };
    } else {
      delBtn.style.display = 'none';
    }
  }

  openModal('eventDetailModal');
};
