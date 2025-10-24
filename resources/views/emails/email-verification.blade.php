<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - GeoCasa Bohol</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #1a202c;
            background-color: #f7fafc;
            margin: 0;
            padding: 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .header {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: white;
            padding: 48px 32px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .tagline {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .content {
            padding: 48px 32px;
        }
        
        .greeting {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 24px;
            letter-spacing: -0.5px;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 32px;
            color: #4a5568;
        }
        
        .cta-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2b6cb0 0%, #3182ce 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 14px 0 rgba(49, 130, 206, 0.3);
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(49, 130, 206, 0.4);
        }
        
        .info-section {
            background-color: #edf2f7;
            border-radius: 12px;
            padding: 24px;
            margin: 32px 0;
            border-left: 4px solid #3182ce;
        }
        
        .info-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-text {
            color: #4a5568;
            font-size: 14px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 32px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background-color: #f7fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .feature-icon {
            font-size: 20px;
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }
        
        .feature-text {
            font-size: 14px;
            color: #4a5568;
            font-weight: 500;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 40px 0;
        }
        
        .footer {
            background-color: #f7fafc;
            padding: 32px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-brand {
            font-weight: 700;
            color: #2d3748;
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .footer-tagline {
            color: #718096;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        .footer-links {
            margin: 24px 0;
        }
        
        .footer-links a {
            color: #3182ce;
            text-decoration: none;
            margin: 0 16px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .footer-contact {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .security-notice {
            background-color: #fef5e7;
            border: 1px solid #f6ad55;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            font-size: 14px;
            color: #744210;
        }
        
        .security-notice strong {
            color: #744210;
        }
        
        .disclaimer {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 24px;
            line-height: 1.5;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 32px 24px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-links a {
                display: block;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo">GeoCasa Bohol</div>
                <div class="tagline">Your Trusted Real Estate Partner</div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Welcome, {{ $user->name }}!</div>
            
            <div class="message">
                @if($user->role === 'broker')
                    Thank you for your interest in joining GeoCasa Bohol as a licensed real estate broker. We're excited to review your application and welcome you to our professional network.
                @else
                    Thank you for choosing GeoCasa Bohol as your real estate partner. We're excited to help you discover the perfect property in beautiful Bohol.
                @endif
            </div>

            <div class="message">
                @if($user->role === 'broker')
                    To complete your broker application and allow our admin team to review your credentials, please verify your email address:
                @else
                    To complete your registration and access our exclusive property listings, please verify your email address:
                @endif
            </div>

            <div class="cta-container">
                <a href="{{ $verificationUrl }}" class="cta-button">
                    Verify Email Address
                </a>
            </div>

            <div class="info-section">
                <div class="info-title">Important Notice</div>
                <div class="info-text">This verification link expires in 24 hours for your security. If you didn't create an account with us, please ignore this email.</div>
            </div>

            <div class="divider"></div>

            <div class="message">
                <strong>What's Next?</strong><br>
                @if($user->role === 'broker')
                    After email verification, your application will be reviewed by our admin team. Once approved, you'll have access to:
                @else
                    After verification, you'll have access to:
                @endif
            </div>

            <div class="features-grid">
                @if($user->role === 'broker')
                    <div class="feature-item">
                        <div class="feature-icon">📋</div>
                        <div class="feature-text">Application Review Process</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🏢</div>
                        <div class="feature-text">Broker Dashboard Access</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🏠</div>
                        <div class="feature-text">Property Management Tools</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📊</div>
                        <div class="feature-text">Client Transaction Tracking</div>
                    </div>
                @else
                    <div class="feature-item">
                        <div class="feature-icon">🏠</div>
                        <div class="feature-text">Premium Property Listings</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🤝</div>
                        <div class="feature-text">Trusted Broker Network</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📋</div>
                        <div class="feature-text">Property Inquiries</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📊</div>
                        <div class="feature-text">Transaction Tracking</div>
                    </div>
                @endif
            </div>

            <div class="security-notice">
                @if($user->role === 'broker')
                    <strong>Application Process:</strong> Your broker application will be reviewed by our admin team after email verification. This typically takes 1-3 business days. You'll receive email notifications about your application status.
                @else
                    <strong>Security:</strong> Your account will remain inactive until email verification is complete. This helps us maintain a secure platform for all users.
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-brand">GeoCasa Bohol</div>
            <div class="footer-tagline">Connecting you to Bohol's finest properties</div>
            
            <div class="footer-links">
                <a href="#">Browse Properties</a>
                <a href="#">Find Brokers</a>
                <a href="#">Contact Support</a>
            </div>
            
            <div class="divider"></div>
            
            <div class="footer-contact">
                <div>📧 support@geocasa-bohol.com</div>
                <div>🌐 www.geocasa-bohol.com</div>
                <div>📍 Tagbilaran City, Bohol, Philippines</div>
            </div>
            
            <div class="disclaimer">
                This email was sent to {{ $user->email }}. If you have questions about this verification request, please contact our support team.
            </div>
        </div>
    </div>
</body>
</html>
