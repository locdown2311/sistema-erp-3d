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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'https://pixeldrain.com/api/file/')) {
            return str_replace('/api/file/', '/u/', $this->image_path);
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'modeler_requests/')) {
            return asset('storage/' . $this->image_path);
        }

        return "https://pixeldrain.com/u/{$this->image_path}";
    }

    public function getThumbnailUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'modeler_requests/')) {
            return asset('storage/' . $this->image_path);
        }

        if (str_starts_with($this->image_path, 'https://pixeldrain.com/api/file/')) {
            return $this->image_path . '/thumbnail';
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return "https://pixeldrain.com/api/file/{$this->image_path}/thumbnail";
    }
}
