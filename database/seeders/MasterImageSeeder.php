<?php

namespace Database\Seeders;

use App\Models\Master;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MasterImageSeeder extends Seeder
{
    /**
     * Master photos mapping
     * Using locally generated placeholder images
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
        Storage::disk('public')->makeDirectory('masters', 0755, true);

        foreach ($this->photos as $firstName => [$fullName, $color]) {
            $filename = strtolower($firstName);
            $photoPath = "masters/{$filename}.jpg";

            try {
                // Generate placeholder image
                $imageContent = $this->generatePlaceholderImage($fullName, $color, 300, 300);

                if ($imageContent && strlen($imageContent) > 0) {
                    Storage::disk('public')->put($photoPath, $imageContent);
                    // Only update if successfully stored
                    Master::where('first_name', $firstName)->update(['photo' => $photoPath]);
                } else {
                    echo "Warning: Failed to generate image for {$firstName}\n";
                }
            } catch (\Exception $e) {
                echo "Error generating image for {$firstName}: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Generate a simple placeholder image using GD library
     */
    private function generatePlaceholderImage(string $text, string $hexColor, int $width, int $height): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        // Create image
        $image = imagecreatetruecolor($width, $height);
        if (!$image) {
            return null;
        }

        // Parse color
        $rgb = $this->hexToRgb($hexColor);
        $bgColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Fill background
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);

        // Add text (font 5 is a built-in font, no file needed)
        $textWidth = strlen($text) * imagefontwidth(5);
        $textHeight = imagefontheight(5);
        $x = max(10, ($width - $textWidth) / 2);
        $y = ($height - $textHeight) / 2;

        imagestring($image, 5, (int)$x, (int)$y, $text, $textColor);

        // Capture as JPEG
        ob_start();
        imagejpeg($image, null, 85);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content ?: null;
    }

    /**
     * Convert hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', $hex);
        return [
            'r' => intval(substr($hex, 0, 2), 16),
            'g' => intval(substr($hex, 2, 2), 16),
            'b' => intval(substr($hex, 4, 2), 16),
        ];
    }
}
