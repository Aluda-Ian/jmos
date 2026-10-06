@php
  $b = config('jeota');
  $fmtDate = function ($value) {
      if (empty($value)) return null;
      try { return \Illuminate\Support\Carbon::parse($value)->format('d F Y'); } catch (\Throwable $e) { return $value; }
  };

  $items = collect($invoice->items ?? [])->map(function ($item) {
      $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 1);
      $rate = (float) ($item['rate'] ?? $item['unit_price'] ?? 0);
      $amount = (float) ($item['amount'] ?? ($qty * $rate));
      if ($rate == 0 && $qty > 0) { $rate = $amount / $qty; }
      return [
          'name' => $item['description'] ?? $item['name'] ?? 'Deliverable',
          'qty' => $qty,
          'rate' => $rate,
          'amount' => $amount,
      ];
  });

  if ($items->isEmpty()) {
      $items = collect([[
          'name' => trim(($invoice->title ? $invoice->title.' — ' : '').($invoice->type ?? 'Production services')),
          'qty' => 1,
          'rate' => (float) $invoice->amount,
          'amount' => (float) $invoice->amount,
      ]]);
  }

  $subtotal = (float) ($invoice->subtotal ?: $items->sum('amount'));
  $discount = (float) ($invoice->discount ?? 0);
  $tax = (float) ($invoice->tax ?? 0);
  $projectTotal = $subtotal - $discount + $tax;
  $amountDue = (float) $invoice->amount;
  $isPartial = abs($projectTotal - $amountDue) > 0.5;
  $isPaid = strtolower($invoice->status ?? '') === 'paid';
  $issueDate = $fmtDate($invoice->created_at);
  $dueDate = $fmtDate($invoice->due_date) ?? 'Upon receipt';
  $money = fn ($n) => 'KES '.number_format((float) $n, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Invoice {{ $invoice->invoice_no }} — Jeota Media</title>
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
  @include('partials.brand-banner', ['tag' => 'Tax & Commercial Invoice', 'status' => $isPaid ? 'Paid' : ($invoice->status ?? 'Sent'), 'dark' => true])

  <div class="q-body">
    <div class="q-top">
      <div>
        <div class="big">INVOICE</div>
        <div class="meta-l">
          <div>Issue date: <b>{{ $issueDate }}</b></div>
          <div>Invoice no. <b>{{ $invoice->invoice_no }}</b></div>
          <div>Due date: <b>{{ $dueDate }}</b></div>
          <div>KRA PIN: <b>{{ $b['kra_pin'] }}</b></div>
        </div>
        @if($isPaid)
          <div class="paid-stamp">PAID</div>
        @endif
      </div>
      <div class="meta-r">
        <div class="k">Billed To</div>
        <span class="v">{{ $invoice->clientModel->client_name ?? $invoice->client }}</span>
        @if($invoice->clientModel && ($invoice->clientModel->email || $invoice->clientModel->phone))
          <span class="s">{{ $invoice->clientModel->email }}{{ $invoice->clientModel->email && $invoice->clientModel->phone ? ' · ' : '' }}{{ $invoice->clientModel->phone }}</span>
        @endif
        @if($invoice->title)
          <div class="k">Project</div>
          <span class="v">{{ $invoice->title }}</span>
        @endif
        @if($invoice->quote)
          <span class="s">Ref. quotation {{ $invoice->quote->quote_number }}</span>
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
        @foreach($items as $item)
          <tr>
            <td class="it-name">{{ $item['name'] }}</td>
            <td class="q-qty">{{ rtrim(rtrim(number_format($item['qty'], 2), '0'), '.') }}</td>
            <td class="q-rate">{{ number_format($item['rate'], 2) }}</td>
            <td class="q-amt-cell">{{ number_format($item['amount'], 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="q-totals">
      <div class="row"><span class="k">Subtotal</span><span class="v">{{ $money($subtotal) }}</span></div>
      @if($discount > 0)
        <div class="row neg"><span class="k">Discount</span><span class="v">− {{ $money($discount) }}</span></div>
      @endif
      @if($tax > 0)
        <div class="row"><span class="k">VAT</span><span class="v">{{ $money($tax) }}</span></div>
      @endif
      @if($isPartial)
        <div class="row"><span class="k">Project total</span><span class="v">{{ $money($projectTotal) }}</span></div>
        <div class="row grand"><span class="k">Due now · {{ $invoice->type }}</span><span class="v">{{ $money($amountDue) }}</span></div>
      @else
        <div class="row grand"><span class="k">{{ $isPaid ? 'Total paid' : 'Total payable' }}</span><span class="v">{{ $money($amountDue) }}</span></div>
      @endif
    </div>

    <div class="q-sections">
      <div>
        <h5>How to pay</h5>
        <div class="pay-line"><span class="pk">Bank</span><span class="pv">{{ $b['bank']['name'] }} — {{ $b['bank']['branch'] }}</span></div>
        <div class="pay-line"><span class="pk">A/C name</span><span class="pv">{{ $b['bank']['account_name'] }}</span></div>
        <div class="pay-line"><span class="pk">A/C no.</span><span class="pv">{{ $b['bank']['account_no'] }}</span></div>
        <div class="pay-line"><span class="pk">M-Pesa</span><span class="pv">Paybill {{ $b['mpesa']['paybill'] }} · Acc: {{ $b['mpesa']['account'] }}</span></div>
        <div class="pay-line"><span class="pk">Reference</span><span class="pv">{{ $invoice->invoice_no }}</span></div>
      </div>
      <div>
        <h5>Settlement terms</h5>
        <div class="terms-pay">
          <div class="cell"><div class="k">Milestone / Type</div><div class="v">{{ $invoice->type ?? 'Full payment' }}</div></div>
          <div class="cell"><div class="k">Due date</div><div class="v">{{ $dueDate }}</div></div>
        </div>
      </div>
    </div>

    <div class="q-notes">
      <h5>Invoice notes &amp; terms</h5>
      <div class="txt">
        @if(!empty($invoice->notes))
          {!! nl2br(e($invoice->notes)) !!}<br>
        @endif
        Please use <b>{{ $invoice->invoice_no }}</b> as your payment reference and share the M-Pesa code or bank advice with {{ $b['email'] }}.<br>
        Master files are released upon full settlement of this invoice.
      </div>
    </div>

    <div class="q-foot">
      Official Commercial Invoice · {{ $b['company'] }} · {{ $b['address_line1'] }}, {{ $b['address_line2'] }}<br>
      <a href="mailto:{{ $b['email'] }}">{{ $b['email'] }}</a> · {{ $b['phone'] }} · <a href="{{ $b['website'] }}">{{ $b['website_label'] }}</a>
    </div>
  </div>
</div>

</body>
</html>
