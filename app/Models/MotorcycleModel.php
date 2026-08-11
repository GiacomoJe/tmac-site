<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotorcycleModel extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'motorcycle_make_id', 'name', 'slug', 'displacement', 'category',
        'year_start', 'year_end', 'image_path', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'displacement' => 'integer',
        'year_start' => 'integer',
        'year_end' => 'integer',
        'sort_order' => 'integer',
    ];

    public const CATEGORIES = [
        'street' => 'Street / Naked',
        'sport' => 'Sport / Esportiva',
        'trail' => 'Trail / Adventure',
        'scooter' => 'Scooter',
        'custom' => 'Custom / Cruiser',
        'touring' => 'Touring',
        'off' => 'Off-road / Trilha',
    ];

    public function make(): BelongsTo
    {
        return $this->belongsTo(MotorcycleMake::class, 'motorcycle_make_id');
    }

    public function fitments(): HasMany
    {
        return $this->hasMany(MotorcycleFitment::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'motorcycle_fitments')
            ->withPivot(['year_from', 'year_to', 'notes'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getYearRangeAttribute(): string
    {
        if (! $this->year_start) {
            return '';
        }

        $end = $this->year_end ?: 'atual';
        return $this->year_start.'–'.$end;
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->make?->name ?? '').' '.$this->name);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
