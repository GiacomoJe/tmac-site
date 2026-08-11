<div>
    @if($open)
        <div class="fixed inset-0 z-[80] flex items-center justify-center p-4"
             x-data
             x-trap.inert.noscroll="true"
             @keydown.escape.window="$wire.close()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-ink/70 backdrop-blur-sm"
                 wire:click="close"></div>

            {{-- Card do modal --}}
            <div class="relative w-full max-w-[520px] bg-bg-elev rounded-xl border border-line shadow-2xl overflow-hidden"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Faixa azul no topo --}}
                <div class="h-[3px]" style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

                <div class="p-6 md:p-7 relative">
                    {{-- Mega "?" decorativo --}}
                    <div class="absolute top-0 right-3 pointer-events-none select-none">
                        <span class="mega-num mega-num--blue" style="font-size: 130px;">?</span>
                    </div>

                    {{-- Botão fechar --}}
                    @if(\App\Livewire\Site\QuoteCart::hasState())
                        <button type="button" wire:click="close"
                                class="absolute top-3 right-3 w-9 h-9 rounded-lg border border-line bg-bg flex items-center justify-center text-ink-soft hover:text-ink hover:bg-bg-sunken transition z-10"
                                aria-label="Fechar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    @endif

                    <div class="relative">
                        <div class="section-marker">
                            <span class="section-marker__num">UF</span>
                            <span class="section-marker__label">Antes de cotar</span>
                        </div>
                        <h2 class="mt-3 font-display font-black text-[24px] md:text-[28px] tracking-tightest leading-[0.95] text-ink uppercase">
                            Onde fica sua <span class="text-signal">loja</span>?
                        </h2>
                        <p class="mt-2 text-[13px] text-ink-soft max-w-[44ch]">
                            Cada estado tem um <strong>valor mínimo de cotação</strong> diferente por causa da logística e operação regional. Selecione sua UF para começar.
                        </p>
                    </div>

                    <div class="mt-5 relative">
                        <label class="block">
                            <span class="font-mono text-[11px] uppercase tracking-[0.10em] text-ink-faint">Estado (UF)</span>
                            <select wire:model.live="uf"
                                    class="mt-1.5 w-full h-12 bg-bg border border-line rounded-lg px-3.5 text-[15px] font-medium text-ink focus:outline-none focus:ring-2 focus:ring-signal/20 focus:border-signal transition">
                                <option value="">Selecione um estado…</option>
                                @foreach($this->states as $state)
                                    <option value="{{ $state->uf }}">
                                        {{ $state->uf }} — {{ $state->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('uf')
                                <span class="mt-1 text-[12px] text-accent block">{{ $message }}</span>
                            @enderror
                        </label>

                        {{-- Info do estado selecionado --}}
                        @if($this->selectedState)
                            <div class="mt-4 rounded-lg border border-signal/30 bg-signal-soft/40 p-4 flex items-start gap-3"
                                 wire:key="state-info-{{ $this->selectedState->uf }}">
                                <div class="w-10 h-10 rounded-lg bg-signal text-white flex items-center justify-center font-display font-black text-[14px] tracking-tightest flex-shrink-0">
                                    {{ $this->selectedState->uf }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-display font-bold text-[15px] text-ink leading-tight">
                                        {{ $this->selectedState->name }}
                                    </div>
                                    <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint mt-0.5">
                                        Região {{ $this->selectedState->region }}
                                    </div>
                                    <div class="mt-2 text-[12px] text-ink-soft leading-relaxed">
                                        Este estado possui um valor mínimo para cotação. Você acompanha o progresso conforme adiciona produtos.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Botões --}}
                    <div class="mt-6 flex items-center gap-2 relative">
                        @if(\App\Livewire\Site\QuoteCart::hasState())
                            <button type="button" wire:click="close" class="btn btn-ghost h-12 flex-1">
                                Cancelar
                            </button>
                        @endif
                        <button type="button" wire:click="confirm"
                                @if(!$uf) disabled @endif
                                class="btn-signal h-12 flex-1 font-bold uppercase tracking-[0.04em] text-[13px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="confirm" class="flex items-center gap-2">
                                {{ \App\Livewire\Site\QuoteCart::hasState() ? 'Atualizar estado' : 'Confirmar e continuar' }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                            </span>
                            <span wire:loading wire:target="confirm">Salvando…</span>
                        </button>
                    </div>

                    <p class="mt-4 text-[11px] text-ink-faint flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                        Você pode trocar de estado a qualquer momento clicando no carrinho de cotação.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
