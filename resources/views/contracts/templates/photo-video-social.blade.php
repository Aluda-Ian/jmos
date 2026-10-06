{{--
  Jeota Media — Photography, Videography & Social Media Services Agreement
  Source: "PHOTO VIDEO SM AGREEMENT JEOTA" (drawn by Stardust Advocates LLP)

  Rendered by App\Services\Contracts\ContractTemplateService. Available variables:
    $v(key)   -> escaped field value, or a highlighted blank when not provided
    $f        -> normalised field array      $party -> client party details
    $has(...) -> whether any of the given services were selected
    $b        -> config('jeota')              $quote -> linked Quote or null
--}}
@php
  $n = 0;
  $clause = function () use (&$n) { return ++$n; };
  $hasPhoto = $has('photography_project', 'photography_event');
  $hasVideo = $has('videography_project', 'videography_event');
  $hasSocialContent = $has('social_content');
  $hasSocialMgmt = $has('social_management');
  $hasSocial = $hasSocialContent || $hasSocialMgmt;
  $sub = 0;
  $subLetter = function () use (&$sub) { return chr(97 + $sub++); };
@endphp

<h2>{{ $clause() }}. INTERPRETATION</h2>
<p>In this Agreement, unless the context otherwise requires:</p>
<p>"<strong>Services</strong>" shall mean photography, videography, editing, production, social media and/or media-related services as described in the Order Form and Appendix 1.</p>
<p>"<strong>Deliverables</strong>" shall mean all photographs, video recordings, edited media, social media content and related outputs to be provided, as described in Appendix 1.</p>
<p>"<strong>Event</strong>" shall mean the occasion, project, or production for which Services are engaged on a specific date and time.</p>
<p>"<strong>Project</strong>" shall mean the overall assignment, engagement, or body of work commissioned by the Client, including all related activities, Events, deliverables, and services to be performed by the Service Provider under this Agreement, whether completed in a single instance or across multiple phases or dates.</p>
<p>"<strong>Working Days</strong>" shall mean Monday to Friday excluding public holidays in Kenya.</p>

<h2>{{ $clause() }}. SCOPE OF SERVICES</h2>
<p>The Client hereby engages the Service Provider to provide the following professional services: <strong>{{ $f['services_label'] ?: 'photography, videography and social media services' }}</strong>. The location details of the assignment (if need be) are covered under Appendix 1.@if($f['start_date']) The Services shall commence on <strong>{{ $f['start_date'] }}</strong>@if($f['end_date']) and run until <strong>{{ $f['end_date'] }}</strong>@endif.@endif</p>
<p><strong>Scope of Services &amp; Deliverables</strong></p>
@php $roman = ['i', 'ii', 'iii', 'iv', 'v']; $ri = 0; @endphp
@if($hasPhoto)
<p><strong>{{ $roman[$ri++] }}) Photography</strong></p>
<p>The Service Provider shall provide high-resolution, edited images and short-form videos as provided under Appendix 1. The Deliverables will be provided via a Digital Gallery/Cloud Link or such other media as the parties may agree, within the timeframes therein provided.</p>
@endif
@if($hasVideo)
<p><strong>{{ $roman[$ri++] }}) Video Production</strong></p>
<p>The Service Provider shall offer the technical and creative aspects of video production, including pre-production and post-production, in consultation with the Client. The Service Provider shall oversee the professional operation of all camera systems and lighting setups to ensure visual consistency, and the capture of high-quality video and audio. The Service Provider shall direct all production and post-production and undertake digital assembly, including colour grading, sound engineering and integrations. The Deliverables are as provided under Appendix 1.</p>
@endif
@if($hasSocial)
<p><strong>{{ $roman[$ri++] }}) Social Media Ideation, Content Creation &amp; Account Management</strong></p>
<p>The Service Provider shall, in consultation with the Client, ideate, create and post content on behalf of the Client, ensuring the Client's digital presence and the articulation of brand objectives.@if($hasSocialMgmt) The Service Provider shall also have oversight of daily platform operations, content distribution and coordination, in collaboration with the Client,@endif as per the Deliverables provided in Appendix 1.</p>
@endif
@if($has('other'))
<p><strong>{{ $roman[$ri++] }}) Other Services</strong></p>
<p>{{ $f['other_description'] ?: 'Such other media services as are described in the Order Form.' }}</p>
@endif

