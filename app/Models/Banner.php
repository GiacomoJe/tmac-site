<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'image_mobile', 'image_desktop',
        'link_url', 'cta_label', 'image_only', 'position', 'sort_order',
        'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'image_only' => 'boolean',
        'sort_order' => 'integer',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopePosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }
}
