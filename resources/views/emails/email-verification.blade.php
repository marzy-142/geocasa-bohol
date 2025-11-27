@extends('emails.layouts.base')
@section('title','Verify Your Email')
@section('content')
<h1 style="margin-top:0;">Welcome, {{ $user->name }}!</h1>
<p>
@if($user->role === 'broker')
Thank you for starting your broker journey with <strong>GeoCasa Bohol</strong>. Verify your email so we can begin reviewing your credentials.
@else
Thanks for joining <strong>GeoCasa Bohol</strong>. Verify your email to unlock full access to our property discovery experience.
@endif
</p>
<div class="card">
    <p style="margin-top:0; font-weight:600;">Action Required</p>
    <p style="margin:8px 0 16px;">Please confirm your email within the next 24 hours. This helps us keep the platform secure.</p>
    <p style="text-align:center; margin:0 0 8px;">
        <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
    </p>
    <p style="font-size:12px; color:#737373; text-align:center; margin:12px 0 0;">If the button doesn't work, copy & paste this URL:<br><span style="word-break:break-all;">{{ $verificationUrl }}</span></p>
</div>

<h2>What Happens Next?</h2>
@if($user->role === 'broker')
<p>After verification our admin team reviews your application (usually 1–3 business days). You’ll then gain access to:</p>
<ul style="padding-left:20px; margin:12px 0;">
  <li>Broker dashboard & portfolio tools</li>
  <li>Property listing management</li>
  <li>Client inquiry & transaction tracking</li>
  <li>Structured commission workflow</li>
</ul>
@else
<p>After verification you can:</p>
<ul style="padding-left:20px; margin:12px 0;">
  <li>Browse enhanced Bohol property listings</li>
  <li>Connect with trusted local brokers</li>
  <li>Submit and track property inquiries</li>
  <li>Follow transaction progress securely</li>
</ul>
@endif

<div class="panel" style="margin-top:24px;">
    <strong>Security Tip:</strong> We’ll never ask for your password via email. Ignore any suspicious requests and contact support if needed.
</div>

<p style="font-size:12px; color:#737373; margin-top:32px;">This message was sent to {{ $user->email }}. If you didn’t create an account, you can safely ignore it.</p>
@endsection
