<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistPriceHistory extends Model
{
    protected $fillable = [
        'wishlist_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }
}
