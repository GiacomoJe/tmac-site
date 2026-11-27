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
            <span class="uni-hero__title-line">{{ $qtdTexto }} {{ $qtd === 1 ? 'marca própria' : 'marcas próprias' }}</span>
            <span class="uni-hero__title-line uni-hero__title-line--accent uni-hero__title-line--sm">para abastecer o seu negócio.</span>
        </h1>

        <p class="mt-6 text-white/70 text-[15px] md:text-[17px] leading-relaxed max-w-[62ch]">
            O Universo TMAC reúne as {{ mb_strtolower($qtdTexto) }} linhas próprias para quem vive o mercado de duas rodas no Brasil. São mais de 4 mil SKUs em estoque, pensados para atender lojistas, oficinas mecânicas e distribuidores com CNPJ ativo que precisam de giro rápido no balcão e confiança na entrega.
        </p>

        <div class="mt-6">
            <a href="{{ route('site.quote') }}" class="btn-primary"
               onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'universo_hero'})">
                Solicitar cotação B2B
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Índice de navegação rápida --}}
        <div class="mt-10 flex flex-wrap gap-2">
            @php $jumpN = 0; @endphp
            @foreach($brands as $b)
                <a href="#marca-{{ $b->slug }}" class="uni-jump" style="--c: {{ $b->resolved_brand_color }};">
                    <span class="uni-jump__num">{{ str_pad(++$jumpN, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="uni-jump__name">{{ $b->name }}</span>
                </a>
                {{-- Submarcas (ex.: LBJ Premium) logo após a marca-mãe, levando à página própria --}}
                @foreach($b->children as $child)
                    <a href="{{ $child->link_url ?: route('site.tmac-brand', $child->slug) }}" class="uni-jump" style="--c: {{ $child->resolved_brand_color }};">
                        <span class="uni-jump__num">{{ str_pad(++$jumpN, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="uni-jump__name">{{ $child->name }}</span>
                    </a>
                @endforeach
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
     2. VITRINE DAS MARCAS — cabeçalho + uma faixa por marca
     ═══════════════════════════════════════════════════════ --}}
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-10 md:py-14 grid lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-7">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">01</span>
                <span class="section-marker__label">Vitrine das {{ mb_strtolower($qtdTexto) }} marcas</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[42px] leading-[0.98] tracking-tightest text-ink mt-3">
                Escolha a linha certa para o perfil dos seus <span class="text-accent">clientes</span>.
            </h2>
        </div>
        <p class="lg:col-span-5 text-ink-soft text-[15px] md:text-[16px] leading-relaxed">
            Cada marca do grupo tem um papel claro na sua prateleira. Conheça os detalhes de cada linha e veja como montar um mix completo para o seu negócio.
        </p>
    </div>
</section>

@foreach($brands as $i => $brand)
    @php
        $bg      = $brand->resolved_brand_color;
        $fg      = $brand->resolved_text_color;
        $dark    = $brand->darkened_color;
        $isEven  = $i % 2 === 1;
        $num     = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        // TMAC (marca principal): faixa inteira no vermelho, conteúdo centralizado e a foto
        // ao fundo tingida na cor da marca — sem a metade azulada (pedido do cliente).
        // Todas as marcas seguem o mesmo padrão: faixa inteira na cor, conteúdo centralizado no banner.
        $solid   = true;
        $isMain  = $brand->slug === 'tmac';
    @endphp

    <section id="marca-{{ $brand->slug }}"
             class="uni-band {{ $isEven ? 'uni-band--reverse' : '' }} {{ $solid ? 'uni-band--solid uni-band--main' : '' }}"
             style="--c: {{ $bg }}; --c-dark: {{ $dark }}; --fg: {{ $fg }};">

        {{-- ─── Lado da cor / conteúdo ─── --}}
        @if($solid && $brand->image_url)
            <img src="{{ $brand->image_url }}" alt="" aria-hidden="true" loading="lazy" class="uni-band__mainbg">
            <div class="uni-band__mainwash" aria-hidden="true"></div>
        @endif

        <div class="uni-band__content">
            {{-- Número gigante de fundo --}}
            <span class="uni-band__bignum" aria-hidden="true">{{ $num }}</span>

            <div class="uni-band__inner">
                @if($isMain)
                    <div class="uni-band__mainbadge">Marca principal</div>
                @endif
                <div class="uni-band__eyebrow">
                    <span class="uni-band__eyebrow-num">{{ $num }}</span>
                    <span>{{ $brand->name }} | {{ $brand->badge_label ?? 'Linha exclusiva' }}</span>
                </div>

                {{-- Logo ou nome --}}
                <div class="uni-band__brand">
                    @if($brand->logo_url)
                        <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                             class="uni-band__logo {{ $brand->logo_invert ? 'is-light' : '' }}">
                    @else
                        <span class="uni-band__name">{{ $brand->name }}</span>
                    @endif
                </div>

                @if($brand->tagline)
                    <p class="uni-band__tagline">{{ $brand->tagline }}</p>
                @endif

                @if($brand->description)
                    <p class="uni-band__desc">
                        {{ $brand->description }}
                    </p>
                @endif

                <div class="uni-band__actions">
                    <a href="{{ $brand->link_url ?: route('site.tmac-brand', $brand->slug) }}" class="uni-btn uni-btn--solid">
                        Conheça mais da marca {{ $brand->name }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('site.products', ['tmac' => [$brand->slug]]) }}" class="uni-btn uni-btn--ghost">
                        Ver produtos
                    </a>
                </div>

                {{-- Submarcas (ex.: LBJ Premium dentro da LBJ) --}}
                @if($brand->children->isNotEmpty())
                    <div class="uni-band__subbrands mt-5 flex flex-wrap items-center gap-2">
                        <span class="font-mono text-[10px] uppercase tracking-[0.12em] opacity-70">Submarca</span>
                        @foreach($brand->children as $child)
                            <a href="{{ route('site.tmac-brand', $child->slug) }}" class="uni-btn uni-btn--ghost !h-9 !text-[12px]">
                                {{ $child->name }} →
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Lado da imagem ─── --}}
        @unless($solid)
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
        @endunless
    </section>
@endforeach

{{-- ═══════════════════════════════════════════════════════
     3. IMPORTAÇÃO DIRETA E QUALIDADE
     ═══════════════════════════════════════════════════════ --}}
<section class="bg-bg">
    <div class="container-tmac py-12 md:py-16 grid lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-5">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">02</span>
                <span class="section-marker__label">Importação e qualidade</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[40px] leading-[0.98] tracking-tightest text-ink mt-3">
                Importação direta e controle de qualidade <span class="text-signal">do nosso jeito</span>.
            </h2>
        </div>
        <div class="lg:col-span-7 space-y-4 text-ink-soft text-[15px] md:text-[16px] leading-relaxed">
            <p>Na TMAC, a gente cuida de ponta a ponta para que você receba peças prontas para vender e instalar. Nosso escritório próprio na China mantém contato 24 horas com a origem, e os produtos passam por testes de qualidade no Brasil antes de chegar ao seu balcão.</p>
            <p>Com mais de 3,2 mil lojistas ativos e entrega para 26 estados, estamos estruturando nosso próprio centro de distribuição para dar ainda mais agilidade a cada pedido. Você compra direto de quem importa com atendimento humano feito por quem realmente entende de moto.</p>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     4. CTAs — B2B (lojista/oficina) e B2C (consumidor → onde comprar)
     ═══════════════════════════════════════════════════════ --}}
