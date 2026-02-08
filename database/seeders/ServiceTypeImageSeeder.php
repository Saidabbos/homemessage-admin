<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ServiceTypeImageSeeder extends Seeder
{
    /**
     * Service type images mapping
     * Using placeholder images that are descriptive
     */
    private array $images = [
        'classic' => ['Klassik Massaj', 'FF6B6B'],
        'relax' => ['Relaks Massaj', '4ECDC4'],
        'thai' => ['Tailand Massaji', 'FFE66D'],
        'sport' => ['Sport Massaji', '95E1D3'],
        'hot-stone' => ['Issiq Tosh', 'F38181'],
        'foot' => ['Oyoq Massaji', 'AA96DA'],
        'back-neck' => ['Orqa va Bo\'yin', 'FCBAD3'],
        'anti-cellulite' => ['Anti-Sellyulit', 'A8E6CF'],
    ];

    public function run(): void
    {
        // Ensure storage directory exists
        Storage::makeDirectory('service-types', 0755, true);

        foreach ($this->images as $slug => [$name, $color]) {
            $filename = "service-types/{$slug}-massage.jpg";

            // Try to download placeholder image
            try {
                $placeholderUrl = "https://via.placeholder.com/400x300/{$color}/FFFFFF?text=" . urlencode($name);
                $imageContent = @file_get_contents($placeholderUrl);

                if ($imageContent !== false) {
                    Storage::put("public/{$filename}", $imageContent);
                }
            } catch (\Exception $e) {
                // Silently fail - image will use placeholder
            }

            // Update the service type with the image path
            ServiceType::where('slug', $slug)->update(['image' => $filename]);
        }
    }
}
