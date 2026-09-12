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
    const res = await JMOS_API.get('/calendar/events');
    if (res.status === 'success' && Array.isArray(res.data)) {
      CALENDAR_STATE.events = res.data;
    }
  } catch (err) {
    console.warn('Calendar events fetch note:', err.message);
  }
}

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
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice Due'; }

        const delBtn = e.db_id && e.source === 'calendar'
          ? `<button type="button" class="iconact danger" data-del-event="${e.db_id}" title="Delete event" style="width:22px;height:22px"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>`
          : '';

        return `
          <div class="arow" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <span class="time">${escHtml(e.time || 'All Day')}</span>
            <div class="txt" style="flex:1">
              ${escHtml(e.title)}
              <small>${escHtml(e.location ? `${e.location} · ` : '')}${escHtml(e.attendees || '')}</small>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
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
          if (e.event_type === 'shoot') typeClass = 'shoot';
          if (e.event_type === 'deadline') typeClass = 'deadline';
          if (e.event_type === 'invoice') typeClass = 'invoice';

          return `<div class="full-cal-event-pill ${typeClass}" title="${escHtml(e.title)} (${escHtml(e.time || 'All Day')})">
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
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice Due'; }

        const delBtn = e.db_id && e.source === 'calendar'
          ? `<button type="button" class="iconact danger" data-del-event="${e.db_id}" title="Delete event" style="width:22px;height:22px"><svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></button>`
          : '';

        return `
          <div class="arow" style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--line)">
            <div style="flex:1">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px">
                <span class="type ${badgeClass}" style="font-size:10px;padding:2px 6px">${typeName}</span>
                <span style="font-size:11.5px;font-weight:600;color:var(--ink)">${escHtml(e.time || 'All Day')}</span>
              </div>
              <div style="font-size:12.5px;font-weight:600;color:var(--ink);line-height:1.3">${escHtml(e.title)}</div>
              ${e.location ? `<div style="font-size:11px;color:var(--muted);margin-top:2px">📍 ${escHtml(e.location)}</div>` : ''}
              ${e.attendees ? `<div style="font-size:11px;color:var(--muted);margin-top:2px">👥 ${escHtml(e.attendees)}</div>` : ''}
            </div>
            ${delBtn}
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
        if (e.event_type === 'shoot') { badgeClass = 'tint-amber'; typeName = 'Shoot'; }
        if (e.event_type === 'deadline') { badgeClass = 'tint-green'; typeName = 'Deadline'; }
        if (e.event_type === 'invoice') { badgeClass = 'tint-blue'; typeName = 'Invoice'; }

        return `
          <div class="arow" style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 0">
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
// 3. GOOGLE CALENDAR SYNC HELPER
// --------------------------------------------------------------------------
window.syncCalendarWithGoogle = async function() {
  const syncBtn = document.getElementById('fullCalSyncBtn');
  if (syncBtn) {
    syncBtn.disabled = true;
    syncBtn.innerHTML = '<svg viewBox="0 0 24 24" class="spin"><path d="M23 4v6h-6M1 20v-6h6"/></svg> Syncing…';
  }
  try {
    const res = await JMOS_API.post('/calendar/sync', {});
    if (res.status === 'success') {
      showToast('Google Calendar Synced', `${res.synced_count || 'All'} events refreshed & synchronized`);
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
      syncBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>Sync Google Calendar';
    }
  }
};

// --------------------------------------------------------------------------
// 4. EVENT MODAL TRIGGER
// --------------------------------------------------------------------------
window.openScheduleModal = function(prefillDate) {
  const dateInput = document.getElementById('nevtDate');
  if (dateInput) {
    dateInput.value = prefillDate || new Date().toISOString().split('T')[0];
  }
  ['nevtTitle', 'nevtLocation', 'nevtAttendees', 'nevtDesc'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  openModal('eventModal');
};

// --------------------------------------------------------------------------
// 5. EVENT LISTENERS & INITIALIZATION
// --------------------------------------------------------------------------
function initCalendar() {
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

      if (!title || !date) {
        showToast('Title and Date Required', 'Please enter event title and date', true);
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Scheduling…';

      try {
        const startDateTime = `${date}T${time}:00`;
        const endDateTime = endTime ? `${date}T${endTime}:00` : null;

        await JMOS_API.post('/calendar/events', {
          title,
          event_type: type,
          start_time: startDateTime,
          end_time: endDateTime,
          location,
          attendees,
          description: desc
        });

        closeModal('eventModal');
        showToast('Event Scheduled', `${title} added to calendar & synced with Google Calendar`);
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

  // Delete calendar event delegation
  document.addEventListener('click', async (e) => {
    const delEventBtn = e.target.closest('[data-del-event]');
    if (delEventBtn) {
      const dbId = delEventBtn.getAttribute('data-del-event');
      if (confirm('Remove this event from calendar?')) {
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

  fetchCalendarEvents().then(() => {
    renderDashboardCalendar();
    renderFullCalendar();
  });
}
