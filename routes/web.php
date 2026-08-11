<?php

use App\Http\Controllers\Site\BrandController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\MotorcyclePartsController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\QuoteCartController;
use App\Http\Controllers\Site\QuoteController;
use App\Http\Controllers\Site\RepresentativeController;
use App\Http\Controllers\Site\TmacBrandController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('site.home');

Route::get('/produtos', [ProductController::class, 'index'])->name('site.products');
Route::get('/produto/{product:slug}', [ProductController::class, 'show'])->name('site.product');

Route::get('/marcas', [BrandController::class, 'index'])->name('site.brands');
Route::get('/marca/{brand:slug}', [BrandController::class, 'show'])->name('site.brand');

Route::get('/universo-tmac', [TmacBrandController::class, 'index'])->name('site.tmac-brands');
Route::get('/universo-tmac/{tmacBrand:slug}', [TmacBrandController::class, 'show'])->name('site.tmac-brand');

Route::get('/representantes', [RepresentativeController::class, 'index'])->name('site.representatives');

// Compatibilidade peça → moto (filtros por marca/modelo/ano)
Route::get('/pecas/{marca}',                  [MotorcyclePartsController::class, 'byMake'])->name('site.parts.make');
Route::get('/pecas/{marca}/{modelo}',         [MotorcyclePartsController::class, 'byModel'])->name('site.parts.model');
Route::get('/pecas/{marca}/{modelo}/{ano}',   [MotorcyclePartsController::class, 'byYear'])->name('site.parts.year')->whereNumber('ano');

Route::get('/cotacao', [QuoteController::class, 'show'])->name('site.quote');

// Endpoints AJAX do carrinho de cotação (CSRF padrão)
Route::post('/cotacao/cart/add',    [QuoteCartController::class, 'add'])->name('site.cart.add');
Route::post('/cotacao/cart/update', [QuoteCartController::class, 'update'])->name('site.cart.update');
Route::post('/cotacao/cart/remove', [QuoteCartController::class, 'remove'])->name('site.cart.remove');

Route::get('/p/{page:slug}', [PageController::class, 'show'])->name('site.page');
