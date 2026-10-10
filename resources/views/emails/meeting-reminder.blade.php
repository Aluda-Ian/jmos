@extends('emails.layout')

@section('content')
  <div class="headline">📅 {{ $eventTitle ?? 'Upcoming meeting' }} {{ !empty($whenLabel) ? '— '.$whenLabel : '' }}</div>
  <p class="body-text">
    Hello <strong>{{ $recipientName ?? 'there' }}</strong>,<br>
    @if(($reminderKind ?? '') === 'hour')
      A quick reminder: <strong>{{ $eventTitle }}</strong> {{ $whenLabel ?? 'starts soon' }}.
    @elseif(($reminderKind ?? '') === 'day')
      This is a reminder that <strong>{{ $eventTitle }}</strong> is scheduled for {{ $whenLabel ?? 'tomorrow' }}.
    @else
      This is an automated reminder regarding your upcoming schedule with <strong>Jeota Media</strong>.
    @endif
  </p>

  @if(!empty($meetLink))
    <div style="text-align:center;margin:0 0 22px">
      <a href="{{ $meetLink }}" class="btn-action" style="background-color:#1a73e8;color:#FFFFFF;text-decoration:none;display:inline-block;padding:13px 28px;border-radius:8px;font-weight:600;font-size:14px">🎥 Join Google Meet</a>
      <div style="font-size:12px;color:#6E6763;margin-top:8px;word-break:break-all">{{ $meetLink }}</div>
    </div>
  @endif

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

  <p class="body-text" style="font-size:12.5px;color:#6E6763;margin-top:20px">
    Sent by {{ config('jeota.company') }} · {{ config('jeota.email') }} · {{ config('jeota.phone') }}
  </p>
@endsection
