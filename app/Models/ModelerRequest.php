<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelerRequest extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'whatsapp',
        'description',
        'image_path',
        'budget_range',
    ];

    protected $casts = [
        'image_path' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to resolve a single path into a display URL.
     */
    protected function resolveImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'https://pixeldrain.com/api/file/')) {
            return str_replace('/api/file/', '/u/', $path);
        }
        
        if (str_starts_with($path, 'https://pixeldrain.com/u/')) {
            return $path;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'modeler_requests/')) {
            return asset('storage/' . $path);
        }

        // If it's just a 12-character alphanumeric ID, it's a PixelDrain ID
        if (preg_match('/^[a-zA-Z0-9]{10,12}$/', $path)) {
            return "https://pixeldrain.com/u/{$path}";
        }

        // Fallback for cases where it's already a full URL but we missed the prefix
        if (str_contains($path, 'pixeldrain.com')) {
            return $path;
        }

        return "https://pixeldrain.com/u/{$path}";
    }

    /**
     * Helper to resolve a single path into a thumbnail URL.
     */
    protected function resolveThumbnailUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'modeler_requests/')) {
            return asset('storage/' . $path);
        }

        if (str_starts_with($path, 'https://pixeldrain.com/api/file/')) {
            return str_ends_with($path, '/thumbnail') ? $path : $path . '/thumbnail';
        }

        if (str_starts_with($path, 'https://pixeldrain.com/u/')) {
            $id = str_replace('https://pixeldrain.com/u/', '', $path);
            return "https://pixeldrain.com/api/file/{$id}/thumbnail";
        }

        // If it already ends with /thumbnail, just return it (assuming it's a valid URL)
        if (str_ends_with($path, '/thumbnail')) {
            return $path;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // If it's just a 12-character alphanumeric ID, it's a PixelDrain ID
        if (preg_match('/^[a-zA-Z0-9]{10,12}$/', $path)) {
            return "https://pixeldrain.com/api/file/{$path}/thumbnail";
        }

        return "https://pixeldrain.com/api/file/{$path}/thumbnail";
    }

    /**
     * Get all image URLs as an array.
     */
    public function getImageUrlsAttribute(): array
    {
        $paths = $this->image_path;

        if (empty($paths)) {
            return [];
        }

        // Legacy support: if somehow a plain string got through
        if (is_string($paths)) {
            $paths = [$paths];
        }

        return array_filter(array_map(fn($p) => $this->resolveImageUrl($p), $paths));
    }

    /**
     * Get all thumbnail URLs as an array.
     */
    public function getThumbnailUrlsAttribute(): array
    {
        $paths = $this->image_path;

        if (empty($paths)) {
            return [];
        }

        if (is_string($paths)) {
            $paths = [$paths];
        }

        return array_filter(array_map(fn($p) => $this->resolveThumbnailUrl($p), $paths));
    }

    /**
     * Backward-compatible: get first image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }

    /**
     * Backward-compatible: get first thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_urls[0] ?? null;
    }
}
