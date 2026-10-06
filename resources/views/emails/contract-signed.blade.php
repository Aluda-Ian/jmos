@extends('emails.layout')

@php $b = config('jeota'); @endphp

@section('content')
  <div class="headline">Agreement signed ✓</div>
  <p class="body-text">
    The agreement <strong>{{ $contract->contract_number }}</strong> ({{ $contract->title }}) between
    <strong>{{ $b['company'] }}</strong> and <strong>{{ $contract->client_name }}</strong> was signed by
    <strong>{{ $contract->client_signed_name }}</strong>{{ $contract->client_signed_position ? ', '.$contract->client_signed_position : '' }}
    on {{ $contract->signed_at?->timezone('Africa/Nairobi')->format('d F Y \a\t H:i') }} (EAT).
  </p>

  <div style="text-align:center;margin:0 0 24px">
    <a href="{{ $signUrl }}" class="btn-action" style="background-color:#D62828;color:#FFFFFF;text-decoration:none;display:inline-block;padding:13px 28px;border-radius:8px;font-weight:600;font-size:14px">View / Download Signed Copy</a>
  </div>

  <p class="body-text" style="font-size:13px;color:#6E6763">
    Keep this email for your records. Open the link above and choose <em>Print / Save PDF</em> to keep a PDF copy.
    For anything else, write to <a href="mailto:{{ $b['email'] }}" style="color:#D62828">{{ $b['email'] }}</a>.
  </p>
@endsection
