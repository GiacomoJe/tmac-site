<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MotorcycleMake;
use App\Models\MotorcycleModel;
use App\Models\Product;
use App\Services\SeoMeta;
use Illuminate\Http\Request;

class MotorcyclePartsController extends Controller
{
    public function byMake(string $marca, Request $request, SeoMeta $seo)
    {
        $make = MotorcycleMake::active()->where('slug', $marca)->firstOrFail();

        $models = $make->models()->active()->orderBy('name')->get();

        // Lista produtos com qualquer fitment dos modelos dessa marca
        $modelIds = $models->pluck('id');

        $products = Product::with('brand')->active()
            ->whereHas('fitments', fn ($q) => $q->whereIn('motorcycle_model_id', $modelIds))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(24)->withQueryString();

        $seo->set(
            "Peças para motos {$make->name}",
            "Peças de moto compatíveis com toda a linha {$make->name}. Cotação direta para lojistas, importação direta.",
            canonical: route('site.parts.make', ['marca' => $make->slug]),
        );

        return view('site.motorcycle-parts', [
            'make' => $make,
            'model' => null,
            'year' => null,
            'models' => $models,
            'products' => $products,
            'brands' => Brand::active()->ordered()->get(),
            'categories' => Category::active()->ordered()->get(),
        ]);
    }

    public function byModel(string $marca, string $modelo, Request $request, SeoMeta $seo)
    {
        $make = MotorcycleMake::active()->where('slug', $marca)->firstOrFail();
        $model = MotorcycleModel::active()
            ->where('motorcycle_make_id', $make->id)
            ->where('slug', $modelo)
            ->firstOrFail();

        $products = Product::with('brand')->active()
            ->forMotorcycle($model->id)
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(24)->withQueryString();

        $seo->set(
            "Peças para {$make->name} {$model->name}",
            "Catálogo de peças compatíveis com {$make->name} {$model->name}. ".
            "Atendimento exclusivo CNPJ.",
            canonical: route('site.parts.model', ['marca' => $make->slug, 'modelo' => $model->slug]),
        );

        return view('site.motorcycle-parts', [
            'make' => $make,
            'model' => $model,
            'year' => null,
            'models' => $make->models()->active()->orderBy('name')->get(),
            'products' => $products,
            'brands' => Brand::active()->ordered()->get(),
            'categories' => Category::active()->ordered()->get(),
        ]);
    }

    public function byYear(string $marca, string $modelo, int $ano, Request $request, SeoMeta $seo)
    {
        $make = MotorcycleMake::active()->where('slug', $marca)->firstOrFail();
        $model = MotorcycleModel::active()
            ->where('motorcycle_make_id', $make->id)
            ->where('slug', $modelo)
            ->firstOrFail();

        abort_if($ano < 1950 || $ano > (int) date('Y') + 1, 404);

        $products = Product::with('brand')->active()
            ->forMotorcycle($model->id, $ano)
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(24)->withQueryString();

        $seo->set(
            "Peças para {$make->name} {$model->name} {$ano}",
            "Encontre peças compatíveis com {$make->name} {$model->name} {$ano}. ".
            "Importação direta, atendimento CNPJ.",
            canonical: route('site.parts.year', [
                'marca' => $make->slug, 'modelo' => $model->slug, 'ano' => $ano,
            ]),
        );

        return view('site.motorcycle-parts', [
            'make' => $make,
            'model' => $model,
            'year' => $ano,
            'models' => $make->models()->active()->orderBy('name')->get(),
            'products' => $products,
            'brands' => Brand::active()->ordered()->get(),
            'categories' => Category::active()->ordered()->get(),
        ]);
    }
}
