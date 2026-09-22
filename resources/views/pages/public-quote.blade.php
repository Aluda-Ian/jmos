<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Commercial Proposal · {{ $quote->quote_number }} — Jeota Media</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&family=IBM+Plex+Mono:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --red: #C52523;
      --red-dark: #9e1a18;
      --ink: #0f172a;
      --muted: #64748b;
      --border: #e2e8f0;
      --bg: #f8fafc;
      --card: #ffffff;
      --green: #16a34a;
      --green-soft: #f0fdf4;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
      background: var(--bg);
      color: var(--ink);
      line-height: 1.5;
      padding: 24px 16px 48px;
    }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .container {
      max-width: 800px;
      margin: 0 auto;
    }
    .card {
      background: var(--card);
      border-radius: 12px;
      box-shadow: 0 4px 20px -2px rgba(0,0,0,0.06), 0 2px 6px -1px rgba(0,0,0,0.04);
      border: 1px solid var(--border);
      overflow: hidden;
    }
    .banner {
      background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #5a0c0b 80%, #C52523 100%);
      color: #fff;
      padding: 28px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    .brand-name {
      font-size: 22px;
      font-weight: 900;
      letter-spacing: -0.5px;
    }
    .brand-tag {
      font-size: 11px;
      letter-spacing: 1px;
      text-transform: uppercase;
      opacity: 0.85;
    }
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      background: rgba(255,255,255,0.2);
    }
    .body {
      padding: 32px;
    }
    .meta-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      padding-bottom: 20px;
      margin-bottom: 24px;
      border-bottom: 1px solid var(--border);
    }
    .meta-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .meta-val {
      font-size: 15px;
      font-weight: 600;
      color: var(--ink);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
      font-size: 13.5px;
    }
    th {
      background: #f1f5f9;
      color: var(--muted);
      font-weight: 600;
      text-align: left;
      padding: 10px 12px;
      font-size: 12px;
    }
    td {
      padding: 12px;
      border-bottom: 1px solid var(--border);
    }
    .totals-box {
      background: #f8fafc;
      border-radius: 8px;
      padding: 16px 20px;
      border: 1px solid var(--border);
      margin-bottom: 24px;
    }
    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
      font-size: 13.5px;
      color: var(--muted);
    }
    .total-row.grand {
      font-size: 18px;
      font-weight: 800;
      color: var(--ink);
      border-top: 2px solid var(--border);
      padding-top: 10px;
      margin-top: 10px;
      margin-bottom: 0;
    }
    .approve-bar {
      background: var(--green-soft);
      border: 2px solid #86efac;
      border-radius: 10px;
      padding: 24px;
      text-align: center;
      margin-top: 24px;
    }
    .btn-approve {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--green);
      color: #fff;
      font-size: 16px;
      font-weight: 700;
      padding: 14px 36px;
      border-radius: 8px;
      text-decoration: none;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
      transition: background 0.2s, transform 0.1s;
    }
    .btn-approve:hover {
      background: #15803d;
      transform: translateY(-1px);
    }
    .success-alert {
      background: #dcfce7;
      color: #166534;
      padding: 18px 24px;
      border-radius: 8px;
      font-weight: 600;
      text-align: center;
      margin-top: 20px;
    }
    .footer {
      text-align: center;
      margin-top: 32px;
      font-size: 12px;
      color: var(--muted);
    }
    @media (max-width: 600px) {
      .meta-grid { grid-template-columns: 1fr; gap: 12px; }
      .body { padding: 20px; }
      .banner { padding: 20px; }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card">
    <div class="banner">
      <div>
        <div class="brand-name">JEOTA MEDIA</div>
        <div class="brand-tag">Storytellers for a Better World · Nairobi, Kenya</div>
      </div>
      <div>
        <span class="badge">{{ strtoupper($quote->status ?? 'Draft') }}</span>
      </div>
    </div>

    <div class="body">
      @if(session('success'))
        <div class="success-alert">
          {{ session('success') }}
        </div>
      @endif

      <div class="meta-grid">
        <div>
          <div class="meta-label">Prepared For</div>
          <div class="meta-val">{{ $quote->recipient_name }}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $quote->recipient_email ?? '' }} {{ $quote->recipient_phone ? '· ' . $quote->recipient_phone : '' }}</div>
        </div>
        <div style="text-align:right">
          <div class="meta-label">Proposal Details</div>
          <div class="meta-val mono" style="color:var(--red)">{{ $quote->quote_number }}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">Valid until: <b>{{ now()->addDays($quote->validity_days ?? 14)->format('M d, Y') }}</b></div>
        </div>
      </div>

      <div style="margin-bottom:20px">
        <h3 style="font-size:17px;font-weight:700;color:var(--ink);margin-bottom:4px">{{ $quote->title }}</h3>
        <p style="font-size:13px;color:var(--muted)">Review the itemized deliverables and commercial breakdown below.</p>
      </div>

      <table>
        <thead>
          <tr>
            <th>Deliverable / Specification</th>
            <th style="text-align:center;width:60px">Qty</th>
            <th style="text-align:right;width:120px">Rate (KES)</th>
            <th style="text-align:right;width:140px">Amount (KES)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($quote->items ?? [] as $item)
            @php
              $qty = (float) ($item['quantity'] ?? 1);
              $rate = (float) ($item['rate'] ?? $item['unit_price'] ?? 0);
              $amt = (float) ($item['amount'] ?? ($qty * $rate));
            @endphp
            <tr>
              <td style="font-weight:600">{{ $item['description'] ?? 'Scope deliverable' }}</td>
              <td style="text-align:center">{{ $qty }}</td>
              <td style="text-align:right" class="mono">{{ number_format($rate, 2) }}</td>
              <td style="text-align:right;font-weight:700" class="mono">{{ number_format($amt, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="totals-box">
        <div class="total-row">
          <span>Subtotal</span>
          <span class="mono">KES {{ number_format((float) ($quote->subtotal ?? $quote->total_amount), 2) }}</span>
        </div>
        @if(($quote->discount ?? 0) > 0)
          <div class="total-row" style="color:var(--red)">
            <span>Negotiated Discount</span>
            <span class="mono">- KES {{ number_format((float) $quote->discount, 2) }}</span>
          </div>
        @endif
        <div class="total-row grand">
          <span>Total Proposal Amount</span>
          <span class="mono" style="color:var(--red)">KES {{ number_format((float) $quote->total_amount, 2) }}</span>
        </div>
      </div>

      @if(!empty($quote->notes))
        <div style="background:#f9fafb;border:1px solid var(--border);border-radius:8px;padding:16px;margin-bottom:24px;font-size:12.5px;line-height:1.6">
          <div style="font-weight:700;font-size:11.5px;color:var(--muted);text-transform:uppercase;margin-bottom:4px">Scope &amp; Production Terms</div>
          <div>{!! nl2br(e($quote->notes)) !!}</div>
        </div>
      @endif

      <!-- Approval Section -->
      @if(in_array(strtolower($quote->status), ['accepted', 'invoiced']))
        <div class="success-alert" style="margin-top:24px">
          ✓ This quotation was approved on <b>{{ $quote->updated_at->format('M d, Y') }}</b>.
          @if($quote->convertedInvoice)
            <div style="font-size:12px;margin-top:4px">Official Invoice <b>{{ $quote->convertedInvoice->invoice_no }}</b> has been issued.</div>
          @endif
        </div>
      @else
        <div class="approve-bar">
          <h4 style="font-size:16px;color:#166534;margin-bottom:6px">Ready to proceed with this proposal?</h4>
          <p style="font-size:13px;color:#15803d;margin-bottom:16px">Click below to approve this quotation. An official invoice with deposit payment instructions will be instantly generated for you.</p>
          <form method="POST" action="{{ route('quotes.public.approve', $quote->quote_number) }}">
            @csrf
            <button type="submit" class="btn-approve">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Approve Quotation &amp; Request Invoice
            </button>
          </form>
        </div>
      @endif
    </div>
  </div>

  <div class="footer">
    Jeota Media Limited · Keystone Park, Nairobi, Kenya · <a href="https://jeotamedia.co.ke" style="color:var(--muted)">jeotamedia.co.ke</a> · +254 712 345 678
  </div>
</div>

</body>
</html>
