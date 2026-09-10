<div>
    @if (! $priceList)
        <section class="container-tmac pt-16 pb-16 text-center max-w-md mx-auto">
            <h1 class="font-display font-extrabold text-xl text-ink mb-2">Nenhuma tabela publicada ainda</h1>
            <p class="text-sm text-ink-soft">Assim que o time comercial publicar a Tabela de Vendas, ela aparece aqui automaticamente.</p>
        </section>
    @else
        {{-- Barra de tabela + total (sticky) --}}
        <div class="sticky top-14 z-30 bg-bg-elev border-b border-line">
            <div class="container-tmac flex items-center gap-3 py-2.5">
                <div class="flex-1 min-w-0">
                    <label class="block text-[10px] font-bold uppercase tracking-mono-up text-ink-faint">Tabela / Estado</label>
                    <select wire:model.live="tabela" class="w-full max-w-[160px] rounded-sm border border-line text-sm font-bold text-signal py-1.5 px-2">
                        @foreach ($tabelas as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-[10px] font-bold uppercase tracking-mono-up text-ink-faint">Total do pedido</div>
                    <div class="font-display font-extrabold text-lg text-signal">
                        {{ number_format($resumo['total'], 2, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Abas --}}
        <div class="container-tmac flex gap-1 border-b border-line mt-1">
            <button wire:click="mudarAba('busca')"
                    class="px-4 py-2.5 text-sm font-bold uppercase tracking-mono-up border-b-2 {{ $aba === 'busca' ? 'border-accent text-signal' : 'border-transparent text-ink-faint' }}">
                Buscar itens
            </button>
            <button wire:click="mudarAba('pedido')"
                    class="px-4 py-2.5 text-sm font-bold uppercase tracking-mono-up border-b-2 {{ $aba === 'pedido' ? 'border-accent text-signal' : 'border-transparent text-ink-faint' }}">
                Meu pedido ({{ $resumo['qtd'] }})
            </button>
        </div>

        <div class="container-tmac py-4">

            @if ($aba === 'busca')
                {{-- Busca --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-mono-up text-ink-faint mb-1">Pesquisar</label>
                        <input type="search" wire:model.live.debounce.400ms="q1" placeholder="código ou descrição"
                               class="w-full rounded-sm border border-line px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-mono-up text-ink-faint mb-1">Refinar</label>
                        <input type="search" wire:model.live.debounce.400ms="q2" placeholder="filtra dentro do resultado"
                               class="w-full rounded-sm border border-line px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <button wire:click="filtrar('todos')" class="btn-chip {{ $filtro === 'todos' ? 'btn-chip--on' : '' }}">Todos</button>
                    <button wire:click="filtrar('promo')" class="btn-chip {{ $filtro === 'promo' ? 'btn-chip--on' : '' }}">Só promoção</button>
                    <button wire:click="filtrar('extra')" class="btn-chip {{ $filtro === 'extra' ? 'btn-chip--on' : '' }}">Só promoção extra</button>
                    @if ($q1 || $q2)
                        <button wire:click="limparBusca" class="text-xs text-ink-faint underline">limpar busca</button>
                    @endif
                    <span class="ml-auto text-xs text-ink-faint">
                        {{ min($mostrar, $itensTotal) }} de {{ $itensTotal }} item(ns)
                    </span>
                </div>

                <div wire:loading.class="opacity-50" class="divide-y divide-line border border-line rounded-lg overflow-hidden bg-bg-elev">
                    {{-- cabeçalho — só desktop --}}
                    <div class="hidden md:grid grid-cols-12 gap-2 px-3 py-2 bg-bg-sunken text-[10px] font-bold uppercase tracking-mono-up text-ink-faint">
                        <div class="col-span-1">Cód.</div>
                        <div class="col-span-3">Descrição</div>
                        <div class="col-span-1">Marca</div>
                        <div class="col-span-1">Status</div>
                        <div class="col-span-1 text-right">Tabela</div>
                        <div class="col-span-1 text-center">Desc. %</div>
                        <div class="col-span-1 text-center">Qtd.</div>
                        <div class="col-span-1 text-right">Final</div>
                        <div class="col-span-2 text-right">Total</div>
                    </div>

                    @forelse ($itens as $item)
                        @include('livewire.cliente._item-row', [
                            'item' => $item,
                            'qtd' => (int) ($qtds[$item->cod] ?? 0),
                            'desconto' => (float) ($descontos[$item->cod] ?? 0),
                        ])
                    @empty
                        <div class="px-4 py-10 text-center text-sm text-ink-faint">Nenhum item encontrado.</div>
                    @endforelse
                </div>

                @if ($itensTotal > $mostrar)
                    <div class="text-center mt-4">
                        <button wire:click="mostrarMais" wire:loading.attr="disabled"
                                class="bg-signal hover:bg-signal-dark text-white text-sm font-bold uppercase tracking-mono-up rounded-sm px-5 py-2.5">
                            Mostrar mais {{ min(40, $itensTotal - $mostrar) }} itens
                        </button>
                    </div>
                @endif

            @else
                {{-- Meu pedido --}}
                @error('pedido')
                    <div class="mb-3 rounded-DEFAULT bg-accent-soft text-accent-dark text-sm px-4 py-3">{{ $message }}</div>
                @enderror

                @if ($pedidoItens->isEmpty())
                    <div class="text-center py-10 text-sm text-ink-faint">
                        Nenhum item com quantidade ainda. Volte em "Buscar itens".
                    </div>
                @else
                    <div class="divide-y divide-line border border-line rounded-lg overflow-hidden bg-bg-elev mb-6">
                        <div class="hidden md:grid grid-cols-12 gap-2 px-3 py-2 bg-bg-sunken text-[10px] font-bold uppercase tracking-mono-up text-ink-faint">
                            <div class="col-span-1">Cód.</div>
                            <div class="col-span-3">Descrição</div>
                            <div class="col-span-1">Marca</div>
                            <div class="col-span-1">Status</div>
                            <div class="col-span-1 text-right">Tabela</div>
                            <div class="col-span-1 text-center">Desc. %</div>
                            <div class="col-span-1 text-center">Qtd.</div>
                            <div class="col-span-1 text-right">Final</div>
                            <div class="col-span-2 text-right">Total</div>
                        </div>

                        @foreach ($pedidoItens as $item)
                            @include('livewire.cliente._item-row', [
                                'item' => $item,
                                'qtd' => (int) ($qtds[$item->cod] ?? 0),
                                'desconto' => (float) ($descontos[$item->cod] ?? 0),
                            ])
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm mb-6">
                        <div><span class="text-ink-faint">Itens:</span> <b>{{ $resumo['qtd'] }}</b></div>
                        <div><span class="text-ink-faint">Peças:</span> <b>{{ $resumo['pecas'] }}</b></div>
                        <div><span class="text-ink-faint">Total:</span> <b class="text-signal">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</b></div>
                        @if ($resumo['totalBloqueado'] > 0)
                            <div class="text-accent"><span class="text-ink-faint">Fora do total (sem estoque):</span> <b>R$ {{ number_format($resumo['totalBloqueado'], 2, ',', '.') }}</b></div>
                        @endif
                        <button wire:click="limparPedido" wire:confirm="Apagar todas as quantidades e descontos do pedido?" class="ml-auto text-xs text-ink-faint underline">
                            limpar pedido
                        </button>
                    </div>

                    {{-- Dados do cliente para o pedido --}}
                    <div class="bg-bg-elev border border-line rounded-lg p-4 mb-24 md:mb-6">
                        <h2 class="text-xs font-bold uppercase tracking-mono-up text-signal mb-3">Dados para o pedido</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <x-cliente.field label="Razão social *" model="razaoSocial" />
                            <x-cliente.field label="CNPJ" model="cnpj" />
                            <x-cliente.field label="Responsável *" model="responsavel" />
                            <x-cliente.field label="Telefone *" model="telefone" />
                            <x-cliente.field label="E-mail *" model="email" type="email" />
                            <x-cliente.field label="Vendedor" model="vendedor" />
                            <x-cliente.field label="Transportadora" model="transportadora" />
                            <x-cliente.field label="CNPJ transportadora" model="cnpjTransportadora" />
                            <x-cliente.field label="Prazo de pagamento" model="prazoPagamento" />
                            <x-cliente.field label="Observações" model="observacoes" />
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Rodapé fixo — ações --}}
        <div class="fixed left-0 right-0 bottom-0 z-30 bg-bg-elev border-t-4 border-signal px-4 py-3 flex items-center gap-3 shadow-[0_-4px_16px_rgba(0,0,0,0.08)]">
            <div class="hidden sm:block text-sm">
                <span class="text-ink-faint">Total:</span>
                <b class="text-signal">R$ {{ number_format($resumo['total'], 2, ',', '.') }}</b>
                <span class="text-ink-faint">· {{ $resumo['pecas'] }} peças</span>
            </div>
            <div class="flex-1 sm:flex-none ml-auto flex gap-2">
                <button wire:click="salvarPedido" wire:loading.attr="disabled" wire:confirm="Confirmar envio do pedido?"
                        class="flex-1 sm:flex-none bg-accent hover:bg-accent-dark text-white text-xs sm:text-sm font-bold uppercase tracking-mono-up rounded-sm px-4 py-3">
                    Salvar pedido
                </button>
            </div>
        </div>

        @if ($pedidoSalvoId)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show"
                 class="fixed right-4 bottom-24 z-40 bg-signal text-white rounded-DEFAULT px-4 py-3 shadow-lg max-w-xs text-sm font-semibold">
                Pedido salvo com sucesso!
            </div>
        @endif
    @endif
</div>
