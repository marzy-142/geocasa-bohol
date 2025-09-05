<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broker Assigned to Your Property</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border: 1px solid #e2e8f0;
        }
        .property-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .broker-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .next-steps {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .footer {
            background-color: #374151;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
        .highlight {
            color: #2563eb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 GeoCasa Bohol</h1>
        <h2>Broker Assigned to Your Property</h2>
    </div>

    <div class="content">
        <p>Dear <strong>{{ $sellerName }}</strong>,</p>

        <p>Great news! We have assigned a professional real estate broker to assist you with your property listing request.</p>

        <div class="property-details">
            <h3>📋 Your Property Details</h3>
            <p><strong>Property:</strong> {{ $propertyTitle }}</p>
            <p><strong>Location:</strong> {{ $propertyLocation }}</p>
            <p><strong>Asking Price:</strong> <span class="highlight">₱{{ $askingPrice }}</span></p>
            <p><strong>Request ID:</strong> #{{ $requestId }}</p>
        </div>

        <div class="broker-info">
            <h3>👨‍💼 Your Assigned Broker</h3>
            <p><strong>Name:</strong> {{ $brokerName }}</p>
            <p><strong>Email:</strong> <a href="mailto:{{ $brokerEmail }}">{{ $brokerEmail }}</a></p>
            <p><strong>Phone:</strong> {{ $brokerPhone }}</p>
            <p><strong>License Number:</strong> {{ $brokerLicense }}</p>
        </div>

        <div class="next-steps">
            <h3>📞 What Happens Next?</h3>
            <ul>
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
        GeoCasa Bohol Team</p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} GeoCasa Bohol. All rights reserved.</p>
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>For support, contact us at support@geocasa-bohol.com</p>
    </div>
</body>
</html>