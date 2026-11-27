<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajustes do cliente (27/nov/2026 — "site.docx"):
 *  - LBJ Premium: hero preto, selo "LBJ" (sai o "Submarca LBJ"), textos novos e logo próprio.
 *  - LBJ: logo novo (branco com contorno preto) e sem "premium" no selo e nos textos.
 * Tudo continua editável no painel (Universo TMAC → LBJ Premium → "Página da marca").
 */
return new class extends Migration
{
    public function up(): void
    {
        $pageContent = [
            'hero_title'    => 'LBJ PREMIUM: o padrão máximo em motopeças para quem exige excelência',
            'hero_subtitle' => 'Acabamento superior, resistência e durabilidade elevada para a sua loja atender os motociclistas mais exigentes do Brasil.',
            'hero_cta'      => 'Solicitar cotação da linha LBJ PREMIUM',
            'about_title'   => 'Qualidade elevada para quem vive sobre duas rodas',
            'about_text'    => "A LBJ PREMIUM reúne as peças que se destacam pelo nível de exigência na fabricação e no acabamento. É a escolha certa para o lojista e para a oficina que atendem clientes dispostos a investir em componentes de alta performance para o corre diário ou para a estrada.\n\nCada item passa por testes de qualidade rigorosos no Brasil antes de chegar ao seu balcão. O foco é entregar encaixe preciso e vida útil prolongada, fortalecendo a confiança de quem compra e de quem instala.",
            'families_title' => 'Peças selecionadas para o seu estoque',
            'families_intro' => 'Confira as principais famílias de produtos da linha LBJ PREMIUM disponíveis para cotação no atacado:',
            'families'      => ['Kit transmissão – em breve', 'Amortecedores', 'Patim de freio'],
            'diffs_title'   => 'Mais valor agregado para o seu negócio',
            'diffs_intro'   => 'Colocar a LBJ PREMIUM na prateleira posiciona a sua loja como referência em qualidade no setor de motopeças.',
            'diffs' => [
                ['title' => 'Acabamento diferenciado', 'text' => 'Peças com visual e encaixe precisos que chamam atenção logo no primeiro contato no balcão.'],
                ['title' => 'Durabilidade comprovada na prática', 'text' => 'Componentes pensados para aguentar o uso intenso, reduzindo retornos de garantia na oficina.'],
                ['title' => 'Fidelização do cliente final', 'text' => 'Quem encontra uma peça que resolve o problema de primeira volta sempre na sua loja.'],
            ],
            'cta_title'     => 'Leve a linha LBJ PREMIUM para o seu estoque',
            'cta_text'      => 'Atendemos exclusivamente empresas com CNPJ ativo em todos os 26 estados do Brasil, sem venda direta para o consumidor final. Aqui, a parceria é de verdade para você chegar mais longe com a gente.',
            'cta_primary'   => 'Solicitar cotação com CNPJ',
            'cta_secondary' => 'Seja um distribuidor autorizado',
        ];

        DB::table('tmac_brands')->where('slug', 'lbj-premium')->update([
            'badge_label'      => 'LBJ',
            'tagline'          => 'O padrão máximo em motopeças para quem exige excelência',
            'description'      => 'O padrão máximo em motopeças: acabamento superior, resistência e durabilidade elevada para os clientes mais exigentes.',
            'accent_color'     => 'ink',
            'brand_color'      => '#0A0A0A',   // preto (pedido do cliente)
            'text_theme'       => 'light',
            'page_content'     => json_encode($pageContent, JSON_UNESCAPED_UNICODE),
            'meta_title'       => 'LBJ PREMIUM: o padrão máximo em motopeças para quem exige excelência',
            'meta_description' => 'Acabamento superior, resistência e durabilidade elevada para a sua loja atender os motociclistas mais exigentes do Brasil. Atacado para CNPJ.',
            'logo_path'        => 'images/brands/lbj-premium/logo-original.svg',
            'updated_at'       => now(),
        ]);

        // LBJ: logo novo (branco com contorno preto) e sem "premium" nos textos —
        // agora "premium" é só a LBJ Premium, que tem página própria.
        $lbj = DB::table('tmac_brands')->where('slug', 'lbj')->first();
        if ($lbj) {
            $pc = json_decode($lbj->page_content ?? '[]', true) ?: [];
            $pc['hero_title'] = 'LBJ: qualidade superior para quem exige o máximo em duas rodas';
            $pc['cta_title']  = 'Leve a linha LBJ para o seu estoque';
            foreach ($pc['diffs'] ?? [] as $i => $d) {
                if (isset($d['text'])) {
                    $pc['diffs'][$i]['text'] = str_ireplace('padrão premium', 'padrão superior', $d['text']);
                }
            }

            DB::table('tmac_brands')->where('slug', 'lbj')->update([
                'logo_path'    => 'images/brands/lbj/logo-original.svg',
                'badge_label'  => 'Qualidade superior',
                'meta_title'   => 'LBJ: qualidade superior para quem exige o máximo em duas rodas',
                'page_content' => json_encode($pc, JSON_UNESCAPED_UNICODE),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Conteúdo editorial — sem rollback automático (ajuste pelo painel).
    }
};
