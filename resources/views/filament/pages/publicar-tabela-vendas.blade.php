<x-filament-panels::page>

    @php $ativa = $this->getActivePriceList(); @endphp

    @if (session('tabela_sucesso'))
        <div class="rounded-xl border border-success-300 bg-success-50 text-success-700 dark:border-success-800 dark:bg-success-950 dark:text-success-400 p-4 mb-6 text-sm font-medium">
            {{ session('tabela_sucesso') }}
        </div>
    @endif

    @if (session('tabela_erro'))
        <div class="rounded-xl border border-danger-300 bg-danger-50 text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-400 p-4 mb-6 text-sm font-medium">
            <p class="font-bold mb-1">Falha ao publicar a tabela</p>
            <p>{{ session('tabela_erro') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-danger-300 bg-danger-50 text-danger-700 dark:border-danger-800 dark:bg-danger-950 dark:text-danger-400 p-4 mb-6 text-sm font-medium">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-2">Tabela em vigor</h2>
        @if ($ativa)
            <p class="text-base font-semibold">{{ $ativa->original_filename }}</p>
            <p class="text-sm text-gray-500">
                Publicada em {{ $ativa->published_at?->format('d/m/Y H:i') }}
                — {{ $ativa->products_count }} produtos · {{ $ativa->states_count }} tabelas/estados
            </p>
        @else
            <p class="text-sm text-gray-500">Nenhuma tabela publicada ainda. Envie o primeiro arquivo abaixo.</p>
        @endif
    </div>

    {{--
        Form comum (não é Livewire) de propósito: um único POST multipart direto
        pra rota, tratado pelo Apache/PHP como qualquer upload padrão. Arquivos
        de catálogo real chegam a dezenas de MB — o componente FileUpload do
        Livewire faz um upload assíncrono em DUAS etapas (grava um arquivo
        temporário antes mesmo do submit) e essa etapa extra é onde apareciam
        falhas com arquivos grandes.
    --}}
    <form method="POST" action="{{ route('admin.tabela-vendas.upload') }}" enctype="multipart/form-data">
        @csrf

        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <label for="arquivo" class="block text-sm font-medium text-gray-950 dark:text-white mb-1">
                Arquivo .xlsx atualizado
            </label>
            <p class="text-xs text-gray-500 mb-3">
                Precisa ter as abas Config, Tabela - Modelo, Tabela - Estoque, BA, as tabelas de cada estado e as 6
                abas de promoção — mesmo formato do arquivo que você já mantém no Excel. Tamanho máximo: 50&nbsp;MB.
            </p>
            <input
                type="file"
                name="arquivo"
                id="arquivo"
                accept=".xlsx,.xlsm,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel.sheet.macroEnabled.12"
                required
                class="block w-full text-sm text-gray-950 dark:text-white file:me-4 file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-500"
            />
        </div>

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Publicar tabela
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
