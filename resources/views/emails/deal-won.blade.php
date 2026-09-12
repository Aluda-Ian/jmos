@extends('emails.layout')

@section('content')
  <div class="headline">🎉 Deal Won — Project Kickoff Alert!</div>
  <p class="body-text">
    Team,<br>
    Great news! We have successfully closed a new project in the <strong>JMOS Sales Funnel</strong>. The automated project cascade has been initiated.
  </p>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Deal / Project</span>
      <span class="info-val">{{ $dealTitle ?? 'Brand Film Campaign' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Client Name</span>
      <span class="info-val">{{ $clientName ?? 'Direct Client' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Contract Value</span>
      <span class="info-val" style="color:#1C7A4E;font-size:15px">KES {{ number_format($value ?? 0) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">60% Deposit Invoice</span>
      <span class="info-val">KES {{ number_format(($value ?? 0) * 0.6) }} (Drafted)</span>
    </div>
    <div class="info-row">
      <span class="info-label">Automated Recipe</span>
      <span class="info-val"><span class="badge badge-green">8 Tasks Generated &amp; Assigned</span></span>
    </div>
  </div>

  <p class="body-text" style="font-size:13.5px;color:#6E6763">
    The project is now live on the JMOS delivery board. Lead editors, cinematographers, and production managers should review their task boards and scheduled shoot dates.
  </p>

  <div style="text-align:center;margin:24px 0 10px">
    <a href="{{ url('/') }}" class="btn-action">View Live Project in JMOS &rarr;</a>
  </div>
@endsection