<h2>{{ $clause() }}. CLIENT'S DUTIES AND OBLIGATIONS</h2>
<p>The Client shall:</p>
<ol type="a">
  <li>Provide the Service Provider with timely access to all necessary brand assets, including but not limited to, high-resolution logos, style guides, existing media libraries, and proprietary information as may be required for the performance of the Services.</li>
  <li>Acknowledge that the Service Provider's delivery schedule is contingent upon prompt feedback and collaboration, and shall as such review all presented ideas, strategies, scripts, and creative drafts within a reasonable time, but in any case not later than {{ $f['feedback_days_words'] }} ({{ $f['feedback_days'] }}) business days of receipt. Failure to provide written approval or consolidated feedback within this time-frame may result in delays and a shift in the final delivery dates and may, at the Service Provider's discretion, be deemed as approval of the work submitted by the Service Provider.</li>
  @if($hasPhoto || $hasVideo)
  <li>For all scheduled on-site productions, be responsible for securing necessary permits, location clearances, transport and parking for the Service Provider. The Client shall ensure that the filming environment is in a condition suitable for professional capture at the agreed-upon start time. Any delays caused by the Client's failure to prepare the location or ensure the arrival of required internal talent may be billed at the Service Provider's hourly rate.</li>
  @endif
  @if($hasSocialMgmt)
  <li>Grant the Service Provider all necessary administrative permissions and secure access credentials for the social media platforms designated in the scope of work. The Client remains responsible for maintaining the primary security of these accounts and shall notify the Service Provider immediately of any unauthorized access or password changes.</li>
  @endif
  <li>Take full responsibility for the factual accuracy of all information provided to the Service Provider regarding the Client's products, services, or professional claims. The Client shall ensure that all content approved for publication complies with relevant industry regulations and consumer protection laws.</li>
</ol>

<h2>{{ $clause() }}. FEES AND PAYMENT TERMS</h2>
<ol type="a">
  <li>In consideration for the Services rendered by the Service Provider, the Client agrees to pay a total contract fee of Kenya Shillings {!! $f['fee_words'] ? '<strong>'.e($f['fee_words']).'</strong>' : '' !!} (<strong>Kshs. {!! $v('fee_formatted') !!}</strong>) exclusive of taxes. A {{ $f['deposit_percent'] }}% non-refundable deposit @if($f['fee'] > 0)of <strong>Kshs. {{ number_format($f['fee'] * $f['deposit_percent'] / 100, 2) }}</strong>@endif shall be paid upon the execution of this Agreement. The Service Provider shall not be obligated to commence any work or reserve any production dates until this payment has been received in cleared funds. The balance of the contract fees shall be paid at the completion of filming and production prior to the release of the deliverables. The Service Provider reserves the right to withhold final files and cease account management services until the balance is settled in full.</li>
  <li>Any payment not received within {{ $f['payment_days_words'] }} ({{ $f['payment_days'] }}) days of the invoice due date shall be considered past due, and shall accrue interest at a rate of {{ $f['late_interest'] }}% per month. The Client shall be responsible for all costs of collection, including reasonable attorney's fees.</li>
  <li>The Client shall reimburse the Service Provider for all pre-approved out-of-pocket expenses incurred in connection with the Services, including but not limited to: travel and mileage, specialized equipment rentals, location fees, and third-party stock media licenses. Invoices for expenses shall be paid within seven (7) days of presentation.</li>
  <li>In the event of a payment default, the Service Provider maintains the right to immediately suspend all services&mdash;including social media account management and content publishing&mdash;without liability. Services will resume only upon the receipt of all outstanding balances, including late fees.</li>
</ol>

<h2>{{ $clause() }}. INTELLECTUAL PROPERTY</h2>
<p>The Service Provider and the Client shall have joint copyright and intellectual property rights of all original and edited works created during the provision of the Services. Upon full payment, the Client is granted a perpetual right of use. The Client and the Service Provider shall not sell or transfer these images to third parties without prior written consent.</p>

<h2>{{ $clause() }}. CONTENT APPROVAL AND REVISIONS</h2>
<p>The Client is entitled to {{ $f['revision_rounds_words'] }} ({{ $f['revision_rounds'] }}) rounds of minor revisions for video content. Any additional edits or changes to the creative direction after the shoot will be billed at the Service Provider's hourly rate. The Service Provider maintains ultimate creative control regarding the artistic style and editing of the photography.</p>

