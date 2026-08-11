<?php

namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasSeo, HasSlug;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image_path',
        'sort_order', 'is_active', 'seo_title', 'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** Categoria pai (se for subcategoria). */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Subcategorias filhas (relação 1:N). */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /** Subcategorias ativas (com eager load chain pronto). */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->active()->ordered();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** Apenas categorias raiz (sem parent). */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** Apenas subcategorias (tem parent). */
    public function scopeSub(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
}
