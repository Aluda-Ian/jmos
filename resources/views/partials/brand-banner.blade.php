@php $b = config('jeota'); @endphp
<div class="q-banner" @if(!empty($dark)) style="background:linear-gradient(100deg,#17161A 0%,#3a1112 60%,#8B0714 100%)" @endif>
  <div>
    <div class="co">JEOTA MEDIA LTD</div>
    <div class="tag">{{ $tag ?? $b['tagline'] }}</div>
    @isset($status)
      <span class="status-pill">{{ $status }}</span>
    @endisset
  </div>
  <div class="right">
    <div class="contact">
      <div><a href="mailto:{{ $b['email'] }}">{{ $b['email'] }}</a></div>
      <div><a href="tel:{{ $b['phone_e164'] }}">{{ $b['phone'] }}</a></div>
      <div><a href="{{ $b['website'] }}" target="_blank" rel="noopener">{{ $b['website_label'] }}</a></div>
      <div>{{ $b['address_line1'] }}</div>
      <div>{{ $b['address_line2'] }}</div>
    </div>
    <div class="q-logo"><img src="{{ asset('assets/img/jeota-logo.png') }}" alt="Jeota Media"></div>
  </div>
</div>
