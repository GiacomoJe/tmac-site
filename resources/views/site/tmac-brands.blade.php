@extends('layouts.site')

@section('content')

@php
    $extenso  = [1=>'Uma',2=>'Duas',3=>'Três',4=>'Quatro',5=>'Cinco',6=>'Seis',7=>'Sete',8=>'Oito'];
    $qtd      = $brands->count();
    $qtdTexto = $extenso[$qtd] ?? $qtd;
@endphp

{{-- ═══════════════════════════════════════════════════════
     1. HERO — faixas de cor das marcas + título
     ═══════════════════════════════════════════════════════ --}}
<section class="uni-hero">
    {{-- Faixas verticais coloridas de fundo (uma por marca) --}}
    <div class="uni-hero__stripes" aria-hidden="true">
        @foreach($brands as $b)
            <span class="uni-hero__stripe" style="--c: {{ $b->resolved_brand_color }};"></span>
        @endforeach
    </div>

    {{-- Grid técnico sutil --}}
    <div class="uni-hero__grid" aria-hidden="true"></div>

    <nav class="container-tmac relative z-10 pt-4">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em] text-white/50">
            <a href="{{ route('site.home') }}" class="hover:text-white transition">Início</a>
            <span class="text-white/25">/</span>
            <span class="text-white">Universo TMAC</span>
        </div>
    </nav>

    <div class="container-tmac relative z-10 flex-1 flex flex-col justify-center py-14 md:py-20">
        <div class="flex items-center gap-3 mb-6 flex-wrap">
            <span class="uni-badge">
                <span class="uni-badge__dot"></span>
                Marcas próprias
            </span>
            <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-white/45">
                {{ $qtd }} identidades · 1 grupo
            </span>
        </div>

        <h1 class="uni-hero__title">
            <span class="uni-hero__title-line">{{ $qtdTexto }} marcas.</span>
            <span class="uni-hero__title-line uni-hero__title-line--accent">Um único universo.</span>
        </h1>

        <p class="mt-6 text-white/65 text-[15px] md:text-[17px] leading-relaxed max-w-[64ch]">
            O Universo TMAC reúne marcas especializadas para atender diferentes necessidades do mercado de motopeças. Da reposição ao desempenho, da tecnologia ao acabamento premium, cada marca possui sua própria identidade, mantendo o mesmo compromisso com qualidade, inovação e confiança que fazem da TMAC uma referência nacional no segmento de duas rodas.
        </p>

        {{-- Índice de navegação rápida --}}
        <div class="mt-10 flex flex-wrap gap-2">
            @foreach($brands as $i => $b)
                <a href="#marca-{{ $b->slug }}" class="uni-jump" style="--c: {{ $b->resolved_brand_color }};">
                    <span class="uni-jump__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="uni-jump__name">{{ $b->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Indicador de scroll --}}
    <div class="uni-hero__scroll" aria-hidden="true">
        <span class="font-mono text-[9px] uppercase tracking-[0.14em] text-white/40">Role</span>
        <span class="uni-hero__scroll-line"></span>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     2. FAIXAS POR MARCA — cada uma com sua identidade
     ═══════════════════════════════════════════════════════ --}}
@foreach($brands as $i => $brand)
    @php
        $bg      = $brand->resolved_brand_color;
        $fg      = $brand->resolved_text_color;
        $dark    = $brand->darkened_color;
        $isEven  = $i % 2 === 1;
        $num     = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
    @endphp

    <section id="marca-{{ $brand->slug }}"
             class="uni-band {{ $isEven ? 'uni-band--reverse' : '' }}"
             style="--c: {{ $bg }}; --c-dark: {{ $dark }}; --fg: {{ $fg }};">

        {{-- ─── Lado da cor / conteúdo ─── --}}
        <div class="uni-band__content">
            {{-- Número gigante de fundo --}}
            <span class="uni-band__bignum" aria-hidden="true">{{ $num }}</span>

            <div class="uni-band__inner">
                <div class="uni-band__eyebrow">
                    <span class="uni-band__eyebrow-num">{{ $num }}</span>
                    <span>{{ $brand->badge_label ?? 'Linha exclusiva' }}</span>
                </div>

                {{-- Logo ou nome --}}
                <div class="uni-band__brand">
                    @if($brand->logo_url)
                        <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                             class="uni-band__logo {{ $brand->text_theme === 'light' ? 'is-light' : '' }}">
                    @else
                        <span class="uni-band__name">{{ $brand->name }}</span>
                    @endif
                </div>

                @if($brand->tagline)
                    <p class="uni-band__tagline">{{ $brand->tagline }}</p>
                @endif

                @if($brand->description)
                    <p class="uni-band__desc">
                        {{ \Illuminate\Support\Str::limit($brand->description, 260) }}
                    </p>
                @endif

                <div class="uni-band__actions">
                    <a href="{{ $brand->link_url ?: route('site.tmac-brand', $brand->slug) }}" class="uni-btn uni-btn--solid">
                        Conhecer {{ $brand->name }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="uni-btn uni-btn--ghost">
                        Ver produtos
                    </a>
                </div>
            </div>
        </div>

        {{-- ─── Lado da imagem ─── --}}
        <div class="uni-band__media">
            @if($brand->image_url)
                <img src="{{ $brand->image_url }}" alt="{{ $brand->name }}" loading="lazy">
            @else
                <div class="uni-band__placeholder" aria-hidden="true">
                    <span>{{ $brand->name }}</span>
                </div>
            @endif
            <span class="uni-band__media-edge" aria-hidden="true"></span>
        </div>
    </section>
@endforeach

{{-- ═══════════════════════════════════════════════════════
     3. CTA FINAL
     ═══════════════════════════════════════════════════════ --}}
<section class="relative bg-ink text-white overflow-hidden">
    {{-- Faixa multicolor no topo --}}
    <div class="uni-rainbow" aria-hidden="true">
        @foreach($brands as $b)
            <span style="background: {{ $b->resolved_brand_color }};"></span>
        @endforeach
    </div>

    <div class="container-tmac py-16 md:py-24 text-center max-w-3xl mx-auto">
        <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-white/45 mb-4">
            Distribuição autorizada
        </div>
        <h2 class="font-display font-black text-[30px] md:text-[46px] leading-[0.95] tracking-tightest uppercase">
            Quer revender<br>o <span class="text-accent">Universo TMAC</span>?
        </h2>
        <p class="mt-5 text-white/65 text-[15px] md:text-[16px] leading-relaxed max-w-[52ch] mx-auto">
            Atendimento exclusivo para CNPJ. Cotação respondida em até 1 dia útil, com condições escalonadas por volume.
        </p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center">
            <a href="{{ route('site.quote') }}" class="btn-primary">
                Solicitar cotação
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('site.products') }}" class="btn bg-white/10 backdrop-blur text-white border border-white/20 hover:bg-white/15">
                Ver catálogo completo
            </a>
        </div>
    </div>
</section>

@endsection
