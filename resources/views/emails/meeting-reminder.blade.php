@extends('emails.layout')

@section('content')
  <div class="headline">📅 Upcoming Event Reminder</div>
  <p class="body-text">
    Hello <strong>{{ $recipientName ?? 'Team Member' }}</strong>,<br>
    This is an automated reminder regarding your upcoming schedule in <strong>JMOS &amp; Google Calendar</strong>.
  </p>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Event / Meeting</span>
      <span class="info-val">{{ $eventTitle ?? 'Client Briefing & Discovery' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Event Type</span>
      <span class="info-val"><span class="badge badge-red">{{ ucfirst($eventType ?? 'Meeting') }}</span></span>
    </div>
    <div class="info-row">
      <span class="info-label">Date &amp; Time</span>
      <span class="info-val" style="color:#C52523">{{ $eventDateTime ?? 'Today at 10:00 AM' }}</span>
    </div>
    @if(!empty($location))
    <div class="info-row">
      <span class="info-label">Location / Link</span>
      <span class="info-val">{{ $location }}</span>
    </div>
    @endif
    @if(!empty($attendees))
    <div class="info-row">
      <span class="info-label">Attendees</span>
      <span class="info-val">{{ $attendees }}</span>
    </div>
    @endif
  </div>

  @if(!empty($description))
  <p class="body-text" style="background:#FAF7F6;padding:12px 16px;border-left:3px solid #C52523;border-radius:4px;font-size:13.5px">
    <strong>Agenda / Notes:</strong><br>{{ $description }}
  </p>
  @endif

  <div style="text-align:center;margin:24px 0 10px">
    <a href="{{ url('/') }}" class="btn-action">View Calendar in JMOS &rarr;</a>
  </div>
@endsection
