<div>
    <a href="{{ route('site.quote') }}"
       class="quote-float md:hidden fixed bottom-5 left-5 right-5 z-30 text-white rounded-xl shadow-2xl transition-colors duration-300 overflow-hidden {{ $count === 0 ? 'hidden' : '' }} {{ $meetsMin && $count > 0 ? 'quote-float--ready' : '' }}"
       data-cart-float>
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0 relative">
                <svg class="quote-cart__icon-cart w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 4h2l2.5 12h11L21 7H6"/><circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/></svg>
                <svg class="quote-cart__icon-check w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/60 leading-tight flex items-center gap-1.5">
                    Cotação
                    @if($stateUf)
                        <span class="opacity-50">·</span>
                        <span class="text-white font-bold">{{ $stateUf }}</span>
                    @endif
                </div>
                <div class="font-semibold text-[13px] leading-tight mt-0.5 truncate">
                    <span data-cart-items-label>{{ $count }} {{ $count === 1 ? 'item' : 'itens' }}</span>
                    @if($stateUf)
                        ·
                        <span data-cart-progress-label class="{{ $meetsMin ? 'text-whatsapp' : 'text-signal' }}">
                            @if($meetsMin)
                                mínimo atingido
                            @else
                                faltam {{ 100 - $progress }}%
                            @endif
                        </span>
                    @endif
                </div>
            </div>
            <span class="bg-accent text-white rounded-full w-7 h-7 flex items-center justify-center text-[12px] font-bold font-mono flex-shrink-0" data-cart-count>
                {{ $count }}
            </span>
            <svg class="w-4 h-4 text-white/60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
        </div>

        {{-- Mini progress bar --}}
        @if($stateUf)
            <div class="h-[3px] bg-white/10 relative">
                <div class="absolute inset-y-0 left-0 {{ $meetsMin ? 'bg-whatsapp' : 'bg-signal' }} transition-all duration-500"
                     data-cart-progress-bar
                     style="width: {{ $progress }}%"></div>
            </div>
        @endif
    </a>
</div>
