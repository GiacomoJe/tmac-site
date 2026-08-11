<div class="bike-selector">
    {{-- Mega número decorativo no fundo --}}
    <div class="absolute -top-4 -right-2 md:-top-6 md:right-4 z-0">
        <span class="mega-num mega-num--blue">01</span>
    </div>

    <div class="bike-selector__head">
        <div class="section-marker">
            <span class="section-marker__num">01</span>
            <span class="section-marker__label">Encontre por moto</span>
        </div>

        <h3 class="mt-3 font-display font-black text-[26px] md:text-[34px] tracking-tightest leading-[0.95] text-ink uppercase">
            Qual é a <span class="text-signal">sua moto</span>?
        </h3>

        <p class="mt-2 text-[13px] md:text-[14px] text-ink-soft max-w-[44ch]">
            Filtre as peças compatíveis com seu modelo em segundos.
        </p>

        {{-- Tick row decorativo (velocímetro) --}}
        <div class="tick-row mt-3">
            @for($t = 0; $t < 14; $t++)
                @php
                    $cls = match(true) {
                        $t < 6 => 'tick-row__tick tick-row__tick--on',
                        $t === 6 => 'tick-row__tick tick-row__tick--peak',
                        default => 'tick-row__tick',
                    };
                @endphp
                <span class="{{ $cls }}"></span>
            @endfor
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        {{-- Marca --}}
        <label class="bike-selector__field">
            <span class="bike-selector__label">Marca</span>
            <select wire:model.live="makeSlug" class="bike-selector__select">
                <option value="">Selecione</option>
                @foreach($makes as $make)
                    <option value="{{ $make->slug }}">{{ $make->name }}</option>
                @endforeach
            </select>
        </label>

        {{-- Modelo --}}
        <label class="bike-selector__field">
            <span class="bike-selector__label">Modelo</span>
            <select wire:model.live="modelSlug" class="bike-selector__select" @if(!$makeSlug) disabled @endif>
                <option value="">{{ $makeSlug ? 'Selecione o modelo' : 'Escolha a marca antes' }}</option>
                @foreach($models as $model)
                    <option value="{{ $model->slug }}">{{ $model->name }}</option>
                @endforeach
            </select>
        </label>

        {{-- Ano --}}
        <label class="bike-selector__field">
            <span class="bike-selector__label">Ano</span>
            <select wire:model.live="year" class="bike-selector__select" @if(!$modelSlug) disabled @endif>
                <option value="">{{ $modelSlug ? 'Qualquer ano' : 'Escolha o modelo antes' }}</option>
                @foreach($years as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </label>

        {{-- CTA azul --}}
        <button type="button" wire:click="findParts"
                @if(!$makeSlug) disabled @endif
                class="btn-signal btn-block h-12 font-bold uppercase tracking-[0.04em] text-[13px] disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="findParts" class="flex items-center gap-2">
                Ver peças
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </span>
            <span wire:loading wire:target="findParts">Buscando…</span>
        </button>
    </div>

    {{-- Rodapé com spec strip (info de painel) --}}
    <div class="mt-5 pt-4 border-t border-line-soft flex items-center justify-between flex-wrap gap-3 relative z-10">
        <div class="spec-strip">
            <span class="spec-strip__val">+150</span>
            <span>MODELOS</span>
            <span class="spec-strip__sep"></span>
            <span class="spec-strip__val">12K+</span>
            <span>PEÇAS</span>
            <span class="spec-strip__sep"></span>
            <span class="spec-strip__val">26</span>
            <span>ESTADOS</span>
        </div>
        <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-signal rounded-full" style="animation: tmac-pulse 1.8s ease-in-out infinite"></span>
                Compatibilidade verificada
            </span>
        </div>
    </div>
</div>
