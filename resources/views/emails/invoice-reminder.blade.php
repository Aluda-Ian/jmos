@extends('emails.layout')

@php $b = config('jeota'); @endphp

@section('content')
  <div class="headline">Invoice {{ $invoiceNo ?? '' }}</div>
  <p class="body-text">
    Dear <strong>{{ $clientName ?? 'Valued Client' }}</strong>,<br>
    Thank you for working with <strong>Jeota Media</strong>. Your invoice{{ !empty($invoiceTitle) ? ' for '.$invoiceTitle : '' }} is ready. You can view, print or download it using the button below.
  </p>

  @if(!empty($invoiceUrl))
    <div style="text-align:center;margin:0 0 24px">
      <a href="{{ $invoiceUrl }}" class="btn-action" style="background-color:#D62828;color:#FFFFFF;text-decoration:none;display:inline-block;padding:13px 28px;border-radius:8px;font-weight:600;font-size:14px">View &amp; Download Invoice</a>
    </div>
  @endif

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Invoice Number</span>
      <span class="info-val">{{ $invoiceNo ?? '—' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Billing Type</span>
      <span class="info-val">{{ $invoiceType ?? '—' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Amount Payable</span>
      <span class="info-val" style="font-size:16px;color:#D62828">KES {{ number_format($amount ?? 0, 2) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Due Date</span>
      <span class="info-val">{{ $dueDate ?? 'Upon receipt' }}</span>
    </div>
    @if(!empty($etims))
      <div class="info-row">
        <span class="info-label">eTIMS</span>
        <span class="info-val"><span class="badge badge-green">✓ KRA eTIMS Validated</span></span>
      </div>
    @endif
  </div>

  <div style="background-color:#FAF7F6;border:1px solid #ECE6E4;border-radius:10px;padding:16px 20px;margin-bottom:20px">
    <b style="font-size:13.5px;color:#1C1614;display:block;margin-bottom:8px">How to pay</b>
    <div style="font-size:13px;line-height:1.7;color:#4A4340">
      <strong>Bank:</strong> {{ $b['bank']['name'] }} — {{ $b['bank']['branch'] }}<br>
      <strong>Account Name:</strong> {{ $b['bank']['account_name'] }}<br>
      <strong>Account Number:</strong> {{ $b['bank']['account_no'] }}<br>
      <strong>M-Pesa Paybill:</strong> {{ $b['mpesa']['paybill'] }} &nbsp;·&nbsp; <strong>Account:</strong> {{ $b['mpesa']['account'] }}<br>
      <strong>Payment Reference:</strong> {{ $invoiceNo ?? '' }}
    </div>
  </div>

  <p class="body-text" style="font-size:13px;color:#6E6763">
    Once payment is made, kindly share the M-Pesa confirmation code or bank advice with
    <a href="mailto:{{ $b['email'] }}" style="color:#D62828">{{ $b['email'] }}</a> or call {{ $b['phone'] }} so we can issue your official receipt.
  </p>
@endsection
