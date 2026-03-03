<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PixelDrainService
{
    /**
     * Uploads an image from a base64 string to PixelDrain.
     *
     * @param string $base64
     * @param string $filename
     * @return string|null The full PixelDrain URL or null on failure.
     */
    public function uploadBase64(string $base64, string $filename): ?string
    {
        $data = explode(',', $base64, 2);
        $imageData = base64_decode($data[1] ?? $data[0]);

        if ($imageData === false) {
            throw new \InvalidArgumentException('Invalid base64 encoding.');
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!array_key_exists($mimeType, $allowedMimes)) {
            throw new \InvalidArgumentException('Disallowed file type: ' . $mimeType);
        }

        // Prepare a multipart request
        $response = Http::withBasicAuth('', config('services.pixeldrain.key'))
            ->attach('file', $imageData, $filename)
            ->post('https://pixeldrain.com/api/file');

        if ($response->successful()) {
            return $this->getUrl($response->json('id'));
        }

        \Log::error('PixelDrain Base64 upload failed', ['response' => $response->body()]);
        return null;
    }

    /**
     * Uploads an image from an UploadedFile object to PixelDrain.
     *
     * @param UploadedFile $file
     * @return string|null The full PixelDrain URL or null on failure.
     */
    public function uploadFile(UploadedFile $file): ?string
    {
        $response = Http::withBasicAuth('', config('services.pixeldrain.key'))
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post('https://pixeldrain.com/api/file');

        if ($response->successful()) {
            return $this->getUrl($response->json('id'));
        }

        \Log::error('PixelDrain File upload failed', ['response' => $response->body()]);
        return null;
    }

    /**
     * Generates the public URL for a given PixelDrain file ID.
     *
     * @param string $id
     * @return string
     */
    public function getUrl(string $id): string
    {
        return "https://pixeldrain.com/api/file/$id";
    }
}
