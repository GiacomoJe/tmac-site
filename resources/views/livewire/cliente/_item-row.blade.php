@php
    $codJs = addslashes($item->cod);
    $bloqueado = (bool) $item->bloqueado;
    $total = $qtd > 0 ? round($item->valorFinal($desconto) * $qtd, 2) : 0;
    $valorFinal = $item->valorFinal($desconto);
    $badgeClass = match (true) {
        $item->promo === 'PROMOÇÃO' => 'bg-accent text-white',
        $item->promo === 'PROMOÇÃO EXTRA' => 'bg-warn text-ink',
        (bool) $item->tag => 'bg-signal text-white',
        default => 'bg-bg-sunken text-ink-faint',
    };
    $badgeLabel = $item->promo ?: ($item->tag ?: '—');
@endphp

{{-- Desktop --}}
<div class="hidden md:grid grid-cols-12 gap-2 px-3 py-2.5 items-center text-sm {{ $qtd > 0 ? 'bg-signal-soft/40' : '' }}" wire:key="row-d-{{ $item->id }}">
    <div class="col-span-1 font-mono text-xs font-bold truncate" title="{{ $item->cod }}">{{ $item->cod }}</div>
    <div class="col-span-3 truncate" title="{{ $item->descricao }}">{{ $item->descricao }}</div>
    <div class="col-span-1 text-xs text-ink-soft truncate">{{ $item->marca }}</div>
    <div class="col-span-1">
        <span class="inline-block text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $badgeClass }}">{{ $badgeLabel }}</span>
        @if ($bloqueado)
            <div class="text-[10px] font-bold text-accent mt-0.5">{{ $item->status }}</div>
        @endif
    </div>
    <div class="col-span-1 text-right {{ $item->promo ? 'text-accent-dark font-bold' : '' }}">
        {{ number_format($item->preco, 2, ',', '.') }}
    </div>
    <div class="col-span-1 text-center">
        <input type="number" min="0" max="100" step="1"
               value="{{ $desconto > 0 ? rtrim(rtrim(number_format($desconto, 2, '.', ''), '0'), '.') : '' }}"
               @disabled($item->promo)
               wire:change="setDesconto('{{ $codJs }}', $event.target.value)"
               class="w-16 text-center rounded-sm border border-line px-1 py-1 text-sm disabled:bg-bg-sunken disabled:text-ink-faint">
    </div>
    <div class="col-span-1 text-center">
        <input type="number" min="0" step="1" value="{{ $qtd > 0 ? $qtd : '' }}"
               wire:change="setQtd('{{ $codJs }}', $event.target.value)"
               class="w-16 text-center rounded-sm border border-line px-1 py-1 text-sm font-bold text-signal">
    </div>
    <div class="col-span-1 text-right">{{ number_format($valorFinal, 2, ',', '.') }}</div>
    <div class="col-span-2 text-right font-bold {{ $bloqueado ? 'text-ink-faint line-through' : 'text-signal' }}">
        {{ $total > 0 ? number_format($total, 2, ',', '.') : '—' }}
    </div>
</div>

{{-- Mobile --}}
<div class="md:hidden px-3 py-3 {{ $qtd > 0 ? 'bg-signal-soft/40' : '' }}" wire:key="row-m-{{ $item->id }}">
    <div class="flex items-start justify-between gap-2 mb-1">
        <div class="min-w-0">
            <div class="font-mono text-xs font-bold text-ink-soft">{{ $item->cod }}</div>
            <div class="text-sm font-medium leading-snug">{{ $item->descricao }}</div>
            <div class="text-xs text-ink-faint mt-0.5">{{ $item->marca }} @if($item->grupo) · {{ $item->grupo }} @endif</div>
        </div>
        <span class="shrink-0 text-[10px] font-bold uppercase rounded-full px-2 py-0.5 {{ $badgeClass }}">{{ $badgeLabel }}</span>
    </div>

    @if ($bloqueado)
        <div class="text-[11px] font-bold text-accent mb-1">{{ $item->status }} — não entra no total</div>
    @endif

    <div class="flex items-end gap-2 mt-2">
        <div class="text-xs text-ink-faint">
            <div class="{{ $item->promo ? 'text-accent-dark font-bold' : '' }}">R$ {{ number_format($item->preco, 2, ',', '.') }}</div>
            <div class="uppercase font-bold tracking-mono-up">Tabela</div>
        </div>

        <div class="w-16">
            <input type="number" min="0" max="100" step="1"
                   value="{{ $desconto > 0 ? rtrim(rtrim(number_format($desconto, 2, '.', ''), '0'), '.') : '' }}"
                   @disabled($item->promo)
                   wire:change="setDesconto('{{ $codJs }}', $event.target.value)"
                   placeholder="0%"
                   class="w-full text-center rounded-sm border border-line px-1 py-2 text-sm disabled:bg-bg-sunken disabled:text-ink-faint">
            <div class="text-[9px] text-center text-ink-faint uppercase font-bold mt-0.5">Desc.</div>
        </div>

        <div class="w-16">
            <input type="number" min="0" step="1" value="{{ $qtd > 0 ? $qtd : '' }}"
                   wire:change="setQtd('{{ $codJs }}', $event.target.value)"
                   placeholder="0"
                   class="w-full text-center rounded-sm border border-line px-1 py-2 text-sm font-bold text-signal">
            <div class="text-[9px] text-center text-ink-faint uppercase font-bold mt-0.5">Qtd.</div>
        </div>

        <div class="flex-1 text-right">
            <div class="font-bold {{ $bloqueado ? 'text-ink-faint line-through' : 'text-signal' }}">
                {{ $total > 0 ? 'R$ '.number_format($total, 2, ',', '.') : '—' }}
            </div>
            <div class="text-[9px] text-ink-faint uppercase font-bold">Total</div>
        </div>
    </div>
</div>
