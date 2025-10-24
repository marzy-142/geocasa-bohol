<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AssetOptimizationService
{
    /**
     * Get asset statistics
     */
    public function getAssetStats(): array
    {
        return [
            'total_assets' => $this->getTotalAssets(),
            'asset_size' => $this->getAssetSize(),
            'asset_types' => $this->getAssetTypes(),
            'asset_performance' => $this->getAssetPerformance(),
        ];
    }

    /**
     * Get total assets
     */
    private function getTotalAssets(): int
    {
        $assetPath = public_path('build');
        $files = glob($assetPath . '/**/*');
        return count($files);
    }

    /**
     * Get asset size
     */
    private function getAssetSize(): array
    {
        $assetPath = public_path('build');
        $totalSize = 0;
        $fileCount = 0;
        
        if (is_dir($assetPath)) {
            $files = glob($assetPath . '/**/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                    $fileCount++;
                }
            }
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
            'average_size' => $fileCount > 0 ? round($totalSize / $fileCount) : 0,
        ];
    }

    /**
     * Get asset types
     */
    private function getAssetTypes(): array
    {
        $assetPath = public_path('build');
        $types = [
            'css' => 0,
            'js' => 0,
            'images' => 0,
            'fonts' => 0,
            'other' => 0,
        ];
        
        if (is_dir($assetPath)) {
            $files = glob($assetPath . '/**/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    
                    switch ($extension) {
                        case 'css':
                            $types['css']++;
                            break;
                        case 'js':
                            $types['js']++;
                            break;
                        case 'jpg':
                        case 'jpeg':
                        case 'png':
                        case 'gif':
                        case 'svg':
                        case 'webp':
                            $types['images']++;
                            break;
                        case 'woff':
                        case 'woff2':
                        case 'ttf':
                        case 'otf':
                            $types['fonts']++;
                            break;
                        default:
                            $types['other']++;
                            break;
                    }
                }
            }
        }
        
        return $types;
    }

    /**
     * Get asset performance
     */
    private function getAssetPerformance(): array
    {
        $performance = Cache::get('asset_performance', []);
        
        if (empty($performance)) {
            return [
                'average_load_time' => 0,
                'fastest_load_time' => 0,
                'slowest_load_time' => 0,
                'asset_count' => 0,
            ];
        }
        
        return [
            'average_load_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_load_time' => min($performance),
            'slowest_load_time' => max($performance),
            'asset_count' => count($performance),
        ];
    }

    /**
     * Optimize assets
     */
    public function optimizeAssets(): array
    {
        $optimizations = [];
        
        // Optimize CSS
        $optimizations['css_optimization'] = $this->optimizeCss();
        
        // Optimize JavaScript
        $optimizations['js_optimization'] = $this->optimizeJavaScript();
        
        // Optimize images
        $optimizations['image_optimization'] = $this->optimizeImages();
        
        // Optimize fonts
        $optimizations['font_optimization'] = $this->optimizeFonts();
        
        return $optimizations;
    }

    /**
     * Optimize CSS
     */
    private function optimizeCss(): array
    {
        try {
            $cssPath = public_path('build/css');
            $files = glob($cssPath . '/*.css');
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                $size = filesize($file);
                
                // Check for large CSS files
                if ($size > 100 * 1024) { // 100KB
                    $optimizations[] = "CSS file '{$filename}' is large. Consider minifying or splitting";
                }
                
                // Check for unused CSS
                if (str_contains($content, '/* unused */')) {
                    $optimizations[] = "CSS file '{$filename}' contains unused styles";
                }
                
                // Check for duplicate selectors
                $selectors = [];
                preg_match_all('/[^{}]+{/', $content, $matches);
                foreach ($matches[0] as $selector) {
                    $cleanSelector = trim($selector);
                    if (isset($selectors[$cleanSelector])) {
                        $optimizations[] = "CSS file '{$filename}' has duplicate selectors";
                        break;
                    }
                    $selectors[$cleanSelector] = true;
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize JavaScript
     */
    private function optimizeJavaScript(): array
    {
        try {
            $jsPath = public_path('build/js');
            $files = glob($jsPath . '/*.js');
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                $size = filesize($file);
                
                // Check for large JS files
                if ($size > 500 * 1024) { // 500KB
                    $optimizations[] = "JS file '{$filename}' is large. Consider minifying or splitting";
                }
                
                // Check for console.log statements
                if (str_contains($content, 'console.log')) {
                    $optimizations[] = "JS file '{$filename}' contains console.log statements";
                }
                
                // Check for debug code
                if (str_contains($content, 'debugger')) {
                    $optimizations[] = "JS file '{$filename}' contains debugger statements";
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize images
     */
    private function optimizeImages(): array
    {
        try {
            $imagePath = public_path('build/images');
            $files = glob($imagePath . '/*.{jpg,jpeg,png,gif,svg,webp}', GLOB_BRACE);
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $size = filesize($file);
                
                // Check for large images
                if ($size > 1024 * 1024) { // 1MB
                    $optimizations[] = "Image '{$filename}' is large. Consider compressing or resizing";
                }
                
                // Check for PNG images that could be WebP
                if (str_ends_with($filename, '.png') && $size > 100 * 1024) { // 100KB
                    $optimizations[] = "PNG image '{$filename}' could be converted to WebP for better compression";
                }
                
                // Check for JPEG images that could be WebP
                if ((str_ends_with($filename, '.jpg') || str_ends_with($filename, '.jpeg')) && $size > 100 * 1024) {
                    $optimizations[] = "JPEG image '{$filename}' could be converted to WebP for better compression";
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize fonts
     */
    private function optimizeFonts(): array
    {
        try {
            $fontPath = public_path('build/fonts');
            $files = glob($fontPath . '/*.{woff,woff2,ttf,otf}', GLOB_BRACE);
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $size = filesize($file);
                
                // Check for large font files
                if ($size > 500 * 1024) { // 500KB
                    $optimizations[] = "Font '{$filename}' is large. Consider using font subsetting";
                }
                
                // Check for TTF/OTF fonts that could be WOFF2
                if ((str_ends_with($filename, '.ttf') || str_ends_with($filename, '.otf')) && $size > 100 * 1024) {
                    $optimizations[] = "Font '{$filename}' could be converted to WOFF2 for better compression";
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get asset recommendations
     */
    public function getAssetRecommendations(): array
    {
        $stats = $this->getAssetStats();
        $recommendations = [];
        
        // Size recommendations
        if ($stats['asset_size']['total_size_mb'] > 10) {
            $recommendations[] = 'Total asset size is large. Consider optimizing assets';
        }
        
        // Performance recommendations
        $performance = $stats['asset_performance'];
        if ($performance['average_load_time'] > 1000) { // 1s
            $recommendations[] = 'Asset load time is slow. Consider optimizing assets';
        }
        
        // Type recommendations
        $types = $stats['asset_types'];
        if ($types['images'] > 50) {
            $recommendations[] = 'Many images. Consider using lazy loading or image optimization';
        }
        
        if ($types['css'] > 20) {
            $recommendations[] = 'Many CSS files. Consider combining or using CSS modules';
        }
        
        if ($types['js'] > 20) {
            $recommendations[] = 'Many JS files. Consider combining or using code splitting';
        }
        
        return $recommendations;
    }

    /**
     * Get asset health
     */
    public function getAssetHealth(): array
    {
        $stats = $this->getAssetStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check size
        if ($stats['asset_size']['total_size_mb'] > 10) {
            $health['status'] = 'unhealthy';
            $health['checks']['size'] = 'Large asset size: ' . $stats['asset_size']['total_size_mb'] . 'MB';
        } else {
            $health['checks']['size'] = 'OK';
        }
        
        // Check performance
        $performance = $stats['asset_performance'];
        if ($performance['average_load_time'] > 1000) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow asset loading: ' . $performance['average_load_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get asset performance metrics
     */
    public function getAssetPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test asset performance
        $this->testAssetPerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'asset_time' => $executionTime,
            'asset_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_assets' => $this->getTotalAssets(),
            'asset_size_mb' => $this->getAssetSize()['total_size_mb'],
        ];
    }

    /**
     * Test asset performance
     */
    private function testAssetPerformance(): void
    {
        try {
            // Test asset loading by checking if files exist
            $assetPath = public_path('build');
            $files = glob($assetPath . '/**/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    filesize($file);
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Asset test failed: " . $e->getMessage());
        }
    }
}

