<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Upload an image to storage
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Delete an image from storage
     */
    public function delete(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Replace an existing image with a new one
     */
    public function replace(?string $oldPath, UploadedFile $newFile, string $directory): string
    {
        $this->delete($oldPath);
        return $this->upload($newFile, $directory);
    }
}
