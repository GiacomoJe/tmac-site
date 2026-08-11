<div>
    @if($count > 0)
        <div class="quote-bar mt-4">
            <div class="quote-bar__count">{{ $count }}</div>
            <div class="flex-1">
                <div class="quote-bar__label">
                    {{ $count === 1 ? 'item adicionado à sua cotação' : 'itens adicionados à sua cotação' }}
                </div>
            </div>
            <a href="{{ route('site.quote') }}" class="quote-bar__cta">
                Cotar
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endif
</div>
