<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'title',
        'price',
        'previous_price',
        'image_url',
        'last_price_update',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'previous_price' => 'decimal:2',
        'last_price_update' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function histories()
    {
        return $this->hasMany(WishlistPriceHistory::class);
    }
}
