@props(['product'])

<article class="p-card">
    <a href="{{ route('site.product', $product->slug) }}" class="p-card__img block">
        <span class="p-card__sku">{{ $product->sku }}</span>
        @if($product->main_image)
            <img src="{{ asset('storage/'.$product->main_image) }}"
                 alt="{{ $product->name }}" loading="lazy" decoding="async">
        @else
            <span class="opacity-50">{{ $product->brand?->name }}</span>
        @endif
    </a>

    <div class="p-card__body">
        <h3 class="p-card__title">
            <a href="{{ route('site.product', $product->slug) }}" class="hover:text-accent transition">{{ $product->name }}</a>
        </h3>

        @php $motos = $product->fitment_chips; @endphp
        <div class="p-card__foot">
        @if($motos)
            <div class="p-card__fit" title="Compatível com: {{ implode(' · ', $motos) }}">
                <svg class="p-card__fit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="5.5" cy="17" r="3.5"/><circle cx="18.5" cy="17" r="3.5"/>
                    <path d="M5.5 17h5l4-7h-3M14.5 10l2-3h3M12 10l3 7"/>
                </svg>
                @foreach(array_slice($motos, 0, 2) as $m)
                    <span class="p-card__fit-chip">{{ $m }}</span>
                @endforeach
                @if(count($motos) > 2)
                    <span class="p-card__fit-more">+{{ count($motos) - 2 }}</span>
                @endif
            </div>
        @endif

        <button type="button"
                onclick="TMAC.addToQuote({{ $product->id }}, 1, { sku: '{{ $product->sku }}' })"
                class="p-card__add">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Adicionar
        </button>
        </div>
    </div>
</article>