<h2>{{ $clause() }}. CANCELLATION AND RESCHEDULING</h2>
<p>Requests for rescheduling must be made at least {{ $f['reschedule_days_words'] }} ({{ $f['reschedule_days'] }}) days in advance and the parties will work in good faith to find a mutually agreeable makeup date. If the Client cancels the session within {!! $v('cancellation_hours') !!} hours of the scheduled start time, the Service Provider's hourly fee shall apply.</p>

<h2>{{ $clause() }}. CONFIDENTIALITY</h2>
<p>Both parties agree to keep all proprietary information, including brand assets and materials, marketing strategies, pricing, and non-public business data, strictly confidential. This obligation extends beyond the termination of this Agreement.</p>

<h2>{{ $clause() }}. TERMINATION</h2>
<p>Either party may terminate this Agreement by giving the other party <strong>{{ $f['termination_days_words'] }} ({{ $f['termination_days'] }}) days</strong> written notice. In the event of termination, the Client shall pay for all work completed up to the date of termination, and the Service Provider shall deliver any completed work for which payment has been received. If the termination is occasioned on account of breach by the Service Provider, the deposit shall be returned in full to the Client. The Client shall forfeit the deposit if the termination is on account of breach by the Client.</p>

<h2>{{ $clause() }}. GOVERNING LAW</h2>
<p>This Agreement shall be governed by the Laws of Kenya and the language of the Agreement shall be the English language. The parties shall attempt to resolve all disputes arising out of this Agreement in a spirit of co-operation without formal proceedings. Any dispute which cannot be so resolved shall be referred to arbitration in accordance with the provisions of the Arbitration Act, the seat of which shall be agreed between the parties.</p>

@if(trim((string) $f['special_terms']) !== '')
<h2>{{ $clause() }}. SPECIAL CONDITIONS</h2>
<p>{!! nl2br(e($f['special_terms'])) !!}</p>
@endif

<h2>{{ $clause() }}. ENTIRE AGREEMENT</h2>
<p>This Agreement contains the entire agreement between the parties as to the subject hereof. The Agreement may not be modified or amended except in writing signed by both the parties. If any provision of this Agreement is deemed invalid or unenforceable under applicable law, the remaining provisions will continue in full force and effect. Failure by the Client to enforce any provision of this Agreement shall not constitute or be construed as a waiver of that provision or of the right to enforce it at a later time.</p>
<p>IN WITNESS WHEREOF the duly authorized representatives of the parties have, on the date on the first page, executed this Agreement.</p>

<h2 class="appendix">APPENDIX 1 &mdash; DELIVERABLES</h2>
@if($hasPhoto)
<h3>{{ $subLetter() }}) Photography and Reels</h3>
<p>The Service Provider shall provide the following: {!! $v('photo_count') !!} high-resolution edited images and {!! $v('reel_count') !!} short-form videos per {{ strtolower($f['delivery_period']) }}. Deliverables will be provided via {{ $f['delivery_method'] }} within {!! $v('photo_delivery_days') !!} business days following the shoot.@if($f['event_date']) The Event is scheduled for <strong>{{ $f['event_date'] }}</strong>.@endif</p>
@endif
@if($hasVideo)
<h3>{{ $subLetter() }}) Videography</h3>
<p>The Service Provider shall perform professional Videography Services including:</p>
<ol type="i">
  <li>Production: {!! $v('filming_sessions') !!} filming session(s) per {{ strtolower($f['delivery_period']) }}, each lasting up to {!! $v('session_hours') !!} hours at {!! $v('location') !!}.</li>
  <li>Equipment: Provision of all necessary cameras, lighting, and audio recording equipment.</li>
  <li>Post-Production: Professional editing including colour grading, sound design, licensed background music, and {{ $f['revision_rounds_words'] }} ({{ $f['revision_rounds'] }}) rounds of revisions.</li>
  <li>Deliverables: {!! $v('video_count') !!} high-definition final video(s) ({{ $f['video_resolution'] }} resolution) delivered in {{ $f['video_format'] }} format via {{ $f['video_platform'] }}.</li>
  <li>Turnaround: Final files will be delivered within {!! $v('video_delivery_days') !!} business days following the final shoot date.</li>
