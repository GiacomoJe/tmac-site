<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = ['platform', 'label', 'url', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Plataformas suportadas e seus rótulos padrão.
     * Os SVGs ficam no componente <x-site.social-icon>.
     */
    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'whatsapp'  => 'WhatsApp',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
        'linkedin'  => 'LinkedIn',
        'x'         => 'X (Twitter)',
        'telegram'  => 'Telegram',
        'pinterest' => 'Pinterest',
        'spotify'   => 'Spotify',
        'threads'   => 'Threads',
        'website'   => 'Site / Outro',
    ];

    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: (self::PLATFORMS[$this->platform] ?? ucfirst($this->platform));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Atalho memoizado em cache pra usar no header/footer/rail sem N+1.
     */
    public static function visible()
    {
        return Cache::rememberForever('social_links.visible', function () {
            return static::active()->ordered()->get();
        });
    }

    protected static function booted(): void
    {
        $bust = fn () => Cache::forget('social_links.visible');
        static::saved($bust);
        static::deleted($bust);
    }
}
