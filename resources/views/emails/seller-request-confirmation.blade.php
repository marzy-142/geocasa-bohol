@extends('emails.layouts.base')
@section('title', 'Property Listing Request Confirmed')

@section('content')
  <div style="text-align:center; margin-bottom:16px;">
    <div style="display:inline-flex; width:60px; height:60px; background-color:#10b981; border-radius:9999px; align-items:center; justify-content:center;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white" style="width: 32px; height: 32px;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
      </svg>
    </div>
    <h1 style="margin: 12px 0 0 0; font-size:22px;">Property Listing Request Confirmed!</h1>
    <p class="muted" style="margin: 6px 0 0 0;">Thank you for choosing GeoCasa Bohol</p>
  </div>

  <p>Dear {{ $sellerRequest->name }},</p>
  <p>Great news! Your property listing request has been successfully submitted and a broker has been assigned to assist you.</p>

  <div class="card" style="border-left:4px solid #3b82f6;">
    <strong>Request ID:</strong> #{{ $sellerRequest->id }}<br>
    <small class="muted">Please keep this ID for your records</small>
  </div>

  @if($assignedBroker)
    <div class="card" style="border:2px solid #4f46e5; background:linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
      <h3 style="margin:0 0 8px 0; color:#4f46e5;">Your Assigned Broker</h3>
      <p style="margin: 5px 0 15px 0; color: #6b7280;">
        {{ $assignmentMethod === 'manual' ? 'You selected' : 'We matched you with' }} a verified professional
        <span style="display:inline-block; background-color:#10b981; color:#fff; padding:2px 10px; border-radius:12px; font-size:12px; font-weight:600; margin-left:6px;">✓ Verified</span>
      </p>
      <h4 style="margin: 15px 0 5px 0; font-size: 18px;">{{ $assignedBroker->name }}</h4>
      @if($assignedBroker->brokerage_firm_name)
        <p style="margin: 0 0 15px 0; color: #6b7280;">{{ $assignedBroker->brokerage_firm_name }}</p>
      @endif
      @if($assignedBroker->office_contact_number)
        <div style="margin:8px 0; color:#374151;">Office: {{ $assignedBroker->office_contact_number }}</div>
      @endif
      @if($assignedBroker->city)
        <div style="margin:8px 0; color:#374151;">{{ $assignedBroker->city }}, {{ $assignedBroker->province }}</div>
      @endif
      @if($assignedBroker->years_experience)
        <div style="margin:8px 0; color:#374151;">{{ $assignedBroker->years_experience }} years of experience</div>
      @endif
      <div class="card" style="background:#fef3c7; border-left:4px solid #f59e0b; margin:12px 0 0 0;">
        <strong>{{ $assignedBroker->name }}</strong> will contact you within 24 hours to discuss your property and next steps.
      </div>
    </div>
  @endif

  <div class="card">
    <h4 style="margin:0 0 8px 0;">Your Property Details</h4>
    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
      <span style="font-weight:600; color:#6b7280;">Property Title:</span>
      <span>{{ $sellerRequest->property_title }}</span>
    </div>
    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
      <span style="font-weight:600; color:#6b7280;">Location:</span>
      <span>{{ $sellerRequest->city }}, {{ $sellerRequest->province }}</span>
    </div>
    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e5e7eb;">
      <span style="font-weight:600; color:#6b7280;">Asking Price:</span>
      <span>₱{{ number_format((float)($sellerRequest->asking_price ?? 0), 2) }}</span>
    </div>
    @if($sellerRequest->lot_area)
      <div style="display:flex; justify-content:space-between; padding:8px 0;">
        <span style="font-weight:600; color:#6b7280;">Lot Area:</span>
        <span>{{ $sellerRequest->lot_area }} sqm</span>
      </div>
    @endif
  </div>

  <div style="margin: 16px 0;">
    <h3 style="color: #1f2937;">What Happens Next?</h3>
    <div style="display:flex; margin:12px 0;">
      <div style="width:32px; height:32px; background-color:#10b981; color:#fff; border-radius:9999px; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:12px;">✓</div>
      <div>
        <h4 style="margin:0 0 4px 0;">Broker Assigned</h4>
        <p style="margin:0; color:#6b7280;">{{ $assignedBroker ? $assignedBroker->name . ' has been assigned to your property' : 'A broker has been assigned to your property' }}</p>
      </div>
    </div>
    @php $steps = [
      ['Initial Contact (Within 24 Hours)', 'Your broker will reach out to introduce themselves and discuss your property goals.'],
      ['Property Review', 'Your broker will review details and may schedule a visit to assess the property.'],
      ['Property Listing', 'Once approved, your property will be professionally listed on our platform.'],
      ['Marketing & Showings', 'Your broker will market your property and coordinate showings with buyers.'],
    ]; @endphp
    @foreach($steps as $i => $row)
      <div style="display:flex; margin:12px 0;">
        <div style="width:32px; height:32px; background-color:#3b82f6; color:#fff; border-radius:9999px; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:12px;">{{ $i+1 }}</div>
        <div>
          <h4 style="margin:0 0 4px 0;">{{ $row[0] }}</h4>
          <p style="margin:0; color:#6b7280;">{{ $row[1] }}</p>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card" style="background-color:#fef3c7; border-left:4px solid #f59e0b;">
    <strong>Important:</strong> Please keep your contact information up to date. If you need to update your details, reply to this email with your request ID #{{ $sellerRequest->id }}.
  </div>

  <h3 style="color: #1f2937; margin-top: 16px;">Need Help?</h3>
  <p>If you have any questions, feel free to contact us:</p>
  <ul class="muted">
    <li>📧 Email: support@geocasabohol.com</li>
    <li>📞 Phone: +63 38 123 4567 (Mon-Fri, 8AM-6PM)</li>
  </ul>

  <p class="muted" style="font-size: 12px; margin-top: 15px;">
    This email was sent to {{ $sellerRequest->email }} because you submitted a property listing request.<br>
    Request ID: #{{ $sellerRequest->id }}
  </p>
@endsection
