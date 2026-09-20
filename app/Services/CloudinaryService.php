<?php

namespace App\Services;

use Cloudinary\Uploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CloudinaryService
{
    protected bool $cloudinaryConfigured = false;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if ((!$cloudName || !$apiKey || !$apiSecret) && config('services.cloudinary.url')) {
            $cloudinaryUrl = parse_url(config('services.cloudinary.url'));
            $cloudName = $cloudName ?: ($cloudinaryUrl['host'] ?? null);
            $apiKey = $apiKey ?: ($cloudinaryUrl['user'] ?? null);
            $apiSecret = $apiSecret ?: (isset($cloudinaryUrl['pass'])
                ? rawurldecode($cloudinaryUrl['pass'])
                : null);
        }

        if ($cloudName && $apiKey && $apiSecret) {
            \Cloudinary::config([
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'secure' => true,
            ]);
            $this->cloudinaryConfigured = true;
        }
    }

    /**
     * Upload an uploaded file or file path.
     * Returns image URL (Cloudinary or local storage fallback).
     */
    public function upload(UploadedFile|string $file, string $folder = 'products'): string
    {
        $filePath = is_string($file) ? $file : $file->getRealPath();

        if ($this->cloudinaryConfigured) {
            try {
                $response = Uploader::upload($filePath, [
                    'folder' => $folder,
                ]);

                if (!empty($response['secure_url'])) {
                    return $response['secure_url'];
                }
            } catch (\Throwable $e) {
                Log::error('Cloudinary upload error: ' . $e->getMessage());
            }
        }

        // Fallback: save to local public disk
        if ($file instanceof UploadedFile) {
            $filename = Str::uuid() . '.' . $file->extension();
            $path = $file->storeAs($folder, $filename, 'public');
            return asset('storage/' . $path);
        }

        return '';
    }
}