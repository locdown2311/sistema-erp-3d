<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLink extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'icon',
        'is_active',
        'order',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
