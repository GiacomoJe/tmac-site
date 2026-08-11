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
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasSeo, HasSlug, SoftDeletes;

    protected $fillable = [
        'brand_id', 'tmac_brand_id', 'sku', 'quote_price', 'name', 'slug',
        'short_description', 'description', 'specifications',
        'main_image', 'is_featured', 'is_active', 'sort_order',
        'seo_title', 'seo_description',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'quote_price' => 'decimal:2',
    ];

    /**
     * Hidden — nunca expomos o preço de cotação ao cliente.
     */
    protected $hidden = ['quote_price'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tmacBrand(): BelongsTo
    {
        return $this->belongsTo(TmacBrand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function fitments(): HasMany
    {
        return $this->hasMany(MotorcycleFitment::class);
    }

    public function motorcycleModels(): BelongsToMany
    {
        return $this->belongsToMany(MotorcycleModel::class, 'motorcycle_fitments')
            ->withPivot(['year_from', 'year_to', 'notes'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filtra produtos compatíveis com uma moto (modelo) opcionalmente em um ano.
     */
    public function scopeForMotorcycle(Builder $query, ?int $modelId, ?int $year = null): Builder
    {
        if (! $modelId) {
            return $query;
        }

        return $query->whereHas('fitments', function (Builder $f) use ($modelId, $year) {
            $f->where('motorcycle_model_id', $modelId);

            if ($year) {
                $f->where(function (Builder $q) use ($year) {
                    $q->where(function (Builder $qq) use ($year) {
                            $qq->whereNull('year_from')->orWhere('year_from', '<=', $year);
                        })
                        ->where(function (Builder $qq) use ($year) {
                            $qq->whereNull('year_to')->orWhere('year_to', '>=', $year);
                        });
                });
            }
        });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
              ->orWhere('sku', 'like', $like)
              ->orWhere('short_description', 'like', $like);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
