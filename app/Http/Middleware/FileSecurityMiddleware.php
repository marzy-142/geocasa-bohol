<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Services\FileSecurityService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FileSecurityMiddleware
{
    protected FileSecurityService $fileSecurityService;

    public function __construct(FileSecurityService $fileSecurityService)
    {
        $this->fileSecurityService = $fileSecurityService;
    }

    /**
     * Handle an incoming request with file uploads
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process requests with file uploads
        if (empty($request->allFiles())) {
            return $next($request);
        }

        $validationErrors = [];
        $securityWarnings = [];

        // Get file validation rules based on the route
        $validationRules = $this->getValidationRulesForRoute($request);

        // Validate all uploaded files
        foreach ($request->allFiles() as $key => $files) {
            // Handle both single files and arrays of files
            $filesToValidate = is_array($files) ? $files : [$files];
            
            foreach ($filesToValidate as $index => $file) {
                if ($file instanceof UploadedFile) {
                    $fieldKey = is_array($files) ? "{$key}.{$index}" : $key;
                    $this->validateSingleFile($file, $fieldKey, $validationRules, $validationErrors, $securityWarnings);
                }
            }
        }

        // Log security warnings
        if (!empty($securityWarnings)) {
            Log::warning('File upload security warnings', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'route' => $request->route()?->getName(),
                'warnings' => $securityWarnings
            ]);
        }

        // Return validation errors if any
        if (!empty($validationErrors)) {
            Log::warning('File upload security violations', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'route' => $request->route()?->getName(),
                'errors' => $validationErrors
            ]);

            return response()->json([
                'message' => 'File validation failed',
                'errors' => $validationErrors
            ], 422);
        }

        return $next($request);
    }

    /**
     * Validate a single file
     */
    private function validateSingleFile(
        UploadedFile $file, 
        string $fieldKey, 
        array $validationRules, 
        array &$validationErrors, 
        array &$securityWarnings
    ): void {
        // Get specific rules for this field
        $fieldRules = $validationRules[$fieldKey] ?? $validationRules['default'] ?? [];
        
        // Perform security validation
        $result = $this->fileSecurityService->validateFile($file, $fieldRules);
        
        // Collect errors
        if (!empty($result['errors'])) {
            $validationErrors[$fieldKey] = $result['errors'];
        }
        
        // Collect warnings
        if (!empty($result['warnings'])) {
            $securityWarnings[$fieldKey] = $result['warnings'];
        }

        // Log file upload attempt
        Log::info('File upload security check', [
            'field' => $fieldKey,
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'valid' => $result['valid'],
            'errors_count' => count($result['errors']),
            'warnings_count' => count($result['warnings'])
        ]);
    }

    /**
     * Get validation rules based on the current route
     */
    private function getValidationRulesForRoute(Request $request): array
    {
        $routeName = $request->route()?->getName();
        
        // Define route-specific validation rules
        $routeRules = [
            // User registration files
            'register' => [
                'prc_id_file' => [
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
                    'max_size' => 5 * 1024 * 1024 // 5MB
                ],
                'business_permit_file' => [
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
                    'max_size' => 5 * 1024 * 1024
                ],
                'additional_documents' => [
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                    'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
                    'max_size' => 5 * 1024 * 1024
                ]
            ],
            
            // Property files
            'properties.store' => [
                'images' => [
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                    'max_size' => 2 * 1024 * 1024 // 2MB for property images
                ],
                'documents' => [
                    'allowed_extensions' => ['pdf', 'doc', 'docx'],
                    'allowed_mime_types' => [
                        'application/pdf', 
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    'max_size' => 5 * 1024 * 1024
                ],
                'virtual_tour_images' => [
                    'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                    'allowed_mime_types' => ['image/jpeg', 'image/png'],
                    'max_size' => 5 * 1024 * 1024
                ]
            ],
            
            'properties.update' => [
                'new_images' => [
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                    'max_size' => 2 * 1024 * 1024
                ],
                'new_documents' => [
                    'allowed_extensions' => ['pdf', 'doc', 'docx'],
                    'allowed_mime_types' => [
                        'application/pdf', 
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ],
                    'max_size' => 5 * 1024 * 1024
                ],
                'new_virtual_tour_images' => [
                    'allowed_extensions' => ['jpg', 'jpeg', 'png'],
                    'allowed_mime_types' => ['image/jpeg', 'image/png'],
                    'max_size' => 5 * 1024 * 1024
                ]
            ],
            
            // Seller request files
            'seller-requests.store' => [
                'uploaded_images' => [
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                    'max_size' => 5 * 1024 * 1024
                ]
            ]
        ];

        // Return rules for the current route or default rules
        return $routeRules[$routeName] ?? [
            'default' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
                'max_size' => 5 * 1024 * 1024
            ]
        ];
    }
}