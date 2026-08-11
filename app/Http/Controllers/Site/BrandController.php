<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\SeoMeta;

class BrandController extends Controller
{
    public function index(SeoMeta $seo)
    {
        $seo->set('Marcas — TMAC Import', 'Conheça todas as marcas importadas e distribuídas pela TMAC.');
        return view('site.brands', ['brands' => Brand::active()->ordered()->get()]);
    }

    public function show(Brand $brand, SeoMeta $seo)
    {
        abort_unless($brand->is_active, 404);

        $seo->set($brand->meta_title, $brand->meta_description, canonical: route('site.brand', $brand->slug));

        $products = $brand->products()->active()->orderBy('sort_order')->paginate(24);

        return view('site.brand', compact('brand', 'products'));
    }
}
