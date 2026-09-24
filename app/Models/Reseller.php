<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reseller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company_name', 'cnpj', 'slug',
        'zip', 'address', 'number', 'complement', 'neighborhood', 'city', 'state_id',
        'latitude', 'longitude',
        'phone', 'whatsapp', 'email', 'website', 'instagram',
        'opening_hours', 'notes', 'tmac_brands',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'tmac_brands'  => 'array',
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'sort_order'   => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Reseller $r) {
            if (! $r->slug) {
                $base = Str::slug($r->name.'-'.$r->city);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $r->id ?? 0)->exists()) {
                    $slug = $base.'-'.(++$i);
                }
                $r->slug = $slug;
            }
        });
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeGeocoded(Builder $q): Builder
    {
        return $q->whereNotNull('latitude')->whereNotNull('longitude');
    }

    /**
     * Ordena por proximidade e calcula a distância em km (fórmula de Haversine).
     * Uso: Reseller::active()->nearby($lat, $lng, 100)->get()
     */
    public function scopeNearby(Builder $q, float $lat, float $lng, ?float $radiusKm = null): Builder
    {
        $haversine = "(6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(latitude))
        ))";

        $q->geocoded()
          ->select('*')
          ->selectRaw("{$haversine} AS distance_km", [$lat, $lng, $lat])
          ->orderBy('distance_km');

        if ($radiusKm) {
            $q->havingRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm]);
        }

        return $q;
    }

    /** Endereço completo formatado para exibição. */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            trim(($this->address ?? '').' '.($this->number ?? '')),
            $this->complement,
            $this->neighborhood,
            trim($this->city.($this->state?->uf ? ' - '.$this->state->uf : '')),
            $this->zip,
        ]);

        return implode(' · ', $parts);
    }

    /** Endereço em uma linha para geocodificação / Google Maps. */
    public function getGeoAddressAttribute(): string
    {
        return implode(', ', array_filter([
            trim(($this->address ?? '').' '.($this->number ?? '')),
            $this->neighborhood,
            $this->city,
            $this->state?->uf,
            'Brasil',
        ]));
    }

    public function getMapsUrlAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps/dir/?api=1&destination={$this->latitude},{$this->longitude}";
        }
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->geo_address);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $num = preg_replace('/\D/', '', (string) ($this->whatsapp ?: $this->phone));
        if (! $num) return null;
        if (strlen($num) <= 11) $num = '55'.$num;

        return "https://wa.me/{$num}";
    }
}
