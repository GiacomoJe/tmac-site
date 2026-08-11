<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Builder simples de meta tags por página.
 * Uso típico no controller:
 *   app(SeoMeta::class)->set($product->meta_title, $product->meta_description, ...);
 * E no layout Blade: {!! app(SeoMeta::class)->render() !!}
 */
class SeoMeta
{
    protected string $title = '';
    protected string $description = '';
    protected ?string $image = null;
    protected ?string $canonical = null;
    protected string $type = 'website';
    protected array $jsonLd = [];

    public function set(?string $title, ?string $description = null, ?string $image = null, ?string $canonical = null): static
    {
        $appName = config('app.name');
        $this->title = $title ? "$title — $appName" : $appName;
        $this->description = Str::limit(strip_tags((string) $description), 158);
        $this->image = $image;
        $this->canonical = $canonical;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function addJsonLd(array $data): static
    {
        $this->jsonLd[] = $data;
        return $this;
    }

    public function render(): string
    {
        $tags = [
            '<title>'.e($this->title).'</title>',
            '<meta name="description" content="'.e($this->description).'">',
            '<meta property="og:title" content="'.e($this->title).'">',
            '<meta property="og:description" content="'.e($this->description).'">',
            '<meta property="og:type" content="'.e($this->type).'">',
        ];

        if ($this->image) {
            $tags[] = '<meta property="og:image" content="'.e($this->image).'">';
            $tags[] = '<meta name="twitter:image" content="'.e($this->image).'">';
        }

        if ($this->canonical) {
            $tags[] = '<link rel="canonical" href="'.e($this->canonical).'">';
        }

        foreach ($this->jsonLd as $ld) {
            $tags[] = '<script type="application/ld+json">'.json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>';
        }

        return implode("\n", $tags);
    }
}
