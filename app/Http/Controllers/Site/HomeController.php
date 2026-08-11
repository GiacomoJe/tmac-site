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

        return view('site.home', [
            'banners' => Banner::active()->position('home_top')->orderBy('sort_order')->get(),
            'featuredCategories' => Category::active()->ordered()->take(8)->get(),
            'featuredBrands' => Brand::active()->ordered()->take(12)->get(),
            'tmacBrands' => TmacBrand::active()->featured()->ordered()->take(4)->get(),
            'testimonials' => Testimonial::active()->ordered()->take(3)->get(),
            'featuredProducts' => Product::with('brand')->active()->featured()->orderBy('sort_order')->take(8)->get(),
            'states' => State::orderBy('uf')->get(['id','uf','name']),
        ]);
    }
}
