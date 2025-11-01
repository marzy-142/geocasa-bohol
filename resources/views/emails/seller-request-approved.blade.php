@extends('emails.layouts.base')
@section('title', 'Your Seller Request Has Been Approved')

@section('content')
    <h1 style="font-size:20px; margin:0 0 12px; color:#111827;">Your seller request has been approved</h1>
    <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">Hi {{ $sellerRequest->name }},</p>
    <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">Your property request "<strong>{{ $sellerRequest->property_title }}</strong>" has been approved and is now prepared as a listing.</p>

    @if(isset($property) && $property)
        <div class="card">
            <div style="font-weight:600; color:#111827; margin-bottom:8px;">Listing details</div>
            <ul style="padding-left:18px; margin:0;">
                <li style="font-size:14px; color:#374151; margin:6px 0;"><strong>Title:</strong> {{ $property->title }}</li>
                <li style="font-size:14px; color:#374151; margin:6px 0;"><strong>Location:</strong> {{ $property->location }}</li>
                <li style="font-size:14px; color:#374151; margin:6px 0;"><strong>Total Price:</strong> ₱{{ number_format((float) ($property->total_price ?? 0), 0) }}</li>
                <li style="font-size:14px; color:#374151; margin:6px 0;"><strong>Lot Area (sqm):</strong> {{ number_format((float) ($property->lot_area_sqm ?? 0), 2) }}</li>
            </ul>
            @php
                $publicUrl = null;
                try { $publicUrl = route('public.properties.show', ['property' => $property->slug]); } catch (Throwable $e) {}
            @endphp
            @if(!empty($publicUrl))
                <div style="margin-top:16px;">
                    <a href="{{ $publicUrl }}" class="btn">View Listing</a>
                </div>
            @endif
        </div>
    @endif

  {{-- Contact Your Broker --}}
  @if(isset($broker) && $broker)
    @php
      $photo = null;
      $candidates = [$broker->avatar ?? null, $broker->profile_image ?? null];
      foreach ($candidates as $c) {
        if (!empty($c)) { $photo = (str_starts_with($c, 'http') || str_starts_with($c, '/')) ? $c : url('storage/'.$c); break; }
      }
      if (!$photo) { $photo = url('images/logo.png'); }
    @endphp
    <div class="card" style="display:flex; gap:12px; align-items:center;">
      <img src="{{ $photo }}" alt="Broker Photo" style="width:56px; height:56px; border-radius:9999px; object-fit:cover; border:1px solid #e5e7eb;" />
      <div>
        <div style="font-weight:600; color:#111827;">Contact Your Broker</div>
        <div style="font-size:14px; color:#374151; margin-top:4px;"><strong>{{ $broker->name }}</strong></div>
        <div style="font-size:13px; color:#6b7280;">
          @if(!empty($broker->email)) Email: <a href="mailto:{{ $broker->email }}">{{ $broker->email }}</a> @endif
          @if(!empty($broker->phone)) &nbsp;•&nbsp; Phone: <a href="tel:{{ $broker->phone }}">{{ $broker->phone }}</a> @endif
        </div>
      </div>
    </div>
  @endif    <p style="font-size:14px; line-height:1.6; margin:0 0 12px; color:#374151;">Your assigned broker will contact you shortly with next steps. If you have questions, simply reply to this email.</p>
    <p class="muted" style="margin-top:16px;">Thank you for choosing GeoCasa Bohol.</p>
@endsection
