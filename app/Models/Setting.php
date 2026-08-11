<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group', 'label'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting.$key", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    public static function put(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget("setting.$key");
    }

    protected static function booted(): void
    {
        static::saved(fn (Setting $s) => Cache::forget("setting.{$s->key}"));
        static::deleted(fn (Setting $s) => Cache::forget("setting.{$s->key}"));
    }
}
