@extends('layouts.site')

@section('content')

{{-- ═══════════ 1. HERO COMPACTO ═══════════
     Ajuste nov/26: banner reduzido e SEM foto (cliente não quer foto do time).
     O campo "imagem do hero" do painel não é mais usado nesta página. --}}
<section class="relative overflow-hidden bg-ink text-white flex flex-col"
         style="min-height: clamp(300px, 42vh, 420px);">

    {{-- Overlay dramático --}}
    <div class="absolute inset-0"
         style="background:
            linear-gradient(180deg, rgba(15,16,20,0.55) 0%, rgba(15,16,20,0.35) 40%, rgba(15,16,20,0.95) 100%),
            radial-gradient(ellipse at 20% 100%, rgba(225,29,42,0.30), transparent 50%);"></div>

    {{-- Pattern diagonal sutil --}}
    <div class="absolute inset-0 opacity-[0.07] mix-blend-overlay pointer-events-none"
         style="background-image: repeating-linear-gradient(115deg, transparent, transparent 60px, rgba(255,255,255,0.4) 60px, rgba(255,255,255,0.4) 61px);"></div>

    {{-- Trilha azul no topo --}}
    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    {{-- Crumb no topo --}}
    <nav class="container-tmac relative z-10 pt-4">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em] text-white/60">
            <a href="{{ route('site.home') }}" class="hover:text-white transition">Início</a>
            <span class="text-white/30">/</span>
            <span class="text-white">Quem somos</span>
        </div>
    </nav>

    {{-- Conteúdo do hero: no fluxo normal (não absoluto) — o banner cresce junto
         com o texto e nunca corta um título longo. mt-auto mantém o texto embaixo. --}}
    <div class="container-tmac relative z-10 mt-auto pt-10 md:pt-14 pb-8 md:pb-10">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
            <span class="speed-badge">
                <span class="speed-badge__dot"></span>
                {{ $page->hero_eyebrow ?: 'Quem somos' }}
            </span>
            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
                Desde 2016 · 10 anos de estrada · B2B
            </div>
        </div>

        <h1 class="font-display font-black uppercase leading-[0.92] tracking-tightest text-white
                   text-[30px] sm:text-[40px] md:text-[50px] lg:text-[58px] max-w-[24ch]">
            @php
                $heroTitle = $page->hero_title ?: 'Mais que uma marca, somos parceiros de verdade.';
                $heroHighlight = $page->hero_highlight ?: 'parceiros de verdade';
            @endphp
            {!! preg_replace('/(' . preg_quote($heroHighlight, '/') . ')/i', '<span class="text-accent">$1</span>', e($heroTitle), 1) !!}
        </h1>

        <p class="mt-5 text-white/75 text-[15px] md:text-[18px] leading-relaxed max-w-[58ch]">
            {{ $page->hero_description ?: 'A TMAC nasceu em 2016, em São Paulo, com um objetivo claro: caminhar lado a lado com quem vive do mercado de duas rodas no Brasil. Em 2026, completamos 10 anos de estrada como uma das maiores importadoras e distribuidoras de motopeças do país.' }}
        </p>
    </div>

</section>

{{-- ═══════════════════════════════════════════════════════════════
     Textos: documento "Abertura e história" (nov/26).
     O topo (etiqueta, título, destaque e descrição) é editável em
     Painel → Páginas → Quem somos. Os blocos abaixo estão neste arquivo.
     ═══════════════════════════════════════════════════════════════ --}}

