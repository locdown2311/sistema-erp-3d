<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintCost extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'name', 'filament_weight_g', 'filament_price_kg',
        'filament_cost', 'print_time_hours', 'printer_wattage', 'kwh_rate',
        'energy_cost', 'printer_price', 'printer_lifespan_hours',
        'depreciation_cost', 'post_processing_hours', 'labor_rate',
        'labor_cost', 'total_cost', 'margin_percent', 'suggested_price',
    ];

    protected $casts = [
        'filament_weight_g' => 'decimal:2',
        'filament_price_kg' => 'decimal:2',
        'filament_cost' => 'decimal:2',
        'print_time_hours' => 'decimal:2',
        'printer_wattage' => 'decimal:2',
        'kwh_rate' => 'decimal:4',
        'energy_cost' => 'decimal:2',
        'printer_price' => 'decimal:2',
        'printer_lifespan_hours' => 'decimal:2',
        'depreciation_cost' => 'decimal:2',
        'post_processing_hours' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'suggested_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
