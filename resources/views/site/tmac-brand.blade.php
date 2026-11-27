@extends('layouts.site')

@section('content')

@php
    $c    = $brand->resolved_brand_color;
    $dark = $brand->darkened_color;
    $fg   = $brand->resolved_text_color;
    $isLightText = $brand->text_theme === 'light';
@endphp

<div class="brand-page" style="--c: {{ $c }}; --c-dark: {{ $dark }}; --fg: {{ $fg }};">

@php
    // Textos da página (Painel → Universo TMAC → editar → "Página da marca").
    $heroTitle    = $brand->content('hero_title');
    // O logo já mostra o nome da marca: remove o "MARCA:" / "MARCA –" do início do título.
    if ($heroTitle) {
        $semMarca = preg_replace('/^\s*'.preg_quote($brand->name, '/').'\s*[:\-–—|]\s*/iu', '', $heroTitle);
        if ($semMarca !== '' && $semMarca !== $heroTitle) {
            $heroTitle = mb_strtoupper(mb_substr($semMarca, 0, 1)).mb_substr($semMarca, 1);
        }
    }
    $heroSubtitle = $brand->content('hero_subtitle', $heroTitle ? null : $brand->tagline);
    $heroCta      = $brand->content('hero_cta', "Solicitar cotação da linha {$brand->name}");
    $aboutTitle   = $brand->content('about_title');
    $aboutText    = $brand->content('about_text', $brand->description);
    $famTitle     = $brand->content('families_title');
    $famIntro     = $brand->content('families_intro');
    $families     = array_values(array_filter((array) $brand->content('families', [])));
    $hlTitle      = $brand->content('highlight_title');
    $hlText       = $brand->content('highlight_text');
    $diffsTitle   = $brand->content('diffs_title');
    $diffsIntro   = $brand->content('diffs_intro');
    $diffs        = array_values(array_filter((array) $brand->content('diffs', []), fn ($d) => ! empty($d['title'])));
    $ctaTitle     = $brand->content('cta_title', "Abasteça sua loja com a linha {$brand->name}.");
    $ctaText      = $brand->content('cta_text', 'Atendimento exclusivo para empresas com CNPJ ativo em todo o Brasil. Monte sua cotação e receba sua tabela personalizada.');
    $ctaPrimary   = $brand->content('cta_primary', 'Solicitar cotação agora');
    $ctaSecondary = $brand->content('cta_secondary', 'Seja distribuidor');

    // Parágrafos: separados por linha em branco no painel
    $paras = fn (?string $t) => $t ? array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', $t)))) : [];

    $sec = 0; // numeração dos marcadores de seção
@endphp

{{-- ═══════════════════════════════════════════════════════
     1. HERO COMPACTO
     ═══════════════════════════════════════════════════════ --}}
@php
    // Foto de fundo do hero: usa a cadastrada da marca; se não houver,
    // cai num banco de imagens locais por tema (ver public/images/brands/hero/).
    $heroBg = $brand->image_url ?: asset('images/brands/hero/default.jpg');
