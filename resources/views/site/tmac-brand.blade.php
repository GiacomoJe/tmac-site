@extends('layouts.site')

@section('content')

@php
    $c    = $brand->resolved_brand_color;
    $dark = $brand->darkened_color;
    $fg   = $brand->resolved_text_color;
    $isLightText = $brand->text_theme === 'light';
@endphp

<div class="brand-page" style="--c: {{ $c }}; --c-dark: {{ $dark }}; --fg: {{ $fg }};">

{{-- ═══════════════════════════════════════════════════════
     1. HERO COMPACTO
     ═══════════════════════════════════════════════════════ --}}
@php
    // Foto de fundo do hero: usa a cadastrada da marca; se não houver,
    // cai num banco de imagens locais por tema (ver storage/images/brands/hero/).
    $heroBg = $brand->image_url ?: asset('storage/images/brands/hero/default.jpg');
@endphp
<section class="brand-hero">
    <img src="{{ $heroBg }}" alt="" aria-hidden="true" fetchpriority="high"
         class="brand-hero__bg"
         onerror="this.style.display='none'">
    <div class="brand-hero__wash" aria-hidden="true"></div>
    <div class="brand-hero__texture" aria-hidden="true"></div>
    <span class="brand-hero__watermark" aria-hidden="true">{{ $brand->name }}</span>

    <div class="container-tmac relative z-10 pt-3.5">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em]"
             style="color: {{ $fg }}; opacity: .6;">
            <a href="{{ route('site.home') }}" class="hover:opacity-100 transition">Início</a>
            <span style="opacity:.5">/</span>
            <a href="{{ route('site.tmac-brands') }}" class="hover:opacity-100 transition">Universo TMAC</a>
            <span style="opacity:.5">/</span>
            <span style="opacity:1">{{ $brand->name }}</span>
        </div>
    </div>

    <div class="container-tmac relative z-10 flex-1 flex flex-col items-center justify-center text-center py-10 md:py-12">
        <div class="brand-hero__badge">
            <span class="brand-hero__badge-dot"></span>
            {{ $brand->badge_label ?? 'Linha exclusiva' }}
        </div>

        {{-- Logo grande e centralizado --}}
        @if($brand->logo_url)
            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                 class="brand-hero__logo {{ $isLightText ? 'is-light' : '' }}">
        @else
            <h1 class="brand-hero__name">{{ $brand->name }}</h1>
        @endif

        @if($brand->tagline)
            <p class="brand-hero__tagline">{{ $brand->tagline }}</p>
        @endif

        {{-- Contador de produtos --}}
        @if($productsCount > 0)
            <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="brand-hero__count">
                <span class="brand-hero__count-num">{{ $productsCount }}</span>
                <span class="brand-hero__count-label">
                    {{ $productsCount === 1 ? 'produto na linha' : 'produtos na linha' }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </span>
            </a>
        @endif
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     2. SOBRE — compacto, 2 colunas
     ═══════════════════════════════════════════════════════ --}}
@if($brand->description)
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-10 md:py-14 grid lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-4">
            <div class="brand-marker">
                <span class="brand-marker__num">01</span>
                <span class="brand-marker__label">Sobre a linha</span>
            </div>
            <h2 class="brand-why">
                Por que<br><span class="brand-why__name">{{ $brand->name }}</span>?
            </h2>
        </div>

        <div class="lg:col-span-8">
            <p class="text-[15px] md:text-[16px] leading-relaxed text-ink-soft">
                {{ $brand->description }}
            </p>

            <div class="mt-6 flex flex-wrap gap-2.5">
                <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="brand-btn brand-btn--solid">
                    Ver catálogo completo
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('site.quote') }}" class="brand-btn brand-btn--outline">
                    Solicitar cotação
                </a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     3. PRODUTOS DA LINHA — protagonista da página
     ═══════════════════════════════════════════════════════ --}}
@if($products->isNotEmpty())
<section class="bg-bg-sunken">
    <div class="container-tmac py-12 md:py-16">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-7">
            <div>
                <div class="brand-marker">
                    <span class="brand-marker__num">02</span>
                    <span class="brand-marker__label">Catálogo {{ $brand->name }}</span>
                </div>
                <h2 class="font-display font-black text-[26px] md:text-[36px] leading-[0.95] tracking-tightest text-ink mt-3 uppercase">
                    Peças da <span style="color: {{ $c }};">linha</span>
                </h2>
                <p class="mt-2 text-[14px] text-ink-soft">
                    Adicione à cotação e receba sua tabela atacadista.
                </p>
            </div>

            @if($productsCount > $products->count())
                <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="brand-btn brand-btn--outline">
                    Ver todos os {{ $productsCount }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2.5">
            @foreach($products as $product)
                <x-site.product-card :product="$product" />
            @endforeach
        </div>

        {{-- CTA de cotação abaixo do grid --}}
        <div class="brand-quote-cta">
            <div>
                <div class="font-display font-black text-[18px] md:text-[22px] tracking-tightish leading-tight">
                    Montou sua lista?
                </div>
                <p class="text-[13px] mt-1 opacity-80">
                    Finalize a cotação e receba os preços da linha {{ $brand->name }}.
                </p>
            </div>
            <a href="{{ route('site.quote') }}" class="brand-quote-cta__btn">
                Solicitar cotação
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@else
    {{-- Sem produtos vinculados ainda --}}
    <section class="bg-bg-sunken">
        <div class="container-tmac py-12 text-center">
            <p class="text-ink-soft text-[15px]">
                Os produtos da linha {{ $brand->name }} estão sendo cadastrados.
            </p>
            <a href="{{ route('site.quote') }}" class="brand-btn brand-btn--solid mt-5">
                Solicitar cotação
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>
@endif

{{-- ═══════════════════════════════════════════════════════
     4. OUTRAS MARCAS
     ═══════════════════════════════════════════════════════ --}}
@if($others->isNotEmpty())
<section class="bg-ink text-white">
    <div class="container-tmac py-10 md:py-14">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="brand-marker brand-marker--dark">
                <span class="brand-marker__num">03</span>
                <span class="brand-marker__label">Outras marcas do Universo TMAC</span>
            </div>
            <a href="{{ route('site.tmac-brands') }}"
               class="font-mono text-[10px] uppercase tracking-[0.08em] text-white/50 hover:text-white transition hidden sm:inline">
                Ver todas →
            </a>
        </div>

        <div class="brand-others">
            @foreach($others as $o)
                @php
                    $oc  = $o->resolved_brand_color;
                    $ofg = $o->resolved_text_color;
                @endphp
                <a href="{{ $o->link_url ?: route('site.tmac-brand', $o->slug) }}"
                   class="brand-others__item"
                   style="--oc: {{ $oc }}; --ofg: {{ $ofg }};">
                    <span class="brand-others__bar"></span>
                    <div class="brand-others__body">
                        @if($o->logo_url)
                            <img src="{{ $o->logo_url }}" alt="{{ $o->name }}"
                                 class="brand-others__logo {{ $o->text_theme === 'light' ? 'is-light' : '' }}">
                        @else
                            <span class="brand-others__name">{{ $o->name }}</span>
                        @endif
                        <span class="brand-others__badge">{{ $o->badge_label }}</span>
                    </div>
                    <span class="brand-others__arrow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

</div>{{-- /.brand-page --}}

@endsection
