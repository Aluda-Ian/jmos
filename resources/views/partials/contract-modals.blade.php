{{-- ==========================================================================
     JMOS — Contract builder & contract workspace modals
     ========================================================================== --}}
<style>
  .ct-section{border:1px solid var(--line);border-radius:10px;padding:14px 16px;margin-bottom:14px;background:var(--surface)}
  .ct-section > .ct-h{font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--red);margin-bottom:10px;display:flex;align-items:center;gap:8px}
  .ct-section details > summary{cursor:pointer;font-size:12.5px;font-weight:600;color:var(--ink);list-style:none;display:flex;align-items:center;gap:6px}
  .ct-section details > summary::before{content:'▸';color:var(--muted);transition:transform .15s}
  .ct-section details[open] > summary::before{transform:rotate(90deg)}
  .ct-section details[open] > summary{margin-bottom:10px}
  .ct-services{display:grid;grid-template-columns:1fr 1fr;gap:6px 14px}
  .ct-services label{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink);cursor:pointer;padding:6px 8px;border-radius:7px;border:1px solid var(--line)}
  .ct-services label:has(input:checked){border-color:var(--red-line);background:var(--red-soft)}
  .ct-services input{accent-color:var(--red)}
  .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
  .ct-ai{background:linear-gradient(135deg,rgba(245,158,11,.08),rgba(239,68,68,.06));border-color:rgba(239,68,68,.25)}
  .ct-ai .ai-note{font-size:11.5px;color:var(--muted);margin-top:6px}
  .ct-editor-bar{display:flex;gap:4px;flex-wrap:wrap;padding:6px;border:1px solid var(--line);border-bottom:none;border-radius:8px 8px 0 0;background:var(--panel-2);position:sticky;top:0;z-index:2}
  .ct-editor-bar button{border:1px solid transparent;background:none;color:var(--ink);font:600 12px Inter,sans-serif;padding:4px 9px;border-radius:6px;cursor:pointer}
  .ct-editor-bar button:hover{background:var(--surface);border-color:var(--line)}
  .ct-editor{border:1px solid var(--line);border-radius:0 0 8px 8px;padding:18px 22px;max-height:52vh;overflow-y:auto;background:var(--surface);color:var(--ink);font-size:13.5px;line-height:1.7;outline:none}
  .ct-editor[contenteditable="false"]{background:var(--panel-2);cursor:default}
  .ct-editor > :first-child{margin-top:0}
  .ct-editor h2{font-size:14px;margin:20px 0 6px;letter-spacing:.02em;color:var(--ink)}
  .ct-editor h2.appendix{color:var(--red);border-top:1px solid var(--line);padding-top:14px;margin-top:28px}
  .ct-editor h3{font-size:13.5px;margin:14px 0 4px}
  .ct-editor p{margin:0 0 9px}
  .ct-editor ol,.ct-editor ul{margin:0 0 10px 22px}
  .ct-editor li{margin-bottom:5px}
  .ct-editor table{width:100%;border-collapse:collapse;margin:6px 0 12px;font-size:12.5px}
  .ct-editor th,.ct-editor td{border-bottom:1px solid var(--line);padding:6px 8px;text-align:left}
  .ct-editor .blank{background:var(--amber-soft);color:var(--amber);padding:0 4px;border-radius:3px;font-weight:700}
  .ct-tabs{display:flex;gap:4px;border-bottom:1px solid var(--line);margin-bottom:14px}
  .ct-tabs button{background:none;border:none;border-bottom:2px solid transparent;padding:8px 12px;font:600 13px Inter,sans-serif;color:var(--muted);cursor:pointer}
  .ct-tabs button.active{color:var(--red);border-bottom-color:var(--red)}
  .ct-warn{background:var(--amber-soft);color:var(--amber);border-radius:8px;padding:8px 12px;font-size:12.5px;font-weight:600;margin-bottom:12px}
  .ct-signed{background:var(--green-soft);color:var(--green);border-radius:8px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:12px}
  .ct-send-row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end}
  .ct-link{font-family:'IBM Plex Mono',monospace;font-size:11.5px;color:var(--muted);word-break:break-all;background:var(--panel-2);border-radius:6px;padding:7px 10px;margin-top:8px}
  @media (max-width:760px){
    .ct-services,.grid3,.ct-send-row{grid-template-columns:1fr}
  }
