@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4"
         x-data="{ filtersOpen: false }">

    {{-- Breadcrumb --}}
    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">Produtos</span>
    </nav>

    {{-- Section header --}}
    <div class="mt-6">
        <x-site.eyebrow text="Catálogo"/>
        <h1 class="section-title mt-1.5">Peças e acessórios.</h1>
        <p class="section-sub">
            @if($q)Resultados para <strong class="text-ink">"{{ $q }}"</strong> · @endif
            {{ $products->total() }} {{ $products->total() === 1 ? 'item' : 'itens' }} · atualizado hoje
        </p>
    </div>

    {{-- Barra superior: busca + (mobile) abrir filtros --}}
    <div class="mt-5 flex gap-2">
        <form method="GET" class="search-bar flex-1">
            <svg class="w-[18px] h-[18px] text-ink-faint" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="q" value="{{ $q }}" placeholder="SKU, nome ou part number…">
            {{-- Preserva filtros ativos --}}
            @foreach($selectedCats as $c)
                <input type="hidden" name="cat[]" value="{{ $c }}">
            @endforeach
            @foreach($selectedBrands as $b)
                <input type="hidden" name="brand[]" value="{{ $b }}">
            @endforeach
            @foreach($selectedTmac as $t)
                <input type="hidden" name="tmac[]" value="{{ $t }}">
            @endforeach
            @if($selectedMake)<input type="hidden" name="moto" value="{{ $selectedMake }}">@endif
            @if($selectedModel)<input type="hidden" name="modelo" value="{{ $selectedModel }}">@endif
            @if($selectedYear)<input type="hidden" name="ano" value="{{ $selectedYear }}">@endif
            <button class="btn-dark btn-sm shrink-0">Buscar</button>
        </form>

        {{-- Botão "Filtros" — só mobile/tablet --}}
        <button type="button"
                @click="filtersOpen = true"
                class="lg:hidden inline-flex items-center gap-2 h-12 px-4 rounded-lg bg-ink text-white text-[13px] font-semibold border border-ink hover:bg-ink/90 transition shrink-0"
                aria-label="Abrir filtros">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
            Filtros
            @php $activeCount = count($selectedCats) + count($selectedBrands); @endphp
            @if($activeCount > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-accent text-[10px] font-bold">{{ $activeCount }}</span>
            @endif
        </button>
    </div>

    {{-- Chips ativos (rápida visualização do que está filtrado) --}}
    @if(!empty($selectedCats) || !empty($selectedBrands) || !empty($selectedTmac) || $selectedMake)
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">Filtros ativos:</span>

            {{-- Chip da moto --}}
            @if($selectedMake)
                @php
                    $mkName = $makes->firstWhere('slug', $selectedMake)?->name ?? $selectedMake;
                    $mdName = $selectedModel ? ($models->firstWhere('slug', $selectedModel)?->name ?? $selectedModel) : null;
                    $motoLabel = $mkName . ($mdName ? " {$mdName}" : '') . ($selectedYear ? " {$selectedYear}" : '');
                    $urlSemMoto = array_filter([
                        'q' => $q ?: null,
                        'cat' => $selectedCats,
                        'brand' => $selectedBrands,
                        'tmac' => $selectedTmac,
                    ]);
                @endphp
                <a href="{{ route('site.products', $urlSemMoto) }}" class="filter-chip filter-chip--moto">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h5l1 4M5.5 17.5 9 9h6l3.5 8.5M9 9 7 6H4"/>
                    </svg>
                    {{ $motoLabel }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </a>
            @endif

            @php
                // Mapa slug → nome (procura nas raízes e nas subcategorias)
                $catNameMap = [];
                foreach ($categoryTree as $root) {
                    $catNameMap[$root->slug] = $root->name;
                    foreach ($root->activeChildren as $sub) {
                        $catNameMap[$sub->slug] = $sub->name;
                    }
                }
                $brandNameMap = $brands->pluck('name', 'slug');
                $tmacNameMap = $tmacBrands->keyBy('slug');
            @endphp

            @foreach($selectedTmac as $slug)
                @php
                    $remaining = array_values(array_diff($selectedTmac, [$slug]));
                    $url = route('site.products', array_filter([
                        'q' => $q ?: null,
                        'cat' => $selectedCats,
                        'brand' => $selectedBrands,
                        'tmac' => $remaining,
                        'moto' => $selectedMake, 'modelo' => $selectedModel, 'ano' => $selectedYear,
                    ]));
                    $tb = $tmacNameMap[$slug] ?? null;
                    $bg = $tb?->resolved_brand_color ?? '#1A1B1F';
                @endphp
                <a href="{{ $url }}" class="filter-chip text-white border-0" style="background-color: {{ $bg }};">
                    {{ $tb?->name ?? $slug }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </a>
            @endforeach

            @foreach($selectedCats as $slug)
                @php
                    $remaining = array_values(array_diff($selectedCats, [$slug]));
                    $url = route('site.products', array_filter([
                        'q' => $q ?: null,
                        'cat' => $remaining,
                        'brand' => $selectedBrands,
                        'tmac' => $selectedTmac,
                        'moto' => $selectedMake, 'modelo' => $selectedModel, 'ano' => $selectedYear,
                    ]));
                @endphp
                <a href="{{ $url }}" class="filter-chip filter-chip--cat">
                    {{ $catNameMap[$slug] ?? $slug }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </a>
            @endforeach

            @foreach($selectedBrands as $slug)
                @php
                    $remaining = array_values(array_diff($selectedBrands, [$slug]));
                    $url = route('site.products', array_filter([
                        'q' => $q ?: null,
                        'cat' => $selectedCats,
                        'brand' => $remaining,
                        'tmac' => $selectedTmac,
                        'moto' => $selectedMake, 'modelo' => $selectedModel, 'ano' => $selectedYear,
                    ]));
                @endphp
                <a href="{{ $url }}" class="filter-chip filter-chip--brand">
                    {{ $brandNameMap[$slug] ?? $slug }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </a>
            @endforeach

            <a href="{{ route('site.products', $q ? ['q' => $q] : []) }}"
               class="font-mono text-[10px] uppercase tracking-[0.06em] text-accent hover:underline ml-1">
                Limpar tudo
            </a>
        </div>
    @endif

    {{-- ═══ BARRA HORIZONTAL: moto + Universo TMAC ═══ --}}
    <x-site.product-filters-bar
        :tmac-brands="$tmacBrands"
        :selected-tmac="$selectedTmac"
        :makes="$makes"
        :models="$models"
        :years="$years"
        :selected-make="$selectedMake"
        :selected-model="$selectedModel"
        :selected-year="$selectedYear"
        :selected-cats="$selectedCats"
        :selected-brands="$selectedBrands"
        :q="$q"
    />

    {{-- Quote bar quando há itens --}}
    <livewire:site.quote-cart-summary />

    {{-- ═══════ LAYOUT 2 COLUNAS: sidebar + grid ═══════ --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-[260px,1fr] gap-6">

        {{-- ── Sidebar desktop ── --}}
        <aside class="hidden lg:block">
            <div class="sticky top-[88px]">
                <x-site.product-filters
                    :category-tree="$categoryTree"
                    :brands="$brands"
                    :selected-cats="$selectedCats"
                    :selected-brands="$selectedBrands"
                    :selected-tmac="$selectedTmac"
                    :selected-make="$selectedMake"
                    :selected-model="$selectedModel"
                    :selected-year="$selectedYear"
                    :q="$q"
                />
            </div>
        </aside>

        {{-- ── Grid de produtos ── --}}
        <div>
            @if($products->isEmpty())
                <div class="p-8 text-center border border-dashed border-line rounded-xl bg-bg-elev">
                    <div class="font-display font-bold text-lg text-ink mb-1.5">Nenhuma peça encontrada</div>
                    <p class="text-sm text-ink-soft leading-relaxed max-w-md mx-auto">
                        Não encontramos peças no catálogo com esses critérios. Solicite cotação que buscamos pra você.
                    </p>
                    <a href="{{ route('site.quote') }}" class="btn-primary mt-4 inline-flex">Solicitar cotação</a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2.5">
                    @foreach($products as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ═══════ DRAWER MOBILE DE FILTROS (teleported pra body) ═══════ --}}
    <template x-teleport="body">
        <div x-show="filtersOpen" x-cloak
             @keydown.escape.window="filtersOpen = false"
             class="fixed inset-0 z-[60] lg:hidden">
            {{-- Backdrop --}}
            <div @click="filtersOpen = false"
                 x-transition:enter="transition-opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Painel: bottom sheet em mobile (vem de baixo) --}}
            <aside x-transition:enter="transition-transform ease-out duration-300"
                   x-transition:enter-start="translate-y-full"
                   x-transition:enter-end="translate-y-0"
                   x-transition:leave="transition-transform ease-in duration-200"
                   x-transition:leave-start="translate-y-0"
                   x-transition:leave-end="translate-y-full"
                   class="absolute left-0 right-0 bottom-0 max-h-[92vh] bg-bg rounded-t-2xl shadow-2xl flex flex-col overflow-hidden">

                {{-- Handle visual --}}
                <div class="pt-3 pb-1 flex justify-center">
                    <span class="w-12 h-1.5 bg-line rounded-full"></span>
                </div>

                {{-- Header drawer --}}
                <div class="flex items-center justify-between px-5 pb-3 border-b border-line">
                    <div>
                        <div class="font-display font-extrabold text-[18px] tracking-tightish text-ink leading-none">Filtros</div>
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint mt-1">
                            {{ $products->total() }} itens disponíveis
                        </div>
                    </div>
                    <button @click="filtersOpen = false" class="icon-btn" aria-label="Fechar filtros">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                {{-- Conteúdo scrollable --}}
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <x-site.product-filters
                        :category-tree="$categoryTree"
                        :brands="$brands"
                        :selected-cats="$selectedCats"
                        :selected-brands="$selectedBrands"
                        :q="$q"
                    />
                </div>
            </aside>
        </div>
    </template>

</section>
@endsection
