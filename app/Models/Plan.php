<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'max_products', 'max_sales_per_month',
        'max_wishlists', 'features', 'is_active', 'mp_plan_id',
        'can_export_reports', 'can_use_nfe', 'priority_store', 'can_customize_store',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'can_export_reports' => 'boolean',
            'can_use_nfe' => 'boolean',
            'priority_store' => 'boolean',
            'can_customize_store' => 'boolean',
        ];
    }

    /**
     * Verifica se um limite numérico foi atingido.
     * Retorna true se o limite existe e $currentCount >= limite.
     * Retorna false se o limite é null (ilimitado).
     */
    public function limitReached(string $column, int $currentCount): bool
    {
        $limit = $this->{$column};
        if ($limit === null) return false; // ilimitado
        return $currentCount >= $limit;
    }
}
