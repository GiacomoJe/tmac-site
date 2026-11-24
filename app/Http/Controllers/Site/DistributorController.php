<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SeoMeta;

/**
 * "Seja distribuidor" — exibe o formulário do Lark DENTRO do site (iframe),
 * para o cliente não sair da TMAC. A URL do formulário continua editável em
 * Painel → Configurações → distributor_form_url.
 */
class DistributorController extends Controller
{
    public const DEFAULT_FORM_URL = 'https://tsgmrsomo1jj.sg.larksuite.com/share/base/form/shrlgNTTXRvcR6JaDOqBRJnrXDd';

    public function __invoke(SeoMeta $seo)
    {
        $seo->set(
            'Seja distribuidor — TMAC Import',
            'Cadastre sua empresa para revender as linhas TMAC, Corami, Atrox e LBJ. Atendimento exclusivo para CNPJ em todo o Brasil.',
            canonical: route('site.distributor'),
        );

        return view('site.distributor', [
            'formUrl' => Setting::get('distributor_form_url') ?: self::DEFAULT_FORM_URL,
        ]);
    }
}
