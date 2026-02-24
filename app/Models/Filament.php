<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filament extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'color', 'brand', 'price_per_kg',
        'weight_grams', 'remaining_grams', 'diameter_mm',
        'print_temp_min', 'print_temp_max',
        'bed_temp_min', 'bed_temp_max',
        'notes', 'active',
    ];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'weight_grams' => 'decimal:2',
        'remaining_grams' => 'decimal:2',
        'diameter_mm' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function getRemainingPercentAttribute(): float
    {
        if ($this->weight_grams <= 0) return 0;
        return round(($this->remaining_grams / $this->weight_grams) * 100, 1);
    }
}
