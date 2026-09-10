<?php

namespace App\Filament\Pages;

use App\Models\SalesPriceList;
use Filament\Pages\Page;

/**
 * Tela de publicação semanal da Tabela de Vendas — equivalente ao botão
 * "ATUALIZAR TABELA" do app antigo. O admin envia o .xlsx atualizado aqui;
 * já entra atrás do login do painel, então não precisamos de uma segunda senha.
 *
 * O envio do arquivo em si NÃO passa pelo componente FileUpload do Livewire
 * (propositalmente — ver App\Http\Controllers\Admin\PublicarTabelaVendasController):
 * a view desta página tem um <form> comum, que envia direto pra uma rota comum.
 * Isso evita o upload assíncrono em duas etapas do Livewire, que é onde
 * apareciam falhas silenciosas com arquivos grandes antes mesmo de chegar
 * na importação.
 */
class PublicarTabelaVendas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Tabela de Vendas';

    protected static ?string $navigationLabel = 'Publicar tabela';

    protected static ?string $title = 'Publicar Tabela de Vendas';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.publicar-tabela-vendas';

    public function getActivePriceList(): ?SalesPriceList
    {
        return SalesPriceList::active();
    }
}