{{-- ═══════════ 1B. ABERTURA — operação 100% atacado ═══════════ --}}
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-10 md:py-14 grid lg:grid-cols-12 gap-6 lg:gap-10 items-center">
        <div class="lg:col-span-8">
            <p class="text-[16px] md:text-[19px] leading-relaxed text-ink">
                Nossa operação é <strong>100% voltada para o atacado</strong>. Atendemos exclusivamente empresas com CNPJ ativo, como lojistas, oficinas mecânicas e distribuidores, levando peças de reposição e acessórios para 26 estados e mais de 3,2 mil lojistas ativos.
            </p>
        </div>
        <div class="lg:col-span-4 lg:text-right">
            <a href="{{ route('site.quote') }}" class="btn-primary"
               onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'quem_somos_abertura'})">
                Solicitar cotação B2B
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ═══════════ 2. ESTRUTURA, LOGÍSTICA E FUTURO ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="absolute -top-10 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">01</span>
    </div>

    <div class="container-tmac py-12 md:py-20 relative">
        {{-- Título e texto empilhados, ocupando a largura toda --}}
        <div>
            <div>
                <div class="section-marker section-marker--lg">
                    <span class="section-marker__num">01</span>
                    <span class="section-marker__label">Estrutura, logística e futuro</span>
                </div>
                <h2 class="font-display font-black text-[28px] md:text-[42px] leading-[0.98] tracking-tightest text-ink mt-3">
                    Preparados para o hoje e estruturados para <span class="text-signal">2027</span>.
                </h2>
            </div>
            <div class="mt-5 space-y-4 text-[15px] md:text-[17px] leading-relaxed text-ink-soft">
                <p>Quem vende no balcão precisa de estoque disponível e entrega rápida. Por isso, cuidamos de cada etapa do processo, da fábrica até a sua loja. Contamos com escritório próprio na China e contato 24 horas com a origem para garantir o padrão de fabricação. Antes de chegar às suas prateleiras, as peças passam por testes de qualidade no Brasil.</p>
                <p>Estamos vivendo um novo capítulo da nossa história. Desenvolvemos nossa logística com galpão próprio, atualizamos nossos sistemas e seguimos nos preparando fortemente para 2027, garantindo ainda mais agilidade e controle em cada pedido.</p>
            </div>
        </div>

        {{-- Vídeo institucional em largura total, tocando no próprio site.
             Carrega só a miniatura; o player do YouTube (modo privacidade) entra ao clicar,
             para não pesar no carregamento da página. --}}
        <div class="mt-10 md:mt-12"
             x-data="{ playing: false }">
            <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink-faint mb-3">
                Quer conhecer de perto a nossa operação? Assista ao vídeo institucional de 2024
            </div>
            <div class="relative rounded-2xl overflow-hidden bg-ink aspect-video shadow-[0_24px_60px_-30px_rgba(0,0,0,0.6)]">
                <template x-if="playing">
                    <iframe class="absolute inset-0 w-full h-full"
                            src="https://www.youtube-nocookie.com/embed/7y7NNzsjINs?start=1&autoplay=1&rel=0&modestbranding=1&playsinline=1"
                            title="Vídeo institucional TMAC 2024"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen></iframe>
                </template>

                <button type="button" x-show="!playing"
                        @click="playing = true; window.dataLayer&&dataLayer.push({event:'video_play',source:'quem_somos'})"
                        class="group absolute inset-0 w-full h-full text-left"
                        aria-label="Reproduzir vídeo institucional TMAC 2024">
                    <img src="https://i.ytimg.com/vi/7y7NNzsjINs/maxresdefault.jpg" alt="" loading="lazy"
                         onerror="this.onerror=null;this.src='https://i.ytimg.com/vi/7y7NNzsjINs/hqdefault.jpg'"
                         class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-95 transition">
                    <span class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/10 to-transparent"></span>
                    <span class="absolute inset-0 flex items-center justify-center">
                        <span class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-accent text-white flex items-center justify-center shadow-xl group-hover:scale-105 transition">
                            <svg class="w-7 h-7 md:w-8 md:h-8 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                    <span class="absolute left-5 right-5 bottom-5 md:left-8 md:bottom-7 text-white">
                        <span class="block font-mono text-[10px] uppercase tracking-[0.12em] text-white/70">TMAC · 2024</span>
                        <span class="block font-display font-extrabold text-[18px] md:text-[24px] tracking-tightish mt-1">Vídeo institucional</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 3. PORTFÓLIO E MARCAS PRÓPRIAS ═══════════ --}}
