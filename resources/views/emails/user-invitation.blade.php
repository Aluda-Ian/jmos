@extends('emails.layout')

@section('content')
  <div class="headline">Welcome to JMOS, {{ $userName }}! 🎉</div>
  <p class="body-text">
    An account has been created for you on the <strong>Jeota Media Operating System (JMOS)</strong>. You have been granted access as <strong>{{ $role ?? 'Team Member' }}</strong> in the <strong>{{ $department ?? 'Production' }}</strong> department.
  </p>

  <p class="body-text" style="margin-bottom:14px">
    To activate your workspace and choose your personal password, use the direct setup button or your 6-digit verification code below:
  </p>

  <!-- CTA Button -->
  <div style="text-align:center;margin:24px 0 20px;">
    <a href="{{ $setupUrl ?? url('/?email=' . urlencode($email ?? '') . '&otp=' . ($otp ?? '')) }}" target="_blank" style="display:inline-block;background:#C52523;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;padding:13px 28px;border-radius:8px;box-shadow:0 4px 14px rgba(197,37,35,0.25);">
      Set Up Your Password &amp; Sign In &rarr;
    </a>
  </div>

  <!-- Verification Code Box -->
  <div style="text-align:center;margin:20px 0;background:#FFF4F2;border:2px dashed #C52523;border-radius:12px;padding:18px;">
    <div style="font-size:11px;color:#8F2B26;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:6px">Your 6-Digit Password Setup Code</div>
    <div style="font-family:'IBM Plex Mono',monospace,sans-serif;font-size:34px;font-weight:700;letter-spacing:8px;color:#C52523;">
      {{ $otp ?? '000000' }}
    </div>
    <div style="font-size:11.5px;color:#8F2B26;margin-top:6px;font-weight:500;">
      Valid for {{ $expiresInHours ?? 48 }} hours
    </div>
  </div>

  <!-- Account Details Summary Card -->
  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Login Email</span>
      <span class="info-val">{{ $email }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Role / Access Level</span>
      <span class="info-val">{{ ucfirst($role ?? 'Team Member') }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Department</span>
      <span class="info-val">{{ $department ?? 'Production' }}</span>
    </div>
    @if(!empty($invitedBy))
    <div class="info-row">
      <span class="info-label">Invited By</span>
      <span class="info-val">{{ $invitedBy }}</span>
    </div>
    @endif
  </div>

  <!-- Instructions -->
  <div style="margin:24px 0;padding:16px 20px;background:#F8FAFC;border-radius:10px;border:1px solid #E2E8F0;">
    <div style="font-weight:700;font-size:13px;color:#1E293B;margin-bottom:8px">Getting Started with JMOS:</div>
    <ol style="margin:0;padding-left:18px;font-size:13px;color:#475569;line-height:1.6">
      <li>Click the setup button above or go to the JMOS login page.</li>
      <li>Enter your 6-digit setup code and choose a secure password (at least 6 characters).</li>
      <li>Access your active projects, assigned tasks, deliverable reviews, and team calendars.</li>
    </ol>
  </div>

  <p class="body-text" style="font-size:12.5px;color:#6E6763;line-height:1.5;">
    <strong>Need help?</strong> If you have any questions or need access adjustments, reply directly to this email or contact your project lead at Jeota Media.
  </p>
@endsection
