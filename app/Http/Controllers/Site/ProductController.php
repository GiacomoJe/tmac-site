<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MotorcycleMake;
use App\Models\MotorcycleModel;
use App\Models\Product;
use App\Models\TmacBrand;
use App\Services\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    public function index(Request $request, SeoMeta $seo)
    {
        $q = $request->string('q')->toString();

        // ── Categorias selecionadas (multi) — aceita ?cat[]=motor&cat[]=freios
        //    e também o legado ?category=motor (single)
        $catSlugs = collect(Arr::wrap($request->input('cat', [])))
            ->filter()
            ->map(fn ($s) => trim((string) $s))
            ->values();

        if ($request->filled('category') && $catSlugs->isEmpty()) {
            $catSlugs = collect([$request->string('category')->toString()]);
        }

        // ── Marcas selecionadas (multi) — ?brand[]=cobreq&brand[]=tmac
        //    e também o legado ?brand=cobreq (single)
        $brandSlugs = collect(Arr::wrap($request->input('brand', [])))
            ->filter()
            ->map(fn ($s) => trim((string) $s))
            ->values();

        // ── Universo TMAC selecionado (multi) — ?tmac[]=corami&tmac[]=lbj
        //    e também o legado ?tmac=corami (single)
        $tmacSlugs = collect(Arr::wrap($request->input('tmac', [])))
            ->filter()
            ->map(fn ($s) => trim((string) $s))
            ->values();

        // ── Compatibilidade com moto — ?moto=honda&modelo=cg-150&ano=2015
        $makeSlug  = $request->string('moto')->toString();
        $modelSlug = $request->string('modelo')->toString();
        $year      = $request->integer('ano') ?: null;

        $products = Product::with(['brand', 'tmacBrand'])
            ->active()
            ->search($q ?: null)
            ->when($brandSlugs->isNotEmpty(),
                fn ($qq) => $qq->whereHas('brand', fn ($b) => $b->whereIn('slug', $brandSlugs))
            )
            ->when($tmacSlugs->isNotEmpty(),
                fn ($qq) => $qq->whereHas('tmacBrand', fn ($t) => $t->whereIn('slug', $tmacSlugs))
            )
            ->when($catSlugs->isNotEmpty(),
                fn ($qq) => $qq->whereHas('categories', fn ($c) => $c->whereIn('slug', $catSlugs))
            )
            // Filtro por compatibilidade de moto
            ->when($makeSlug, function ($qq) use ($makeSlug, $modelSlug, $year) {
                $qq->whereHas('fitments', function ($f) use ($makeSlug, $modelSlug, $year) {
                    $f->whereHas('model', function ($m) use ($makeSlug, $modelSlug) {
                        $m->whereHas('make', fn ($mk) => $mk->where('slug', $makeSlug));
                        if ($modelSlug) {
                            $m->where('slug', $modelSlug);
                        }
                    });

                    if ($year) {
                        $f->where(fn ($y) => $y->whereNull('year_from')->orWhere('year_from', '<=', $year))
                          ->where(fn ($y) => $y->whereNull('year_to')->orWhere('year_to', '>=', $year));
                    }
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        // Árvore: categorias raiz com subcategorias ativas
        $categoryTree = Category::with('activeChildren')
            ->root()
            ->active()
            ->ordered()
            ->get();

        $seo->set(
            $q ? "Resultados para \"$q\"" : 'Produtos',
            'Catálogo completo de produtos TMAC Import. Filtre por marca, categoria ou busque por SKU.',
            canonical: route('site.products'),
        );

        // Dados para o seletor de moto
        $makes = MotorcycleMake::active()->ordered()->get(['id', 'name', 'slug']);

        $models = $makeSlug
            ? MotorcycleModel::active()
                ->whereHas('make', fn ($m) => $m->where('slug', $makeSlug))
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'year_start', 'year_end'])
            : collect();

        $years = collect();
        if ($modelSlug && ($model = $models->firstWhere('slug', $modelSlug))) {
            $start = $model->year_start ?: 2000;
            $end   = $model->year_end ?: (int) date('Y');
            $years = collect(range($end, $start));
        }

        return view('site.products', [
            'products'      => $products,
            'brands'        => Brand::active()->ordered()->get(),
            'tmacBrands'    => TmacBrand::active()->ordered()->get(),
            'categoryTree'  => $categoryTree,
            'q'             => $q,
            'selectedCats'  => $catSlugs->all(),
            'selectedBrands'=> $brandSlugs->all(),
            'selectedTmac'  => $tmacSlugs->all(),
            // Moto
            'makes'         => $makes,
            'models'        => $models,
            'years'         => $years,
            'selectedMake'  => $makeSlug ?: null,
            'selectedModel' => $modelSlug ?: null,
            'selectedYear'  => $year,
        ]);
    }

    public function show(Product $product, SeoMeta $seo)
    {
        abort_unless($product->is_active, 404);
        $product->load(['brand', 'categories', 'images', 'fitments.model.make']);

        $seo->set(
            $product->meta_title,
            $product->meta_description,
            image: $product->main_image ? asset('storage/'.$product->main_image) : null,
            canonical: route('site.product', $product->slug),
        )->setType('product');

        $seo->addJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => strip_tags((string) $product->short_description),
            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
            'image' => $product->main_image ? asset('storage/'.$product->main_image) : null,
            'url' => route('site.product', $product->slug),
        ]);

        $related = Product::with('brand')->active()
            ->whereKeyNot($product->id)
            ->when($product->categories->isNotEmpty(),
                fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $product->categories->pluck('id'))))
            ->take(4)
            ->get();

        return view('site.product', compact('product', 'related'));
    }
}
