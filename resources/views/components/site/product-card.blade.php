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
        @if($product->brand)
            <div class="p-card__brand">{{ $product->brand->name }}</div>
        @endif
        <h3 class="p-card__title">
            <a href="{{ route('site.product', $product->slug) }}" class="hover:text-accent transition">{{ $product->name }}</a>
        </h3>
        <div class="p-card__meta">
            <span class="p-card__ref">{{ $product->short_description ? Str::limit($product->short_description, 22) : 'SKU '.$product->sku }}</span>
            <button type="button"
                    onclick="TMAC.addToQuote({{ $product->id }}, 1, { sku: '{{ $product->sku }}' })"
                    class="p-card__add" aria-label="Adicionar à cotação">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            </button>
        </div>
    </div>
</article>
