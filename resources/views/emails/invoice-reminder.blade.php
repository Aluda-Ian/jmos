@extends('emails.layout')

@section('content')
  <div class="headline">Invoice &amp; Payment Statement</div>
  <p class="body-text">
    Dear <strong>{{ $clientName ?? 'Valued Client' }}</strong>,<br>
    Please find below the payment details for your production invoice with <strong>Jeota Media</strong>.
  </p>

  <div class="info-card">
    <div class="info-row">
      <span class="info-label">Invoice Number</span>
      <span class="info-val">{{ $invoiceNo ?? 'JM-0146' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Billing Type</span>
      <span class="info-val">{{ $invoiceType ?? '60% Production Deposit' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Amount Payable</span>
      <span class="info-val" style="font-size:16px;color:#1C7A4E">KES {{ number_format($amount ?? 0) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Due Date</span>
      <span class="info-val" style="color:#C52523">{{ $dueDate ?? 'Immediate' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">eTIMS Compliant</span>
      <span class="info-val"><span class="badge badge-green">✓ KRA eTIMS Validated</span></span>
    </div>
  </div>

  <div style="background-color:#FAF7F6;border:1px solid #ECE6E4;border-radius:10px;padding:16px 20px;margin-bottom:20px">
    <b style="font-size:13.5px;color:#1C1614;display:block;margin-bottom:8px">Banking &amp; M-Pesa Payment Details:</b>
    <div style="font-size:13px;line-height:1.7;color:#4A4340">
      <strong>Bank:</strong> NCBA Bank Kenya<br>
      <strong>Account Name:</strong> Jeota Media Limited<br>
      <strong>Account Number:</strong> 1008273921<br>
      <strong>M-Pesa Paybill:</strong> 880100 &nbsp;·&nbsp; <strong>Account:</strong> {{ $invoiceNo ?? 'JM-0146' }}
    </div>
  </div>

  <p class="body-text" style="font-size:13px;color:#6E6763">
    Once payment is transmitted, kindly share the M-Pesa confirmation code or bank advice so our accounts team can credit your account and issue an official receipt.
  </p>
@endsection
