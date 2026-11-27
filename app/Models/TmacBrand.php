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
        'parent_id', 'name', 'slug', 'badge_label', 'tagline', 'description', 'page_content',
        'image_path', 'logo_path', 'accent_color', 'brand_color', 'text_theme', 'link_url',
        'sort_order', 'is_active', 'is_featured',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'page_content' => 'array',
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

    /** Marca "mãe" (ex.: LBJ é a mãe da LBJ Premium). */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** Submarcas (ex.: LBJ Premium). */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Só marcas principais (sem submarcas) — usadas na contagem "Quatro marcas". */
    public function scopeTopLevel(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }

    /** Lê um campo dos textos da página da marca (page_content), com fallback. */
    public function content(string $key, mixed $fallback = null): mixed
    {
        $value = data_get($this->page_content ?? [], $key);

        return ($value === null || $value === '' || $value === []) ? $fallback : $value;
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
        if ($this->image_path) {
            return self::resolveAssetUrl($this->image_path);
        }

        // Sem foto cadastrada no painel: usa a foto padrão versionada da marca, se existir
        // (public/images/brands/{slug}/foto.webp). Assim a foto aparece mesmo antes do migrate.
        if ($this->slug && is_file(public_path("images/brands/{$this->slug}/foto.webp"))) {
            return asset("images/brands/{$this->slug}/foto.webp");
        }

        return null;
    }

    /** Marcas cujo logo é exibido sempre nas cores originais (sem o filtro que deixa tudo branco). */
    public const LOGO_SEM_FILTRO = ['lbj', 'lbj-premium'];

    public function getLogoUrlAttribute(): ?string
    {
        // LBJ / LBJ Premium: enquanto o painel apontar para o logo antigo (ou nenhum),
        // usa o logo novo enviado pelo cliente.
        if (in_array($this->slug, self::LOGO_SEM_FILTRO, true)
            && (! $this->logo_path || str_ends_with($this->logo_path, '/logo-white.svg'))
            && is_file(public_path("images/brands/{$this->slug}/logo-original.svg"))) {
            return asset("images/brands/{$this->slug}/logo-original.svg");
        }

        return self::resolveAssetUrl($this->logo_path);
    }

    /**
     * Logos com texto claro recebem um filtro que os pinta de branco. LBJ, LBJ Premium
     * e arquivos com "logo-original" no nome ficam sempre nas cores originais
     * (branco com contorno preto), mesmo se trocarem o logo pelo painel.
     */
    public function getLogoInvertAttribute(): bool
    {
        return $this->text_theme === 'light'
            && ! in_array($this->slug, self::LOGO_SEM_FILTRO, true)
            && ! str_contains((string) $this->logo_path, 'logo-original');
    }

    /**
     * Resolve o caminho da imagem conforme a origem:
     *  - "images/..."  → asset fixo versionado em public/images (arte do site)
     *  - demais        → upload do admin, servido via storage/
     */
    public static function resolveAssetUrl(?string $path): ?string
    {
        if (! $path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return str_starts_with($path, 'images/')
            ? asset($path)
            : asset('storage/'.$path);
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
