<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'uf', 'region', 'min_quote_value'];

    protected $casts = [
        'min_quote_value' => 'decimal:2',
    ];

    /**
     * Mínimo efetivo: valor configurado ou fallback do config('quote.default_minimum')
     */
    public function getEffectiveMinimumAttribute(): float
    {
        return (float) ($this->min_quote_value ?? config('quote.default_minimum', 2000));
    }

    public function representatives(): BelongsToMany
    {
        return $this->belongsToMany(Representative::class)
            ->withPivot('is_primary');
    }

    public function primaryRepresentative()
    {
        return $this->representatives()
            ->wherePivot('is_primary', true)
            ->where('is_active', true)
            ->first()
            ?? $this->representatives()->where('is_active', true)->first();
    }

    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class);
    }
}
