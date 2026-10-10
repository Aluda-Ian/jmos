{{-- ==========================================================================
     JMOS — Contract document editor (branded agreement, edited in place like a quotation)
     ========================================================================== --}}
@php $b = config('jeota'); $sig = config('jeota.signatory'); @endphp
<style>
  .ce-overlay{position:fixed;inset:0;z-index:60;background:var(--panel-2);display:none;flex-direction:column}
  .ce-overlay.on{display:flex}
  .ce-bar{flex:0 0 auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:10px 18px;background:var(--surface);border-bottom:1px solid var(--line);box-shadow:0 2px 10px rgba(0,0,0,.04)}
  .ce-bar .ttl{display:flex;align-items:center;gap:8px;min-width:0}
  .ce-bar .ttl b{font-family:'Poppins',sans-serif;font-size:14px;color:var(--ink);white-space:nowrap}
  .ce-bar .hint{font-size:12px;color:var(--muted);flex:1;min-width:180px}
  .ce-bar .hint .warn{color:var(--amber);font-weight:600}
  .ce-bar .ce-grp{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
  .ce-bar .btn{font-size:12px;padding:6px 11px;font-weight:600;color:var(--ink)}
  .ce-bar .btn.primary{color:#fff}
  .ce-bar select{font:500 12px Inter,sans-serif;padding:6px 8px;border:1px solid var(--line);border-radius:8px;background:var(--surface);color:var(--ink);max-width:220px}
  .ce-ai{display:none;padding:12px 18px;background:linear-gradient(135deg,rgba(245,158,11,.08),rgba(197,37,35,.06));border-bottom:1px solid var(--line)}
  .ce-ai.on{display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap}
  .ce-ai textarea{flex:1;min-width:260px;min-height:54px;border:1px solid var(--line);border-radius:8px;padding:8px 10px;font:13px Inter,sans-serif;background:var(--surface);color:var(--ink)}
  .ce-ai .note{font-size:11.5px;color:var(--muted);width:100%}
  .ce-scroll{flex:1;overflow:auto;padding:26px 16px 60px}

  /* The document — always white paper with Jeota brand colours (same as quotations) */
  .ce-doc{--red:#D62828;--red-deep:#A81E1E;--ink:#17161A;--muted:#6E6A66;--faint:#9A958F;--line:#EAE7E3;--paper:#fff;--panel:#FAF9F7;
    max-width:820px;margin:0 auto;background:#fff;color:#17161A;border-radius:12px;box-shadow:0 12px 48px rgba(23,22,26,.12);overflow:hidden;font-family:Inter,-apple-system,'Segoe UI',sans-serif}
  .ce-banner{background:linear-gradient(100deg,#DA4433 0%,#AE2221 52%,#8B0714 100%);color:#fff;padding:32px 44px;display:flex;justify-content:space-between;gap:24px;flex-wrap:wrap}
  .ce-banner .co{font-family:Archivo,'Arial Black',Inter,sans-serif;font-weight:800;font-size:32px;line-height:1;text-transform:uppercase}
  .ce-banner .tag{font-size:11px;letter-spacing:.34em;text-transform:uppercase;margin-top:12px;opacity:.92}
  .ce-banner .pill{display:inline-block;margin-top:14px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;background:rgba(255,255,255,.2);color:#fff}
  .ce-banner .right{display:flex;gap:16px;align-items:flex-start}
  .ce-banner .contact{text-align:right;font-size:11.5px;line-height:1.85}
  .ce-banner .logo{width:74px;height:74px;border-radius:50%;background:#fff;display:grid;place-items:center;flex:0 0 auto}
  .ce-banner .logo img{width:50px;height:50px;object-fit:contain}
  .ce-body{padding:36px 48px 40px}
  .ce-cover{text-align:center;padding-bottom:24px;border-bottom:1px solid #EAE7E3}
  .ce-cover .kind{font-family:Archivo,'Arial Black',Inter,sans-serif;font-weight:800;font-size:28px;text-decoration:underline;text-underline-offset:6px}
  .ce-cover .sm{font-size:11.5px;letter-spacing:.2em;color:#6E6A66;margin:16px 0 4px;text-transform:uppercase}
  .ce-cover .party{font-weight:700;font-size:18px;margin-top:4px;text-transform:uppercase}
  .ce-cover .role{font-size:12px;color:#6E6A66}
  .ce-cover .and{font-weight:700;margin:10px 0 2px;font-size:13px}
  .ce-cover .subject{font-weight:700;font-size:14px;text-transform:uppercase;letter-spacing:.03em}
  .ce-cover .ref{margin-top:14px;font-size:11px;color:#9A958F;letter-spacing:.06em;text-transform:uppercase}
  .ce-intro{margin-top:22px;font-size:13.5px;line-height:1.75}
  .ce-intro p{margin:0 0 11px}
  .ce-clauses{font-size:13.5px;line-height:1.75;outline:none;margin-top:6px}
  .ce-clauses h2{font-size:14.5px;letter-spacing:.03em;margin:26px 0 8px;color:#17161A}
  .ce-clauses h2.appendix{margin-top:36px;padding-top:18px;border-top:1px solid #EAE7E3;color:#D62828;font-size:14px}
  .ce-clauses h3{font-size:13.5px;margin:16px 0 6px}
  .ce-clauses p{margin:0 0 10px}
  .ce-clauses ol,.ce-clauses ul{margin:0 0 12px 24px}
  .ce-clauses li{margin:0 0 7px;padding-left:4px}
  .ce-clauses table{width:100%;border-collapse:collapse;margin:8px 0 14px;font-size:12.5px}
  .ce-clauses th,.ce-clauses td{padding:8px 10px;border:1px solid #EAE7E3;text-align:left;vertical-align:top}
  .ce-clauses th{background:#F5F3F0;font-weight:600}
  .ce-clauses table.order-form th{background:#D62828;color:#fff;border-color:#D62828}
  .ce-clauses .tick{color:#D62828;font-weight:700;cursor:pointer;margin-right:4px;user-select:none}
  .ce-doc .cf{border-bottom:1px dashed #D9C9C6;padding:0 1px;border-radius:2px}
  .ce-doc .cf.blank{background:#FFF4CC;color:#8A6100;border-bottom-color:#E5C565;font-weight:600}
  .ce-doc:not(.locked) .cf:hover{background:#FBECEC}
  .ce-doc .cf[contenteditable="true"]:focus{outline:none;background:#FBECEC;border-bottom-color:#D62828}
  .ce-clauses[contenteditable="true"]:focus{outline:none}
  .ce-doc.locked .cf{border-bottom:none}
  .ce-doc.locked .cf.blank{background:none;color:inherit}
  .ce-sigs{display:grid;grid-template-columns:1fr 1fr;gap:26px;margin-top:10px}
  .ce-sig{border:1px solid #EAE7E3;border-radius:10px;padding:16px 18px;background:#FAF9F7}
  .ce-sig .lbl{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9A958F}
  .ce-sig .who{font-weight:700;font-size:14px;margin-top:4px}
  .ce-sig .box{height:96px;display:flex;align-items:flex-end;justify-content:center;border-bottom:1.5px solid #17161A;margin:10px 0 8px;background:#fff;border-radius:6px 6px 0 0}
  .ce-sig .box img{max-height:90px;max-width:100%;object-fit:contain}
  .ce-sig .box .pending{align-self:center;color:#9A958F;font-size:12px}
  .ce-sig .meta{font-size:12px;color:#6E6A66;line-height:1.6}
  .ce-sec{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#D62828;margin:32px 0 10px;padding-top:20px;border-top:1px solid #EAE7E3}
  .ce-foot{margin-top:24px;text-align:center;font-size:11px;color:#9A958F;line-height:1.7}
  @media (max-width:760px){
    .ce-body{padding:22px 18px}
    .ce-banner{padding:22px 20px}
    .ce-banner .co{font-size:24px}
    .ce-sigs{grid-template-columns:1fr}
    .ce-bar .hint{order:5;width:100%}
  }
</style>

<div class="ce-overlay" id="ctrEditor" role="dialog" aria-modal="true" aria-label="Contract editor">
  <div class="ce-bar">
    <div class="ttl">
      <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.close()" title="Back to contracts">&larr; Back</button>
      <b id="ctrEdNumber">New contract</b>
      <span class="pill" id="ctrEdStatus">DRAFT</span>
    </div>
    <div class="hint" id="ctrEdHint">Click any text on the agreement to edit it. Highlighted fields still need filling.</div>
    <div class="ce-grp" id="ctrEdTools">
      <select id="ctrEdFill" onchange="window.JMOS_CONTRACTS.fillFrom(this.value); this.value=''" title="Fill client details from JMOS">
        <option value="">Fill from client / quote…</option>
      </select>
      <button type="button" class="btn" id="ctrEdAiBtn" onclick="window.JMOS_CONTRACTS.toggleAi()">✦ AI assist</button>
      <button type="button" class="btn" id="ctrEdSave" onclick="window.JMOS_CONTRACTS.save()">Save</button>
      <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.preview()">Preview ↗</button>
      <button type="button" class="btn primary" id="ctrEdSend" onclick="window.JMOS_CONTRACTS.openSend()" style="background:var(--red);border-color:var(--red)">Send to client</button>
      <button type="button" class="btn" id="ctrEdVoid" onclick="window.JMOS_CONTRACTS.voidActive()" title="Withdraw this contract">Void</button>
      <button type="button" class="btn" id="ctrEdDelete" onclick="window.JMOS_CONTRACTS.deleteActive()" style="color:var(--red)" title="Delete this contract">Delete</button>
    </div>
    <div class="ce-grp" id="ctrEdLockedTools" style="display:none">
      <button type="button" class="btn primary" onclick="window.JMOS_CONTRACTS.preview(true)" style="background:var(--red);border-color:var(--red)">Open signed copy ↗</button>
    </div>
  </div>

  <div class="ce-ai" id="ctrEdAi">
    <textarea id="ctrEdAiText" placeholder="Tell Gemini what to change, e.g. &quot;3-month hotel retainer, add an exclusivity clause for events in Nairobi, allow 3 revision rounds, remove the social media sections&quot;"></textarea>
    <div style="display:flex;flex-direction:column;gap:6px">
      <button type="button" class="btn primary" id="ctrEdAiRun" onclick="window.JMOS_CONTRACTS.aiRevise()" style="background:var(--red);border-color:var(--red)">✦ Revise agreement</button>
      <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.toggleAi(false)">Close</button>
    </div>
    <div class="note" id="ctrEdAiNote">Gemini rewrites the clauses but keeps Jeota's protections and never invents facts. Review the result before sending.</div>
  </div>

  <div class="ce-scroll">
    <div class="ce-doc" id="ctrEdDoc">
      <div class="ce-banner">
        <div>
          <div class="co">JEOTA MEDIA LTD</div>
          <div class="tag">Services Agreement</div>
          <span class="pill" id="ctrEdBannerStatus">Draft</span>
        </div>
        <div class="right">
          <div class="contact">
            <div>{{ $b['email'] }}</div><div>{{ $b['phone'] }}</div><div>{{ $b['website_label'] }}</div>
            <div>{{ $b['address_line1'] }}</div><div>{{ $b['address_line2'] }}</div>
          </div>
          <div class="logo"><img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota Media"></div>
        </div>
      </div>

      <div class="ce-body">
        <div class="ce-cover">
          <div class="kind">SERVICES AGREEMENT</div>
          <div class="sm">Between</div>
          <div class="party">{{ strtoupper($b['company']) }}</div>
          <div class="role">(The Company)</div>
          <div class="and">-AND-</div>
          <div class="party"><span class="cf" data-f="client_name" contenteditable="true"></span></div>
          <div class="role">(The Client)</div>
          <div class="sm">Over</div>
          <div class="subject"><span class="cf" data-f="title" contenteditable="true"></span></div>
          <div class="ref">Agreement No. <span id="ctrEdRef"></span></div>
        </div>

        <div class="ce-intro">
          <p><strong>THIS AGREEMENT</strong> is made on the date of the last signature below.</p>
          <p><strong>BETWEEN:</strong></p>
          <p><strong>{{ strtoupper($b['company']) }}</strong>, incorporated under the Companies Act, Laws of Kenya, @if(!empty($b['po_box'])) of Post Office Box Number {{ $b['po_box'] }}, @else of {{ $b['address_line1'] }}, {{ $b['address_line2'] }}, @endif in the Republic of Kenya (hereinafter referred to as the "<strong>Service Provider</strong>", which expression shall, where the context so admits, include its successors, assigns, agents, and representatives);</p>
          <p><strong>AND</strong></p>
          <p><strong><span class="cf" data-f="client_name" contenteditable="true"></span></strong> (Reg./ID No. <span class="cf" data-f="client_registration" contenteditable="true"></span>), of Post Office Box Number <span class="cf" data-f="client_po_box" contenteditable="true"></span>, <span class="cf" data-f="client_address" contenteditable="true"></span>, in the Republic of Kenya (hereinafter referred to as the "<strong>Client</strong>", which expression shall, where the context so admits, include their successors, assigns, agents, and representatives).</p>
        </div>

        <div class="ce-clauses" id="ctrEdClauses" contenteditable="true" spellcheck="true"></div>

        <div class="ce-sec">Execution</div>
        <p style="font-size:13px;color:#6E6A66;margin:0 0 12px">IN WITNESS WHEREOF the duly authorized representatives of the parties have executed this Agreement electronically.</p>
        <div class="ce-sigs">
          <div class="ce-sig">
            <div class="lbl">The Client</div>
            <div class="who"><span class="cf" data-f="client_name" contenteditable="true"></span></div>
            <div class="box"><span class="pending" id="ctrEdClientSig">Signed online by the client</span></div>
            <div class="meta"><span class="cf" data-f="signatory_name" contenteditable="true"></span> — <span class="cf" data-f="signatory_position" contenteditable="true"></span></div>
          </div>
          <div class="ce-sig">
            <div class="lbl">The Service Provider</div>
            <div class="who">{{ $b['company'] }}</div>
            <div class="box"><img src="{{ asset($sig['signature']) }}" alt="{{ $sig['name'] }} signature"></div>
            <div class="meta"><b>{{ $sig['name'] }}</b> — {{ $sig['title'] }}<br><span id="ctrEdProviderNote">Signature applied when sent</span></div>
          </div>
        </div>

        <div class="ce-foot">{{ $b['company'] }} · {{ $b['address_line1'] }}, {{ $b['address_line2'] }} · {{ $b['email'] }} · {{ $b['phone'] }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Send to client -->
<div class="modal" id="ctrSendModal" role="dialog" aria-modal="true" aria-labelledby="ctrSendTitle" style="z-index:70">
  <div class="mbg" data-close="ctrSendModal"></div>
  <div class="mbox" style="max-width:520px">
    <button class="mclose" data-close="ctrSendModal" title="Close" aria-label="Close">&times;</button>
    <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:10.5px;font-weight:700">SEND FOR SIGNATURE</span>
    <h3 id="ctrSendTitle" style="margin-top:6px">Send to client</h3>
    <p class="msub">Sending applies {{ $sig['name'] }}'s signature and opens the agreement for the client to sign online.</p>
    <div class="field"><label for="ctrSendEmail">Client email</label><input id="ctrSendEmail" type="email" autocomplete="off" placeholder="client@company.co.ke"></div>
    <div class="field"><label for="ctrSendPhone">WhatsApp number</label><input id="ctrSendPhone" type="tel" autocomplete="off" placeholder="+254 7…"></div>
    <div class="field"><label for="ctrSendMessage">Personal note (optional)</label><textarea id="ctrSendMessage" rows="2" placeholder="e.g. Great speaking today — here is the agreement we discussed."></textarea></div>
    <div class="mfoot" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end">
      <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.copyLink()">Copy signing link</button>
      <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.sendWhatsApp()" style="background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3)">WhatsApp</button>
      <button type="button" class="btn primary" id="ctrSendEmailBtn" onclick="window.JMOS_CONTRACTS.sendEmail()" style="background:var(--red);border-color:var(--red)">Send by email</button>
    </div>
  </div>
</div>
