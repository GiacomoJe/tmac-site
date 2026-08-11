<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->{$model->getSlugSourceColumn()})) {
                $model->slug = static::generateUniqueSlug($model->{$model->getSlugSourceColumn()});
            }
        });

        static::updating(function ($model) {
            if (empty($model->slug) && ! empty($model->{$model->getSlugSourceColumn()})) {
                $model->slug = static::generateUniqueSlug($model->{$model->getSlugSourceColumn()}, $model->id);
            }
        });
    }

    public function getSlugSourceColumn(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'name';
    }

    protected static function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
