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
}
