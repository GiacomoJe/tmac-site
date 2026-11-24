<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\State;
use App\Models\Testimonial;
use App\Models\TmacBrand;
use App\Services\SeoMeta;

class HomeController extends Controller
{
    public function __invoke(SeoMeta $seo)
    {
        $seo->set(
            'TMAC Import — Produtos importados de qualidade',
            'Importadora com atendimento por representantes em todo o Brasil. Solicite sua cotação online.',
            canonical: route('site.home'),
        );

        // Destaques: os marcados como "destaque" no painel (até 4). Se houver menos
        // de 4 marcados, completa com produtos ativos que tenham foto, para a vitrine
        // nunca ficar com um card só.
        $featuredProducts = Product::with(['brand', 'motorcycleModels'])->active()->featured()
            ->orderBy('sort_order')->take(4)->get();

        if ($featuredProducts->count() < 4) {
            $featuredProducts = $featuredProducts->concat(
                Product::with(['brand', 'motorcycleModels'])->active()
                    ->whereNotNull('main_image')->where('main_image', '!=', '')
                    ->whereNotIn('id', $featuredProducts->pluck('id'))
                    ->latest('id')
                    ->take(4 - $featuredProducts->count())
                    ->get()
            );
        }

        return view('site.home', [
            'banners' => Banner::active()->position('home_top')->orderBy('sort_order')->get(),
            'featuredCategories' => Category::active()->ordered()->take(8)->get(),
            'featuredBrands' => Brand::active()->ordered()->take(12)->get(),
            'tmacBrands' => TmacBrand::active()->featured()->ordered()->take(6)->get(),
            'testimonials' => Testimonial::active()->ordered()->take(3)->get(),
            'featuredProducts' => $featuredProducts,
            'states' => State::orderBy('uf')->get(['id','uf','name']),
        ]);
    }
}