</style>

<!-- A. Contract Builder (new / edit details) -->
<div class="modal" id="contractBuilderModal" role="dialog" aria-modal="true" aria-labelledby="ctrBuilderTitle">
  <div class="mbg" data-close="contractBuilderModal"></div>
  <div class="mbox" style="max-width:780px;max-height:92vh;overflow-y:auto">
    <button class="mclose" data-close="contractBuilderModal" title="Close" aria-label="Close modal">&times;</button>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
      <span class="badge" style="background:var(--red-soft);color:var(--red);font-size:10.5px;font-weight:700">CLIENT AGREEMENT</span>
    </div>
    <h3 id="ctrBuilderTitle">New Contract</h3>
    <p class="msub">Fill the client's details and the deal terms. JMOS builds the agreement from the Jeota template; AI can then tailor it.</p>

    <input type="hidden" id="ctrFormId">

    <div class="ct-section">
      <div class="ct-h">Start from</div>
      <div class="grid2">
        <div class="field">
          <label for="ctrClientSelect">Client in JMOS</label>
          <select id="ctrClientSelect" onchange="window.JMOS_CONTRACTS.onPickClient(this.value)">
            <option value="">— New / not in JMOS —</option>
          </select>
        </div>
        <div class="field">
          <label for="ctrQuoteSelect">Link a quotation (fills fee &amp; deliverables)</label>
          <select id="ctrQuoteSelect" onchange="window.JMOS_CONTRACTS.onPickQuote(this.value)">
            <option value="">— None —</option>
          </select>
        </div>
      </div>
      <div class="field" style="margin-bottom:0">
        <label for="ctrTemplate">Template</label>
        <select id="ctrTemplate"></select>
      </div>
    </div>

    <div class="ct-section">
      <div class="ct-h">Client party</div>
      <div class="grid2">
        <div class="field"><label for="ctrClientName">Client legal name *</label><input id="ctrClientName" placeholder="As per incorporation / business name certificate" autocomplete="off"></div>
        <div class="field"><label for="ctrTitle">Contract title *</label><input id="ctrTitle" placeholder="e.g. Brand Content Retainer 2026" autocomplete="off"></div>
      </div>
      <div class="grid3">
        <div class="field"><label for="ctrRegistration">Reg. / ID No.</label><input id="ctrRegistration" placeholder="e.g. PVT-ABC123" autocomplete="off"></div>
        <div class="field"><label for="ctrPoBox">P.O. Box</label><input id="ctrPoBox" placeholder="e.g. 12345-00100" autocomplete="off"></div>
        <div class="field"><label for="ctrAddress">Physical address</label><input id="ctrAddress" placeholder="e.g. Westlands, Nairobi" autocomplete="off"></div>
      </div>
      <div class="grid2">
        <div class="field"><label for="ctrEmail">Client email</label><input id="ctrEmail" type="email" placeholder="Where the signing link goes" autocomplete="off"></div>
        <div class="field"><label for="ctrPhone">WhatsApp / phone</label><input id="ctrPhone" type="tel" placeholder="+254 7…" autocomplete="off"></div>
      </div>
      <div class="grid2" style="margin-bottom:0">
        <div class="field" style="margin-bottom:0"><label for="ctrSignatoryName">Authorized signatory</label><input id="ctrSignatoryName" placeholder="Person who will sign" autocomplete="off"></div>
        <div class="field" style="margin-bottom:0"><label for="ctrSignatoryPosition">Position</label><input id="ctrSignatoryPosition" placeholder="e.g. Director" autocomplete="off"></div>
      </div>
    </div>

    <div class="ct-section">
      <div class="ct-h">Services &amp; fees</div>
      <div class="ct-services" id="ctrServices"></div>
      <div class="field" id="ctrOtherWrap" style="display:none;margin-top:10px"><label for="ctrF_other_description">Describe the other service</label><input id="ctrF_other_description" data-ct-field="other_description" autocomplete="off"></div>
      <div class="grid3" style="margin-top:12px">
        <div class="field"><label for="ctrF_fee">Total fee (KES, excl. taxes)</label><input id="ctrF_fee" data-ct-field="fee" type="number" min="0" step="0.01" placeholder="0.00"></div>
        <div class="field"><label for="ctrF_deposit_percent">Deposit %</label><input id="ctrF_deposit_percent" data-ct-field="deposit_percent" type="number" min="0" max="100"></div>
        <div class="field"><label for="ctrF_event_date">Event / shoot date</label><input id="ctrF_event_date" data-ct-field="event_date" placeholder="e.g. 14 November 2026"></div>
      </div>
      <div class="grid2" style="margin-bottom:0">
        <div class="field" style="margin-bottom:0"><label for="ctrF_start_date">Start date</label><input id="ctrF_start_date" data-ct-field="start_date" placeholder="e.g. 1 November 2026"></div>
        <div class="field" style="margin-bottom:0"><label for="ctrF_end_date">End date</label><input id="ctrF_end_date" data-ct-field="end_date" placeholder="e.g. 31 January 2027"></div>
      </div>
    </div>

    <div class="ct-section">
      <details>
        <summary>Deliverables (Appendix 1) — leave blank to fill later</summary>
        <div class="grid3">
          <div class="field"><label for="ctrF_photo_count">Edited images</label><input id="ctrF_photo_count" data-ct-field="photo_count" type="number" min="0"></div>
          <div class="field"><label for="ctrF_reel_count">Short-form videos / reels</label><input id="ctrF_reel_count" data-ct-field="reel_count" type="number" min="0"></div>
          <div class="field"><label for="ctrF_delivery_period">Per</label><select id="ctrF_delivery_period" data-ct-field="delivery_period"><option value="month">Month</option><option value="session">Session</option><option value="event">Event</option><option value="project">Project</option></select></div>
          <div class="field"><label for="ctrF_photo_delivery_days">Photo delivery (business days)</label><input id="ctrF_photo_delivery_days" data-ct-field="photo_delivery_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_filming_sessions">Filming sessions</label><input id="ctrF_filming_sessions" data-ct-field="filming_sessions" type="number" min="0"></div>
          <div class="field"><label for="ctrF_session_hours">Hours per session</label><input id="ctrF_session_hours" data-ct-field="session_hours" type="number" min="0"></div>
          <div class="field"><label for="ctrF_video_count">Final videos</label><input id="ctrF_video_count" data-ct-field="video_count" type="number" min="0"></div>
          <div class="field"><label for="ctrF_video_resolution">Resolution</label><input id="ctrF_video_resolution" data-ct-field="video_resolution"></div>
          <div class="field"><label for="ctrF_video_format">Format</label><input id="ctrF_video_format" data-ct-field="video_format"></div>
          <div class="field"><label for="ctrF_video_platform">Delivered via</label><input id="ctrF_video_platform" data-ct-field="video_platform"></div>
          <div class="field"><label for="ctrF_video_delivery_days">Video delivery (business days)</label><input id="ctrF_video_delivery_days" data-ct-field="video_delivery_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_location">Location</label><input id="ctrF_location" data-ct-field="location" placeholder="e.g. Client HQ, Upper Hill"></div>
          <div class="field"><label for="ctrF_platforms">Social platforms</label><input id="ctrF_platforms" data-ct-field="platforms" placeholder="Instagram, TikTok, LinkedIn"></div>
          <div class="field"><label for="ctrF_posts_per_week">Posts per week</label><input id="ctrF_posts_per_week" data-ct-field="posts_per_week" type="number" min="0"></div>
          <div class="field"><label for="ctrF_engagement_hours">Engagement hours / week</label><input id="ctrF_engagement_hours" data-ct-field="engagement_hours" type="number" min="0"></div>
        </div>
      </details>
    </div>

    <div class="ct-section">
      <details>
        <summary>Terms (defaults from the standard agreement)</summary>
        <div class="grid3">
          <div class="field"><label for="ctrF_payment_days">Payment overdue after (days)</label><input id="ctrF_payment_days" data-ct-field="payment_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_late_interest">Late interest % / month</label><input id="ctrF_late_interest" data-ct-field="late_interest" type="number" min="0" step="0.5"></div>
          <div class="field"><label for="ctrF_revision_rounds">Revision rounds</label><input id="ctrF_revision_rounds" data-ct-field="revision_rounds" type="number" min="0"></div>
          <div class="field"><label for="ctrF_feedback_days">Client feedback within (days)</label><input id="ctrF_feedback_days" data-ct-field="feedback_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_reschedule_days">Reschedule notice (days)</label><input id="ctrF_reschedule_days" data-ct-field="reschedule_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_cancellation_hours">Late-cancellation window (hrs)</label><input id="ctrF_cancellation_hours" data-ct-field="cancellation_hours" type="number" min="0"></div>
          <div class="field"><label for="ctrF_termination_days">Termination notice (days)</label><input id="ctrF_termination_days" data-ct-field="termination_days" type="number" min="0"></div>
          <div class="field"><label for="ctrF_agreement_date">Agreement date (optional)</label><input id="ctrF_agreement_date" data-ct-field="agreement_date" type="date"></div>
        </div>
        <div class="field" style="margin-bottom:0"><label for="ctrF_special_terms">Special conditions (added as its own clause)</label><textarea id="ctrF_special_terms" data-ct-field="special_terms" rows="3" placeholder="Optional — e.g. exclusivity, usage limits, travel arrangements"></textarea></div>
      </details>
    </div>

    <div class="ct-section ct-ai">
      <div class="ct-h" style="color:#B45309">✦ J- ai drafting (optional)</div>
      <div class="field" style="margin-bottom:6px">
        <label for="ctrAiInstructions">Tell AI how to tailor this agreement for the client</label>
        <textarea id="ctrAiInstructions" rows="3" placeholder="e.g. This is a 3-month retainer for a hotel. Add a clause that content may only be used for hotel marketing, and make revisions 3 rounds."></textarea>
      </div>
      <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;cursor:pointer"><input type="checkbox" id="ctrUseAi" style="accent-color:var(--red)"> Customise with AI after generating</label>
      <div class="ai-note" id="ctrAiNote">AI keeps Jeota's protections and never invents facts — review the result before sending.</div>
      <label id="ctrRegenerateWrap" style="display:none;align-items:center;gap:8px;font-size:12.5px;font-weight:600;cursor:pointer;margin-top:10px"><input type="checkbox" id="ctrRegenerate" style="accent-color:var(--red)"> Rebuild the agreement text from the template (replaces manual and AI edits)</label>
    </div>

    <div class="mfoot" style="display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap">
      <button type="button" class="btn" data-close="contractBuilderModal">Cancel</button>
      <button type="button" class="btn primary" id="ctrBuilderSubmit" onclick="window.JMOS_CONTRACTS.submitBuilder()" style="font-weight:700">Generate Contract</button>
    </div>
  </div>
