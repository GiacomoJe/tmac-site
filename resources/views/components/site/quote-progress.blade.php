@props([
    'percent' => 0,           // 0-100 (já saturado pelo servidor)
    'state' => null,          // App\Models\State|null
    'meetsMin' => false,      // bool
    'variant' => 'default',   // default | dark | mini
    'showLabel' => true,
    'showChangeState' => false, // mostra botão "trocar estado"
])
@php
    $isMini  = $variant === 'mini';
    $isDark  = $variant === 'dark';

    $heightCls = $isMini ? 'h-1.5' : 'h-2.5';
    $labelTopCls = $isDark
        ? 'text-white/70'
        : 'text-ink-faint';
    $labelStrongCls = $isDark
        ? 'text-white'
        : 'text-ink';
    $trackCls = $isDark
        ? 'bg-white/10'
        : 'bg-bg-sunken';

    // Cor da barra: signal (azul) enquanto não atinge; whatsapp (verde) quando atinge
    $fillCls = $meetsMin
        ? 'bg-whatsapp'
        : 'bg-signal';
@endphp

<div {{ $attributes->merge(['class' => 'quote-progress']) }}>
    @if($showLabel)
        <div class="flex items-center justify-between gap-3 mb-1.5">
            <div class="flex items-center gap-2 min-w-0">
                @if($state)
                    <span class="inline-flex items-center justify-center {{ $isMini ? 'w-6 h-6 text-[10px]' : 'w-7 h-7 text-[11px]' }} rounded-md bg-signal text-white font-display font-black tracking-tightest flex-shrink-0">
                        {{ $state->uf }}
                    </span>
                    <div class="min-w-0 flex flex-col leading-tight">
                        <span class="font-mono text-[9px] uppercase tracking-[0.10em] {{ $labelTopCls }}">
                            Mínimo de cotação · {{ $state->uf }}
                        </span>
                        <span class="font-semibold text-[12px] {{ $labelStrongCls }} truncate">
                            @if($meetsMin)
                                Mínimo atingido — você já pode cotar
                            @else
                                Continue adicionando para liberar a cotação
                            @endif
                        </span>
                    </div>
                @else
                    <div class="flex flex-col leading-tight">
                        <span class="font-mono text-[10px] uppercase tracking-[0.10em] {{ $labelTopCls }}">
                            Selecione um estado
                        </span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="font-mono text-[11px] font-bold tracking-[0.04em] {{ $meetsMin ? 'text-whatsapp' : ($isDark ? 'text-signal' : 'text-signal') }}">
                    {{ $percent }}%
                </span>
                @if($showChangeState && $state)
                    <button type="button"
                            x-data
                            @click="$dispatch('quote-cart:open-state-event'); $wire?.dispatch?.('quote-cart:open-state')"
                            onclick="if(window.Livewire) window.Livewire.dispatch('quote-cart:open-state');"
                            class="font-mono text-[10px] uppercase tracking-[0.08em] {{ $isDark ? 'text-white/55 hover:text-white' : 'text-ink-faint hover:text-signal' }} underline-offset-2 hover:underline transition">
                        trocar
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Trilha + preenchimento --}}
    <div class="relative {{ $heightCls }} {{ $trackCls }} rounded-full overflow-hidden">
        <div class="absolute inset-y-0 left-0 {{ $fillCls }} rounded-full transition-all duration-500 ease-out"
             style="width: {{ $percent }}%">
            {{-- Shimmer interno quando ainda não atingiu --}}
            @if(!$meetsMin && $percent > 0)
                <div class="absolute inset-0 opacity-50 quote-progress__shine"></div>
            @endif
        </div>
        {{-- Marcador 100% (linha vertical sutil indicando a meta) --}}
        <div class="absolute top-0 bottom-0 right-0 w-px {{ $isDark ? 'bg-white/30' : 'bg-ink/15' }}"></div>
    </div>
</div>
