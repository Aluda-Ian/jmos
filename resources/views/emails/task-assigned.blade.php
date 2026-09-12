@extends('emails.layout')

@section('content')
  <div class="headline">New Task Assigned to You</div>
  <p class="body-text">
    Hello <strong>{{ $assigneeName ?? 'Team Member' }}</strong>,<br>
    You have been assigned a new task in the <strong>JMOS</strong> workflow.
  </p>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Task Title</span>
      <span class="info-val">{{ $taskTitle ?? 'Video Editing & Grade' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Project / Client</span>
      <span class="info-val">{{ $projectName ?? 'General Production' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Initial Stage</span>
      <span class="info-val"><span class="badge badge-amber">{{ ucfirst($stage ?? 'To do') }}</span></span>
    </div>
    @if(!empty($deadline))
    <div class="info-row">
      <span class="info-label">Target Deadline</span>
      <span class="info-val" style="color:#C52523">{{ $deadline }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Assigned By</span>
      <span class="info-val">{{ $assignedBy ?? 'Production Lead' }}</span>
    </div>
  </div>

  <p class="body-text" style="font-size:13.5px;color:#6E6763">
    Please log in to your JMOS workspace to review the creative brief, access project assets, and advance the task stage when work begins.
  </p>

  <div style="text-align:center;margin:24px 0 10px">
    <a href="{{ url('/') }}" class="btn-action">Open Task in JMOS &rarr;</a>
  </div>
@endsection
