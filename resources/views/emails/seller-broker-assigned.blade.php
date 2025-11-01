@extends('emails.layouts.base')
@section('title', 'Broker Assigned to Your Property')

@section('content')
  <h1 style="font-size:20px; margin:0 0 12px 0;">Broker Assigned to Your Property</h1>
  <p>Dear <strong>{{ $sellerName }}</strong>,</p>
  <p>Great news! We have assigned a professional real estate broker to assist you with your property listing request.</p>

  <div class="card" style="border-left:4px solid #2563eb;">
    <h3 style="margin:0 0 8px 0;">📋 Your Property Details</h3>
    <p><strong>Property:</strong> {{ $propertyTitle }}</p>
    <p><strong>Location:</strong> {{ $propertyLocation }}</p>
    <p><strong>Asking Price:</strong> <span style="color:#2563eb; font-weight:bold;">₱{{ $askingPrice }}</span></p>
    <p><strong>Request ID:</strong> #{{ $requestId }}</p>
  </div>

  <div class="card" style="border-left:4px solid #10b981;">
    <h3 style="margin:0 0 8px 0;">👨‍💼 Your Assigned Broker</h3>
    <p><strong>Name:</strong> {{ $brokerName }}</p>
    <p><strong>Email:</strong> <a href="mailto:{{ $brokerEmail }}">{{ $brokerEmail }}</a></p>
    <p><strong>Phone:</strong> <a href="tel:{{ $brokerPhone }}">{{ $brokerPhone }}</a></p>
    <p><strong>License Number:</strong> {{ $brokerLicense }}</p>
  </div>

  <div class="card" style="background-color:#fef3c7; border-left:4px solid #f59e0b;">
    <h3 style="margin:0 0 8px 0;">📞 What Happens Next?</h3>
    <ul style="margin:0 0 0 18px; padding:0;">
      <li><strong>{{ $brokerName }}</strong> will contact you within 24-48 hours</li>
      <li>They will schedule a property visit and assessment</li>
      <li>Professional photos and marketing materials will be prepared</li>
      <li>Your property will be listed on our platform and partner sites</li>
      <li>You'll receive regular updates on inquiries and showings</li>
    </ul>
  </div>

  <p><strong>Important:</strong> Please be ready to provide any additional property documents or information your broker may request to ensure the best possible listing.</p>
  <p>If you have any questions or concerns, please don't hesitate to contact your assigned broker directly or reach out to our support team.</p>
  <p>Thank you for choosing GeoCasa Bohol for your real estate needs!</p>

  <p>Best regards,<br>
    <strong>{{ $assignedBy }}</strong><br>
    GeoCasa Bohol Team
  </p>
@endsection