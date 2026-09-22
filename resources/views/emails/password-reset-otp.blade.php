@extends('emails.layout')

@section('content')
  <div class="headline">Password Reset Verification Code</div>
  <p class="body-text">
    Hello <strong>{{ $userName ?? 'Team Member' }}</strong>,<br>
    We received a request to reset your password for your <strong>JMOS (Jeota Media Operating System)</strong> account.
  </p>

  <p class="body-text" style="margin-bottom:12px">
    Use the 6-digit One-Time Password (OTP) below to complete your password reset:
  </p>

  <div style="text-align:center;margin:28px 0;background:#FFF4F2;border:2px dashed #C52523;border-radius:12px;padding:20px;">
    <div style="font-family:'IBM Plex Mono',monospace,sans-serif;font-size:36px;font-weight:700;letter-spacing:8px;color:#C52523;">
      {{ $otp }}
    </div>
    <div style="font-size:12px;color:#8F2B26;margin-top:6px;font-weight:500;">
      Valid for {{ $expiresInMinutes ?? 15 }} minutes
    </div>
  </div>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Account Email</span>
      <span class="info-val">{{ $email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Security Expiry</span>
      <span class="info-val">{{ $expiresInMinutes ?? 15 }} Minutes</span>
    </div>
    @if(!empty($ipAddress))
    <div class="info-row">
      <span class="info-label">Request IP</span>
      <span class="info-val" style="font-family:monospace">{{ $ipAddress }}</span>
    </div>
    @endif
  </div>

  <p class="body-text" style="font-size:13px;color:#6E6763;line-height:1.5;">
    <strong>Security Notice:</strong> If you did not initiate this request, someone else may have entered your email address. You can safely ignore this email; your existing password will remain unchanged.
  </p>
@endsection
