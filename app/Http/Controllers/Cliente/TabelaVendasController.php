<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Services\SeoMeta;

class TabelaVendasController extends Controller
{
    public function show(SeoMeta $seo)
    {
        $seo->set('Tabela de Vendas', 'Consulte preços por estado e monte seu pedido.');

        return view('cliente.tabela');
    }
}
