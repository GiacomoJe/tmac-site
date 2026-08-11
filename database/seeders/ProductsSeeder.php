<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MotorcycleModel;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    /**
     * Atalho: pega o ID de um modelo de moto pelo slug.
     * Se não achar, retorna null e o fitment é pulado.
     */
    protected function model(string $slug): ?int
    {
        return MotorcycleModel::where('slug', $slug)->value('id');
    }

    /**
     * Preço de referência determinístico baseado no SKU.
     * Mesma SKU → mesmo preço (estável entre rodadas de seed).
     * Faixas por prefixo de SKU (categoria implícita):
     *   CB/COB = freios       (80 — 350)
     *   MAG    = elétrica     (40 — 220)
     *   default                (60 — 800)
     */
    protected function priceFromSku(string $sku): float
    {
        // hash determinístico → número 0..999
        $h = abs(crc32($sku)) % 1000;

        return match (true) {
            str_contains($sku, '-CB-') || str_contains($sku, '-COB-') => 80 + ($h % 271),     // 80 — 350
            str_contains($sku, '-MAG-') => 40 + ($h % 181),                                    // 40 — 220
            default => 60 + ($h % 741),                                                        // 60 — 800
        };
    }

    public function run(): void
    {
        // Mapas para resolver IDs por nome/slug rapidamente
        $brands = Brand::pluck('id', 'slug');
        $cats = Category::pluck('id', 'slug');

        // Cada produto: [sku, name, brand_slug, [category_slugs], short, specs, stock_level, is_featured, [fitment_model_slugs => [year_from, year_to]]]
        $products = [
            // ───── FREIOS ─────
            [
                'TMAC-CB-001', 'Pastilha de freio dianteira CG 150/160 Titan Fan',
                'cobreq', ['freios'],
                'Pastilha cerâmica de alta durabilidade para freio dianteiro.',
                ['Tipo' => 'Cerâmica', 'Posição' => 'Dianteira', 'Garantia' => '6 meses'],
                'ok', true,
                [
                    'cg-150-titan' => [2004, 2015],
                    'cg-160-titan' => [2016, null],
                    'cg-150-fan' => [2009, 2015],
                    'cg-160-fan' => [2016, null],
                    'cg-160-start' => [2015, null],
                ],
            ],
            [
                'TMAC-CB-002', 'Pastilha de freio traseira CG 150/160',
                'cobreq', ['freios'],
                'Pastilha traseira sinterizada com retorno rápido.',
                ['Tipo' => 'Sinterizada', 'Posição' => 'Traseira'],
                'ok', false,
                [
                    'cg-150-titan' => [2004, 2015],
                    'cg-160-titan' => [2016, null],
                    'cg-160-fan' => [2016, null],
                ],
            ],
            [
                'TMAC-CB-003', 'Pastilha freio dianteiro Fazer 150/250',
                'cobreq', ['freios'],
                'Para freio dianteiro Yamaha Fazer.',
                ['Tipo' => 'Cerâmica', 'Posição' => 'Dianteira'],
                'ok', false,
                [
                    'fazer-150' => [2014, 2023],
                    'fazer-250' => [2006, 2023],
                    'fazer-250-abs' => [2018, null],
                    'factor-150' => [2016, null],
                ],
            ],
            [
                'TMAC-CB-004', 'Pastilha freio dianteiro Biz 125 / Pop 110i',
                'cobreq', ['freios'],
                'Pastilha de freio para Honda Biz e Pop.',
                ['Tipo' => 'Cerâmica', 'Posição' => 'Dianteira'],
                'ok', false,
                ['biz-125' => [2005, null], 'pop-110i' => [2016, null], 'biz-110i' => [2016, null]],
            ],
            [
                'TMAC-CB-005', 'Disco de freio dianteiro CB 300 / Twister 250',
                'cobreq', ['freios'],
                'Disco flutuante em aço inox.',
                ['Diâmetro' => '276mm', 'Espessura' => '4mm', 'Material' => 'Inox 420'],
                'ok', true,
                ['cb-300r' => [2009, 2015], 'cb-250f-twister' => [2016, null]],
            ],
            [
                'TMAC-CB-006', 'Disco de freio traseiro XRE 300 / Tornado',
                'cobreq', ['freios'],
                'Disco traseiro de aço carbono.',
                ['Diâmetro' => '220mm'],
                'low', false,
                ['xre-300' => [2009, null], 'xr-250-tornado' => [2001, 2008]],
            ],
            [
                'TMAC-CB-007', 'Fluido de freio DOT 4 — 500ml',
                'bosch', ['freios'],
                'Fluido sintético para sistemas hidráulicos.',
                ['Tipo' => 'DOT 4', 'Volume' => '500ml', 'Validade' => '24 meses'],
                'ok', false,
                [],
            ],

            // ───── MOTOR ─────
            [
                'TMAC-NGK-001', 'Vela NGK CPR8EA-9 — CG 150/160',
                'ngk', ['motor'],
                'Vela de ignição original NGK para motores monocilíndricos 150–160cc.',
                ['Rosca' => '10mm', 'Abertura' => '0.9mm', 'Resistor' => 'Sim'],
                'ok', true,
                [
                    'cg-150-titan' => [2004, 2015], 'cg-150-fan' => [2009, 2015],
                    'cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'cg-160-start' => [2015, null],
                    'nxr-150-bros' => [2003, 2015], 'nxr-160-bros' => [2015, null],
                    'biz-125' => [2005, null], 'pop-110i' => [2016, null],
                ],
            ],
            [
                'TMAC-NGK-002', 'Vela NGK CR8E — Fazer 250 / Lander 250',
                'ngk', ['motor'],
                'Vela NGK premium para motores Yamaha 250cc.',
                ['Rosca' => '12mm', 'Abertura' => '0.8mm'],
                'ok', false,
                [
                    'fazer-250' => [2006, 2023], 'fazer-250-abs' => [2018, null],
                    'lander-250' => [2007, null], 'xtz-250-tenere' => [2011, 2019],
                ],
            ],
            [
                'TMAC-NGK-003', 'Vela NGK DR8EA — XRE 300 / Tenere',
                'ngk', ['motor'],
                'Vela com eletrodo central de cobre para 300cc.',
                ['Rosca' => '12mm', 'Tipo' => 'Cobre'],
                'ok', false,
                ['xre-300' => [2009, null], 'xre-300-sahara' => [2022, null], 'tenere-250' => [2011, 2019]],
            ],
            [
                'TMAC-VED-001', 'Junta de cabeçote CG 150/160',
                'vedamotors', ['motor'],
                'Junta de cabeçote em aço com revestimento de fibra.',
                ['Material' => 'Aço/fibra', 'Espessura' => '0.5mm'],
                'ok', false,
                ['cg-150-titan' => [2004, 2015], 'cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-VED-002', 'Kit juntas motor completo Fazer 150',
                'vedamotors', ['motor'],
                'Kit completo de juntas para retífica de motor.',
                ['Inclui' => 'Cabeçote, base, lateral, válvulas', 'Itens' => '12 peças'],
                'low', false,
                ['fazer-150' => [2014, 2023], 'factor-150' => [2016, null]],
            ],
            [
                'TMAC-VED-003', 'Retentor de válvula CG 160',
                'vedamotors', ['motor'],
                'Retentor de haste de válvula em borracha FKM.',
                ['Quantidade' => '2 peças', 'Material' => 'FKM (Viton)'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'nxr-160-bros' => [2015, null]],
            ],
            [
                'TMAC-MAG-007', 'Pistão CG 160 STD 57mm',
                'magnetron', ['motor'],
                'Pistão forjado com anéis para CG 160.',
                ['Diâmetro' => '57mm', 'Padrão' => 'STD'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'cg-160-start' => [2015, null]],
            ],

            // ───── TRANSMISSÃO ─────
            [
                'TMAC-RIF-001', 'Kit relação tração CG 160 (corrente + coroa + pinhão)',
                'riffel', ['transmissao'],
                'Kit completo de tração 428H, 14T pinhão / 39T coroa.',
                ['Corrente' => '428H × 118 elos', 'Pinhão' => '14T', 'Coroa' => '39T'],
                'ok', true,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'cg-160-start' => [2015, null]],
            ],
            [
                'TMAC-RIF-002', 'Kit relação tração Fazer 250',
                'riffel', ['transmissao'],
                'Kit 520H para Yamaha Fazer 250.',
                ['Corrente' => '520H × 110 elos', 'Pinhão' => '14T', 'Coroa' => '45T'],
                'ok', false,
                ['fazer-250' => [2006, 2023], 'fazer-250-abs' => [2018, null], 'lander-250' => [2007, null]],
            ],
            [
                'TMAC-DID-001', 'Corrente DID 520 VX3 — 120 elos',
                'did', ['transmissao'],
                'Corrente com retentores X-Ring, para motos de 250 a 650cc.',
                ['Tipo' => '520 X-Ring', 'Elos' => '120', 'Carga' => '38 kN'],
                'low', true,
                [
                    'fazer-250' => [2006, 2023], 'cb-500f' => [2014, null], 'cb-500x' => [2014, null],
                    'ninja-650' => [2017, null], 'mt-07' => [2015, null], 'gsx-s750' => [2015, null],
                ],
            ],
            [
                'TMAC-RIF-003', 'Pinhão Riffel CG 160 14T',
                'riffel', ['transmissao'],
                'Pinhão de transmissão em aço SAE 1045.',
                ['Dentes' => '14T', 'Passo' => '428H'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'nxr-160-bros' => [2015, null]],
            ],
            [
                'TMAC-RIF-004', 'Coroa Riffel Fazer 250 — 45T',
                'riffel', ['transmissao'],
                'Coroa em aço de alta resistência.',
                ['Dentes' => '45T', 'Passo' => '520H'],
                'ok', false,
                ['fazer-250' => [2006, 2023], 'lander-250' => [2007, null]],
            ],

            // ───── ELÉTRICA ─────
            [
                'TMAC-MAG-001', 'CDI Magnetron CG 150/160',
                'magnetron', ['eletrica'],
                'CDI digital de 6 pinos para CG 150 e 160.',
                ['Pinos' => '6', 'Tensão' => '12V'],
                'ok', false,
                ['cg-150-titan' => [2004, 2015], 'cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-MAG-002', 'Regulador de voltagem Pop 110i / Biz 110i',
                'magnetron', ['eletrica'],
                'Regulador de 12V para Honda Pop e Biz.',
                ['Tensão' => '12V', 'Corrente' => '10A'],
                'ok', false,
                ['pop-110i' => [2016, null], 'biz-110i' => [2016, null], 'biz-125' => [2005, null]],
            ],
            [
                'TMAC-HEL-001', 'Bateria Heliar HMP-7 (7Ah) — CG/Titan/Fazer',
                'heliar', ['eletrica'],
                'Bateria selada AGM, livre de manutenção.',
                ['Capacidade' => '7Ah', 'Tensão' => '12V', 'Tipo' => 'AGM selada'],
                'ok', true,
                [
                    'cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null],
                    'fazer-150' => [2014, 2023], 'fazer-250' => [2006, 2023],
                    'factor-150' => [2016, null], 'crosser-150' => [2014, null],
                ],
            ],
            [
                'TMAC-MOU-001', 'Bateria Moura MA5-D (5Ah) — Biz/Pop',
                'moura', ['eletrica'],
                'Bateria selada compacta de 5Ah.',
                ['Capacidade' => '5Ah', 'Tensão' => '12V'],
                'ok', false,
                ['biz-125' => [2005, null], 'biz-110i' => [2016, null], 'pop-110i' => [2016, null]],
            ],
            [
                'TMAC-MAG-003', 'Bobina de ignição CG 160',
                'magnetron', ['eletrica'],
                'Bobina de alta tensão para CG 160.',
                ['Tensão saída' => '25kV', 'Resistência' => '4.2 Ω'],
                'low', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'cg-160-start' => [2015, null]],
            ],
            [
                'TMAC-MAG-004', 'Motor de partida XRE 300',
                'magnetron', ['eletrica'],
                'Motor de partida com engrenagens reforçadas.',
                ['Potência' => '0.4kW', 'Voltagem' => '12V'],
                'ok', false,
                ['xre-300' => [2009, null]],
            ],

            // ───── FILTROS ─────
            [
                'TMAC-FRM-001', 'Filtro de óleo Fram PH6017A — CG 150/160',
                'fram', ['filtros', 'motor'],
                'Filtro de óleo cartucho.',
                ['Tipo' => 'Cartucho', 'Compatibilidade' => 'CG 150/160, NXR Bros'],
                'ok', true,
                [
                    'cg-150-titan' => [2004, 2015], 'cg-160-titan' => [2016, null],
                    'cg-160-fan' => [2016, null], 'nxr-150-bros' => [2003, 2015], 'nxr-160-bros' => [2015, null],
                ],
            ],
            [
                'TMAC-FRM-002', 'Filtro de ar Fram CA9543 — Fazer 250',
                'fram', ['filtros'],
                'Filtro de ar plissado.',
                ['Material' => 'Papel plissado'],
                'ok', false,
                ['fazer-250' => [2006, 2023], 'fazer-250-abs' => [2018, null]],
            ],
            [
                'TMAC-TEC-001', 'Filtro de óleo Tecfil PSL-90 — Yamaha',
                'tecfil', ['filtros'],
                'Filtro de óleo para Yamaha 150–250cc.',
                ['Marca compatível' => 'Yamaha'],
                'ok', false,
                ['fazer-150' => [2014, 2023], 'fazer-250' => [2006, 2023], 'factor-150' => [2016, null], 'lander-250' => [2007, null]],
            ],
            [
                'TMAC-PRO-001', 'Filtro de ar high flow Procotton — CG 160',
                'procotton', ['filtros'],
                'Filtro de algodão lavável de alta vazão.',
                ['Tipo' => 'Algodão lavável', 'Vida útil' => 'Permanente'],
                'low', true,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-FRM-003', 'Filtro de óleo Fram CH6555 — CB 500',
                'fram', ['filtros'],
                'Filtro spin-on para CB 500F/X.',
                ['Tipo' => 'Spin-on'],
                'ok', false,
                ['cb-500f' => [2014, null], 'cb-500x' => [2014, null]],
            ],

            // ───── SUSPENSÃO ─────
            [
                'TMAC-COF-001', 'Amortecedor traseiro Cofap CG 160',
                'cofap', ['suspensao'],
                'Par de amortecedores traseiros com regulagem de pré-carga.',
                ['Curso' => '85mm', 'Regulagem' => '5 posições', 'Par' => 'Sim (2 unidades)'],
                'ok', true,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-COF-002', 'Amortecedor traseiro Fazer 250',
                'cofap', ['suspensao'],
                'Amortecedor com mola progressiva.',
                ['Curso' => '95mm'],
                'ok', false,
                ['fazer-250' => [2006, 2023], 'fazer-250-abs' => [2018, null]],
            ],
            [
                'TMAC-VED-004', 'Retentor de bengala 33mm CG 160',
                'vedamotors', ['suspensao'],
                'Par de retentores para suspensão dianteira.',
                ['Diâmetro' => '33mm', 'Par' => 'Sim (2 unidades)'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'nxr-160-bros' => [2015, null]],
            ],
            [
                'TMAC-COF-003', 'Mola dianteira XRE 300',
                'cofap', ['suspensao'],
                'Mola progressiva para suspensão dianteira.',
                ['Comprimento' => '420mm'],
                'low', false,
                ['xre-300' => [2009, null]],
            ],

            // ───── ILUMINAÇÃO ─────
            [
                'TMAC-MAG-005', 'Farol LED CG 160 (par)',
                'magnetron', ['iluminacao'],
                'Kit LED de farol alta e baixa.',
                ['Potência' => '40W', 'Tensão' => '12V', 'Cor' => '6000K'],
                'ok', true,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'cg-160-start' => [2015, null]],
            ],
            [
                'TMAC-MAG-006', 'Lâmpada H4 35/35W moto',
                'magnetron', ['iluminacao'],
                'Lâmpada halógena para faróis de moto.',
                ['Base' => 'H4', 'Potência' => '35/35W'],
                'ok', false,
                [],
            ],
            [
                'TMAC-PTK-001', 'Lanterna LED traseira customizada — universal',
                'pro-tork', ['iluminacao'],
                'Lanterna LED universal estilo café racer.',
                ['Cor' => 'Vermelho', 'Universal' => 'Sim'],
                'ok', false,
                [],
            ],

            // ───── PNEUS ─────
            [
                'TMAC-PIR-001', 'Pneu Pirelli MT60 RS 110/80-18 — XRE/Tenere',
                'pirelli', ['pneus-e-camaras'],
                'Pneu dual sport para trail.',
                ['Medida' => '110/80-18', 'Tipo' => 'Dual sport', 'Uso' => '80% asfalto / 20% off'],
                'ok', true,
                ['xre-300' => [2009, null], 'tenere-250' => [2011, 2019], 'lander-250' => [2007, null]],
            ],
            [
                'TMAC-PIR-002', 'Pneu Pirelli Sport Demon 100/80-17 — CG/Titan',
                'pirelli', ['pneus-e-camaras'],
                'Pneu urbano com excelente aderência no molhado.',
                ['Medida' => '100/80-17', 'Posição' => 'Dianteira'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null], 'fazer-150' => [2014, 2023]],
            ],
            [
                'TMAC-MIC-001', 'Pneu Michelin Pilot Street 130/70-17 — CB 250',
                'michelin', ['pneus-e-camaras'],
                'Pneu traseiro esportivo urbano.',
                ['Medida' => '130/70-17', 'Posição' => 'Traseira'],
                'ok', false,
                ['cb-250f-twister' => [2016, null], 'cb-300r' => [2009, 2015], 'fazer-250' => [2006, 2023]],
            ],
            [
                'TMAC-MIC-002', 'Pneu Michelin Power 5 — 180/55-17 (sport)',
                'michelin', ['pneus-e-camaras'],
                'Pneu esportivo de alta performance.',
                ['Medida' => '180/55-17', 'Tipo' => 'Sport'],
                'low', true,
                ['ninja-650' => [2017, null], 'mt-07' => [2015, null], 'gsx-s750' => [2015, null], 'cb-650r' => [2019, null]],
            ],

            // ───── ACESSÓRIOS ─────
            [
                'TMAC-PTK-002', 'Manopla Pro Tork esportiva (par)',
                'pro-tork', ['acessorios'],
                'Manoplas em borracha com encaixe universal.',
                ['Universal' => 'Sim', 'Par' => 'Sim (2 unidades)'],
                'ok', false,
                [],
            ],
            [
                'TMAC-PTK-003', 'Retrovisor Pro Tork articulado universal (par)',
                'pro-tork', ['acessorios'],
                'Retrovisor com base 10mm rosca direita.',
                ['Universal' => 'Sim'],
                'ok', false,
                [],
            ],
            [
                'TMAC-PTK-004', 'Alavanca de freio ajustável CB/CBR',
                'pro-tork', ['acessorios'],
                'Alavanca articulada CNC anodizada.',
                ['Material' => 'Alumínio CNC', 'Ajustes' => '6 níveis'],
                'low', false,
                ['cb-250f-twister' => [2016, null], 'cb-500f' => [2014, null], 'cb-500x' => [2014, null], 'cb-650r' => [2019, null]],
            ],
            [
                'TMAC-PTK-005', 'Protetor de motor CG 160',
                'pro-tork', ['acessorios'],
                'Protetor tubular para CG 160 — pintura epóxi.',
                ['Material' => 'Aço SAE 1020', 'Acabamento' => 'Preto fosco'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-PTK-006', 'Bagageiro traseiro XRE 300',
                'pro-tork', ['acessorios'],
                'Suporte para top case até 30L.',
                ['Capacidade' => '30L'],
                'ok', false,
                ['xre-300' => [2009, null]],
            ],

            // ───── PRODUTOS MAIS ESPECÍFICOS / NICHADOS ─────
            [
                'TMAC-DID-002', 'Corrente DID 525 ZVMX — esportivas 600/700cc',
                'did', ['transmissao'],
                'Corrente Z-Ring premium.',
                ['Tipo' => '525 Z-Ring', 'Elos' => '116'],
                'low', false,
                ['cb-650r' => [2019, null], 'mt-07' => [2015, null], 'ninja-650' => [2017, null]],
            ],
            [
                'TMAC-NGK-004', 'Vela NGK MAR9A-J — BMW G 310',
                'ngk', ['motor'],
                'Vela específica para BMW G 310.',
                ['Aplicação' => 'BMW G 310 R / GS'],
                'low', false,
                ['g-310-r' => [2017, null], 'g-310-gs' => [2017, null]],
            ],
            [
                'TMAC-NGK-005', 'Vela NGK CR9E — Royal Enfield 350',
                'ngk', ['motor'],
                'Vela para motores Royal Enfield 350 J-Series.',
                ['Aplicação' => 'Royal Enfield 350'],
                'ok', false,
                ['meteor-350' => [2021, null], 'hunter-350' => [2023, null], 'classic-350' => [2022, null]],
            ],
            [
                'TMAC-COB-008', 'Pastilha freio dianteira Royal Enfield 650',
                'cobreq', ['freios'],
                'Pastilha para freio dianteiro Continental / Interceptor 650.',
                ['Posição' => 'Dianteira'],
                'ok', false,
                ['continental-gt-650' => [2019, null], 'interceptor-650' => [2019, null], 'super-meteor-650' => [2023, null]],
            ],
            [
                'TMAC-PTK-007', 'Escapamento esportivo CG 160',
                'pro-tork', ['acessorios'],
                'Escapamento full em aço inox, ronco esportivo.',
                ['Material' => 'Inox 304', 'Tipo' => 'Full system'],
                'low', true,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-VED-005', 'Junta de carter NMAX 160',
                'vedamotors', ['motor'],
                'Junta de carter NMAX.',
                ['Material' => 'Papelão hidráulico'],
                'ok', false,
                ['nmax-160' => [2017, null], 'nmax-160-connected' => [2022, null]],
            ],
            [
                'TMAC-FRM-004', 'Filtro de ar NMAX 160',
                'fram', ['filtros'],
                'Filtro original do scooter Yamaha NMAX.',
                ['Tipo' => 'Esponja oleada'],
                'ok', false,
                ['nmax-160' => [2017, null], 'nmax-160-connected' => [2022, null]],
            ],
            [
                'TMAC-FRM-005', 'Filtro de óleo PCX 160',
                'fram', ['filtros'],
                'Filtro original Honda PCX 160.',
                ['Tipo' => 'Cartucho'],
                'ok', false,
                ['pcx-160' => [2021, null], 'adv-150' => [2021, null]],
            ],
            [
                'TMAC-HEL-002', 'Bateria Heliar HMP-9 (9Ah) — XRE/Twister',
                'heliar', ['eletrica'],
                'Bateria selada AGM 9Ah.',
                ['Capacidade' => '9Ah', 'Tipo' => 'AGM selada'],
                'ok', false,
                ['xre-300' => [2009, null], 'cb-250f-twister' => [2016, null], 'cb-300r' => [2009, 2015]],
            ],
            [
                'TMAC-COF-004', 'Kit de bengalas Fazer 250',
                'cofap', ['suspensao'],
                'Par de bengalas de suspensão dianteira.',
                ['Diâmetro' => '37mm', 'Par' => 'Sim'],
                'low', false,
                ['fazer-250' => [2006, 2023], 'fazer-250-abs' => [2018, null]],
            ],
            [
                'TMAC-PTK-008', 'Mocho banco confort CG 160',
                'pro-tork', ['acessorios'],
                'Banco com gel para melhor ergonomia.',
                ['Material' => 'Couro sintético com gel'],
                'ok', false,
                ['cg-160-titan' => [2016, null], 'cg-160-fan' => [2016, null]],
            ],
            [
                'TMAC-NGK-006', 'Cabo de vela NGK universal',
                'ngk', ['eletrica'],
                'Cabo de alta tensão silicone — universal.',
                ['Comprimento' => '50cm', 'Universal' => 'Sim'],
                'ok', false,
                [],
            ],
            [
                'TMAC-PIR-003', 'Câmara de ar Pirelli 90/90-19 — XRE/Bros',
                'pirelli', ['pneus-e-camaras'],
                'Câmara de ar para pneus de trail.',
                ['Medida' => '90/90-19', 'Bico' => 'TR4'],
                'ok', false,
                ['xre-300' => [2009, null], 'nxr-160-bros' => [2015, null], 'lander-250' => [2007, null]],
            ],
            [
                'TMAC-MAG-008', 'Buzina Magnetron 12V dupla',
                'magnetron', ['eletrica', 'acessorios'],
                'Buzina dupla com som potente.',
                ['Universal' => 'Sim'],
                'ok', false,
                [],
            ],
            [
                'TMAC-COB-009', 'Pastilha freio traseiro Pop / Biz',
                'cobreq', ['freios'],
                'Pastilha para freio traseiro a tambor.',
                ['Tipo' => 'Tambor', 'Posição' => 'Traseira'],
                'ok', false,
                ['biz-125' => [2005, null], 'pop-110i' => [2016, null], 'biz-110i' => [2016, null]],
            ],
        ];

        foreach ($products as $i => $p) {
            [$sku, $name, $brandSlug, $catSlugs, $short, $specs, $stock, $featured, $fitments] = $p;

            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'brand_id' => $brands[$brandSlug] ?? null,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'short_description' => $short,
                    'description' => null,
                    'specifications' => $specs,
                    'main_image' => null,
                    'is_featured' => $featured,
                    'is_active' => true,
                    'sort_order' => $i,
                    'stock_level' => $stock,
                    'quote_price' => $this->priceFromSku($sku),
                ]
            );

            // Categorias
            $categoryIds = collect($catSlugs)->map(fn ($s) => $cats[$s] ?? null)->filter()->all();
            if ($categoryIds) {
                $product->categories()->syncWithoutDetaching($categoryIds);
            }

            // Fitments (motos compatíveis)
            foreach ($fitments as $modelSlug => [$yearFrom, $yearTo]) {
                $modelId = $this->model($modelSlug);
                if (! $modelId) {
                    continue;
                }

                $product->fitments()->firstOrCreate(
                    [
                        'motorcycle_model_id' => $modelId,
                        'year_from' => $yearFrom,
                    ],
                    [
                        'year_to' => $yearTo,
                    ]
                );
            }
        }
    }
}
