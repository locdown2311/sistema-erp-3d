<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'slug', 'store_name', 'store_logo', 'whatsapp', 'store_description',
        'store_color_primary', 'store_color_accent', 'store_color_bg',
        'suspended_at', 'suspension_reason', 'flexi_cuts_count',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function products() { return $this->hasMany(Product::class); }
    public function filaments() { return $this->hasMany(Filament::class); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function printCosts() { return $this->hasMany(PrintCost::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function customers() { return $this->hasMany(Customer::class); }

    /**
     * Retorna o plano atual do usuário.
     * Se não houver assinatura ativa, retorna o plano Gratuito como fallback.
     */
    public function currentPlan()
    {
        $subscription = $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();

        if ($subscription) {
            return $subscription->plan;
        }

        // Fallback: retorna plano gratuito
        return Plan::where('slug', 'free')->first();
    }

    /**
     * Verifica se o limite do plano para um recurso foi atingido.
     */
    public function planLimitReached(string $resource): bool
    {
        $plan = $this->currentPlan();
        if (!$plan) return false; // sem plano, sem limites

        return match ($resource) {
            'products' => $plan->limitReached('max_products', $this->products()->count()),
            'sales' => $plan->limitReached('max_sales_per_month', $this->salesThisMonth()),
            'wishlists' => $plan->limitReached('max_wishlists', $this->wishlists()->count()),
            'flexi_cuts' => $plan->limitReached('max_flexi_cuts', $this->flexi_cuts_count),
            default => false,
        };
    }

    /**
     * Retorna o uso atual vs limite para um recurso.
     * Retorna ['current' => X, 'limit' => Y|null]
     */
    public function getPlanUsage(string $resource): array
    {
        $plan = $this->currentPlan();
        if (!$plan) return ['current' => 0, 'limit' => null];

        return match ($resource) {
            'products' => [
                'current' => $this->products()->count(),
                'limit' => $plan->max_products,
            ],
            'sales' => [
                'current' => $this->salesThisMonth(),
                'limit' => $plan->max_sales_per_month,
            ],
            'wishlists' => [
                'current' => $this->wishlists()->count(),
                'limit' => $plan->max_wishlists,
            ],
            'flexi_cuts' => [
                'current' => $this->flexi_cuts_count,
                'limit' => $plan->max_flexi_cuts,
            ],
            default => ['current' => 0, 'limit' => null],
        };
    }

    /**
     * Contagem de vendas do mês atual.
     */
    public function salesThisMonth(): int
    {
        return $this->sales()
            ->where('status', 'completed')
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->count();
    }

    public function getStoreLogoUrlAttribute(): ?string
    {
        if (!$this->store_logo) return null;
        if (str_starts_with($this->store_logo, 'http')) return $this->store_logo;
        return \Illuminate\Support\Facades\Storage::url($this->store_logo);
    }

    public function getStoreLogoThumbnailUrlAttribute(): ?string
    {
        if (!$this->store_logo) return null;
        if (str_starts_with($this->store_logo, 'https://pixeldrain.com/api/file/')) {
            return $this->store_logo . '/thumbnail';
        }
        return $this->store_logo_url;
    }
}
