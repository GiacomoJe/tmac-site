<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ajustes de dados pedidos pelo cliente (doc "Ajuste - site", nov/2026).
 * Tudo idempotente: pode rodar mais de uma vez sem duplicar nada.
 * Depois de rodar, os mesmos dados continuam editáveis no painel.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // 1) Contatos oficiais (página Contato, botões de WhatsApp do site)
        $settings = [
            ['contact_phone',      '+55 11 98904-1153', 'contact', 'Telefone principal'],
            ['whatsapp_number',    '5511947185865',     'contact', 'WhatsApp (E.164)'],
            ['whatsapp_marketing', '5511947185865',     'contact', 'WhatsApp do marketing (página Representantes)'],
            ['contact_email',      'sac@tmacimport.com.br', 'contact', 'E-mail de contato'],
            ['address',            'Alameda Santos, 771 - Jardim Paulista, São Paulo - SP, 01419-001', 'contact', 'Endereço'],
        ];
        foreach ($settings as [$key, $value, $group, $label]) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'label' => $label, 'updated_at' => $now, 'created_at' => $now],
            );
            Cache::forget("setting.$key");
        }

        // 2) Página Contato: remove o texto genérico "Entre em contato com nosso time comercial."
        DB::table('pages')->where('slug', 'contato')
            ->where('content', 'like', '%Entre em contato com nosso time comercial.%')
            ->update(['content' => null, 'updated_at' => $now]);

        // 3) Representante de testes (Rafael) sai do site — fica inativo no painel (pode excluir por lá)
        DB::table('representatives')
            ->where(fn ($q) => $q->where('email', 'rafaelmaia@tmac.com')->orWhere('name', 'like', 'Rafael%'))
            ->update(['is_active' => false, 'updated_at' => $now]);

        // 4) Nova linha "LBJ Premium" no Universo TMAC (textos/cor/logo ajustáveis no painel)
        if (! DB::table('tmac_brands')->where('slug', 'lbj-premium')->exists()) {
            $lbjOrder = (int) (DB::table('tmac_brands')->where('slug', 'lbj')->value('sort_order') ?? 20);

            DB::table('tmac_brands')->insert([
                'name'         => 'LBJ Premium',
                'slug'         => 'lbj-premium',
                'badge_label'  => 'Premium',
                'tagline'      => 'O topo da linha LBJ',
                'description'  => 'A versão premium da LBJ: produtos selecionados, com acabamento superior e durabilidade reforçada, para clientes que buscam o máximo em qualidade.',
                'logo_path'    => null,          // sem logo: o card mostra o nome "LBJ Premium"
                'accent_color' => 'whatsapp',
                'brand_color'  => '#0F3B2A',     // verde LBJ escurecido — trocar no painel se houver cor oficial
                'text_theme'   => 'light',
                'sort_order'   => $lbjOrder + 1,
                'is_active'    => true,
                'is_featured'  => true,
                'meta_title'   => 'LBJ Premium — Universo TMAC',
                'meta_description' => 'LBJ Premium: produtos selecionados com acabamento superior e durabilidade reforçada.',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // 5) Categoria "Iluminação". O produto pode ficar em mais de uma categoria:
        //    os itens continuam na categoria de origem (ex.: Chassi) e passam a aparecer também aqui.
        $catId = DB::table('categories')->where('slug', 'iluminacao')->value('id');
        if (! $catId) {
            $order = (int) (DB::table('categories')->where('slug', 'injecao')->value('sort_order') ?? 0);
            $catId = DB::table('categories')->insertGetId([
                'name'        => 'ILUMINAÇÃO',
                'slug'        => 'iluminacao',
                'description' => 'Blocos ópticos, faróis, lanternas, piscas e lâmpadas.',
                'sort_order'  => $order,
                'is_active'   => true,
                'seo_title'   => 'Iluminação para motos — TMAC Import',
                'seo_description' => 'Blocos ópticos, faróis, lanternas, piscas e lâmpadas para motocicletas. Solicite sua cotação.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $prefixes = ['BLOCO ÓPTICO', 'BLOCO OPTICO', 'BLOCO ÓTICO', 'BLOCO OTICO', 'FAROL', 'LANTERNA', 'LÂMPADA', 'LAMPADA', 'PISCA'];
        $productIds = DB::table('products')
            ->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $p) {
                    $q->orWhere('name', 'like', $p.'%');
                }
            })
            ->pluck('id');

        foreach ($productIds as $pid) {
            DB::table('category_product')->insertOrIgnore([
                'category_id' => $catId,
                'product_id'  => $pid,
                'sort_order'  => 0,
            ]);
        }
    }

    public function down(): void
    {
        // Ajustes de conteúdo — sem rollback automático (revertidos pelo painel, se necessário).
    }
};
