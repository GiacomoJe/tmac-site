<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Livewire\Site\QuoteCart;
use App\Services\SeoMeta;

class QuoteController extends Controller
{
    public function show(SeoMeta $seo)
    {
        $seo->set('Solicitar Cotação', 'Revise os produtos selecionados e envie sua solicitação de cotação.');

        return view('site.quote', [
            'items' => QuoteCart::items(),
            'products' => QuoteCart::products(),
        ]);
    }
}
