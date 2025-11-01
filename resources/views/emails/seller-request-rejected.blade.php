@extends('emails.layouts.base')
@section('title', 'Update on Your Seller Request')

@section('content')
  <h1 style="font-size:20px; margin:0 0 12px; color:#111827;">Update on your seller request</h1>
  <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">Hi {{ $sellerRequest->name }},</p>
  <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">We reviewed your property request "<strong>{{ $sellerRequest->property_title }}</strong>". Unfortunately, it was not approved at this time.</p>

  @if(!empty($rejectionReason))
    <div class="card">
      <div style="font-weight:600; color:#111827; margin-bottom:8px;">Reason provided</div>
      <div style="color:#374151; font-size:14px; line-height:1.6;">{{ $rejectionReason }}</div>
    </div>
  @endif

  <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">If you have questions or would like guidance to resubmit, simply reply to this email and we’ll be happy to help.</p>
  <p class="muted" style="margin-top:16px;">Thank you for considering GeoCasa Bohol.</p>
@endsection
