<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyImageSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('Seeding property images...');

        // Create storage directories if they don't exist
        $directories = [
            'public/properties/images',
            'public/properties/virtual-tours',
            'public/properties/documents'
        ];

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
                $this->command->info("Created directory: {$dir}");
            }
        }

        // Define property images with realistic URLs
        $propertyImages = [
            'beachfront' => [
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1473496169904-658ba7c44d8a?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=2070&q=80'
            ],
            'chocolate_hills' => [
                'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?auto=format&fit=crop&w=2070&q=80'
            ],
            'commercial' => [
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=2070&q=80'
            ],
            'residential' => [
                'https://images.unsplash.com/photo-1465146633011-14f8e0781093?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=2070&q=80'
            ],
            'agricultural' => [
                'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=2070&q=80',
                'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?auto=format&fit=crop&w=2070&q=80'
            ]
        ];

        // Download and store images
        foreach ($propertyImages as $type => $urls) {
            $this->command->info("Downloading {$type} images...");
            
            foreach ($urls as $index => $url) {
                try {
                    // Use cURL for better error handling
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                    
                    $imageData = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($imageData === false || $httpCode !== 200) {
                        $this->command->warn("Failed to download image: {$url} (HTTP: {$httpCode})");
                        continue;
                    }

                    $filename = "{$type}_{$index}.jpg";
                    $directory = storage_path("app/public/properties/images");
                    
                    // Ensure directory exists
                    if (!is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    
                    $filePath = $directory . '/' . $filename;
                    $result = file_put_contents($filePath, $imageData);
                    
                    if ($result !== false) {
                        $this->command->info("✓ Saved: {$filename} ({$result} bytes)");
                    } else {
                        $this->command->warn("Failed to save: {$filename}");
                    }
                    
                } catch (\Exception $e) {
                    $this->command->warn("Error downloading {$url}: " . $e->getMessage());
                }
            }
        }

        $this->command->info('Property images seeded successfully!');
    }
}
