<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'user_id', 'customer_name', 'total', 'notes', 'sale_date', 'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
