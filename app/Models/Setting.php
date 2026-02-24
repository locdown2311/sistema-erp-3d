<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->where('user_id', auth()->id())->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key, 'user_id' => auth()->id()], ['value' => $value]);
    }
}