</div>

<!-- B. Contract Workspace (review, edit, AI revise, send) -->
<div class="modal" id="contractDetailModal" role="dialog" aria-modal="true" aria-labelledby="ctrdTitle">
  <div class="mbg" data-close="contractDetailModal"></div>
  <div class="mbox workspace-modal" style="max-width:980px">
    <div class="workspace-cover-banner" style="background:linear-gradient(135deg,#050507 0%,#121316 28%,#5a0c0b 68%,#C52523 100%);padding:22px 32px;color:#fff;display:flex;flex-direction:column;justify-content:center">
      <button class="mclose" data-close="contractDetailModal" title="Close" aria-label="Close modal" style="top:12px;right:14px;background:rgba(0,0,0,0.45);color:#fff;border:none">&times;</button>
      <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;opacity:0.85;font-weight:700">JEOTA MEDIA · SERVICES AGREEMENT</div>
      <h3 id="ctrdTitle" style="margin:4px 0 0;font-size:22px;color:#fff;font-family:'Poppins',sans-serif">Contract</h3>
      <div style="font-size:12.5px;opacity:0.9;margin-top:4px" id="ctrdSubtitle"></div>
    </div>

    <div class="workspace-modal-body" style="padding:22px 32px 28px">
      <input type="hidden" id="ctrdId">

      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-bottom:14px;border-bottom:1px solid var(--line);margin-bottom:14px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:8px">
          <span class="pill" id="ctrdStatus">Draft</span>
          <span class="mono" id="ctrdFee" style="font-size:15px;font-weight:700;color:var(--ink)"></span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
          <button type="button" class="btn" id="ctrdEditBtn" onclick="window.JMOS_CONTRACTS.openBuilder(window.JMOS_CONTRACTS.active)" style="font-size:11.5px;padding:6px 11px;font-weight:600">Edit details</button>
          <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.preview()" style="font-size:11.5px;padding:6px 11px;font-weight:600">Preview as client ↗</button>
          <button type="button" class="btn" id="ctrdVoidBtn" onclick="window.JMOS_CONTRACTS.voidActive()" style="font-size:11.5px;padding:6px 11px;font-weight:600">Void</button>
          <button type="button" class="btn" id="ctrdDeleteBtn" onclick="window.JMOS_CONTRACTS.deleteActive()" style="font-size:11.5px;padding:6px 11px;font-weight:600;color:var(--red)">Delete</button>
        </div>
      </div>

      <div id="ctrdSignedInfo" class="ct-signed" style="display:none"></div>
      <div id="ctrdBlanks" class="ct-warn" style="display:none"></div>

      <div class="ct-tabs" role="tablist">
        <button type="button" class="active" data-ct-tab="text" onclick="window.JMOS_CONTRACTS.tab('text')">Agreement text</button>
        <button type="button" data-ct-tab="ai" onclick="window.JMOS_CONTRACTS.tab('ai')">✦ Revise with AI</button>
        <button type="button" data-ct-tab="send" onclick="window.JMOS_CONTRACTS.tab('send')">Send for signature</button>
      </div>

      <div data-ct-pane="text">
        <div class="ct-editor-bar" id="ctrdEditorBar">
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('bold')" title="Bold"><b>B</b></button>
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('italic')" title="Italic"><i>I</i></button>
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('underline')" title="Underline"><u>U</u></button>
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('formatBlock','h2')" title="Clause heading">Heading</button>
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('formatBlock','p')" title="Paragraph">Paragraph</button>
          <button type="button" onclick="window.JMOS_CONTRACTS.fmt('insertOrderedList')" title="Numbered list">List</button>
          <span style="flex:1"></span>
          <button type="button" onclick="window.JMOS_CONTRACTS.saveText()" style="color:var(--red)" id="ctrdSaveTextBtn">Save text</button>
        </div>
        <div class="ct-editor" id="ctrdEditor" contenteditable="true" spellcheck="true"></div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:6px">The cover page, parties, order form and signature blocks are added automatically around this text.</div>
      </div>

      <div data-ct-pane="ai" hidden>
        <div class="field">
          <label for="ctrdAiInstructions">What should change?</label>
          <textarea id="ctrdAiInstructions" rows="4" placeholder="e.g. Change the term to 6 months, add an exclusivity clause for hotel events in Nairobi, and allow the client 3 revision rounds."></textarea>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <button type="button" class="btn primary" id="ctrdAiBtn" onclick="window.JMOS_CONTRACTS.aiRevise()" style="font-weight:700">✦ Revise agreement</button>
          <span style="font-size:12px;color:var(--muted)" id="ctrdAiStatus">Takes up to a minute. You can review and edit the result afterwards.</span>
        </div>
        <div style="margin-top:14px;font-size:12px;color:var(--muted)">
          <b style="color:var(--ink)">Previous instructions</b>
          <div id="ctrdAiHistory" style="white-space:pre-wrap;margin-top:4px">—</div>
        </div>
      </div>

      <div data-ct-pane="send" hidden>
        <p style="font-size:13px;color:var(--muted);margin:0 0 12px">Sending applies <b style="color:var(--ink)" id="ctrdSignatoryName">Barny Kiome</b>'s signature on behalf of Jeota Media Limited and opens the agreement for the client to sign online.</p>
        <div class="ct-send-row">
          <div class="field" style="margin-bottom:0"><label for="ctrdSendEmail">Client email</label><input id="ctrdSendEmail" type="email" autocomplete="off"></div>
          <button type="button" class="btn primary" id="ctrdSendEmailBtn" onclick="window.JMOS_CONTRACTS.sendEmail()" style="font-weight:700;height:40px">Send by Email</button>
        </div>
        <div class="field" style="margin-top:10px"><label for="ctrdSendMessage">Personal note (optional)</label><textarea id="ctrdSendMessage" rows="2" placeholder="e.g. Great speaking today — here is the agreement we discussed."></textarea></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.sendWhatsApp()" style="background:rgba(37,211,102,0.12);color:#128C7E;border-color:rgba(37,211,102,0.3);font-weight:600">Share on WhatsApp</button>
          <button type="button" class="btn" onclick="window.JMOS_CONTRACTS.copyLink()" style="font-weight:600">Copy signing link</button>
        </div>
        <div class="ct-link" id="ctrdLink"></div>
      </div>
    </div>
  </div>
</div>
