<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, HasSeo;

    protected $fillable = [
        'slug', 'title', 'content', 'hero_image',
        'hero_eyebrow', 'hero_title', 'hero_highlight', 'hero_description',
        'extra_data',
        'seo_title', 'seo_description', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'extra_data' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Recupera item do extra_data com fallback. */
    public function data(string $key, mixed $fallback = null): mixed
    {
        return data_get($this->extra_data ?? [], $key, $fallback);
    }
}