@endphp
<section class="brand-hero">
    <img src="{{ $heroBg }}" alt="" aria-hidden="true" fetchpriority="high"
         class="brand-hero__bg"
         onerror="this.style.display='none'">
    <div class="brand-hero__wash" aria-hidden="true"></div>
    <div class="brand-hero__texture" aria-hidden="true"></div>

    <div class="container-tmac relative z-10 pt-3.5">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em]"
             style="color: {{ $fg }}; opacity: .6;">
            <a href="{{ route('site.home') }}" class="hover:opacity-100 transition">Início</a>
            <span style="opacity:.5">/</span>
            <a href="{{ route('site.tmac-brands') }}" class="hover:opacity-100 transition">Universo TMAC</a>
            @if($brand->parent)
                <span style="opacity:.5">/</span>
                <a href="{{ route('site.tmac-brand', $brand->parent->slug) }}" class="hover:opacity-100 transition">{{ $brand->parent->name }}</a>
            @endif
            <span style="opacity:.5">/</span>
            <span style="opacity:1">{{ $brand->name }}</span>
        </div>
    </div>

    <div class="container-tmac relative z-10 flex-1 flex flex-col items-center justify-center text-center py-6 md:py-8">
        <div class="brand-hero__badge">
            <span class="brand-hero__badge-dot"></span>
            {{ $brand->badge_label ?? 'Linha exclusiva' }}
        </div>

        {{-- Logo grande e centralizado --}}
        @if($brand->logo_url)
            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                 class="brand-hero__logo {{ $brand->logo_invert ? 'is-light' : '' }}">
        @elseif(! $heroTitle)
            <h1 class="brand-hero__name">{{ $brand->name }}</h1>
        @else
            <span class="brand-hero__name" aria-hidden="true">{{ $brand->name }}</span>
        @endif

        @if($heroTitle)
            <h1 class="brand-hero__title">{{ $heroTitle }}</h1>
        @endif

        @if($heroSubtitle)
            <p class="{{ $heroTitle ? 'brand-hero__sub' : 'brand-hero__tagline' }}">{{ $heroSubtitle }}</p>
        @endif

        <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
            <a href="{{ route('site.quote') }}" class="brand-hero__cta"
               onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'brand_hero',brand:'{{ $brand->slug }}'})">
                {{ $heroCta }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>

            {{-- Contador de produtos --}}
            @if($productsCount > 0)
                <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="brand-hero__count !mt-0">
                    <span class="brand-hero__count-num">{{ $productsCount }}</span>
                    <span class="brand-hero__count-label">
                        {{ $productsCount === 1 ? 'produto na linha' : 'produtos na linha' }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     2. PROPOSTA — título + texto (+ submarcas, ex.: LBJ Premium)
     ═══════════════════════════════════════════════════════ --}}
@if($aboutText)
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-10 md:py-14 grid lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-5">
            <div class="brand-marker">
                <span class="brand-marker__num">{{ sprintf('%02d', ++$sec) }}</span>
                <span class="brand-marker__label">Proposta e foco de aplicação</span>
            </div>
            @if($aboutTitle)
                <h2 class="brand-section-title">{{ $aboutTitle }}</h2>
            @else
                <h2 class="brand-why">
                    Por que<br><span class="brand-why__name">{{ $brand->name }}</span>?
                </h2>
            @endif
        </div>

        <div class="lg:col-span-7 space-y-4">
            @foreach($paras($aboutText) as $p)
                <p class="text-[15px] md:text-[16px] leading-relaxed text-ink-soft">{{ $p }}</p>
            @endforeach

            @if($subBrands->isNotEmpty())
                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($subBrands as $sb)
                        <a href="{{ route('site.tmac-brand', $sb->slug) }}" class="brand-btn brand-btn--outline">
                            Conhecer {{ $sb->name }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     2B. BOX "O QUE VOCÊ ENCONTRA" — famílias com atalho para a busca
     ═══════════════════════════════════════════════════════ --}}
@if($families)
<section class="bg-bg">
    <div class="container-tmac py-10 md:py-12">
        <div class="brand-families">
            <div class="brand-marker">
                <span class="brand-marker__num">{{ sprintf('%02d', ++$sec) }}</span>
                <span class="brand-marker__label">O que você encontra na {{ $brand->name }}</span>
            </div>
            @if($famTitle)
                <h2 class="brand-section-title !text-[24px] md:!text-[32px]">{{ $famTitle }}</h2>
            @endif
            @if($famIntro)
                <p class="mt-2 text-[14px] md:text-[15px] text-ink-soft leading-relaxed max-w-[62ch]">{{ $famIntro }}</p>
            @endif

            <ul class="mt-5 flex flex-wrap gap-2">
                @foreach($families as $fam)
                    @php
                        // "Kit transmissão – em breve": vira chip sem link, com selo "Em breve"
                        $emBreve = (bool) preg_match('/\s*[\-–—(]?\s*em breve\s*\)?\s*$/iu', $fam);
                        $famNome = $emBreve ? trim(preg_replace('/\s*[\-–—(]?\s*em breve\s*\)?\s*$/iu', '', $fam)) : $fam;
                    @endphp
                    <li>
                        @if($emBreve)
                            <span class="brand-family-chip brand-family-chip--soon">
                                {{ $famNome }}
                                <span class="brand-family-chip__soon">Em breve</span>
                            </span>
                        @else
                            <a href="{{ route('site.products', ['q' => $famNome]) }}" class="brand-family-chip">
                                {{ $famNome }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}"
               class="mt-5 inline-flex items-center gap-1.5 font-semibold text-[13px] underline underline-offset-4 hover:opacity-80"
               style="color: {{ $c }};">
                Ver catálogo completo
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     2C. DESTAQUE (opcional) + DIFERENCIAIS
     ═══════════════════════════════════════════════════════ --}}
