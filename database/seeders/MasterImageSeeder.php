<?php

namespace Database\Seeders;

use App\Models\Master;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MasterImageSeeder extends Seeder
{
    /**
     * Master photos mapping
     * Using placeholder images for each master
     */
    private array $photos = [
        'Dilnoza' => ['Dilnoza Karimova', 'FF6B6B'],
        'Aziza' => ['Aziza Rustamova', '4ECDC4'],
        'Jamshid' => ['Jamshid Aliyev', 'FFE66D'],
        'Malika' => ['Malika Saidova', '95E1D3'],
        'Sardor' => ['Sardor Mahmudov', 'F38181'],
        'Gulnora' => ['Gulnora Tosheva', 'AA96DA'],
    ];

    public function run(): void
    {
        // Ensure storage directory exists
        Storage::makeDirectory('masters', 0755, true);

        foreach ($this->photos as $firstName => [$fullName, $color]) {
            // Create filename from first name
            $filename = strtolower($firstName);
            $photoPath = "masters/{$filename}.jpg";

            // Try to download placeholder image
            try {
                $placeholderUrl = "https://via.placeholder.com/300x300/{$color}/FFFFFF?text=" . urlencode($fullName);
                $imageContent = @file_get_contents($placeholderUrl);

                if ($imageContent !== false) {
                    Storage::put("public/{$photoPath}", $imageContent);
                }
            } catch (\Exception $e) {
                // Silently fail - image will use placeholder
            }

            // Update the master with the photo path
            Master::where('first_name', $firstName)->update(['photo' => $photoPath]);
        }
    }
}
