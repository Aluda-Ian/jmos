@extends('emails.layout')

@section('content')
  <div class="headline">💬 New Message in JMOS Team Chat</div>
  <p class="body-text">
    Hello <strong>{{ $recipientName ?? 'Team Member' }}</strong>,<br>
    <strong>{{ $senderName ?? 'A colleague' }}</strong> has started a conversation with you on <strong>JMOS Team Hub</strong>.
  </p>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Sender</span>
      <span class="info-val"><strong>{{ $senderName ?? 'Team Member' }}</strong> ({{ $senderRole ?? 'Jeota Media' }})</span>
    </div>
    <div class="info-row">
      <span class="info-label">Conversation</span>
      <span class="info-val"><span class="badge badge-red">{{ $threadTitle ?? 'Direct 1-on-1 Chat' }}</span></span>
    </div>
    <div class="info-row">
      <span class="info-label">Sent At</span>
      <span class="info-val" style="color:#C52523">{{ $sentAt ?? date('M j, Y H:i') }}</span>
    </div>
  </div>

  <div style="background:#FAF7F6;padding:16px 20px;border-left:4px solid #C52523;border-radius:6px;margin:20px 0;font-size:14px;color:#1A1817;line-height:1.5">
    <strong style="color:#8A1B1A;font-size:12px;text-transform:uppercase;letter-spacing:0.5px">Message Snippet:</strong>
    <p style="margin:8px 0 0;font-style:italic">“{{ $messageText ?? 'New message preview' }}”</p>
  </div>

  <div style="text-align:center;margin:28px 0 12px">
    <a href="{{ url('/') }}" class="btn-action">Open Chat &amp; Reply in JMOS &rarr;</a>
  </div>
@endsection