@php
    // Textos do portfólio por marca (documento "Abertura e história"). Cores/logos vêm do painel.
    $portfolioTexts = [
        'tmac'   => 'A essência do grupo, reunindo a base do nosso catálogo de peças e acessórios com importação direta e reposição constante.',
        'corami' => 'Linha com destaque no segmento de moto, desenvolvida para atender mecânicos e motociclistas que precisam de componentes internos de motor e peças técnicas confiáveis.',
        'atrox'  => 'Visual esportivo aliado à resistência, com destaque especial para a linha de bauletos ATROX.',
        'lbj'    => 'Focada em quem busca acabamento superior, máxima qualidade e maior durabilidade em cada peça.',
        'lbj-premium' => 'O padrão máximo em motopeças: acabamento superior, resistência e durabilidade elevada para os clientes mais exigentes.',
    ];
    // Inclui as submarcas (ex.: LBJ Premium ganha card próprio logo após a LBJ).
    $portfolio = \App\Models\TmacBrand::active()->ordered()->get();
    $portGrid = match (true) {
        $portfolio->count() >= 5 => 'sm:grid-cols-2 lg:grid-cols-5',
        $portfolio->count() === 3 => 'sm:grid-cols-3',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp
<section class="bg-ink text-white relative overflow-hidden">
    <div class="absolute -top-10 -left-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--on-dark">02</span>
    </div>

    <div class="container-tmac py-14 md:py-20 relative">
        <div class="max-w-3xl">
            <div class="section-marker section-marker--lg section-marker--on-dark">
                <span class="section-marker__num">02</span>
                <span class="section-marker__label">Portfólio e marcas próprias</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[42px] leading-[0.98] tracking-tightest mt-3">
                Mais de 4 mil SKUs em estoque para o seu <span class="text-signal">negócio</span>.
            </h2>
            <p class="mt-4 text-white/70 text-[15px] md:text-[16px] leading-relaxed">
                Nosso catálogo reúne mais de 4 mil SKUs em estoque, divididos entre a marca principal e nossas linhas próprias, pensadas para atender diferentes perfis de clientes na sua região:
            </p>
        </div>

        <div class="mt-8 grid grid-cols-2 {{ $portGrid }} gap-3">
            @foreach($portfolio as $tb)
                <a href="{{ $tb->link_url ?: route('site.tmac-brand', $tb->slug) }}"
                   class="tmac-brand-card group"
                   style="--brand-bg: {{ $tb->resolved_brand_color }}; --brand-fg: {{ $tb->resolved_text_color }}; --brand-dark: {{ $tb->darkened_color }};">
                    <div class="tmac-brand-card__texture"></div>
                    <div class="tmac-brand-card__head">
                        @if($tb->logo_url)
                            <img src="{{ $tb->logo_url }}" alt="{{ $tb->name }}" loading="lazy" class="h-9 object-contain object-left {{ $tb->logo_invert ? 'brightness-0 invert' : '' }}">
                        @else
                            <span class="tmac-brand-card__name">{{ $tb->name }}</span>
                        @endif
                    </div>
                    <p class="tmac-brand-card__desc">{{ $portfolioTexts[$tb->slug] ?? $tb->description }}</p>

                    {{-- Descrição completa ao passar o mouse (ou focar com teclado) --}}
                    <div class="tmac-brand-card__full" aria-hidden="true">
                        <span class="tmac-brand-card__full-name">{{ $tb->name }}</span>
                        <p>{{ $portfolioTexts[$tb->slug] ?? $tb->description }}</p>
                    </div>
                    <div class="tmac-brand-card__foot">
                        <span class="tmac-brand-card__badge">{{ $tb->badge_label }}</span>
                        <span class="tmac-brand-card__arrow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 4. NOSSO COMPROMISSO ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="container-tmac py-12 md:py-20 grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">
        <div class="lg:col-span-5">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">03</span>
                <span class="section-marker__label">Nosso compromisso com você</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[42px] leading-[0.98] tracking-tightest text-ink mt-3">
                Ética comercial e parceria que gera <span class="text-accent">resultado</span>.
            </h2>

            <ul class="mt-6 grid grid-cols-2 gap-2">
                @foreach(['Honestidade', 'Qualidade', 'Parceria', 'Comprometimento'] as $i => $pilar)
                    <li class="card px-4 py-3 flex items-center gap-2.5">
                        <span class="font-mono text-[11px] font-bold text-accent">{{ sprintf('%02d', $i + 1) }}</span>
                        <span class="font-display font-extrabold text-[15px] tracking-tightish text-ink">{{ $pilar }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-7 space-y-4 text-[15px] md:text-[16px] leading-relaxed text-ink-soft">
            <p>Na TMAC, acreditamos que ninguém cresce sozinho. Nossa relação com os clientes é construída sobre quatro pilares: <strong class="text-ink">honestidade, qualidade, parceria e comprometimento</strong>.</p>
            <div class="p-5 md:p-6 bg-signal-soft/40 border-l-4 border-signal rounded-r-lg">
                <p class="text-ink">Temos o compromisso ético de <strong>não vender para o consumidor final</strong> e de <strong>nunca furar a cadeia de clientes</strong>.</p>
            </div>
            <p>Trabalhamos para fortalecer o seu balcão e a sua oficina, oferecendo tabela escalonada por volume, representantes em 26 estados e no Distrito Federal, com um atendimento humano feito por gente que entende de moto.</p>
        </div>
    </div>
</section>

{{-- ═══════════ 5. CTA EXCLUSIVO B2B ═══════════ --}}
<section class="relative overflow-hidden bg-accent text-white">
    <div class="absolute inset-0 opacity-[0.10] pointer-events-none"
         style="background-image: repeating-linear-gradient(115deg, transparent, transparent 60px, rgba(255,255,255,0.5) 60px, rgba(255,255,255,0.5) 61px);"></div>
    <div class="container-tmac py-12 md:py-16 relative grid lg:grid-cols-12 gap-6 items-center">
        <div class="lg:col-span-8">
            <h2 class="font-display font-black text-[28px] md:text-[44px] leading-[0.96] tracking-tightest uppercase">
                Vamos criar novos caminhos juntos?
            </h2>
            <p class="mt-3 text-white/85 text-[15px] md:text-[16px] leading-relaxed max-w-[60ch]">
                Se a sua loja, oficina ou distribuidora tem CNPJ ativo e busca um parceiro de verdade para abastecer o estoque com preço de atacado, fale com a nossa equipe.
            </p>
        </div>
        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-2.5">
            <a href="{{ route('site.quote') }}" class="btn bg-white text-accent hover:bg-white/90 font-bold justify-center"
               onclick="window.dataLayer&&dataLayer.push({event:'quote_cta',source:'quem_somos_final'})">
                Solicitar cotação agora
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('site.distributor') }}" class="btn bg-transparent text-white border border-white/50 hover:bg-white/10 justify-center"
               onclick="window.dataLayer&&dataLayer.push({event:'distributor_cta',source:'quem_somos_final'})">
                Quero ser um distribuidor
            </a>
        </div>
    </div>
</section>

@endsection
