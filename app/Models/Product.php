<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'category', 'base_price', 'base_cost',
        'print_time_hours', 'weight_grams', 'image_path', 'active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'print_time_hours' => 'decimal:2',
        'weight_grams' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function printCosts(): HasMany
    {
        return $this->hasMany(PrintCost::class);
    }

    public function getCurrentStockAttribute(): int
    {
        $in = $this->stockMovements()->where('type', 'in')->sum('quantity');
        $out = $this->stockMovements()->where('type', 'out')->sum('quantity');
        return $in - $out;
    }
}
