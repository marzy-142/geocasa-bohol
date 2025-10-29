<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listing Request Confirmed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .content {
            padding: 30px 20px;
        }
        .request-id {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .broker-card {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .broker-card h3 {
            margin-top: 0;
            color: #667eea;
        }
        .info-row {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .info-row svg {
            width: 16px;
            height: 16px;
            margin-right: 10px;
            color: #6b7280;
        }
        .timeline {
            margin: 30px 0;
        }
        .timeline-item {
            display: flex;
            margin: 20px 0;
        }
        .timeline-number {
            width: 32px;
            height: 32px;
            background-color: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .timeline-content h4 {
            margin: 0 0 5px 0;
            color: #1f2937;
        }
        .timeline-content p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .property-details {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .property-details h4 {
            margin-top: 0;
            color: #1f2937;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #1f2937;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .verified-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white" style="width: 32px; height: 32px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1>Property Listing Request Confirmed!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for choosing GeoCasa Bohol</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Dear {{ $sellerRequest->name }},</p>
            
            <p>Great news! Your property listing request has been successfully submitted and a broker has been assigned to assist you.</p>

            <!-- Request ID -->
            <div class="request-id">
                <strong>Request ID:</strong> #{{ $sellerRequest->id }}<br>
                <small style="color: #6b7280;">Please keep this ID for your records</small>
            </div>

            @if($assignedBroker)
            <!-- Broker Information -->
            <div class="broker-card">
                <h3>Your Assigned Broker</h3>
                <p style="margin: 5px 0 15px 0; color: #6b7280;">
                    {{ $assignmentMethod === 'manual' ? 'You selected' : 'We matched you with' }} a verified professional
                    <span class="verified-badge">✓ Verified</span>
                </p>
                
                <h4 style="margin: 15px 0 5px 0; font-size: 18px;">{{ $assignedBroker->name }}</h4>
                @if($assignedBroker->brokerage_firm_name)
                <p style="margin: 0 0 15px 0; color: #6b7280;">{{ $assignedBroker->brokerage_firm_name }}</p>
                @endif

                @if($assignedBroker->office_contact_number)
                <div class="info-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span>{{ $assignedBroker->office_contact_number }}</span>
                </div>
                @endif

                @if($assignedBroker->city)
                <div class="info-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $assignedBroker->city }}, {{ $assignedBroker->province }}</span>
                </div>
                @endif

                @if($assignedBroker->years_experience)
                <div class="info-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $assignedBroker->years_experience }} years of experience</span>
                </div>
                @endif

                <div class="alert" style="margin-top: 15px;">
                    <strong>{{ $assignedBroker->name }}</strong> will contact you within 24 hours to discuss your property and next steps.
                </div>
            </div>
            @endif

            <!-- Property Details -->
            <div class="property-details">
                <h4>Your Property Details</h4>
                <div class="detail-row">
                    <span class="detail-label">Property Title:</span>
                    <span class="detail-value">{{ $sellerRequest->property_title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $sellerRequest->city }}, {{ $sellerRequest->province }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Asking Price:</span>
                    <span class="detail-value">₱{{ number_format($sellerRequest->asking_price, 2) }}</span>
                </div>
                @if($sellerRequest->lot_area)
                <div class="detail-row">
                    <span class="detail-label">Lot Area:</span>
                    <span class="detail-value">{{ $sellerRequest->lot_area }} sqm</span>
                </div>
                @endif
            </div>

            <!-- Timeline -->
            <div class="timeline">
                <h3 style="color: #1f2937;">What Happens Next?</h3>
                
                <div class="timeline-item">
                    <div class="timeline-number" style="background-color: #10b981;">✓</div>
                    <div class="timeline-content">
                        <h4>Broker Assigned</h4>
                        <p>{{ $assignedBroker ? $assignedBroker->name . ' has been assigned to your property' : 'A broker has been assigned to your property' }}</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-number">1</div>
                    <div class="timeline-content">
                        <h4>Initial Contact (Within 24 Hours)</h4>
                        <p>Your broker will reach out to introduce themselves and discuss your property goals.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-number">2</div>
                    <div class="timeline-content">
                        <h4>Property Review</h4>
                        <p>Your broker will review details and may schedule a visit to assess the property.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-number">3</div>
                    <div class="timeline-content">
                        <h4>Property Listing</h4>
                        <p>Once approved, your property will be professionally listed on our platform.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-number">4</div>
                    <div class="timeline-content">
                        <h4>Marketing & Showings</h4>
                        <p>Your broker will market your property and coordinate showings with buyers.</p>
                    </div>
                </div>
            </div>

            <!-- Important Notice -->
            <div class="alert">
                <strong>📌 Important:</strong> Please keep your contact information up to date. If you need to update your details, reply to this email with your request ID #{{ $sellerRequest->id }}.
            </div>

            <!-- Support -->
            <h3 style="color: #1f2937; margin-top: 30px;">Need Help?</h3>
            <p>If you have any questions, feel free to contact us:</p>
            <ul style="color: #6b7280;">
                <li>📧 Email: support@geocasabohol.com</li>
                <li>📞 Phone: +63 38 123 4567 (Mon-Fri, 8AM-6PM)</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>GeoCasa Bohol</strong></p>
            <p>Your trusted partner in Bohol real estate</p>
            <p style="font-size: 12px; margin-top: 15px;">
                This email was sent to {{ $sellerRequest->email }} because you submitted a property listing request.<br>
                Request ID: #{{ $sellerRequest->id }}
            </p>
        </div>
    </div>
</body>
</html>
