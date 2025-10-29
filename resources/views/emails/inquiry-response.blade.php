<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Inquiry Response</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #1f2937;
            font-size: 24px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .property-info {
            background-color: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .property-info h3 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 18px;
        }
        .property-info p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .response-section {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .response-section h3 {
            margin: 0 0 15px 0;
            color: #1e40af;
            font-size: 16px;
        }
        .response-text {
            color: #374151;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .broker-signature {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 14px;
        }
        .contact-info {
            background-color: #f9fafb;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .contact-info h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 16px;
        }
        .contact-item {
            margin: 8px 0;
            color: #4b5563;
            font-size: 14px;
        }
        .contact-item strong {
            color: #1f2937;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 500;
        }
        .button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Response to Your Property Inquiry</h1>
        </div>

        <div class="greeting">
            <p>Dear {{ $inquiry->name }},</p>
            <p>Thank you for your interest in our property. We've received your inquiry and {{ $brokerName }} has responded to your request.</p>
        </div>

        <div class="property-info">
            <h3>{{ $inquiry->property->title }}</h3>
            <p><strong>Type:</strong> {{ ucfirst($inquiry->property->type) }}</p>
            <p><strong>Location:</strong> {{ $inquiry->property->municipality }}, {{ $inquiry->property->province }}</p>
            @if($inquiry->property->total_price)
                <p><strong>Price:</strong> ₱{{ number_format((float)$inquiry->property->total_price) }}</p>
            @endif
        </div>

        <div class="response-section">
            <h3>Message from {{ $brokerName }}:</h3>
            <div class="response-text">{{ $brokerResponse }}</div>
            <div class="broker-signature">
                <strong>{{ $brokerName }}</strong><br>
                Real Estate Broker
            </div>
        </div>

        <div class="contact-info">
            <h3>Your Inquiry Details</h3>
            <div class="contact-item"><strong>Your Email:</strong> {{ $inquiry->email }}</div>
            @if($inquiry->phone)
                <div class="contact-item"><strong>Your Phone:</strong> {{ $inquiry->phone }}</div>
            @endif
            <div class="contact-item"><strong>Inquiry Date:</strong> {{ $inquiry->created_at->format('F d, Y') }}</div>
            @if($inquiry->message)
                <div class="contact-item" style="margin-top: 15px;">
                    <strong>Your Message:</strong><br>
                    <span style="color: #6b7280; font-style: italic;">"{{ $inquiry->message }}"</span>
                </div>
            @endif
        </div>

        @if($inquiry->property->slug && \Illuminate\Support\Facades\Route::has('public.properties.show'))
            <div style="text-align: center;">
                <a href="{{ route('public.properties.show', $inquiry->property->slug) }}" class="button">
                    View Property Details
                </a>
            </div>
        @endif

        <div class="footer">
            <p>This is an automated message from GeoCasa Bohol Property Management System.</p>
            <p>Please reply directly to this email or contact the broker to continue the conversation.</p>
            <p>&copy; {{ date('Y') }} GeoCasa Bohol. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
