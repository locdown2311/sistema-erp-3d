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

    public function currentPlan()
    {
        // Pega a assinatura mais recente que está ativa
        $subscription = $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();

        return $subscription ? $subscription->plan : null;
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
