<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Textos novos enviados pelo cliente (nov/2026):
 *  - "UNIVERSO TMAC.docx"          → vitrine + páginas das marcas TMAC, Corami, Atrox e LBJ
 *  - "Abertura e história.docx"    → topo da página Quem Somos
 * Tudo continua editável no painel (Universo TMAC → "Página da marca"; Páginas → Quem somos).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $brands = [
            'tmac' => [
                'badge_label' => 'Giro e Reposição',
                'description' => 'A essência do grupo. Reúne a base do nosso catálogo de peças e acessórios para motocicletas, com importação direta e reposição constante para o dia a dia da sua loja.',
                'meta_title' => 'TMAC: a marca principal para abastecer sua loja inteira',
                'meta_description' => 'Mais de 4 mil SKUs em estoque com importação direta, entrega para os 26 estados e atendimento exclusivo para quem tem CNPJ ativo.',
                'page_content' => [
                    'hero_title'    => 'TMAC: a marca principal para abastecer sua loja inteira',
                    'hero_subtitle' => 'Mais de 4 mil SKUs em estoque com importação direta, entrega para os 26 estados e atendimento exclusivo para quem tem CNPJ ativo.',
                    'hero_cta'      => 'Solicitar cotação atacadista',
                    'about_title'   => 'Um só parceiro para o seu balcão não parar',
                    'about_text'    => "A TMAC representa a essência do grupo. Nascemos para resolver o corre diário de lojistas, oficinas e distribuidores que precisam de variedade real na prateleira e reposição rápida.\n\nCom mais de 4 mil SKUs no catálogo, você concentra seus pedidos em um único lugar. Hoje, mais de 3,2 mil lojistas ativos em 26 estados confiam na nossa operação para manter o giro alto e atender quem vive sobre duas rodas.",
                    'families_title' => 'Peças de alto giro para o seu estoque',
                    'families_intro' => 'Acesse as principais famílias da marca no catálogo digital e monte sua cotação atacadista:',
                    'families'      => ['Carburador', 'Chave de luz', 'Lanterna traseira', 'Farol completo', 'Retrovisores', 'Aro de roda', 'Cabos', 'Estator'],
                    'diffs_title'   => 'Estrutura de ponta a ponta para você vender mais',
                    'diffs' => [
                        ['title' => 'Importação direta com escritório na China', 'text' => 'Acompanhamos a fabricação de perto, com escritório próprio na China e contato 24 horas com a origem para garantir regularidade no seu abastecimento.'],
                        ['title' => 'Testes de qualidade no Brasil', 'text' => 'Cada peça passa por validação técnica antes de chegar ao seu estoque. É durabilidade e resistência comprovadas para evitar dor de cabeça no pós-venda.'],
                        ['title' => 'Tabela escalonada por volume', 'text' => 'Condições comerciais pensadas para o atacado. Quanto maior o seu pedido, melhor a sua margem na hora de compor o preço no balcão.'],
                        ['title' => 'Atendimento humano e direto', 'text' => 'Aqui você fala de igual para igual com gente que entende de motopeças e conhece a realidade da sua loja e da sua oficina.'],
                    ],
                    'cta_title'     => 'Atendimento exclusivo para CNPJ ativo',
                    'cta_text'      => 'Trabalhamos lado a lado com o revendedor e nunca furamos a cadeia de clientes. Monte sua cotação em minutos e receba sua tabela personalizada para todo o Brasil.',
                    'cta_primary'   => 'Solicitar cotação agora',
                    'cta_secondary' => 'Seja distribuidor',
                ],
            ],
            'corami' => [
                'badge_label' => 'Motor',
                'description' => 'Linha focada no segmento de motor, desenvolvida para atender motociclistas e mecânicos que precisam de componentes para motor com peças técnicas de alta confiabilidade.',
                'meta_title' => 'CORAMI: precisão técnica para o coração da moto',
                'meta_description' => 'Componentes internos de motor e peças técnicas com durabilidade na oficina e giro rápido no balcão. Atacado para CNPJ.',
                'page_content' => [
                    'hero_title'    => 'CORAMI: precisão técnica para o coração da moto.',
                    'hero_subtitle' => 'A linha de componentes internos de motor e peças técnicas feita para quem vive sobre duas rodas e não abre mão de durabilidade na oficina e giro rápido no balcão.',
                    'hero_cta'      => 'Solicitar cotação da linha CORAMI',
                    'about_title'   => 'Destaque no segmento de moto, peça por peça.',
                    'about_text'    => "Quando o assunto é motor, não existe espaço para dúvida. A CORAMI nasceu dentro do universo TMAC com um objetivo claro: entregar componentes internos de motor e peças técnicas que aguentam o tranco do uso diário.\n\nCada item da linha passa por controle rigoroso antes de chegar à sua loja. Isso garante resistência mecânica, encaixe preciso e performance constante para motocicletas que rodam no trabalho ou no lazer.",
                    'families_title' => 'Principais famílias de peças para o seu estoque.',
                    'families_intro' => 'Confira os componentes internos de motor e peças técnicas disponíveis no catálogo digital para abastecer sua loja ou oficina:',
                    'families'      => ['Virabrequim', 'Bielas', 'Câmbio completo', 'Corrente de comando', 'Eixo pinhão', 'Kit pistão', 'Válvulas'],
                    'diffs_title'   => 'Por que ter CORAMI na sua prateleira.',
                    'diffs' => [
                        ['title' => 'Confiabilidade na montagem', 'text' => 'Peças técnicas projetadas para o mecânico trabalhar com segurança, reduzindo retrabalhos e garantindo a satisfação de quem pilota.'],
                        ['title' => 'Giro constante de estoque', 'text' => 'Uma linha focada em itens de reposição essenciais para o segmento de moto, ajudando sua loja a manter o balcão sempre abastecido.'],
                        ['title' => 'Suporte comercial direto', 'text' => 'Atendimento feito por quem entende de duas rodas, com condições exclusivas para lojistas, oficinas e distribuidores com CNPJ ativo.'],
                    ],
                    'cta_title'     => 'Abasteça sua loja com a linha CORAMI.',
                    'cta_text'      => 'Atendemos exclusivamente no atacado B2B para empresas com CNPJ ativo em todo o Brasil. Peça sua tabela personalizada e leve mais confiabilidade para os seus clientes.',
                    'cta_primary'   => 'Solicitar cotação agora',
                    'cta_secondary' => 'Quero ser distribuidor TMAC',
                ],
            ],
            'atrox' => [
                'badge_label' => 'Acessórios',
                'description' => 'Visual esportivo aliado à resistência. A marca reúne acessórios de alto desempenho, com destaque especial para a linha de bauletos ATROX.',
                'meta_title' => 'ATROX: visual esportivo, resistência e alto desempenho',
                'meta_description' => 'Acessórios ATROX para motociclistas que exigem estilo e durabilidade, com destaque para os bauletos. Atacado para CNPJ.',
                'page_content' => [
                    'hero_title'    => 'ATROX: visual esportivo, resistência e alto desempenho para o seu balcão',
                    'hero_subtitle' => 'A linha criada para atender motociclistas que exigem estilo e durabilidade no uso intenso, como os bauletos ATROX puxando o giro da sua loja.',
                    'hero_cta'      => 'Solicitar cotação da linha ATROX',
                    'about_title'   => 'Feita para quem não foge do corre e exige performance na estrada',
                    'about_text'    => "A ATROX nasceu para atender um público que busca um visual esportivo aliado à resistência e ao alto desempenho. Cada item da linha é pensado para aguentar a rotina pesada de quem roda todos os dias sobre duas rodas, seja no trabalho ou na estrada.\n\nPara o lojista, isso significa colocar na prateleira acessórios e peças com apelo visual forte, que chamam a atenção logo na entrada da loja e entregam a confiança que o cliente final procura.",
                    'families_title' => 'O que você encontra na linha ATROX',
                    'families_intro' => 'Confira as principais famílias de produtos ATROX disponíveis no catálogo digital para abastecer a sua loja:',
                    'families'      => ['Bauletos', 'Antena corta pipa', 'Manete esportivo', 'Pedal de câmbio esportivo', 'Manoplas', 'Retrovisores', 'Roda de liga leve'],
                    'highlight_title' => 'Bauletos ATROX: o grande destaque de giro no ponto de venda',
                    'highlight_text'  => 'Os bauletos ATROX são o carro-chefe da marca e um dos itens mais procurados por quem vive de moto. Quem trabalha com entregas ou pega a estrada precisa de praticidade e resistência, e é exatamente aí que o bauleto vira venda certa no balcão.',
                    'diffs_title'   => 'Por que ter ATROX na sua loja',
                    'diffs' => [
                        ['title' => 'Protagonismo dos bauletos', 'text' => 'Produto com procura constante o ano inteiro, ideal para manter o estoque girando rápido.'],
                        ['title' => 'Apelo visual esportivo', 'text' => 'Design que se destaca na vitrine e atrai o olhar do motociclista moderno.'],
                        ['title' => 'Resistência para o dia a dia', 'text' => 'Linha desenvolvida para suportar o uso contínuo sem perder o desempenho.'],
                        ['title' => 'Margem e saída no atacado', 'text' => 'Condições comerciais pensadas para fortalecer o caixa de lojistas, oficinas e distribuidores.'],
                    ],
                    'cta_title'     => 'Abasteça sua loja com a linha ATROX',
                    'cta_text'      => 'Atendimento exclusivo para empresas com CNPJ ativo em todo o Brasil. Monte seu pedido com condições especiais por volume e leve os bauletos e acessórios ATROX para o seu balcão.',
                    'cta_primary'   => 'Solicitar cotação agora',
                    'cta_secondary' => 'Quero ser distribuidor',
                ],
            ],
            'lbj' => [
                'badge_label' => 'Linha Premium',
                'description' => 'Desenvolvida para clientes que exigem acabamento superior, máxima qualidade e maior durabilidade em cada peça.',
                'meta_title' => 'LBJ: a linha premium para quem exige o máximo em duas rodas',
                'meta_description' => 'Máxima qualidade, acabamento superior e maior durabilidade para a sua loja atender os motociclistas mais exigentes do Brasil.',
                'page_content' => [
                    'hero_title'    => 'LBJ: a linha premium para quem exige o máximo em duas rodas',
                    'hero_subtitle' => 'Máxima qualidade, acabamento superior e maior durabilidade para a sua loja atender os motociclistas mais exigentes do Brasil.',
                    'hero_cta'      => 'Solicitar cotação da linha LBJ',
                    'about_title'   => 'Padrão elevado em cada detalhe',
                    'about_text'    => "A LBJ nasceu dentro do Universo TMAC com um propósito claro: entregar peças e componentes com acabamento superior para quem não abre mão de confiança na estrada e no corre diário.\n\nEnquanto o mercado comum disputa apenas preço, a LBJ entrega resistência real e durabilidade prolongada. Cada item da linha passa por controle de qualidade rigoroso antes de chegar ao seu estoque, garantindo que o produto entregue exatamente o que o motociclista espera.\n\nDentro desse universo, surgiu a LBJ PREMIUM, a submarca criada para as peças da LBJ que se destacaram na qualidade, voltada para quem exige cada vez mais na estrada e no balcão.",
                    'families_title' => 'Peças com padrão superior para o seu estoque',
                    'families_intro' => 'Confira as principais famílias de produtos da linha LBJ disponíveis para cotação no atacado:',
                    'families'      => ['Pedal de freio', 'Pedal de câmbio', 'Motor de partida', 'Patim de freio', 'Bico injetor'],
                    'diffs_title'   => 'Mais valor agregado para o seu negócio',
                    'diffs' => [
                        ['title' => 'Acabamento superior', 'text' => 'Peças com visual e encaixe diferenciados que saltam aos olhos já no balcão.'],
                        ['title' => 'Maior durabilidade', 'text' => 'Menos retorno de garantia e mais confiança de quem roda todo dia.'],
                        ['title' => 'Reputação fortalecida', 'text' => 'Quem busca padrão premium volta na loja e na oficina que resolveu o problema de primeira.'],
                        ['title' => 'Valor agregado na venda', 'text' => 'Uma linha pensada para lojistas e distribuidores que querem trabalhar com margem diferenciada.'],
                    ],
                    'cta_title'     => 'Leve a linha premium LBJ para o seu estoque',
                    'cta_text'      => 'Atendemos exclusivamente empresas com CNPJ ativo em todos os 26 estados do Brasil, sem venda direta para o consumidor final. Aqui, a parceria é de verdade para você crescer junto com a gente.',
                    'cta_primary'   => 'Solicitar cotação com CNPJ',
                    'cta_secondary' => 'Seja um distribuidor autorizado',
                ],
            ],
        ];

        foreach ($brands as $slug => $data) {
            $data['page_content'] = json_encode($data['page_content'], JSON_UNESCAPED_UNICODE);
            $data['updated_at'] = $now;
            DB::table('tmac_brands')->where('slug', $slug)->update($data);
        }

        // LBJ Premium passa a ser submarca da LBJ (não conta no "Quatro marcas")
        $lbjId = DB::table('tmac_brands')->where('slug', 'lbj')->value('id');
        if ($lbjId) {
            DB::table('tmac_brands')->where('slug', 'lbj-premium')->update([
                'parent_id'   => $lbjId,
                'badge_label' => 'Submarca LBJ',
                'tagline'     => 'As peças da LBJ que se destacaram na qualidade',
                'description' => 'A submarca criada para as peças da LBJ que se destacaram na qualidade, voltada para quem exige cada vez mais na estrada e no balcão.',
                'updated_at'  => $now,
            ]);
        }

        // Quem Somos — topo da página (substitui os textos de teste)
        DB::table('pages')->where('slug', 'quem-somos')->update([
            'hero_eyebrow'     => 'Quem somos',
            'hero_title'       => 'Mais que uma marca, somos parceiros de verdade.',
            'hero_highlight'   => 'parceiros de verdade',
            'hero_description' => 'A TMAC nasceu em 2016, em São Paulo, com um objetivo claro: caminhar lado a lado com quem vive do mercado de duas rodas no Brasil. Em 2026, completamos 10 anos de estrada como uma das maiores importadoras e distribuidoras de motopeças do país.',
            'seo_title'        => 'Quem somos — TMAC Import, 10 anos de motopeças no atacado',
            'seo_description'  => 'Desde 2016 em São Paulo, a TMAC importa e distribui motopeças exclusivamente para CNPJ: lojistas, oficinas e distribuidores em 26 estados.',
            'updated_at'       => $now,
        ]);
    }

    public function down(): void
    {
        // Conteúdo editorial — sem rollback automático (ajuste pelo painel).
    }
};