</ol>
@endif
@if($hasSocialContent)
<h3>{{ $subLetter() }}) Social Media Ideation &amp; Content Strategy</h3>
<p>The Service Provider shall develop and present a Monthly Content Strategy which includes:</p>
<ol type="i">
  <li>Ideation: A monthly 'Content Calendar' outlining creative concepts, trending audio suggestions, and visual themes tailored to the Client's brand voice.</li>
  <li>Scripting &amp; Storyboarding: Creation of scripts for video content and copy for social media captions, including relevant hashtag research and Call-to-Action (CTA) optimization.</li>
  <li>Competitive Analysis: Brief monthly review of industry trends to ensure content remains relevant and competitive.</li>
</ol>
@endif
@if($hasSocialMgmt)
<h3>{{ $subLetter() }}) Social Media Account Management</h3>
<p>The Service Provider shall provide ongoing Account Management for the following platforms: {!! $v('platforms') !!}. Details and login credentials shall be provided by the Client. Services include:</p>
<ol type="i">
  <li>Posting &amp; Scheduling: Managing the automated or manual posting of approved content according to the agreed-upon frequency ({!! $v('posts_per_week') !!} posts per week).</li>
  <li>Community Engagement: Dedicated {!! $v('engagement_hours') !!} hours per week for responding to comments and direct messages (DMs) to foster community growth.</li>
  <li>Insights &amp; Reporting: Provision of a monthly Analytics Report detailing key performance indicators (KPIs) such as reach, engagement rate, and follower growth.</li>
  <li>Account Security: The Service Provider agrees to use secure password management tools and will not share login credentials with unauthorized third parties.</li>
</ol>
@endif
@if($has('other'))
<h3>{{ $subLetter() }}) Other Services</h3>
<p>{{ $f['other_description'] ?: 'As described in the Order Form.' }}</p>
@endif
@if($quote && !empty($quote->items))
<h3>{{ $subLetter() }}) Commercial Schedule (Quotation {{ $quote->quote_number }})</h3>
<table>
  <thead><tr><th>Deliverable</th><th>Qty</th><th>Amount (KES)</th></tr></thead>
  <tbody>
    @foreach($quote->items as $item)
      @php
        $qty = (float) ($item['quantity'] ?? $item['qty'] ?? 1);
        $amt = (float) ($item['amount'] ?? ($qty * (float) ($item['rate'] ?? 0)));
      @endphp
      <tr><td>{{ $item['description'] ?? $item['name'] ?? 'Deliverable' }}</td><td>{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td><td>{{ number_format($amt, 2) }}</td></tr>
    @endforeach
  </tbody>
</table>
@endif

<h2 class="appendix">APPENDIX 2 &mdash; CONSENT FOR USE OF PERSONAL DATA</h2>
<p>I, the Client's authorized signatory named below, in my own capacity / as the Authorized Signatory of <strong>{{ $party['client_name'] }}</strong>, pursuant to Section 32 of the Data Protection Act, 2019 (and pursuant to all related Laws and Provisions) hereby grant the Service Provider express permission to collect, store, and process my personal data in the form of photographs and video recordings or other formats of personal data pursuant to a Photography, Videography and Social Media Agreement existing between the parties. I allow the Service Provider to use the said data on the Service Provider's official website and professional portfolio; I authorize publication on websites, social media platforms and other platforms (e.g., Instagram, TikTok, LinkedIn, Facebook) to advertise the Service Provider's services; and I authorize inclusion in digital or printed marketing materials, including brochures and brand presentations.</p>
<p>I understand that my image and/or video likeness may be used to advance the commercial interests of the Service Provider. The Service Provider has informed me that the data is processed solely for marketing and service promotion.</p>
<p>This consent is valid for a period of Five (5) years from the date of termination of the contract, after which the data will be archived or deleted unless fresh consent is sought. My data will not be shared with third parties. I acknowledge my rights to access and rectification, to request to see how my data is being used or to ask for corrections. I have the right to withdraw this consent at any time by providing written notice to the Service Provider. I understand that withdrawal does not affect the lawfulness of processing done prior to the withdrawal. I have the right to request deletion of my images from the Service Provider's active marketing channels.</p>
<p><strong>Affirmation:</strong> I confirm that this consent is freely given, specific, and unambiguous. I have not been coerced into signing this form, and my access to the primary services was not made conditional upon granting this promotional consent.</p>
<p><strong>Minors:</strong> If the subject is under 18 years of age, the parent or legal guardian of the minor provides this explicit consent on their behalf as required by Section 33 of the Act.</p>
