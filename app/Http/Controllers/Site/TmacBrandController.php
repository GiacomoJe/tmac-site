<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\TmacBrand;
use App\Services\SeoMeta;

class TmacBrandController extends Controller
{
    public function index(SeoMeta $seo)
    {
        $seo->set(
            'Universo TMAC — Nossas marcas próprias',
            'Quatro marcas próprias — TMAC, Corami, Atrox e LBJ — com mais de 4 mil SKUs em estoque para lojistas, oficinas e distribuidores com CNPJ ativo.',
            canonical: route('site.tmac-brands'),
        );

        return view('site.tmac-brands', [
            // Vitrine: só marcas principais; submarcas (LBJ Premium) aparecem dentro da marca-mãe.
            'brands' => TmacBrand::active()->topLevel()->ordered()
                ->with(['children' => fn ($q) => $q->active()->ordered()])
                ->get(),
        ]);
    }

    public function show(TmacBrand $tmacBrand, SeoMeta $seo)
    {
        abort_unless($tmacBrand->is_active, 404);

        $seo->set(
            $tmacBrand->meta_title ?: "{$tmacBrand->name} — Universo TMAC",
            $tmacBrand->meta_description ?: $tmacBrand->tagline,
            canonical: route('site.tmac-brand', $tmacBrand->slug),
        );

        return view('site.tmac-brand', [
            'brand'  => $tmacBrand,
            'others' => TmacBrand::active()->ordered()->where('id', '!=', $tmacBrand->id)->get(),
            'subBrands' => $tmacBrand->children()->active()->ordered()->get(),
            'products' => \App\Models\Product::with(['brand', 'motorcycleModels'])
                ->active()
                ->where('tmac_brand_id', $tmacBrand->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->take(8)
                ->get(),
            'productsCount' => \App\Models\Product::active()
                ->where('tmac_brand_id', $tmacBrand->id)
                ->count(),
        ]);
    }
}
