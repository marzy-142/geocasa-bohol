<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use App\Services\FileSecurityService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SecureFileUploadRequest extends FormRequest
{
    protected ?FileSecurityService $fileSecurityService = null;

    /**
     * Get the FileSecurityService instance
     */
    protected function getFileSecurityService(): FileSecurityService
    {
        if ($this->fileSecurityService === null) {
            $this->fileSecurityService = app(FileSecurityService::class);
        }
        return $this->fileSecurityService;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Base rules - will be extended by child classes
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateUploadedFiles($validator);
        });
    }

    /**
     * Validate all uploaded files with security checks
     */
    protected function validateUploadedFiles(Validator $validator): void
    {
        foreach ($this->allFiles() as $key => $files) {
            $filesToValidate = is_array($files) ? $files : [$files];
            
            foreach ($filesToValidate as $index => $file) {
                if ($file instanceof UploadedFile) {
                    $fieldKey = is_array($files) ? "{$key}.{$index}" : $key;
                    
                    try {
                        $result = $this->getFileSecurityService()->validateFile($file, [
                            'field_name' => $fieldKey
                        ]);
                        
                        // If validation failed, add errors
                        if (!$result['valid']) {
                            foreach ($result['errors'] as $error) {
                                $validator->errors()->add($fieldKey, $error);
                            }
                        }
                        
                        // Log warnings if any
                        if (!empty($result['warnings'])) {
                            \Log::warning('File upload warnings', [
                                'field' => $fieldKey,
                                'warnings' => $result['warnings']
                            ]);
                        }
                    } catch (\Exception $e) {
                        $validator->errors()->add($fieldKey, 'File validation failed: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Validate a single uploaded file
     */
    protected function validateSingleFile(UploadedFile $file, string $fieldKey, Validator $validator): void
    {
        // Get security validation options for this field
        $options = $this->getSecurityOptionsForField($fieldKey);
        
        // Perform security validation
        $result = $this->fileSecurityService->validateFile($file, $options);
        
        // Add errors to validator
        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                $validator->errors()->add($fieldKey, $error);
            }
        }
        
        // Log warnings (don't fail validation but log for monitoring)
        if (!empty($result['warnings'])) {
            \Log::warning('File upload security warnings', [
                'field' => $fieldKey,
                'filename' => $file->getClientOriginalName(),
                'warnings' => $result['warnings'],
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);
        }
    }

    /**
     * Get security validation options for a specific field
     * Override this method in child classes to provide field-specific rules
     */
    protected function getSecurityOptionsForField(string $fieldKey): array
    {
        return [
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
            'max_size' => 5 * 1024 * 1024 // 5MB default
        ];
    }

    /**
     * Store uploaded files securely
     */
    public function storeFilesSecurely(array $fileFields, string $directory, string $disk = 'local'): array
    {
        $storedFiles = [];
        
        foreach ($fileFields as $fieldKey) {
            if ($this->hasFile($fieldKey)) {
                $files = $this->file($fieldKey);
                $filesToStore = is_array($files) ? $files : [$files];
                
                foreach ($filesToStore as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = $this->fileSecurityService->storeSecurely($file, $directory, $disk);
                        $storedFiles[$fieldKey][] = $path;
                    }
                }
                
                // If single file, return string instead of array
                if (!is_array($files) && isset($storedFiles[$fieldKey])) {
                    $storedFiles[$fieldKey] = $storedFiles[$fieldKey][0];
                }
            }
        }
        
        return $storedFiles;
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'prc_id_file.file' => 'The uploaded PRC ID file is invalid or corrupted. Please try uploading a different file.',
            'prc_id_file.mimes' => 'Invalid PRC ID file format. Allowed formats: PDF, JPG, JPEG, PNG. Please convert your file to an accepted format.',
            'prc_id_file.max' => 'PRC ID file size too large (5MB maximum). Please compress your file or use a smaller image.',
            'prc_id_file.image' => 'The PRC ID file must be a valid image (JPG, PNG) or PDF. Please upload a valid file.',
            'prc_id_file.uploaded' => 'PRC ID file upload failed due to server error. Please try again or contact support if the problem persists.',
            'prc_id_file.dimensions' => 'PRC ID image dimensions are too large. Please resize your image and try again.',

            'business_permit_file.file' => 'The uploaded business permit file is invalid or corrupted. Please try uploading a different file.',
            'business_permit_file.mimes' => 'Invalid business permit file format. Allowed formats: PDF, JPG, JPEG, PNG. Please convert your file to an accepted format.',
            'business_permit_file.max' => 'Business permit file size too large (5MB maximum). Please compress your file or use a smaller image.',
            'business_permit_file.image' => 'The business permit file must be a valid image (JPG, PNG) or PDF. Please upload a valid file.',
            'business_permit_file.uploaded' => 'Business permit file upload failed due to server error. Please try again or contact support if the problem persists.',
            'business_permit_file.dimensions' => 'Business permit image dimensions are too large. Please resize your image and try again.',

            'additional_documents.*.file' => 'One or more additional document files are invalid or corrupted. Please try uploading different files.',
            'additional_documents.*.mimes' => 'Invalid additional document format. Allowed formats: PDF, JPG, JPEG, PNG, DOC, DOCX.',
            'additional_documents.*.max' => 'Additional document file size too large (5MB maximum). Please compress your file or use a smaller image.',
            'additional_documents.*.image' => 'Additional document files must be valid images (JPG, PNG), PDFs, or Office documents.',
            'additional_documents.*.uploaded' => 'One or more additional document uploads failed. Please try again or contact support if the problem persists.',
            'additional_documents.*.dimensions' => 'Additional document image dimensions are too large. Please resize your image and try again.',

            // Generic file messages (only for explicitly file fields)
            '*.file' => 'The uploaded file is invalid or corrupted. Please try uploading a different file.',
            '*.mimes' => 'Invalid file format. Allowed formats: :values. Please convert your file to an accepted format.',
            '*.max' => 'File size too large (:max KB maximum). Please compress your file or use a smaller image.',
            '*.image' => 'The file must be a valid image (JPG, PNG, GIF). Please upload an image file.',
            '*.uploaded' => 'File upload failed due to server error. Please try again or contact support if the problem persists.',
            '*.dimensions' => 'Image dimensions are too large. Please resize your image and try again.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'prc_id_file' => 'PRC license document',
            'business_permit_file' => 'business permit document',
            'additional_documents' => 'additional documents',
            'images' => 'property images',
            'documents' => 'property documents',
            'virtual_tour_images' => 'virtual tour images',
            'uploaded_images' => 'uploaded images'
        ];
    }
}