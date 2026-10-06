@php
  use App\Models\Contract;
  use App\Services\Contracts\ContractTemplateService;

  $b = config('jeota');
  $sig = config('jeota.signatory');
  $tz = 'Africa/Nairobi';
  $isSigned = $contract->status === Contract::STATUS_SIGNED;
  $isVoid = $contract->status === Contract::STATUS_VOID;
  $canSign = $contract->canBeSignedByClient() && ! $isPreview;
  $companySigned = ! empty($contract->provider_signed_at);

  if (!empty($fields['agreement_date'])) {
      $madeOn = \Illuminate\Support\Carbon::parse($fields['agreement_date']);
  } elseif ($isSigned) {
      $madeOn = $contract->signed_at->copy()->timezone($tz);
  } else {
      $madeOn = null;
  }
  $servicesUpper = strtoupper($fields['services_label'] ?: 'Photography, Videography and Social Media Services');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Agreement {{ $contract->contract_number }} — Jeota Media</title>
  @include('partials.brand-doc')
  <style>
    .c-cover{text-align:center;padding:6px 0 26px;border-bottom:1px solid var(--line)}
    .c-cover .kind{font-family:var(--archivo);font-weight:800;font-size:30px;letter-spacing:.02em;text-decoration:underline;text-underline-offset:6px}
    .c-cover .between{font-size:12px;letter-spacing:.2em;color:var(--muted);margin:18px 0 6px;text-transform:uppercase}
    .c-cover .party{font-family:var(--display);font-weight:700;font-size:19px;margin-top:4px}
    .c-cover .role{font-size:12px;color:var(--muted)}
    .c-cover .and{font-weight:700;margin:12px 0 4px;font-size:13px}
    .c-cover .over{font-size:12px;letter-spacing:.2em;color:var(--muted);margin:16px 0 4px}
    .c-cover .subject{font-weight:700;font-size:14px;letter-spacing:.03em}
    .c-cover .ref{margin-top:16px;font-size:11.5px;color:var(--faint);letter-spacing:.06em;text-transform:uppercase}
    .c-intro{margin-top:24px;font-size:13.5px;line-height:1.75}
    .c-intro p{margin:0 0 12px}
    .contract-body{font-size:13.5px;line-height:1.75;color:var(--ink);margin-top:8px}
    .contract-body h2{font-family:var(--display);font-size:14.5px;letter-spacing:.03em;margin:26px 0 8px;color:var(--ink)}
    .contract-body h2.appendix{margin-top:38px;padding-top:20px;border-top:1px solid var(--line);color:var(--red);font-size:14px}
    .contract-body h3{font-size:13.5px;margin:18px 0 6px}
    .contract-body p{margin:0 0 10px}
    .contract-body ol,.contract-body ul{margin:0 0 12px 24px}
    .contract-body li{margin:0 0 7px;padding-left:4px}
    .contract-body table{width:100%;border-collapse:collapse;margin:8px 0 14px;font-size:12.5px}
    .contract-body th{background:var(--panel-2);text-align:left;padding:8px 10px;font-weight:600}
    .contract-body td{padding:8px 10px;border-bottom:1px solid var(--line-soft)}
    .contract-body .blank{background:#FFF4CC;color:#8A6100;padding:0 4px;border-radius:3px;font-weight:600}
    .c-section-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--red);margin:34px 0 12px;padding-top:22px;border-top:1px solid var(--line)}
    .order-form{width:100%;border-collapse:collapse;font-size:12.5px}
    .order-form td{border:1px solid var(--line);padding:9px 11px;vertical-align:top}
    .order-form td.k{width:30%;font-weight:600;background:var(--panel);color:var(--ink)}
    .svc-list{list-style:none;margin:0;padding:0;columns:2;column-gap:18px}
    .svc-list li{margin:0 0 5px;color:var(--muted)}
    .svc-list li.on{color:var(--ink);font-weight:600}
    .svc-list .box{display:inline-block;width:14px;text-align:center;margin-right:6px;font-weight:700;color:var(--red)}
    .sig-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:6px}
    .sig-card{border:1px solid var(--line);border-radius:10px;padding:18px 20px;background:var(--panel)}
    .sig-card .lbl{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--faint)}
    .sig-card .who{font-weight:700;font-size:14px;margin-top:4px}
    .sig-card .sig-box{height:110px;display:flex;align-items:flex-end;border-bottom:1.5px solid var(--ink);margin:10px 0 8px;background:#fff;border-radius:6px 6px 0 0}
    .sig-card .sig-box img{max-height:100px;max-width:100%;object-fit:contain}
    .sig-card .sig-box .pending{width:100%;text-align:center;align-self:center;color:var(--faint);font-size:12px}
    .sig-card .meta{font-size:12px;color:var(--muted);line-height:1.6}
    .sign-panel{margin-top:26px;border:1.5px solid var(--red);border-radius:12px;padding:22px 22px 24px;background:#fff}
    .sign-panel h4{font-family:var(--display);font-size:17px;margin-bottom:4px}
    .sign-panel .hint{font-size:12.5px;color:var(--muted);margin-bottom:16px}
    .sign-panel .row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .sign-panel label{display:block;font-size:12px;font-weight:600;margin-bottom:5px}
    .sign-panel input[type=text]{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font:500 14px var(--sans)}
    .pad-wrap{position:relative;margin-top:14px}
    #sigPad{width:100%;height:180px;border:1.5px dashed #C9C3BD;border-radius:10px;background:#fff;touch-action:none;cursor:crosshair;display:block}
    .pad-actions{display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:12px;color:var(--muted)}
    .pad-actions button{background:none;border:none;color:var(--red);font:600 12px var(--sans);cursor:pointer}
    .sign-panel .check{display:flex;gap:10px;align-items:flex-start;margin:14px 0 0;font-size:13px;line-height:1.5;font-weight:400}
    .check input{margin-top:3px;width:16px;height:16px;accent-color:var(--red);flex:0 0 auto}
    .check small{display:block;color:var(--muted);font-size:12px}
    .errors{background:var(--red-wash);color:var(--red-deep);border:1px solid #f3c9c9;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px}
    .notice.warn{background:#FFF8E6;color:#8A6100;border:1px solid #F1DFA6}
    .notice.bad{background:var(--red-wash);color:var(--red-deep);border:1px solid #f3c9c9}
    .audit{margin-top:18px;font-size:11px;color:var(--faint);line-height:1.7;word-break:break-all}
    .drawn-by{margin-top:22px;font-size:11px;color:var(--faint);line-height:1.6}
    @media(max-width:640px){
      .sig-grid,.sign-panel .row2{grid-template-columns:1fr}
      .svc-list{columns:1}
      .c-cover .kind{font-size:24px}
    }
    @media print{
      .sign-panel,.no-print{display:none!important}
      .contract-body .blank{background:none;color:inherit}
      .c-section-title{page-break-before:auto}
      .sig-grid{page-break-inside:avoid}
    }
  </style>
</head>
<body>

<div class="doc-actions no-print">
  <button type="button" class="doc-btn" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
    Print / Save PDF
  </button>
</div>

<div class="quote-doc">
  @include('partials.brand-banner', ['status' => $isPreview && $contract->status === Contract::STATUS_DRAFT ? 'Draft preview' : $contract->status, 'tag' => 'Services Agreement'])

  <div class="q-body">
    @if(session('success'))
      <div class="notice ok" style="margin:0 0 24px">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="notice bad" style="margin:0 0 24px">{{ session('error') }}</div>
    @endif
    @if($isVoid)
      <div class="notice bad" style="margin:0 0 24px">This agreement has been withdrawn by Jeota Media and can no longer be signed.</div>
    @elseif($contract->status === Contract::STATUS_DRAFT)
      <div class="notice warn" style="margin:0 0 24px">Draft preview — this is how the client will see the agreement. The company signature is applied when it is sent.</div>
    @endif

    {{-- Cover --}}
    <div class="c-cover">
      <div class="kind">SERVICES AGREEMENT</div>
      <div class="between">Between</div>
      <div class="party">{{ strtoupper($b['company']) }}</div>
      <div class="role">(The Company)</div>
      <div class="and">-AND-</div>
      <div class="party">{{ strtoupper($contract->client_name) }}</div>
      <div class="role">(The Client)</div>
      <div class="over">Over</div>
      <div class="subject">{{ $servicesUpper }}</div>
      <div class="ref">Agreement No. {{ $contract->contract_number }}</div>
    </div>

    {{-- Parties --}}
    <div class="c-intro">
      <p><strong>THIS AGREEMENT</strong> is made on
        @if($madeOn)
          the <strong>{{ $madeOn->format('jS') }}</strong> day of <strong>{{ $madeOn->format('F Y') }}</strong>.
        @else
          the date of the last signature below.
        @endif
      </p>
      <p><strong>BETWEEN:</strong></p>
      <p><strong>{{ strtoupper($b['company']) }}</strong>, incorporated under the Companies Act, Laws of Kenya,
        @if(!empty($b['po_box'])) of Post Office Box Number {{ $b['po_box'] }}, @else of {{ $b['address_line1'] }}, {{ $b['address_line2'] }}, @endif
        in the Republic of Kenya (hereinafter referred to as the "<strong>Service Provider</strong>", which expression shall, where the context so admits, include its successors, assigns, agents, and representatives);</p>
      <p><strong>AND</strong></p>
      <p><strong>{{ strtoupper($contract->client_name) }}</strong>@if($contract->client_registration) ({{ $contract->client_registration }})@endif,
        @if($contract->client_po_box) of Post Office Box Number {{ $contract->client_po_box }}, @endif
        @if($contract->client_address) of {{ $contract->client_address }}, @endif
        in the Republic of Kenya (hereinafter referred to as the "<strong>Client</strong>", which expression shall, where the context so admits, include their successors, assigns, agents, and representatives).</p>
    </div>

    {{-- Agreement clauses --}}
    <div class="contract-body">{!! $contract->body !!}</div>

    {{-- Order form --}}
    <div class="c-section-title">Jeota Media Limited — Order Form</div>
    <table class="order-form">
      <tr><td class="k">Client</td><td>{{ $contract->client_name }}</td></tr>
      <tr><td class="k">Contact</td><td>{{ $contract->signatory_name ?: '—' }}</td></tr>
      <tr><td class="k">Address</td><td>{{ collect([$contract->client_po_box ? 'P.O. Box '.$contract->client_po_box : null, $contract->client_address])->filter()->implode(', ') ?: '—' }}</td></tr>
      <tr><td class="k">Phone / E-mail</td><td>{{ collect([$contract->client_phone, $contract->client_email])->filter()->implode(' · ') ?: '—' }}</td></tr>
      <tr><td class="k">Authorized signatory</td><td>{{ $contract->signatory_name ?: '—' }}{{ $contract->signatory_position ? ' — '.$contract->signatory_position : '' }}</td></tr>
      <tr>
        <td class="k">Services</td>
        <td>
          <ul class="svc-list">
            @foreach(ContractTemplateService::SERVICES as $key => $label)
              @php $on = in_array($key, $fields['services'], true); @endphp
              <li class="{{ $on ? 'on' : '' }}"><span class="box">{{ $on ? '✓' : '○' }}</span>{{ $key === 'other' && $on && $fields['other_description'] ? 'Other: '.$fields['other_description'] : $label }}</li>
            @endforeach
          </ul>
        </td>
      </tr>
      <tr><td class="k">Services fees</td><td>@if($fields['fee'] > 0) KES {{ number_format($fields['fee'], 2) }} <span style="color:var(--muted)">({{ $fields['fee_words'] }})</span> exclusive of taxes · {{ $fields['deposit_percent'] }}% deposit @else — @endif</td></tr>
      <tr><td class="k">Dates / Location</td><td>{{ collect([$fields['event_date'] ? 'Event: '.$fields['event_date'] : null, $fields['start_date'] ? 'From '.$fields['start_date'] : null, $fields['end_date'] ? 'to '.$fields['end_date'] : null, $fields['location'] ?: null])->filter()->implode(' · ') ?: '—' }}</td></tr>
    </table>

    {{-- Execution --}}
    <div class="c-section-title">Execution</div>
    <p style="font-size:13px;color:var(--muted);margin-bottom:14px">IN WITNESS WHEREOF the duly authorized representatives of the parties have executed this Agreement electronically.</p>
    <div class="sig-grid">
      <div class="sig-card">
        <div class="lbl">The Client</div>
        <div class="who">{{ $contract->client_name }}</div>
        <div class="sig-box">
          @if($isSigned && $contract->client_signature)
            <img src="{{ $contract->client_signature }}" alt="Client signature">
          @else
            <div class="pending">{{ $canSign ? 'Sign below ↓' : 'Awaiting signature' }}</div>
          @endif
        </div>
        <div class="meta">
          @if($isSigned)
            <b>{{ $contract->client_signed_name }}</b>{{ $contract->client_signed_position ? ' — '.$contract->client_signed_position : '' }}<br>
            Signed {{ $contract->signed_at->copy()->timezone($tz)->format('d F Y, H:i') }} EAT
          @else
            Signed for and on behalf of the Client
          @endif
        </div>
      </div>
      <div class="sig-card">
        <div class="lbl">The Service Provider</div>
        <div class="who">{{ $b['company'] }}</div>
        <div class="sig-box">
          @if($companySigned)
            <img src="{{ asset($sig['signature']) }}" alt="{{ $sig['name'] }} signature">
          @else
            <div class="pending">Applied when the agreement is issued</div>
          @endif
        </div>
        <div class="meta">
          <b>{{ $sig['name'] }}</b> — {{ $sig['title'] }}<br>
          @if($companySigned) Signed {{ $contract->provider_signed_at->copy()->timezone($tz)->format('d F Y') }} @else Signed for and on behalf of {{ $b['company'] }} @endif
        </div>
      </div>
    </div>

    @if($isSigned)
      <div class="notice ok">✓ This agreement was signed by both parties.
        @if($contract->data_consent)<div style="font-size:12.5px;margin-top:6px;font-weight:500">The client granted the Appendix 2 consent for use of personal data.</div>@endif
      </div>
      <div class="audit">
        Electronic signature record — signed by {{ $contract->client_signed_name }} on {{ $contract->signed_at->copy()->timezone($tz)->format('d M Y H:i:s') }} EAT from IP {{ $contract->client_signed_ip }}.
        Document fingerprint (SHA-256): {{ $contract->body_hash }}
        @if($contract->body_hash && $contract->body_hash !== hash('sha256', (string) $contract->body)) <b style="color:var(--red)">— WARNING: the text has changed since signing.</b>@endif
      </div>
    @endif

    @if($canSign)
      <form class="sign-panel" method="POST" action="{{ route('contracts.public.sign', $contract->access_token) }}" id="signForm">
        @csrf
        <h4>Sign this agreement</h4>
        <p class="hint">Please read the full agreement above. Then enter your details, draw your signature and confirm.</p>

        @if($errors->any())
          <div class="errors">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <div class="row2">
          <div>
            <label for="signer_name">Full name *</label>
            <input type="text" id="signer_name" name="signer_name" value="{{ old('signer_name', $contract->signatory_name) }}" required maxlength="255" autocomplete="name">
          </div>
          <div>
            <label for="signer_position">Position / capacity</label>
            <input type="text" id="signer_position" name="signer_position" value="{{ old('signer_position', $contract->signatory_position) }}" maxlength="255" placeholder="e.g. Director">
          </div>
        </div>

        <div class="pad-wrap">
          <label for="sigPad">Signature *</label>
          <canvas id="sigPad" aria-label="Signature pad: draw your signature with a mouse or finger"></canvas>
          <div class="pad-actions"><span>Draw with your mouse, trackpad or finger</span><button type="button" id="sigClear">Clear</button></div>
        </div>
        <input type="hidden" name="signature" id="signatureInput">

        <label class="check"><input type="checkbox" name="agree" value="1" required {{ old('agree') ? 'checked' : '' }}>
          <span>I have read and agree to this Services Agreement, and I am authorized to sign it on behalf of {{ $contract->client_name }}.</span></label>
        <label class="check"><input type="checkbox" name="data_consent" value="1" {{ old('data_consent') ? 'checked' : '' }}>
          <span>I grant the consent for use of personal data in Appendix 2.<small>Optional — your services do not depend on this consent.</small></span></label>

        <div style="margin-top:18px;text-align:center">
          <button type="submit" class="btn-approve" id="signSubmit">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Sign Agreement
          </button>
        </div>
      </form>

      <script>
        (function () {
          var canvas = document.getElementById('sigPad');
          var ctx = canvas.getContext('2d');
          var drawing = false, hasInk = false, last = null;

          function resize() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            var data = hasInk ? canvas.toDataURL() : null;
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.4; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#17163C';
            if (data) { var img = new Image(); img.onload = function () { ctx.drawImage(img, 0, 0, canvas.offsetWidth, canvas.offsetHeight); }; img.src = data; }
          }
          function pos(e) { var r = canvas.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; }

          canvas.addEventListener('pointerdown', function (e) { drawing = true; last = pos(e); canvas.setPointerCapture(e.pointerId); ctx.beginPath(); ctx.arc(last.x, last.y, 1.1, 0, Math.PI * 2); ctx.fillStyle = '#17163C'; ctx.fill(); hasInk = true; });
          canvas.addEventListener('pointermove', function (e) {
            if (!drawing) return;
            var p = pos(e);
            ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(p.x, p.y); ctx.stroke();
            last = p; hasInk = true;
          });
          ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (t) { canvas.addEventListener(t, function () { drawing = false; }); });
          document.getElementById('sigClear').addEventListener('click', function () { ctx.clearRect(0, 0, canvas.width, canvas.height); hasInk = false; });
          window.addEventListener('resize', resize);
          resize();

          document.getElementById('signForm').addEventListener('submit', function (e) {
            if (!hasInk) { e.preventDefault(); alert('Please draw your signature in the box.'); return; }
            document.getElementById('signatureInput').value = canvas.toDataURL('image/png');
            var btn = document.getElementById('signSubmit'); btn.disabled = true; btn.style.opacity = .7;
          });
        })();
      </script>
    @endif

    <div class="drawn-by">Drawn by: Stardust Advocates LLP, C/O Lex Centre LLP, P.O. Box 25620-00100, Nairobi.</div>

    <div class="q-foot">
      {{ $b['company'] }} · {{ $b['address_line1'] }}, {{ $b['address_line2'] }}<br>
      <a href="mailto:{{ $b['email'] }}">{{ $b['email'] }}</a> · {{ $b['phone'] }} · <a href="{{ $b['website'] }}">{{ $b['website_label'] }}</a>
    </div>
  </div>
</div>

</body>
</html>
