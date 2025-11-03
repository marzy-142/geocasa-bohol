# 🧪 **GeoCasa Bohol Security Testing Guide**

## 📋 **How to Test the Enhanced Security System**

This guide provides comprehensive testing instructions for the enhanced authentication and security features implemented in GeoCasa Bohol.

---

## 🚀 **Quick Start Testing**

### **1. Run Automated Tests**

```bash
# Run all security tests
php artisan test --filter=SecurityTest

# Run specific security test
php artisan test tests/Feature/SecurityTest.php::test_password_policy_enforcement

# Run with verbose output
php artisan test --filter=SecurityTest -v
```

### **2. Test Individual Components**

```bash
# Test authentication service
php artisan tinker
>>> app(App\Services\EnhancedAuthenticationService::class)

# Test MFA service
>>> app(App\Services\MultiFactorAuthenticationService::class)

# Test login attempts model
>>> App\Models\LoginAttempt::count()
```

---

## 🔐 **Manual Testing Scenarios**

### **Test 1: Password Policy Enforcement**

#### **Weak Password Test**

1. Go to `/register`
2. Try to register with weak passwords:
    - `password` (too short, no complexity)
    - `12345678` (numbers only)
    - `Password` (no numbers/symbols)
    - `password123` (no uppercase/symbols)

**Expected Result**: Registration should fail with validation errors

#### **Strong Password Test**

1. Try registration with: `StrongPass123!`
   **Expected Result**: Registration should succeed

### **Test 2: Account Lockout Mechanism**

#### **Failed Login Attempts**

1. Go to `/login`
2. Use a valid email with wrong password
3. Attempt login 5 times
4. Try 6th attempt

**Expected Result**:

-   First 5 attempts: "Invalid credentials" error
-   6th attempt: "Account temporarily locked" error
-   Check database: `login_attempts` table should have 5 failed attempts

#### **Verify IP Blocking**

1. Check `login_attempts` table for blocked IPs
2. Try login from same IP after lockout period

**Expected Result**: IP should be blocked for 60 minutes

### **Test 3: Multi-Factor Authentication (MFA)**

#### **Enable MFA for Broker**

```php
// In tinker
$user = User::where('role', 'broker')->first();
$mfaService = app(App\Services\MultiFactorAuthenticationService::class);
$result = $mfaService->enableMfa($user, 'email');
```

**Expected Result**:

-   10 backup codes generated
-   MFA enabled for user
-   Backup codes stored in `mfa_tokens` table

#### **Test MFA Token Verification**

```php
// Generate and verify token
$token = App\Models\MfaToken::createToken($user->id, 'email', 10);
$verifyResult = $mfaService->verifyToken($user, $token->token, 'email');
```

**Expected Result**: Token verification should succeed

### **Test 4: Session Security**

#### **Session Encryption Test**

1. Login to application
2. Check browser developer tools → Application → Cookies
3. Look for session cookie

**Expected Result**: Session cookie should be encrypted (not readable)

#### **Session Timeout Test**

1. Login to application
2. Wait 60 minutes (or modify `SESSION_LIFETIME` for testing)
3. Try to access protected page

**Expected Result**: Should be redirected to login page

#### **Secure Cookie Test**

1. Login over HTTPS
2. Check cookie attributes in browser

**Expected Result**: Cookies should have `Secure` flag set

### **Test 5: Device Fingerprinting**

#### **Track Device Fingerprints**

```php
// In tinker - check login attempts
App\Models\LoginAttempt::latest()->take(5)->get(['email', 'device_fingerprint', 'ip_address'])
```

**Expected Result**: Each login attempt should have unique device fingerprint

### **Test 6: Suspicious Activity Detection**

#### **Simulate Suspicious Activity**

```php
// In tinker
$user = User::first();
$authService = app(App\Services\EnhancedAuthenticationService::class);

// Create multiple failed attempts from different IPs
for ($i = 0; $i < 10; $i++) {
    App\Models\LoginAttempt::recordAttempt([
        'user_id' => $user->id,
        'email' => $user->email,
        'ip_address' => '192.168.1.' . $i,
        'user_agent' => 'Suspicious Browser ' . $i,
        'success' => false,
        'failed_reason' => 'Invalid password',
        'attempted_at' => now(),
    ]);
}

// Check suspicious activity
$activity = $authService->getSuspiciousActivityReport($user);
```

**Expected Result**: Suspicious score should be > 15

---

## 🛠️ **API Testing**

### **Test Enhanced Authentication API**

#### **Register with Strong Password**

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "StrongPass123!",
    "password_confirmation": "StrongPass123!",
    "role": "client"
  }'
```

**Expected Result**: User created successfully

#### **Login with Rate Limiting**

```bash
# Attempt login 6 times with wrong password
for i in {1..6}; do
  curl -X POST http://localhost:8000/api/v1/login \
    -H "Content-Type: application/json" \
    -d '{
      "email": "test@example.com",
      "password": "wrongpassword"
    }'
  echo "Attempt $i completed"
done
```

**Expected Result**: 6th attempt should be rate limited

---

## 📊 **Database Testing**

### **Check Security Tables**

#### **Login Attempts Table**

```sql
-- Check recent login attempts
SELECT email, ip_address, success, failed_reason, attempted_at
FROM login_attempts
ORDER BY attempted_at DESC
LIMIT 10;

