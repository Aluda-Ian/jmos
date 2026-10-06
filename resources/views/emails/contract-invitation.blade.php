@extends('emails.layout')

@php $b = config('jeota'); @endphp

@section('content')
  <div class="headline">Your agreement is ready to sign</div>
  <p class="body-text">
    Dear <strong>{{ $contract->signatory_name ?: $contract->client_name }}</strong>,<br>
    Thank you for choosing <strong>Jeota Media</strong>. Please find our services agreement for
    <strong>{{ $contract->title }}</strong>. It has already been signed on behalf of {{ $b['company'] }} —
    you can read it in full and sign online in a couple of minutes.
  </p>

  @if(!empty($personalMessage))
    <p class="body-text" style="background:#FAF7F6;border-left:3px solid #D62828;padding:12px 16px;border-radius:6px">{!! nl2br(e($personalMessage)) !!}</p>
  @endif

  <div style="text-align:center;margin:0 0 24px">
    <a href="{{ $signUrl }}" class="btn-action" style="background-color:#D62828;color:#FFFFFF;text-decoration:none;display:inline-block;padding:13px 28px;border-radius:8px;font-weight:600;font-size:14px">Review &amp; Sign Agreement</a>
  </div>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Agreement No.</span>
      <span class="info-val">{{ $contract->contract_number }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Client</span>
      <span class="info-val">{{ $contract->client_name }}</span>
    </div>
    @if(!empty($contract->fields['fee']))
      <div class="info-row">
        <span class="info-label">Contract Fee</span>
        <span class="info-val" style="color:#D62828">KES {{ number_format((float) $contract->fields['fee'], 2) }}</span>
      </div>
    @endif
  </div>

  <p class="body-text" style="font-size:13px;color:#6E6763">
    Questions or changes? Reply to this email, write to
    <a href="mailto:{{ $b['email'] }}" style="color:#D62828">{{ $b['email'] }}</a> or call {{ $b['phone'] }}.
  </p>
@endsection
