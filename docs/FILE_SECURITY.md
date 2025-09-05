# File Upload Security System

This document describes the comprehensive file upload security system implemented in the GeoCasa Bohol application.

## Overview

The file security system provides multiple layers of protection against malicious file uploads, including:

- **File Extension Validation**: Blocks dangerous file extensions
- **MIME Type Validation**: Validates file content types
- **File Size Limits**: Enforces maximum file sizes
- **Content Validation**: Checks file headers and magic bytes
- **Virus Scanning**: Optional integration with antivirus services
- **Rate Limiting**: Prevents upload abuse
- **Secure Storage**: Generates unique filenames and sets proper permissions

## Components

### 1. FileSecurityService

**Location**: `app/Services/FileSecurityService.php`

Core service that handles all file validation and security checks.

**Key Methods**:
- `validateFile()`: Comprehensive file validation
- `scanForVirus()`: Virus scanning integration
- `generateSecureFilename()`: Creates unique, safe filenames
- `storeFileSecurely()`: Secure file storage with proper permissions

### 2. FileSecurityMiddleware

**Location**: `app/Http/Middleware/FileSecurityMiddleware.php`

Middleware that automatically applies security validation to file uploads based on routes.

**Features**:
- Route-specific validation rules
- Automatic file scanning
- Security event logging

### 3. FileUploadRateLimitMiddleware

**Location**: `app/Http/Middleware/FileUploadRateLimitMiddleware.php`

Prevents upload abuse by limiting the number of file uploads per user/IP.

**Limits**:
- Per minute: 10 uploads (configurable)
- Per hour: 100 uploads (configurable)

### 4. Secure Request Classes

**Base Class**: `app/Http/Requests/SecureFileUploadRequest.php`

**Specialized Classes**:
- `BrokerRegistrationRequest.php`: Broker registration files
- `PropertyFileUploadRequest.php`: Property-related files
- `SellerRequestUploadRequest.php`: Seller request files

## Configuration

**File**: `config/file-security.php`

### Environment Variables

```env
# Virus Scanning
VIRUS_SCANNING_ENABLED=false
VIRUS_SCANNING_SERVICE=clamav
VIRUS_SCANNING_API_KEY=your_api_key
VIRUS_SCANNING_TIMEOUT=30

# File Validation
MAX_FILE_SIZE=10485760  # 10MB

# Rate Limiting
FILE_UPLOAD_RATE_LIMITING=true
MAX_UPLOADS_PER_MINUTE=10
MAX_UPLOADS_PER_HOUR=100

# Logging
FILE_SECURITY_LOGGING=true
FILE_SECURITY_LOG_CHANNEL=daily
```

## Usage

### 1. Using Secure Request Classes

```php
// In your controller
public function store(PropertyFileUploadRequest $request)
{
    $validated = $request->validated();
    
    // Store files securely
    $storedFiles = $request->storeFilesSecurely([
        'images',
        'documents'
    ], 'properties', 'public');
    
    // Files are automatically validated and stored securely
}
```

### 2. Manual File Validation

```php
use App\Services\FileSecurityService;

$fileSecurityService = new FileSecurityService();

$result = $fileSecurityService->validateFile($uploadedFile, [
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'application/pdf'],
    'max_size' => 5 * 1024 * 1024, // 5MB
    'validate_content' => true,
    'scan_virus' => true
]);

if (!$result['is_valid']) {
    // Handle validation errors
    foreach ($result['errors'] as $error) {
        // Log or display error
    }
}
```

### 3. Applying Middleware to Routes

```php
// In routes/web.php
Route::post('/upload', [UploadController::class, 'store'])
    ->middleware(['file.security', 'file.rate.limit']);
```

## Security Features

### Dangerous Extensions Blocked

- Executables: `exe`, `bat`, `cmd`, `com`, `pif`, `scr`
- Scripts: `php`, `asp`, `jsp`, `js`, `vbs`, `ps1`
- Archives: `zip`, `rar`, `7z` (configurable)
- System files: `dll`, `sys`, `drv`

### Content Validation

- **Magic Bytes**: Validates file headers match extensions
- **MIME Type**: Server-side MIME type validation
- **Suspicious Signatures**: Detects embedded executables or scripts

### Virus Scanning

**Supported Services**:
- ClamAV (local installation)
- VirusTotal API (cloud service)
- Extensible for other services

