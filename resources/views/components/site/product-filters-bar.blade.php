@props([
    'tmacBrands' => null,
    'selectedTmac' => [],
    'makes' => null,
    'models' => null,
    'years' => null,
    'selectedMake' => null,
    'selectedModel' => null,
    'selectedYear' => null,
    // Preservados (vêm da sidebar)
    'selectedCats' => [],
    'selectedBrands' => [],
    'q' => '',
])

<div class="filters-bar">

    {{-- ─── Seletor de moto ─── --}}
    @if($makes && $makes->isNotEmpty())
        <form method="GET" action="{{ route('site.products') }}" class="filters-bar__moto"
              x-data x-on:change="$el.submit()">
            {{-- Preserva demais filtros --}}
            @if($q)<input type="hidden" name="q" value="{{ $q }}">@endif
            @foreach($selectedCats as $c)<input type="hidden" name="cat[]" value="{{ $c }}">@endforeach
            @foreach($selectedBrands as $b)<input type="hidden" name="brand[]" value="{{ $b }}">@endforeach
            @foreach($selectedTmac as $t)<input type="hidden" name="tmac[]" value="{{ $t }}">@endforeach

            <span class="filters-bar__label">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h5l1 4M5.5 17.5 9 9h6l3.5 8.5M9 9 7 6H4"/>
                </svg>
                Minha moto
            </span>

            <select name="moto" class="filters-bar__select">
                <option value="">Marca…</option>
                @foreach($makes as $mk)
                    <option value="{{ $mk->slug }}" @selected($selectedMake === $mk->slug)>{{ $mk->name }}</option>
                @endforeach
            </select>

            <select name="modelo" class="filters-bar__select"
                    @disabled(!$selectedMake || !$models || $models->isEmpty())>
                <option value="">{{ $selectedMake ? 'Modelo…' : 'Modelo' }}</option>
                @if($models)
                    @foreach($models as $md)
                        <option value="{{ $md->slug }}" @selected($selectedModel === $md->slug)>{{ $md->name }}</option>
                    @endforeach
                @endif
            </select>

            <select name="ano" class="filters-bar__select filters-bar__select--sm"
                    @disabled(!$selectedModel || !$years || $years->isEmpty())>
                <option value="">Ano</option>
                @if($years)
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" @selected((int) $selectedYear === (int) $yr)>{{ $yr }}</option>
                    @endforeach
                @endif
            </select>

            @if($selectedMake)
                @php
                    $clearMoto = array_filter([
                        'q' => $q ?: null,
                        'cat' => $selectedCats,
                        'brand' => $selectedBrands,
                        'tmac' => $selectedTmac,
                    ]);
                @endphp
                <a href="{{ route('site.products', $clearMoto) }}" class="filters-bar__clear" aria-label="Limpar moto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </a>
            @endif
        </form>
    @endif

    {{-- ─── Universo TMAC (chips) ─── --}}
    @if($tmacBrands && $tmacBrands->isNotEmpty())
        <div class="filters-bar__tmac">
            <span class="filters-bar__label">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 2l2.5 5 5.5 1-4 4 1 5.5L12 15l-5 2.5 1-5.5-4-4 5.5-1z"/>
                </svg>
                Universo TMAC
            </span>

            <div class="filters-bar__chips">
                @foreach($tmacBrands as $tb)
                    @php
                        $isOn = in_array($tb->slug, $selectedTmac);
                        $novo = $isOn
                            ? array_values(array_diff($selectedTmac, [$tb->slug]))
                            : array_values(array_merge($selectedTmac, [$tb->slug]));
                        $url = route('site.products', array_filter([
                            'q' => $q ?: null,
                            'cat' => $selectedCats,
                            'brand' => $selectedBrands,
                            'tmac' => $novo,
                            'moto' => $selectedMake,
                            'modelo' => $selectedModel,
                            'ano' => $selectedYear,
                        ]));
                    @endphp
                    <a href="{{ $url }}"
                       class="tmac-chip {{ $isOn ? 'is-on' : '' }}"
                       style="--c: {{ $tb->resolved_brand_color }}; --fg: {{ $tb->resolved_text_color }};">
                        <span class="tmac-chip__dot"></span>
                        {{ $tb->name }}
                        @if($isOn)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
