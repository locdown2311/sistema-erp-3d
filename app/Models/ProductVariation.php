<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $fillable = [
        'product_id', 'name', 'type', 'price_modifier', 'cost_modifier', 'sku', 'stock_quantity',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'cost_modifier' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
