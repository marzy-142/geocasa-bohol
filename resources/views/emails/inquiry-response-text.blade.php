Response to Your Property Inquiry

Dear {{ $inquiry->name }},

Thank you for your interest in our property. We've received your inquiry and {{ $brokerName }} has responded to your request.

PROPERTY DETAILS:
{{ $inquiry->property->title }}
Type: {{ ucfirst($inquiry->property->type) }}
Location: {{ $inquiry->property->municipality }}, {{ $inquiry->property->province }}
@if($inquiry->property->total_price)
Price: ₱{{ number_format((float)$inquiry->property->total_price) }}
@endif

MESSAGE FROM {{ strtoupper($brokerName) }}:
{{ $brokerResponse }}

-- 
{{ $brokerName }}
Real Estate Broker

YOUR INQUIRY DETAILS:
Email: {{ $inquiry->email }}
@if($inquiry->phone)
Phone: {{ $inquiry->phone }}
@endif
Inquiry Date: {{ $inquiry->created_at->format('F d, Y') }}

@if($inquiry->message)
Your Message: "{{ $inquiry->message }}"
@endif

@if($inquiry->conversation)
Continue Conversation: {{ route('conversations.show', $inquiry->conversation->id) }}
@endif

@if($inquiry->property->slug)
@php($slug = $inquiry->property->slug)
@if(\Illuminate\Support\Facades\Route::has('public.properties.show'))
View Property Online: {{ route('public.properties.show', $slug) }}
@else
View Property Online: {{ url('/browse-properties/'.$slug) }}
@endif
@endif

---
This is an automated message from GeoCasa Bohol Property Management System.
Please reply directly to this email or contact the broker to continue the conversation.

© {{ date('Y') }} GeoCasa Bohol. All rights reserved.
