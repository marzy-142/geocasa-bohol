<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class FileSecurityService
{
    /**
     * Get dangerous file extensions from config
     */
    private function getDangerousExtensions(): array
    {
        return config('file-security.file_validation.dangerous_extensions', [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp',
            'sh', 'bash', 'csh', 'ksh', 'ps1', 'psm1', 'psd1', 'msi',
            'dll', 'sys', 'drv', 'ocx', 'cpl', 'inf', 'reg', 'hta'
        ]);
    }

    /**
     * Get suspicious file signatures from config
     */
    private function getSuspiciousSignatures(): array
    {
        return config('file-security.file_validation.suspicious_signatures', [
            'MZ' => 'Executable file',
            'PK' => 'ZIP archive (potential executable)',
            '7z' => '7-Zip archive',
            'Rar!' => 'RAR archive',
            '<?php' => 'PHP script',
            '<script' => 'JavaScript code',
            '<html' => 'HTML file with potential scripts'
        ]);
    }

    /**
     * Maximum file sizes by type (in bytes)
     */
    private const MAX_FILE_SIZES = [
        'image' => 10 * 1024 * 1024, // 10MB
        'document' => 25 * 1024 * 1024, // 25MB
        'video' => 100 * 1024 * 1024, // 100MB
        'default' => 5 * 1024 * 1024 // 5MB
    ];

    /**
     * Validate uploaded file with comprehensive security checks
     */
    public function validateFile(UploadedFile $file, array $options = []): array
    {
        // Enhanced skip validation check with better debugging
        $appEnv = config('app.env');
        $skipValidation = env('SKIP_FILE_VALIDATION', false);
        
        \Log::info('FileSecurityService: Environment check', [
            'app_env' => $appEnv,
            'skip_validation' => $skipValidation,
            'skip_validation_type' => gettype($skipValidation)
        ]);
        
        // More flexible condition - check for both boolean true and string 'true'
        if ($appEnv === 'local' && ($skipValidation === true || $skipValidation === 'true' || $skipValidation === '1')) {
            \Log::info('FileSecurityService: VALIDATION BYPASSED - File upload allowed without checks', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            return [
                'valid' => true,
                'errors' => [],
                'warnings' => [],
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => strtolower($file->getClientOriginalExtension()),
                    'validation_bypassed' => true
                ]
            ];
        }

        $result = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'metadata' => []
        ];

        // Add debugging information
        \Log::info('FileSecurityService: Starting validation', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'options' => $options
        ]);

        try {
            // Basic file validation
            $this->validateBasicFile($file, $result);
            \Log::info('FileSecurityService: After basic validation', ['errors' => $result['errors']]);
            
            // Extension validation
            $this->validateExtension($file, $result, $options);
            \Log::info('FileSecurityService: After extension validation', ['errors' => $result['errors']]);
            
            // MIME type validation
            $this->validateMimeType($file, $result, $options);
            \Log::info('FileSecurityService: After MIME validation', ['errors' => $result['errors']]);
            
            // File size validation
            $this->validateFileSize($file, $result, $options);
            \Log::info('FileSecurityService: After size validation', ['errors' => $result['errors']]);
            
            // Content validation (magic bytes)
            $this->validateFileContent($file, $result);
            \Log::info('FileSecurityService: After content validation', ['errors' => $result['errors'], 'warnings' => $result['warnings']]);
            
            // Filename validation
            $this->validateFilename($file, $result);
            \Log::info('FileSecurityService: After filename validation', ['errors' => $result['errors']]);
            
            // Virus scanning simulation (placeholder for real antivirus)
            $this->performVirusScan($file, $result);
            \Log::info('FileSecurityService: After virus scan', ['errors' => $result['errors']]);
            
            // Image-specific validation
            if ($this->isImageFile($file)) {
                $this->validateImage($file, $result);
                \Log::info('FileSecurityService: After image validation', ['errors' => $result['errors']]);
            }
            
            // Document-specific validation
            if ($this->isDocumentFile($file)) {
                $this->validateDocument($file, $result);
                \Log::info('FileSecurityService: After document validation', ['errors' => $result['errors']]);
            }
            
            $result['valid'] = empty($result['errors']);
            
            \Log::info('FileSecurityService: Final result', [
                'valid' => $result['valid'],
                'errors' => $result['errors'],
                'warnings' => $result['warnings']
            ]);
            
        } catch (Exception $e) {
            Log::error('File validation error: ' . $e->getMessage());
            $result['errors'][] = 'File validation failed due to system error';
        }

        return $result;
    }

    /**
     * Basic file validation
     */
    private function validateBasicFile(UploadedFile $file, array &$result): void
    {
        if (!$file->isValid()) {
            $result['errors'][] = 'File upload failed or corrupted';
            return;
        }

        if ($file->getSize() === 0) {
            $result['errors'][] = 'Empty file not allowed';
        }

        $result['metadata']['original_name'] = $file->getClientOriginalName();
        $result['metadata']['size'] = $file->getSize();
        $result['metadata']['mime_type'] = $file->getMimeType();
    }

    /**
     * Validate file extension
     */
    private function validateExtension(UploadedFile $file, array &$result, array $options): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Check for dangerous extensions
        $dangerousExtensions = $this->getDangerousExtensions();
        if (in_array($extension, $dangerousExtensions)) {
            $result['errors'][] = "Dangerous file type '{$extension}' not allowed";
            $this->logSecurityEvent('dangerous_extension', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'ip' => request()->ip()
            ]);
            return;
        }

        // Check allowed extensions if specified
        if (isset($options['allowed_extensions'])) {
            $allowedExtensions = array_map('strtolower', $options['allowed_extensions']);
            if (!in_array($extension, $allowedExtensions)) {
                $result['errors'][] = "File extension '{$extension}' not allowed. Allowed: " . implode(', ', $allowedExtensions);
            }
        }

        $result['metadata']['extension'] = $extension;
    }

    /**
     * Validate MIME type
     */
    private function validateMimeType(UploadedFile $file, array &$result, array $options): void
    {
        $mimeType = $file->getMimeType();
        
        // Check allowed MIME types if specified
        $allowedMimeTypes = $options['allowed_mime_types'] ?? config('file-security.file_validation.allowed_mime_types');
        if (!empty($allowedMimeTypes)) {
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $result['errors'][] = "MIME type '{$mimeType}' not allowed";
                $this->logSecurityEvent('invalid_mime_type', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'allowed_mimes' => $allowedMimeTypes,
                    'ip' => request()->ip()
                ]);
            }
        }

        // Validate MIME type matches extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->mimeTypeMatchesExtension($mimeType, $extension)) {
            $result['warnings'][] = "MIME type '{$mimeType}' doesn't match extension '{$extension}'";
        }
    }

    /**
     * Validate file size
     */
    private function validateFileSize(UploadedFile $file, array &$result, array $options): void
    {
        $fileSize = $file->getSize();
        $maxSize = $options['max_size'] ?? config('file-security.file_validation.max_file_size') ?? $this->getMaxSizeForFileType($file);
        
        if ($fileSize > $maxSize) {
            $result['errors'][] = "File size ({$this->formatBytes($fileSize)}) exceeds maximum allowed size ({$this->formatBytes($maxSize)})";
            $this->logSecurityEvent('file_size_exceeded', [
                'filename' => $file->getClientOriginalName(),
                'size' => $fileSize,
                'max_size' => $maxSize,
                'ip' => request()->ip()
            ]);
        }
    }

    /**
     * Validate file content using magic bytes
     */
    private function validateFileContent(UploadedFile $file, array &$result): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            $result['errors'][] = 'Cannot read file content';
            return;
        }

        $header = fread($handle, 1024); // Read first 1KB
        fclose($handle);

        // Check for suspicious signatures
        $suspiciousSignatures = $this->getSuspiciousSignatures();
        foreach ($suspiciousSignatures as $signature => $description) {
            // Skip PK signature check for PDF files as they commonly contain ZIP-compressed content
            if ($signature === 'PK' && $file->getMimeType() === 'application/pdf') {
                \Log::info('FileSecurityService: Skipping PK check for PDF', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType()
                ]);
                continue;
            }
            
            if (strpos($header, $signature) === 0 || strpos($header, $signature) !== false) {
                $result['warnings'][] = "Suspicious content detected: {$description}";
                $this->logSecurityEvent('suspicious_signature', [
                    'filename' => $file->getClientOriginalName(),
                    'signature' => $signature,
                    'description' => $description,
                    'ip' => request()->ip()
                ]);
            }
        }

        // Check for embedded scripts in images
        if ($this->isImageFile($file) && $this->containsScript($header)) {
            $result['errors'][] = 'Image contains embedded scripts or malicious content';
        }
    }

    /**
     * Validate filename for security issues
     */
    private function validateFilename(UploadedFile $file, array &$result): void
    {
        $filename = $file->getClientOriginalName();
        
        // Check for path traversal attempts
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            $result['errors'][] = 'Filename contains invalid characters';
        }

        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            $result['errors'][] = 'Filename contains null bytes';
        }

        // Check filename length
        if (strlen($filename) > 255) {
            $result['errors'][] = 'Filename too long (max 255 characters)';
        }

        // Check for suspicious patterns
        if (preg_match('/\.(php|asp|jsp|js)\./i', $filename)) {
            $result['errors'][] = 'Filename contains suspicious double extensions';
        }
    }

    /**
     * Perform virus scan (placeholder - integrate with real antivirus)
     */
    private function performVirusScan(UploadedFile $file, array &$result): void
    {
        // This is a placeholder for real virus scanning
        // In production, integrate with ClamAV, Windows Defender API, or cloud-based scanners
        
        $filePath = $file->getRealPath();
        
        // Simulate virus scan with basic heuristics
        $suspiciousPatterns = [
            'eval(',
            'exec(',
            'system(',
            'shell_exec(',
            'passthru(',
            'base64_decode(',
            'gzinflate(',
            'str_rot13('
        ];

        $content = file_get_contents($filePath);
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                $result['warnings'][] = "Suspicious code pattern detected: {$pattern}";
            }
        }

        // Log scan attempt
        $this->logSecurityEvent('virus_scan_completed', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'clean'
        ]);
    }

    /**
     * Validate image files
     */
    private function validateImage(UploadedFile $file, array &$result): void
    {
        $imageInfo = @getimagesize($file->getRealPath());
        
        if (!$imageInfo) {
            $result['errors'][] = 'Invalid or corrupted image file';
            return;
        }

        $result['metadata']['image_width'] = $imageInfo[0];
        $result['metadata']['image_height'] = $imageInfo[1];
        $result['metadata']['image_type'] = $imageInfo[2];

        // Check image dimensions
        $maxWidth = 4096;
        $maxHeight = 4096;
        
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            $result['errors'][] = "Image dimensions ({$imageInfo[0]}x{$imageInfo[1]}) exceed maximum allowed ({$maxWidth}x{$maxHeight})";
        }

        // Check for EXIF data that might contain malicious content
        if (function_exists('exif_read_data')) {
            $exifData = @exif_read_data($file->getRealPath());
            if ($exifData && isset($exifData['UserComment'])) {
                if ($this->containsScript($exifData['UserComment'])) {
                    $result['warnings'][] = 'Image EXIF data contains suspicious content';
                }
            }
        }
    }

    /**
     * Validate document files
     */
    private function validateDocument(UploadedFile $file, array &$result): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        // PDF-specific validation
        if ($extension === 'pdf') {
            $this->validatePDF($file, $result);
        }
        
        // Office document validation
        if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
            $this->validateOfficeDocument($file, $result);
        }
    }

    /**
     * Validate PDF files
     */
    private function validatePDF(UploadedFile $file, array &$result): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            $result['errors'][] = 'Cannot read PDF file';
            return;
        }

        $header = fread($handle, 8);
        fclose($handle);

        if (strpos($header, '%PDF-') !== 0) {
            $result['errors'][] = 'Invalid PDF file format';
        }
    }

    /**
     * Validate Office documents
     */
    private function validateOfficeDocument(UploadedFile $file, array &$result): void
    {
        // Basic validation for Office documents
        $mimeType = $file->getMimeType();
        $validOfficeMimes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];

        if (!in_array($mimeType, $validOfficeMimes)) {
            $result['warnings'][] = 'Office document MIME type validation failed';
        }
    }

    /**
     * Generate secure filename
     */
    public function generateSecureFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return ($prefix ? $prefix . '_' : '') . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Store file securely
     */
    public function storeSecurely(UploadedFile $file, string $directory, string $disk = 'local'): string
    {
        $filename = $this->generateSecureFilename($file);
        $path = $file->storeAs($directory, $filename, $disk);
        
        // Set restrictive permissions
        $fullPath = Storage::disk($disk)->path($path);
        if (file_exists($fullPath)) {
            chmod($fullPath, 0644); // Read-only for group and others
        }
        
        return $path;
    }

    /**
     * Helper methods
     */
    private function isImageFile(UploadedFile $file): bool
    {
        return strpos($file->getMimeType(), 'image/') === 0;
    }

    private function isDocumentFile(UploadedFile $file): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        
        return in_array($file->getMimeType(), $documentMimes);
    }

    private function mimeTypeMatchesExtension(string $mimeType, string $extension): bool
    {
        $mimeMap = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain']
        ];

        return isset($mimeMap[$extension]) && in_array($mimeType, $mimeMap[$extension]);
    }

    private function getMaxSizeForFileType(UploadedFile $file): int
    {
        if ($this->isImageFile($file)) {
            return self::MAX_FILE_SIZES['image'];
        }
        
        if ($this->isDocumentFile($file)) {
            return self::MAX_FILE_SIZES['document'];
        }
        
        return self::MAX_FILE_SIZES['default'];
    }

    private function containsScript(string $content): bool
    {
        $scriptPatterns = [
            '/<script[^>]*>/i',
            '/<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i'
        ];

        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log security events with proper context
     */
    private function logSecurityEvent(string $event, array $context = []): void
    {
        $logLevel = config('file-security.logging.level', 'warning');
        $logChannel = config('file-security.logging.channel', 'default');
        
        $context['event'] = $event;
        $context['timestamp'] = now()->toISOString();
        $context['user_agent'] = request()->userAgent();
        
        Log::channel($logChannel)->log($logLevel, "File security event: {$event}", $context);
    }
}