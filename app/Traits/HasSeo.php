<?php

namespace App\Traits;

trait HasSeo
{
    public function getMetaTitleAttribute(): string
    {
        return $this->seo_title ?: ($this->title ?? $this->name ?? config('app.name'));
    }

    public function getMetaDescriptionAttribute(): string
    {
        return $this->seo_description ?: ($this->short_description ?? $this->description ?? '');
    }
}
