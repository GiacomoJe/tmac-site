<a href="{{ route('site.quote') }}"
   class="quote-cart {{ $count > 0 ? 'quote-cart--has-items' : '' }} {{ $meetsMin && $count > 0 ? 'quote-cart--ready' : '' }}"
   data-cart-root
   aria-label="Cotação ({{ $count }} {{ $count === 1 ? 'item' : 'itens' }}){{ $meetsMin && $count > 0 ? ' — pronta para enviar' : '' }}">
    <span class="quote-cart__icon">
        {{-- Carrinho (padrão) --}}
        <svg class="quote-cart__icon-cart w-[19px] h-[19px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M3 4h2l2.5 12h11L21 7H6"/><circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/>
        </svg>
        {{-- Check (quando atinge o mínimo) --}}
        <svg class="quote-cart__icon-check w-[19px] h-[19px]" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M5 12l5 5L20 7"/>
        </svg>
    </span>
    <span class="quote-cart__label">
        <span class="quote-cart__label-top">
            @if($stateUf)
                Cotação · <span class="text-white font-bold">{{ $stateUf }}</span>
            @else
                Cotação
            @endif
        </span>
        <span class="quote-cart__label-bot">
            <span data-cart-items-label>
                @if($count > 0)
                    {{ $count }} {{ $count === 1 ? 'item' : 'itens' }}
                @else
                    vazia
                @endif
            </span>
            @if($stateUf)
                <span class="ml-1 opacity-70" data-cart-pct-wrap>· <span data-cart-progress-label-pct>{{ $progress }}%</span></span>
                <span class="quote-cart__ready-tag" data-cart-ready-tag>· liberado</span>
            @endif
        </span>
    </span>
    <span class="quote-cart__badge {{ $count === 0 ? 'hidden' : '' }}" data-cart-count>{{ $count }}</span>

    {{-- Mini progress bar (só quando tem itens + UF) --}}
    @if($stateUf)
        <span class="quote-cart__progress {{ $count === 0 ? 'hidden' : '' }}">
            <span class="quote-cart__progress-fill {{ $meetsMin ? 'is-met' : '' }}"
                  data-cart-progress-bar
                  style="width: {{ $progress }}%"></span>
        </span>
    @endif
</a>