@if($diffs || $hlTitle)
<section class="bg-bg border-y border-line">
    <div class="container-tmac py-10 md:py-14">
        <div class="brand-marker">
            <span class="brand-marker__num">{{ sprintf('%02d', ++$sec) }}</span>
            <span class="brand-marker__label">Diferenciais para o balcão e a oficina</span>
        </div>

        @if($hlTitle)
            <div class="mt-4 grid lg:grid-cols-12 gap-6 items-start">
                <h2 class="brand-section-title lg:col-span-5 !mt-0">{{ $hlTitle }}</h2>
                @if($hlText)
                    <div class="lg:col-span-7 space-y-3">
                        @foreach($paras($hlText) as $p)
                            <p class="text-[15px] leading-relaxed text-ink-soft">{{ $p }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if($diffsTitle)
            <h{{ $hlTitle ? '3' : '2' }} class="brand-section-title {{ $hlTitle ? '!text-[20px] md:!text-[24px] !mt-10' : '' }}">{{ $diffsTitle }}</h{{ $hlTitle ? '3' : '2' }}>
        @endif

        @if($diffsIntro)
            <p class="mt-3 text-[15px] md:text-[16px] leading-relaxed text-ink-soft max-w-[62ch]">{{ $diffsIntro }}</p>
        @endif

        @if($diffs)
            <div class="mt-6 grid sm:grid-cols-2 {{ count($diffs) >= 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-3">
                @foreach($diffs as $i => $d)
                    <div class="brand-diff">
                        <span class="brand-diff__num">{{ sprintf('%02d', $i + 1) }}</span>
                        <h3 class="brand-diff__title">{{ $d['title'] }}</h3>
                        @if(! empty($d['text']))
                            <p class="brand-diff__text">{{ $d['text'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
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
                    <span class="brand-marker__num">{{ sprintf('%02d', ++$sec) }}</span>
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
     3B. CONVERSÃO B2B
     ═══════════════════════════════════════════════════════ --}}
<section class="brand-convert">
    <div class="container-tmac py-12 md:py-16 relative z-10 grid lg:grid-cols-12 gap-6 items-center">
        <div class="lg:col-span-8">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] opacity-70 mb-3">Atendimento exclusivo CNPJ</div>
            <h2 class="font-display font-black text-[26px] md:text-[40px] leading-[0.98] tracking-tightest">{{ $ctaTitle }}</h2>
            @if($ctaText)
                <p class="mt-3 text-[15px] md:text-[16px] leading-relaxed opacity-90 max-w-[60ch]">{{ $ctaText }}</p>
            @endif
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-2.5">
            <a href="{{ route('site.quote') }}" class="brand-quote-cta__btn justify-center"
               onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'brand_convert',brand:'{{ $brand->slug }}'})">
                {{ $ctaPrimary }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('site.distributor') }}" class="brand-convert__ghost justify-center"
               onclick="window.dataLayer&&dataLayer.push({event:'distributor_cta',source:'brand_convert',brand:'{{ $brand->slug }}'})">
                {{ $ctaSecondary }}
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     4. OUTRAS MARCAS
     ═══════════════════════════════════════════════════════ --}}
@if($others->isNotEmpty())
<section class="bg-ink text-white">
    <div class="container-tmac py-10 md:py-14">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="brand-marker brand-marker--dark">
                <span class="brand-marker__num">{{ sprintf('%02d', ++$sec) }}</span>
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
                                 class="brand-others__logo {{ $o->logo_invert ? 'is-light' : '' }}">
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
