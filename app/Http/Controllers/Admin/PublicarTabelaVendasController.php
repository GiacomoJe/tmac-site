<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Pages\PublicarTabelaVendas;
use App\Http\Controllers\Controller;
use App\Services\SalesTable\PriceListImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Recebe o .xlsx da tela "Publicar tabela" do painel.
 *
 * Propositalmente NÃO é um componente Livewire: planilhas de catálogo real
 * (dezenas de MB, dezenas de milhares de linhas) passam por um upload comum
 * de formulário (um único POST multipart, tratado direto pelo Apache/PHP),
 * em vez do upload assíncrono em duas etapas que o componente FileUpload do
 * Livewire faz (um POST próprio só para gravar o arquivo temporário, e só
 * depois o submit do formulário). Essa segunda etapa é onde apareciam falhas
 * (`UnableToRetrieveMetadata` no arquivo em livewire-tmp/) só com arquivos
 * grandes — um form comum evita essa camada extra inteira.
 */
class PublicarTabelaVendasController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'arquivo' => ['required', 'file', 'max:51200', 'mimes:xlsx,xlsm'],
        ], [], ['arquivo' => 'arquivo .xlsx']);

        $file = $request->file('arquivo');
        $originalName = $file->getClientOriginalName();
        $relativePath = $file->store('tabela-vendas/uploads', 'local');
        $absolutePath = Storage::disk('local')->path($relativePath);

        try {
            $priceList = app(PriceListImporter::class)->import($absolutePath, $originalName, $request->user());

            Storage::disk('local')->delete($relativePath);

            return redirect()
                ->to(PublicarTabelaVendas::getUrl())
                ->with('tabela_sucesso', "Tabela publicada com sucesso! {$priceList->products_count} produtos · {$priceList->states_count} tabelas/estados. Já está valendo para todos os clientes.");
        } catch (Throwable $e) {
            report($e);

            Storage::disk('local')->delete($relativePath);

            return redirect()
                ->to(PublicarTabelaVendas::getUrl())
                ->with('tabela_erro', $e->getMessage());
        }
    }
}