-- Check blocked IPs
SELECT ip_address, blocked_until
FROM login_attempts
WHERE blocked_until > NOW();

-- Check suspicious activity
SELECT email, COUNT(*) as attempts, COUNT(DISTINCT ip_address) as unique_ips
FROM login_attempts
WHERE attempted_at >= NOW() - INTERVAL 1 HOUR
GROUP BY email
HAVING attempts > 5;
```

#### **MFA Tokens Table**

```sql
-- Check active MFA tokens
SELECT user_id, type, expires_at, used_at
FROM mfa_tokens
WHERE expires_at > NOW() AND used_at IS NULL;

-- Check backup codes
SELECT user_id, token
FROM mfa_tokens
WHERE type = 'backup' AND used_at IS NULL;
```

---

## 🔍 **Security Headers Testing**

### **Check Security Headers**

```bash
# Test security headers
curl -I http://localhost:8000/

# Expected headers:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Strict-Transport-Security: max-age=31536000; includeSubDomains
```

---

## 🧪 **Load Testing**

### **Test Rate Limiting Under Load**

```bash
# Install Apache Bench (if not installed)
# Windows: Download from Apache website
# Linux: sudo apt-get install apache2-utils

# Test login rate limiting
ab -n 100 -c 10 -p login_data.json -T application/json http://localhost:8000/login

# Create login_data.json
echo '{"email":"test@example.com","password":"wrongpassword"}' > login_data.json
```

**Expected Result**: Requests should be rate limited after 5 attempts

---

## 📱 **Browser Testing**

### **Test Session Security in Browser**

#### **Chrome DevTools**

1. Open Chrome DevTools (F12)
2. Go to Application → Cookies
3. Login to application
4. Check session cookie attributes

**Expected Results**:

-   `HttpOnly`: true
-   `Secure`: true (if HTTPS)
-   `SameSite`: Strict
-   Value should be encrypted (not readable)

#### **Test Session Expiry**

1. Login to application
2. Close browser
3. Reopen browser and try to access protected page

**Expected Result**: Should be redirected to login (if `expire_on_close` is true)

---

## 🚨 **Security Monitoring Testing**

### **Check Security Logs**

```bash
# Check Laravel logs for security events
tail -f storage/logs/laravel.log | grep -E "(login|security|MFA|suspicious)"

# Look for these log entries:
# - "Successful login"
# - "Failed login attempt"
# - "MFA token generated"
# - "Suspicious activity detected"
# - "Account locked"
```

### **Test Logging Configuration**

```php
// In tinker - test logging
Log::info('Security test log entry', ['test' => 'security_monitoring']);
```

**Expected Result**: Entry should appear in `storage/logs/laravel.log`

---

## 🔧 **Configuration Testing**

### **Test Security Configuration**

```php
// In tinker - check security config
config('security.mfa.enabled')
config('security.login.max_attempts')
config('security.password.min_length')
config('session.encrypt')
config('session.secure')
```

**Expected Results**:

-   MFA enabled: true
-   Max attempts: 5
-   Min password length: 12
-   Session encrypt: true
-   Session secure: true

---

## ✅ **Test Checklist**

### **Password Security**

-   [ ] Weak passwords rejected
-   [ ] Strong passwords accepted
-   [ ] Password complexity enforced
-   [ ] Breach detection working

### **Account Protection**

-   [ ] Failed attempts tracked
-   [ ] Account lockout after 5 attempts
-   [ ] IP blocking functional
-   [ ] Suspicious activity detected

### **Session Security**

-   [ ] Sessions encrypted
-   [ ] Secure cookies set
-   [ ] Session timeout working
-   [ ] Session regeneration on login

### **MFA System**

-   [ ] MFA can be enabled
-   [ ] Backup codes generated
-   [ ] Token verification working
-   [ ] Token expiry enforced

### **API Security**

-   [ ] Rate limiting functional
-   [ ] Consistent validation
-   [ ] Security headers present
-   [ ] Error handling proper

---

## 🎯 **Expected Test Results Summary**

| Test Category        | Expected Result                 | Pass Criteria                   |
| -------------------- | ------------------------------- | ------------------------------- |
| **Password Policy**  | Strong passwords only           | Weak passwords rejected         |
| **Account Lockout**  | 5 attempts then lockout         | 6th attempt blocked             |
| **Session Security** | Encrypted, secure cookies       | Headers present, encrypted data |
| **MFA System**       | Tokens work, backup codes valid | All MFA functions operational   |
| **Rate Limiting**    | API requests limited            | 5 requests then throttled       |
| **Logging**          | Security events logged          | All events in log files         |

---

## 🚀 **Running Full Test Suite**

```bash
# Run all tests
php artisan test

# Run security tests only
php artisan test --filter=Security

# Run with coverage (if configured)
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/SecurityTest.php
```

---

## 📞 **Troubleshooting**

### **Common Issues**

1. **Tests failing**: Check database migrations are run
2. **MFA not working**: Verify MFA tokens table exists
3. **Rate limiting not working**: Check Redis/cache configuration
4. **Session issues**: Verify session driver configuration

### **Debug Commands**

```bash
# Check database tables
php artisan migrate:status

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check logs
php artisan log:clear
tail -f storage/logs/laravel.log
```

---

**✅ Ready to test the enhanced security system!**