<section class="relative bg-ink text-white overflow-hidden">
    {{-- Faixa multicolor no topo --}}
    <div class="uni-rainbow" aria-hidden="true">
        @foreach($brands as $b)
            <span style="background: {{ $b->resolved_brand_color }};"></span>
        @endforeach
    </div>

    <div class="container-tmac py-12 md:py-16 grid lg:grid-cols-12 gap-4">
        {{-- B2B --}}
        <div class="lg:col-span-7 rounded-2xl bg-accent p-6 md:p-8 flex flex-col">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-white/75 mb-3">Exclusivo B2B · CNPJ ativo</div>
            <h2 class="font-display font-black text-[24px] md:text-[34px] leading-[1] tracking-tightest">
                Sua loja ou oficina precisa de reposição com preço de atacado?
            </h2>
            <p class="mt-3 text-white/85 text-[15px] leading-relaxed max-w-[56ch]">
                Atendemos exclusivamente empresas com CNPJ ativo, sem venda direta para o consumidor final. Monte sua lista de produtos, peça sua tabela personalizada por volume e receba em todo o Brasil.
            </p>
            <div class="mt-6">
                <a href="{{ route('site.quote') }}" class="btn bg-white text-accent hover:bg-white/90 font-bold"
                   onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'universo_b2b'})">
                    Solicitar cotação agora
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- B2C --}}
        <div class="lg:col-span-5 rounded-2xl bg-white/[0.06] border border-white/15 p-6 md:p-8 flex flex-col">
            <div class="font-mono text-[10px] uppercase tracking-[0.14em] text-white/55 mb-3">Para o motociclista</div>
            <h2 class="font-display font-black text-[22px] md:text-[28px] leading-[1.02] tracking-tightest">
                Está procurando peças do Universo TMAC?
            </h2>
            <p class="mt-3 text-white/70 text-[15px] leading-relaxed">
                Use nossa ferramenta de parceiros e encontre um parceiro próximo de você.
            </p>
            <div class="mt-6">
                <a href="{{ route('site.resellers') }}" class="btn bg-white/10 text-white border border-white/25 hover:bg-white/15"
                   onclick="window.dataLayer&&dataLayer.push({event:'resellers_cta',source:'universo_b2c'})">
                    Localize um parceiro TMAC
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
