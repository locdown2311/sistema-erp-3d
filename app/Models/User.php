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
}
