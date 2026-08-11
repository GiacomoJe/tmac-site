<?php

namespace Database\Seeders;

use App\Models\TmacBrand;
use Illuminate\Database\Seeder;

class TmacBrandsSeeder extends Seeder
{
    public function run(): void
    {
        // Corrige eventual slug antigo "l3j" (versão anterior do seed)
        TmacBrand::where('slug', 'l3j')->update(['slug' => 'lbj', 'name' => 'LBJ']);

        $items = [
            [
                'name'         => 'TMAC',
                'slug'         => 'tmac',
                'badge_label'  => 'Marca principal',
                'tagline'      => 'Criando novos caminhos',
                'description'  => 'A TMAC é a marca que representa a essência do grupo. Especializada na importação e distribuição de peças e acessórios para motocicletas, oferece um dos portfólios mais completos do mercado brasileiro, atendendo desde motocicletas de baixa cilindrada até modelos de maior desempenho. Com foco em qualidade, disponibilidade e inovação, a TMAC investe constantemente na ampliação do catálogo, desenvolvimento de novos produtos e fortalecimento da sua rede de distribuidores em todo o Brasil. Mais do que fornecer peças, a marca busca construir relacionamentos duradouros com seus parceiros, oferecendo confiança, suporte e soluções que movimentam o mercado duas rodas.',
                'brand_color'  => '#ED1C24',  // vermelho TMAC oficial (Pantone 2347 C)
                'text_theme'   => 'light',
                'accent_color' => 'accent',
                'logo_path'    => 'images/logo-tmac-white.svg',
                'sort_order'   => 5,          // primeira da lista
                // Não destacada na home: a home inteira já é da TMAC.
                // Aparece apenas na página /universo-tmac.
                'is_featured'  => false,
                'meta_title'   => 'TMAC — Importação e distribuição de peças para motocicleta',
                'meta_description' => 'TMAC: marca principal do grupo. Portfólio completo de peças e acessórios para motocicletas, da baixa cilindrada ao alto desempenho.',
            ],
            [
                'name'         => 'Corami',
                'slug'         => 'corami',
                'badge_label'  => 'Motor',
                'tagline'      => 'Excelência em baixa e média cilindrada',
                'description'  => 'Uma linha de peças para motor de excelência e qualidade de baixa e média cilindrada. Desenvolvida para os modelos mais populares do mercado brasileiro, com qualidade industrial e custo competitivo.',
                'brand_color'  => '#DA5F06',  // laranja Corami oficial (Pantone 2028 C)
                'text_theme'   => 'light',
                'accent_color' => 'accent',
                'logo_path'    => 'images/brands/corami/logo-white.svg',
                'sort_order'   => 10,
                'is_featured'  => true,
                'meta_title'   => 'Corami — Peças de motor TMAC',
                'meta_description' => 'Corami: linha de peças para motor de excelência. Qualidade industrial e preço competitivo para lojistas.',
            ],
            [
                'name'         => 'LBJ',
                'slug'         => 'lbj',
                'badge_label'  => 'Premium',
                'tagline'      => 'O melhor em cada detalhe',
                'description'  => 'Linha premium para quem busca o melhor em cada detalhe. Componentes diferenciados com acabamento superior e durabilidade reforçada, voltada para mercado de alta cilindrada e clientes exigentes.',
                'brand_color'  => '#00814F',  // verde LBJ oficial (Pantone 2422 C)
                'text_theme'   => 'light',
                'accent_color' => 'whatsapp',
                'logo_path'    => 'images/brands/lbj/logo-white.svg',
                'sort_order'   => 20,
                'is_featured'  => true,
                'meta_title'   => 'LBJ — Linha premium TMAC',
                'meta_description' => 'LBJ: linha premium TMAC. Componentes de alto padrão para motociclistas exigentes.',
            ],
            [
                'name'         => 'Atrox',
                'slug'         => 'atrox',
                'badge_label'  => 'Acessórios',
                'tagline'      => 'Design, inovação e qualidade',
                'description'  => 'Com os melhores acessórios que combinam design, inovação e qualidade. Linha em constante renovação para acompanhar as tendências do mercado e oferecer mais ao motociclista.',
                'brand_color'  => '#BD192B',  // vermelho Atrox oficial (Pantone 2035 C)
                'text_theme'   => 'light',
                'accent_color' => 'accent',
                'logo_path'    => 'images/brands/atrox/logo-white.svg',
                'sort_order'   => 30,
                'is_featured'  => true,
                'meta_title'   => 'Atrox — Acessórios para motocicleta',
                'meta_description' => 'Atrox: linha de acessórios TMAC. Design, inovação e qualidade em cada peça.',
            ],
            [
                'name'         => 'Motoled',
                'slug'         => 'motoled',
                'badge_label'  => 'Iluminação',
                'tagline'      => 'Ilumina o caminho com segurança e estilo',
                'description'  => 'A linha que ilumina o caminho com segurança e estilo. Lâmpadas, faróis, lanternas e iluminação automotiva em LED para todas as aplicações da motocicleta.',
                'brand_color'  => '#009ED0',  // azul Motoled oficial (Pantone 298 C)
                'text_theme'   => 'light',
                'accent_color' => 'signal',
                'logo_path'    => 'images/brands/motoled/logo-white.svg',
                'sort_order'   => 40,
                'is_featured'  => true,
                'meta_title'   => 'Motoled — Iluminação automotiva TMAC',
                'meta_description' => 'Motoled: lâmpadas, faróis e iluminação automotiva. Segurança e estilo para sua moto.',
            ],
        ];

        foreach ($items as $data) {
            TmacBrand::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true]),
            );
        }
    }
}