**ClamAV Setup**:
```bash
# Install ClamAV
sudo apt-get install clamav clamav-daemon

# Update virus definitions
sudo freshclam

# Enable in config
VIRUS_SCANNING_ENABLED=true
VIRUS_SCANNING_SERVICE=clamav
```

### Rate Limiting

- **Per-user limits**: Authenticated users tracked by user ID
- **Per-IP limits**: Anonymous users tracked by IP address
- **Sliding window**: Uses cache-based sliding window algorithm
- **Configurable**: Limits can be adjusted per environment

## File Storage Security

### Secure Filenames

- **Sanitization**: Removes dangerous characters
- **Unique names**: Prevents filename collisions
- **Extension preservation**: Maintains file type information

Example: `user_document.pdf` → `20240115_143022_a1b2c3d4.pdf`

### File Permissions

- **Files**: 644 (readable by owner and group, not executable)
- **Directories**: 755 (accessible but not writable by others)

### Storage Locations

- **Public files**: `storage/app/public/` (images, public documents)
- **Private files**: `storage/app/private/` (sensitive documents)
- **Quarantine**: `storage/app/quarantine/` (suspicious files)

## Monitoring and Logging

### Security Events Logged

- Dangerous file extensions detected
- Invalid MIME types
- File size violations
- Suspicious content signatures
- Virus scan results
- Rate limit violations

### Log Format

```json
{
    "event": "dangerous_extension",
    "filename": "malicious.exe",
    "extension": "exe",
    "ip": "192.168.1.100",
    "user_id": 123,
    "user_agent": "Mozilla/5.0...",
    "timestamp": "2024-01-15T14:30:22Z"
}
```

## Best Practices

### For Developers

1. **Always use secure request classes** for file uploads
2. **Apply middleware** to all file upload routes
3. **Validate on both client and server** sides
4. **Store files outside web root** when possible
5. **Use unique filenames** to prevent conflicts
6. **Set proper file permissions** after upload
7. **Monitor security logs** regularly

### For System Administrators

1. **Enable virus scanning** in production
2. **Configure rate limits** based on usage patterns
3. **Monitor disk space** for uploaded files
4. **Set up log rotation** for security logs
5. **Regular security audits** of uploaded files
6. **Backup uploaded files** securely

## Troubleshooting

### Common Issues

**File uploads failing silently**:
- Check middleware is applied to routes
- Verify file permissions on storage directories
- Check PHP upload limits (`upload_max_filesize`, `post_max_size`)

**Virus scanning not working**:
- Verify ClamAV installation: `clamscan --version`
- Check virus definitions are updated: `sudo freshclam`
- Verify API keys for cloud services

**Rate limiting too restrictive**:
- Adjust limits in `config/file-security.php`
- Clear cache: `php artisan cache:clear`
- Check user authentication status

### Debug Mode

Enable detailed logging:

```env
FILE_SECURITY_LOGGING=true
LOG_LEVEL=debug
```

## Security Considerations

### Known Limitations

1. **Client-side validation bypass**: Always validate server-side
2. **MIME type spoofing**: Use content validation in addition to MIME types
3. **Polyglot files**: Files that are valid in multiple formats
4. **Zero-day threats**: Virus scanners may not detect newest threats

### Recommendations

1. **Regular updates**: Keep virus definitions and security rules updated
2. **Defense in depth**: Use multiple validation layers
3. **User education**: Train users on safe file handling
4. **Incident response**: Have procedures for handling security breaches

## API Reference

### FileSecurityService Methods

```php
// Validate a single file
public function validateFile(UploadedFile $file, array $options = []): array

// Scan file for viruses
public function scanForVirus(UploadedFile $file): bool

// Generate secure filename
public function generateSecureFilename(UploadedFile $file, string $prefix = ''): string

// Store file securely
public function storeFileSecurely(UploadedFile $file, string $directory, string $disk = 'public'): string
```

### Configuration Options

```php
// File validation options
$options = [
    'allowed_extensions' => ['jpg', 'png', 'pdf'],
    'allowed_mime_types' => ['image/jpeg', 'application/pdf'],
    'max_size' => 5 * 1024 * 1024, // 5MB
    'validate_content' => true,
    'scan_virus' => true,
    'check_filename' => true
];
```

## Support

For questions or issues with the file security system:

1. Check this documentation
2. Review security logs
3. Test with debug mode enabled
4. Contact the development team

---

**Last Updated**: January 2024  
**Version**: 1.0.0