<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ServiceTypeImageSeeder extends Seeder
{
    /**
     * Service type images mapping
     * Using locally generated placeholder images
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
        Storage::disk('public')->makeDirectory('service-types', 0755, true);

        foreach ($this->images as $slug => [$name, $color]) {
            $filename = "service-types/{$slug}-massage.jpg";

            try {
                // Generate placeholder image
                $imageContent = $this->generatePlaceholderImage($name, $color, 400, 300);

                if ($imageContent && strlen($imageContent) > 0) {
                    Storage::disk('public')->put($filename, $imageContent);
                    // Only update if successfully stored
                    ServiceType::where('slug', $slug)->update(['image' => $filename]);
                } else {
                    echo "Warning: Failed to generate image for {$slug}\n";
                }
            } catch (\Exception $e) {
                echo "Error generating image for {$slug}: " . $e->getMessage() . "\n";
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
