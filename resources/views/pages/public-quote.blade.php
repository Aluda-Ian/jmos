@php
  $b = config('jeota');
  $money = fn ($n) => 'KES '.number_format((float) $n, 2);
  $issued = optional($quote->sent_at ?? $quote->created_at);
  $validUntil = ($quote->sent_at ?? $quote->created_at ?? now())->copy()->addDays($quote->validity_days ?? 14);
  $status = strtolower($quote->status ?? 'draft');
  $isApproved = in_array($status, ['accepted', 'invoiced']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Quote {{ $quote->quote_number }} — Jeota Media</title>
  @include('partials.brand-doc')
</head>
<body>

<div class="doc-actions">
  <button type="button" class="doc-btn" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
    Print / Save PDF
  </button>
</div>

<div class="quote-doc">
  @include('partials.brand-banner', ['status' => $quote->status ?? 'Draft'])

  <div class="q-body">
    @if(session('success'))
      <div class="notice ok" style="margin:0 0 24px">{{ session('success') }}</div>
    @endif

    <div class="q-top">
      <div>
        <div class="big">QUOTE</div>
        <div class="meta-l">
          <div>Date: <b>{{ $issued->format('d F Y') }}</b></div>
          <div>Quote no. <b>{{ $quote->quote_number }}</b></div>
          <div>Valid until: <b>{{ $validUntil->format('d F Y') }}</b></div>
        </div>
      </div>
      <div class="meta-r">
        <div class="k">Project</div>
        <span class="v">{{ $quote->title }}</span>
        <div class="k">Prepared for</div>
        <span class="v">{{ $quote->recipient_name }}</span>
        @if($quote->recipient_email || $quote->recipient_phone)
          <span class="s">{{ $quote->recipient_email }}{{ $quote->recipient_email && $quote->recipient_phone ? ' · ' : '' }}{{ $quote->recipient_phone }}</span>
        @endif
      </div>
    </div>

    <table class="q-table">
      <thead>
        <tr>
          <th>Deliverable</th>
          <th class="c" style="width:60px">Qty</th>
          <th class="n q-rate">Rate</th>
          <th class="n" style="width:130px">Amount (KES)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($quote->items ?? [] as $item)
          @php
            $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 1);
            $rate = (float) ($item['rate'] ?? $item['unit_price'] ?? 0);
            $amt = (float) ($item['amount'] ?? ($qty * $rate));
          @endphp
          <tr>
            <td class="it-name">{{ $item['description'] ?? $item['name'] ?? 'Scope deliverable' }}</td>
            <td class="q-qty">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
            <td class="q-rate">{{ number_format($rate, 2) }}</td>
            <td class="q-amt-cell">{{ number_format($amt, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="q-totals">
      <div class="row"><span class="k">Subtotal</span><span class="v">{{ $money($quote->subtotal ?? $quote->total_amount) }}</span></div>
      @if(($quote->discount ?? 0) > 0)
        <div class="row neg"><span class="k">Discount</span><span class="v">− {{ $money($quote->discount) }}</span></div>
      @endif
      @if(($quote->tax ?? 0) > 0)
        <div class="row"><span class="k">VAT</span><span class="v">{{ $money($quote->tax) }}</span></div>
      @endif
      <div class="row grand"><span class="k">Total payable</span><span class="v">{{ $money($quote->total_amount) }}</span></div>
    </div>

    <div class="q-sections">
      <div>
        <h5>Payment details</h5>
        <div class="pay-line"><span class="pk">Bank</span><span class="pv">{{ $b['bank']['name'] }} — {{ $b['bank']['branch'] }}</span></div>
        <div class="pay-line"><span class="pk">A/C name</span><span class="pv">{{ $b['bank']['account_name'] }}</span></div>
        <div class="pay-line"><span class="pk">A/C no.</span><span class="pv">{{ $b['bank']['account_no'] }}</span></div>
        <div class="pay-line"><span class="pk">M-Pesa</span><span class="pv">Paybill {{ $b['mpesa']['paybill'] }} · Acc: {{ $b['mpesa']['account'] }}</span></div>
      </div>
      <div>
        <h5>Payment terms</h5>
        <div class="terms-pay">
          <div class="cell"><div class="k">Deposit (60%)</div><div class="v">{{ $money((float) $quote->total_amount * 0.6) }}</div></div>
          <div class="cell"><div class="k">On delivery (40%)</div><div class="v">{{ $money((float) $quote->total_amount * 0.4) }}</div></div>
        </div>
      </div>
    </div>

    @if(!empty($quote->notes) || !empty($quote->terms))
      <div class="q-notes">
        <h5>Scope &amp; production terms</h5>
        <div class="txt">
          @if(!empty($quote->notes)){!! nl2br(e($quote->notes)) !!}@endif
          @if(!empty($quote->terms))<br>{!! nl2br(e($quote->terms)) !!}@endif
        </div>
      </div>
    @endif

    @if($isApproved)
      <div class="notice ok">
        ✓ This quotation was approved on {{ $quote->updated_at->format('d F Y') }}.
        @if($quote->convertedInvoice)
          <div style="font-size:12.5px;margin-top:6px;font-weight:500">
            Invoice <b>{{ $quote->convertedInvoice->invoice_no }}</b> has been issued —
            <a href="{{ $quote->convertedInvoice->publicUrl() }}" style="color:inherit">view invoice</a>.
          </div>
        @endif
      </div>
    @else
      <div class="approve-bar">
        <h4>Ready to proceed with this proposal?</h4>
        <p>Approve this quotation and your 60% deposit invoice with payment instructions will be generated instantly.</p>
        <form method="POST" action="{{ route('quotes.public.approve', $quote->quote_number) }}">
          @csrf
          <button type="submit" class="btn-approve">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Approve Quotation &amp; Request Invoice
          </button>
        </form>
      </div>
    @endif

    <div class="q-foot">
      {{ $b['company'] }} · {{ $b['address_line1'] }}, {{ $b['address_line2'] }}<br>
      <a href="mailto:{{ $b['email'] }}">{{ $b['email'] }}</a> · {{ $b['phone'] }} · <a href="{{ $b['website'] }}">{{ $b['website_label'] }}</a>
    </div>
  </div>
</div>

</body>
</html>
