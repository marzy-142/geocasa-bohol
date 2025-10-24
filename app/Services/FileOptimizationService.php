<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class FileOptimizationService
{
    /**
     * Optimize image files
     */
    public function optimizeImage(UploadedFile $file, string $path, int $maxWidth = 1920, int $quality = 85): string
    {
        try {
            $image = Image::make($file);
            
            // Resize if too large
            if ($image->width() > $maxWidth) {
                $image->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Optimize quality
            $image->encode('jpg', $quality);
            
            // Generate optimized filename
            $filename = $this->generateOptimizedFilename($file, 'jpg');
            $fullPath = $path . '/' . $filename;
            
            // Store optimized image
            Storage::disk('public')->put($fullPath, $image->stream());
            
            Log::info("Image optimized and stored: {$fullPath}");
            
            return $fullPath;
        } catch (\Exception $e) {
            Log::error("Failed to optimize image: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate optimized filename
     */
    private function generateOptimizedFilename(UploadedFile $file, string $extension): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = now()->format('Y_m_d_H_i_s');
        $random = str_random(8);
        
        return "{$originalName}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Optimize multiple images
     */
    public function optimizeImages(array $files, string $path, int $maxWidth = 1920, int $quality = 85): array
    {
        $optimizedPaths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $optimizedPaths[] = $this->optimizeImage($file, $path, $maxWidth, $quality);
            }
        }
        
        return $optimizedPaths;
    }

    /**
     * Clean up old files
     */
    public function cleanupOldFiles(string $directory, int $daysOld = 30): int
    {
        $deletedCount = 0;
        $cutoffDate = now()->subDays($daysOld);
        
        try {
            $files = Storage::disk('public')->allFiles($directory);
            
            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                
                if ($lastModified < $cutoffDate->timestamp) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }
            
            Log::info("Cleaned up {$deletedCount} old files from {$directory}");
            
        } catch (\Exception $e) {
            Log::error("Failed to cleanup old files: " . $e->getMessage());
        }
        
        return $deletedCount;
    }

    /**
     * Get file size statistics
     */
    public function getFileSizeStats(string $directory): array
    {
        $totalSize = 0;
        $fileCount = 0;
        $largestFile = null;
        $largestSize = 0;
        
        try {
            $files = Storage::disk('public')->allFiles($directory);
            
            foreach ($files as $file) {
                $size = Storage::disk('public')->size($file);
                $totalSize += $size;
                $fileCount++;
                
                if ($size > $largestSize) {
                    $largestSize = $size;
                    $largestFile = $file;
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to get file size stats: " . $e->getMessage());
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
            'average_size' => $fileCount > 0 ? round($totalSize / $fileCount) : 0,
            'largest_file' => $largestFile,
            'largest_size' => $largestSize,
        ];
    }

    /**
     * Compress files
     */
    public function compressFiles(string $directory): array
    {
        $compressedFiles = [];
        
        try {
            $files = Storage::disk('public')->allFiles($directory);
            
            foreach ($files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                
                // Only compress certain file types
                if (in_array(strtolower($extension), ['txt', 'csv', 'json', 'xml'])) {
                    $content = Storage::disk('public')->get($file);
                    $compressed = gzcompress($content, 9);
                    
                    if ($compressed !== false) {
                        $compressedPath = $file . '.gz';
                        Storage::disk('public')->put($compressedPath, $compressed);
                        $compressedFiles[] = $compressedPath;
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to compress files: " . $e->getMessage());
        }
        
        return $compressedFiles;
    }

    /**
     * Generate file thumbnails
     */
    public function generateThumbnails(string $imagePath, array $sizes = [150, 300, 600]): array
    {
        $thumbnails = [];
        
        try {
            $image = Image::make(Storage::disk('public')->path($imagePath));
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            
            foreach ($sizes as $size) {
                $thumbnail = clone $image;
                $thumbnail->resize($size, $size, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                
                $thumbnailPath = "{$directory}/thumbnails/{$filename}_{$size}x{$size}.{$extension}";
                Storage::disk('public')->put($thumbnailPath, $thumbnail->stream());
                $thumbnails[] = $thumbnailPath;
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to generate thumbnails: " . $e->getMessage());
        }
        
        return $thumbnails;
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload(UploadedFile $file, array $allowedTypes = [], int $maxSize = 5120): bool
    {
        // Check file size (in KB)
        if ($file->getSize() > $maxSize * 1024) {
            return false;
        }
        
        // Check file type
        if (!empty($allowedTypes) && !in_array($file->getClientOriginalExtension(), $allowedTypes)) {
            return false;
        }
        
        // Check for malicious files
        if ($this->isMaliciousFile($file)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if file is potentially malicious
     */
    private function isMaliciousFile(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        // Check for executable files
        $executableExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js'];
        if (in_array($extension, $executableExtensions)) {
            return true;
        }
        
        // Check for script files
        $scriptExtensions = ['php', 'asp', 'jsp', 'cgi'];
        if (in_array($extension, $scriptExtensions)) {
            return true;
        }
        
        // Check MIME type
        $dangerousMimeTypes = [
            'application/x-php',
            'application/x-msdownload',
            'application/x-executable',
        ];
        
        if (in_array($mimeType, $dangerousMimeTypes)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get file information
     */
    public function getFileInfo(string $filePath): array
    {
        try {
            $fullPath = Storage::disk('public')->path($filePath);
            
            if (!file_exists($fullPath)) {
                return [];
            }
            
            $pathInfo = pathinfo($fullPath);
            $stat = stat($fullPath);
            
            return [
                'name' => $pathInfo['basename'],
                'extension' => $pathInfo['extension'],
                'size' => $stat['size'],
                'size_mb' => round($stat['size'] / 1024 / 1024, 2),
                'created_at' => date('Y-m-d H:i:s', $stat['ctime']),
                'modified_at' => date('Y-m-d H:i:s', $stat['mtime']),
                'is_readable' => is_readable($fullPath),
                'is_writable' => is_writable($fullPath),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get file info: " . $e->getMessage());
            return [];
        }
    }
}

