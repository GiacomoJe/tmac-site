<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TmacBrand extends Model
{
    use HasFactory;

    /** Paleta de acentos aceitos (usados no front pra colorir o card). */
    public const ACCENTS = [
        'signal'   => 'Azul (Signal)',
        'accent'   => 'Vermelho (Accent)',
        'ink'      => 'Preto (Ink)',
        'whatsapp' => 'Verde (WhatsApp)',
    ];

    protected $fillable = [
        'name', 'slug', 'badge_label', 'tagline', 'description',
        'image_path', 'logo_path', 'accent_color', 'brand_color', 'text_theme', 'link_url',
        'sort_order', 'is_active', 'is_featured',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (TmacBrand $brand) {
            if (! $brand->slug) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    /** Classes Tailwind por accent_color, prontas pra usar nos cards/badges. */
    public function getAccentClassesAttribute(): array
    {
        return match ($this->accent_color) {
            'accent'   => ['solid' => 'bg-accent text-white border-accent',     'soft' => 'bg-accent-soft text-accent',     'text' => 'text-accent'],
            'ink'      => ['solid' => 'bg-ink text-white border-ink',           'soft' => 'bg-bg-elev text-ink',            'text' => 'text-ink'],
            'whatsapp' => ['solid' => 'bg-whatsapp text-white border-whatsapp', 'soft' => 'bg-whatsapp/15 text-whatsapp',   'text' => 'text-whatsapp'],
            default    => ['solid' => 'bg-signal text-white border-signal',     'soft' => 'bg-signal-soft text-signal',     'text' => 'text-signal'],
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    /** Cor sólida da marca, com fallback baseado no accent_color escolhido. */
    public function getResolvedBrandColorAttribute(): string
    {
        if ($this->brand_color) return $this->brand_color;
        return match ($this->accent_color) {
            'accent'   => '#E11D2A',
            'ink'      => '#1A1B1F',
            'whatsapp' => '#25D366',
            default    => '#004DFF',
        };
    }

    /** Define a cor ideal de texto sobre a cor da marca (branco ou preto). */
    public function getResolvedTextColorAttribute(): string
    {
        return $this->text_theme === 'dark' ? '#1A1B1F' : '#FFFFFF';
    }

    /** Variante mais escura da cor da marca pra hover/sombras. */
    public function getDarkenedColorAttribute(): string
    {
        $hex = ltrim($this->resolved_brand_color, '#');
        if (strlen($hex) !== 6) return $this->resolved_brand_color;

        $r = max(0, hexdec(substr($hex, 0, 2)) - 30);
        $g = max(0, hexdec(substr($hex, 2, 2)) - 30);
        $b = max(0, hexdec(substr($hex, 4, 2)) - 30);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
