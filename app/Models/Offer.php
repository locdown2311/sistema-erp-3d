<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'name', 'description', 'image_path', 'price',
        'original_price', 'affiliate_url', 'category', 'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->original_price || $this->original_price <= $this->price) return null;
        return round((1 - $this->price / $this->original_price) * 100);
    }
}
