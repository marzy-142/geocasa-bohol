# Security Configuration Guide

This document outlines the security configuration options available in the GeoCasa Bohol application.

## Environment Variables

Add the following environment variables to your `.env` file to customize security settings:

### Security Headers

```env
# Enable/disable security headers globally
SECURITY_HEADERS_ENABLED=true

# Content Security Policy
CSP_ENABLED=true
CSP_REPORT_ONLY=false
CSP_REPORT_URI=

# HTTP Strict Transport Security (HSTS)
HSTS_ENABLED=true
HSTS_MAX_AGE=31536000
HSTS_INCLUDE_SUBDOMAINS=true
HSTS_PRELOAD=true

# Other Security Headers
X_FRAME_OPTIONS=DENY
X_CONTENT_TYPE_OPTIONS=nosniff
X_XSS_PROTECTION="1; mode=block"
REFERRER_POLICY=strict-origin-when-cross-origin
```

### Rate Limiting

```env
# Enable/disable rate limiting globally
RATE_LIMITING_ENABLED=true

# Rate limits for different endpoint types (format: max_attempts,decay_minutes)
RATE_LIMIT_AUTH=5,1
RATE_LIMIT_API_PUBLIC=30,1
RATE_LIMIT_API_USER=60,1
RATE_LIMIT_API_BROKER=100,1
RATE_LIMIT_API_ADMIN=200,1
RATE_LIMIT_FILE_UPLOAD=10,1

# Rate limit headers
RATE_LIMIT_INCLUDE_HEADERS=true
RATE_LIMIT_RETRY_AFTER_HEADER=true
```

### CORS Configuration

```env
# Frontend URL for CORS
FRONTEND_URL=http://localhost:3000
```

### Security Logging

```env
# Enable security event logging
SECURITY_LOGGING_ENABLED=true
SECURITY_LOG_CHANNEL=daily
LOG_RATE_LIMITS=true
LOG_SECURITY_HEADERS=false
```

## Security Features Implemented

### 1. Security Headers Middleware

- **Content Security Policy (CSP)**: Prevents XSS attacks by controlling resource loading
- **HTTP Strict Transport Security (HSTS)**: Forces HTTPS connections
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **X-XSS-Protection**: Enables browser XSS filtering
- **Referrer Policy**: Controls referrer information leakage
- **Permissions Policy**: Controls browser feature access

### 2. Rate Limiting

- **API Rate Limiting**: Configurable limits for different user types
- **Authentication Rate Limiting**: Strict limits on login attempts
- **File Upload Rate Limiting**: Separate limits for file operations
- **Granular Control**: Different limits for public, user, broker, and admin endpoints

### 3. CORS Configuration

- **Flexible Origin Control**: Support for multiple frontend domains
- **Credential Support**: Secure cookie and authentication handling
- **Method and Header Control**: Precise control over allowed operations

### 4. File Security

- **Virus Scanning**: Integration with ClamAV for malware detection
- **Content Validation**: MIME type and extension verification
- **Rate Limiting**: Separate limits for file upload operations
- **Dangerous Extension Blocking**: Prevention of executable file uploads

## Configuration Files

### Security Configuration (`config/security.php`)

Centralized configuration for all security-related settings including:
- Security headers configuration
- Rate limiting settings
- CSP directives
- Permissions policy
- Sensitive route patterns
- Logging configuration

### CORS Configuration (`config/cors.php`)

Cross-Origin Resource Sharing settings:
- Allowed origins, methods, and headers
- Credential support
- Exposed headers for rate limiting

### File Security Configuration (`config/file-security.php`)

File upload security settings:
- Allowed file types and extensions
- Virus scanning configuration
- Upload rate limiting
- Content validation rules

## Middleware Usage

### Global Middleware

- `SecurityHeadersMiddleware`: Applied to all routes
- `HandleCors`: Applied to API routes

### Route-Specific Middleware

- `api.rate.limit`: Custom rate limiting per route group
- `file.security`: File upload security validation
- `file.rate.limit`: File upload rate limiting

## Rate Limiting Strategy

### Public Routes
- Authentication: 5 requests/minute
- Property listings: 30 requests/minute
- Seller requests: 3 requests/minute

### Authenticated Routes
- User endpoints: 10-20 requests/minute
- Broker endpoints: 40 requests/minute
- Admin endpoints: 100 requests/minute

### File Operations
- File uploads: 10 requests/minute (separate from API limits)

## Security Best Practices

1. **Regular Updates**: Keep security configurations updated
2. **Monitoring**: Enable security logging for threat detection
3. **Testing**: Regularly test rate limits and security headers
4. **Environment-Specific**: Use different settings for development/production
5. **Documentation**: Keep security configurations documented

## Troubleshooting

### Common Issues

1. **CORS Errors**: Check `FRONTEND_URL` in `.env`
2. **Rate Limit Issues**: Adjust limits in security configuration
3. **CSP Violations**: Review and update CSP directives
4. **File Upload Failures**: Check file security middleware configuration

### Debugging

- Enable security logging to track issues
- Check Laravel logs for security-related errors
- Use browser developer tools to inspect security headers
- Monitor rate limit headers in API responses

## Production Recommendations

1. **Enable HSTS**: Force HTTPS in production
2. **Strict CSP**: Remove `unsafe-inline` and `unsafe-eval` if possible
3. **Rate Limiting**: Implement stricter limits for production
4. **Monitoring**: Set up alerts for security violations
5. **Regular Audits**: Periodically review security configurations