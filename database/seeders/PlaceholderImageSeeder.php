<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaceholderImageSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $this->command->info('Creating placeholder images...');

        // Create storage directories if they don't exist
        $directories = [
            'public/properties/images',
            'public/properties/virtual-tours',
            'public/properties/documents'
        ];

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
            }
        }

        // Create placeholder images using simple SVG data
        $placeholders = [
            'beachfront' => [
                'title' => 'Beachfront Property',
                'color' => '#87CEEB', // Sky blue
                'count' => 3
            ],
            'chocolate_hills' => [
                'title' => 'Agricultural Land',
                'color' => '#90EE90', // Light green
                'count' => 2
            ],
            'commercial' => [
                'title' => 'Commercial Lot',
                'color' => '#DDA0DD', // Plum
                'count' => 2
            ],
            'residential' => [
                'title' => 'Residential Lot',
                'color' => '#F0E68C', // Khaki
                'count' => 2
            ],
            'agricultural' => [
                'title' => 'Agricultural Land',
                'color' => '#98FB98', // Pale green
                'count' => 2
            ]
        ];

        foreach ($placeholders as $type => $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $filename = "{$type}_{$i}.jpg";
                $svgContent = $this->generatePlaceholderSVG($config['title'], $config['color'], $i + 1);
                
                // Convert SVG to a simple base64 encoded image
                $imageData = base64_decode('data:image/svg+xml;base64,' . base64_encode($svgContent));
                
                Storage::put("public/properties/images/{$filename}", $imageData);
                $this->command->info("Created placeholder: {$filename}");
            }
        }

        $this->command->info('Placeholder images created successfully!');
    }

    private function generatePlaceholderSVG($title, $color, $number)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="800" height="600" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="' . $color . '" opacity="0.3"/>
    <rect width="100%" height="100%" fill="none" stroke="' . $color . '" stroke-width="4"/>
    <circle cx="400" cy="200" r="60" fill="' . $color . '" opacity="0.6"/>
    <rect x="350" y="280" width="100" height="80" fill="' . $color . '" opacity="0.6" rx="10"/>
    <text x="400" y="400" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="#333">' . $title . '</text>
    <text x="400" y="430" font-family="Arial, sans-serif" font-size="16" text-anchor="middle" fill="#666">Property Image #' . $number . '</text>
    <text x="400" y="480" font-family="Arial, sans-serif" font-size="14" text-anchor="middle" fill="#888">GeoCasa Bohol</text>
</svg>';
    }
}
