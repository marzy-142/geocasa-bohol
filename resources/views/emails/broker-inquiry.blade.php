<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Inquiry</title>
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
        .content {
            padding: 30px 20px;
        }
        .inquiry-type {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0;
        }
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .message-box {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border: 1px solid #e5e7eb;
        }
        .contact-info {
            margin: 15px 0;
        }
        .contact-info div {
            margin: 8px 0;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>📬 New Inquiry from Directory</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Someone is interested in your services</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Hello {{ $broker->name }},</p>
            
            <p>You've received a new inquiry through the GeoCasa Bohol Broker Directory!</p>

            <!-- Inquiry Type -->
            <div style="text-align: center; margin: 20px 0;">
                <span class="inquiry-type">
                    {{ ucfirst(str_replace('_', ' ', $inquiryData['inquiry_type'])) }} Inquiry
                </span>
            </div>

            <!-- Contact Information -->
            <div class="info-box">
                <h3 style="margin-top: 0; color: #1f2937;">Contact Information</h3>
                <div class="contact-info">
                    <div><strong>Name:</strong> {{ $inquiryData['name'] }}</div>
                    <div><strong>Email:</strong> <a href="mailto:{{ $inquiryData['email'] }}">{{ $inquiryData['email'] }}</a></div>
                    @if(!empty($inquiryData['phone']))
                    <div><strong>Phone:</strong> {{ $inquiryData['phone'] }}</div>
                    @endif
                </div>
            </div>

            <!-- Message -->
            <div class="message-box">
                <h3 style="margin-top: 0; color: #1f2937;">Message</h3>
                <p style="white-space: pre-wrap; margin: 0;">{{ $inquiryData['message'] }}</p>
            </div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:{{ $inquiryData['email'] }}" class="button">
                    Reply to {{ $inquiryData['name'] }}
                </a>
            </div>

            <!-- Tips -->
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong>💡 Quick Response Tips:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Respond within 24 hours for best results</li>
                    <li>Personalize your response based on their inquiry type</li>
                    <li>Provide clear next steps</li>
                    <li>Include your contact information</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>GeoCasa Bohol</strong></p>
            <p>This inquiry was sent through your public broker profile</p>
            <p style="font-size: 12px; margin-top: 15px;">
                To manage your directory settings, log in to your broker dashboard
            </p>
        </div>
    </div>
</body>
</html>
