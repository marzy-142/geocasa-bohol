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

@if($inquiry->property->slug)
View Property Online: {{ route('properties.show', $inquiry->property->slug) }}
@endif

---
This is an automated message from GeoCasa Bohol Property Management System.
Please reply directly to this email or contact the broker to continue the conversation.

© {{ date('Y') }} GeoCasa Bohol. All rights reserved.
