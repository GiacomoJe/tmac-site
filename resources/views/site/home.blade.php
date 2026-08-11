@extends('layouts.site')

@section('content')

@php
    $wppNumber = \App\Models\Setting::get('whatsapp_number');
    $catalogUrl = \App\Models\Setting::get('catalog_pdf_url');

    // Garante pelo menos um "slide" (fallback) se admin não tiver banner cadastrado
    $slides = $banners->count() > 0 ? $banners : collect([(object) [
        'title' => 'O melhor para sua loja,',
        'subtitle' => 'direto da fábrica.',
        'image_mobile' => null,
        'image_desktop' => null,
        'link_url' => null,
        'cta_label' => null,
    ]]);
    $hasMultiple = $slides->count() > 1;
@endphp

{{-- ═══════════ 1. HERO CINEMATOGRÁFICO (carrossel quando >1 banner) ═══════════ --}}
<section class="relative overflow-hidden bg-ink text-white"
         style="min-height: clamp(420px, 60vh, 680px);"
         x-data="{
             current: 0,
             count: {{ $slides->count() }},
             autoplay: {{ $hasMultiple ? 'true' : 'false' }},
             interval: 6500,
             timer: null,
             startX: 0,
             imageOnly: @json($slides->map(fn($s) => (bool) ($s->image_only ?? false))->values()),
             get isCleanSlide() { return this.imageOnly[this.current] === true; },

             /* start() é idempotente: sempre limpa o timer anterior antes de criar um novo.
                Evita múltiplos setInterval empilhados (causa do carrossel 'acelerado'). */
             start() {
                 this.stop();
                 if (!this.autoplay || this.count < 2) return;
                 if (document.hidden) return;
                 this.timer = setInterval(() => this.next(), this.interval);
             },
             stop() {
                 if (this.timer) { clearInterval(this.timer); this.timer = null; }
             },
             next() { this.current = (this.current + 1) % this.count; },
             prev() { this.current = (this.current - 1 + this.count) % this.count; },
             go(i) { this.current = i; this.start(); },
             touchStart(e) { this.startX = e.touches[0].clientX; this.stop(); },
             touchEnd(e) {
                 const dx = e.changedTouches[0].clientX - this.startX;
                 if (Math.abs(dx) > 40) { dx < 0 ? this.next() : this.prev(); }
                 this.start();
             }
         }"
         x-init="
             start();
             /* Pausa quando a aba perde o foco e retoma ao voltar (evita acúmulo de ticks) */
             document.addEventListener('visibilitychange', () => {
                 document.hidden ? stop() : start();
             });
             /* Garante limpeza se o componente for removido/re-renderizado */
             $el._tmacCleanup = () => stop();
         "
         x-on:mouseenter="stop()"
         x-on:mouseleave="start()"
         @touchstart="touchStart($event)"
         @touchend="touchEnd($event)">

    {{-- ─── Camada de imagens (cross-fade) ─── --}}
    @foreach($slides as $idx => $slide)
        @php $isClean = (bool) ($slide->image_only ?? false); @endphp
        <div x-show="current === {{ $idx }}"
             x-transition:enter="transition-opacity ease-out duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 z-0"
             @if($idx !== 0) style="display: none;" @endif>
            @if($slide->image_mobile || $slide->image_desktop)
                @php $picture = $slide->image_desktop ?: $slide->image_mobile; @endphp
                @if($isClean && $slide->link_url)
                    <a href="{{ $slide->link_url }}" class="absolute inset-0 block">
                @endif
                <picture class="absolute inset-0">
                    @if($slide->image_desktop)
                        <source media="(min-width: 768px)" srcset="{{ asset('storage/' . $slide->image_desktop) }}">
                    @endif
                    <img src="{{ asset('storage/' . ($slide->image_mobile ?? $slide->image_desktop)) }}"
                         alt="{{ $slide->title ?? 'TMAC Import' }}"
                         @if($idx === 0) fetchpriority="high" @else loading="lazy" @endif
                         class="w-full h-full object-cover">
                </picture>
                {{-- Overlay gradiente: somente quando NÃO é banner limpo (arte limpa não precisa de escurecimento) --}}
                @unless($isClean)
                    <div class="absolute inset-0 bg-gradient-to-r from-ink via-ink/85 to-ink/40"></div>
                @endunless
                @if($isClean && $slide->link_url)
                    </a>
                @endif
            @else
                {{-- Fallback institucional (apenas quando não há banner cadastrado): foto moody + overlays --}}
                <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=1920&q=70"
                     alt="" aria-hidden="true"
                     @if($idx === 0) fetchpriority="high" @else loading="lazy" @endif
                     class="absolute inset-0 w-full h-full object-cover opacity-55">
                <div class="absolute inset-0"
                     style="background:
                        radial-gradient(ellipse at 30% 50%, rgba(225,29,42,0.22), transparent 60%),
                        linear-gradient(110deg, rgba(15,16,20,0.96) 0%, rgba(26,27,31,0.88) 45%, rgba(26,27,31,0.55) 100%);"></div>
                <div class="absolute inset-0 opacity-20 mix-blend-overlay"
                     style="background-image: repeating-linear-gradient(115deg, transparent, transparent 40px, rgba(225,29,42,0.10) 40px, rgba(225,29,42,0.10) 42px);"></div>
            @endif
        </div>
    @endforeach

    <div class="container-tmac relative z-10 py-10 md:py-16 lg:py-20"
         x-show="!isCleanSlide"
         x-transition:enter="transition-opacity ease-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
            <span class="speed-badge">
                <span class="speed-badge__dot"></span>
                ATACADO DE PEÇAS
            </span>
            <div class="tick-row tick-row--on-dark">
                @for($t = 0; $t < 10; $t++)
                    <span class="tick-row__tick {{ $t < 5 ? 'tick-row__tick--on' : '' }} {{ $t === 5 ? 'tick-row__tick--peak' : '' }}"></span>
                @endfor
            </div>
            <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55 hidden sm:inline-block">B2B · CNPJ</span>
        </div>

        {{-- ─── Texto que muda (título / subtítulo) — empilhados no mesmo espaço ─── --}}
        <div class="hero-stack">
            @foreach($slides as $idx => $slide)
                <h1 :class="current === {{ $idx }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                    class="hero-stack__item transition-opacity duration-500 font-display font-black text-[40px] md:text-[64px] lg:text-[80px] leading-[0.95] tracking-tightest text-white max-w-[20ch] uppercase">
                    {{ $slide->title ?? 'O melhor para sua loja,' }}@if($slide->subtitle)<br><span class="text-accent">{{ $slide->subtitle }}</span>@endif
                </h1>
            @endforeach
        </div>

        <p class="mt-5 text-white/75 text-[15px] md:text-[17px] leading-relaxed max-w-[44ch]">
            Mais de 12 mil itens em estoque para entrega em todo o Brasil. Atendimento exclusivo CNPJ com tabela escalonada por volume.
        </p>

        {{-- Feature pills (fixas) --}}
        <div class="mt-7 grid grid-cols-2 md:grid-cols-4 gap-3 max-w-3xl">
            @foreach([
                ['icon' => 'truck',    'title' => 'Importação',  'sub' => 'Direta'],
                ['icon' => 'crown',    'title' => 'Atendimento', 'sub' => 'Exclusivo'],
                ['icon' => 'tag',      'title' => 'Preços por',  'sub' => 'Volume'],
                ['icon' => 'package',  'title' => 'Envio para',  'sub' => 'todo o Brasil'],
            ] as $f)
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-white/10 border border-white/15 flex items-center justify-center flex-shrink-0">
                        @switch($f['icon'])
                            @case('truck')   <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h13v10H3zM16 10h4l1 3v3h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg> @break
                            @case('crown')   <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 17h18l-2-9-4 3-3-6-3 6-4-3z"/></svg> @break
                            @case('tag')     <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 12V4h-8L3 13l8 8 9-9z"/><circle cx="7.5" cy="7.5" r="1"/></svg> @break
                            @case('package') <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v9"/></svg> @break
                        @endswitch
                    </div>
                    <div class="leading-tight">
                        <div class="text-[12px] text-white/60 font-mono uppercase tracking-[0.06em]">{{ $f['title'] }}</div>
                        <div class="text-[13px] font-semibold text-white">{{ $f['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ─── CTA primário que troca + CTA secundário fixo ─── --}}
        <div class="mt-8 flex flex-wrap gap-3 items-center">
            <div class="hero-stack">
                @foreach($slides as $idx => $slide)
                    <a :class="current === {{ $idx }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                       href="{{ $slide->link_url ?? route('site.quote') }}"
                       class="hero-stack__item transition-opacity duration-500 btn-primary">
                        {{ $slide->cta_label ?: 'Solicitar cotação' }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('site.products') }}" class="btn bg-white/10 backdrop-blur text-white border border-white/20 hover:bg-white/15">
                Ver produtos
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>

    {{-- ─── Controles do carrossel (sempre visíveis quando >1 banner, mesmo em banners limpos) ─── --}}
    @if($hasMultiple)
        <div class="absolute bottom-5 md:bottom-7 left-0 right-0 z-20 pointer-events-none">
            <div class="container-tmac flex items-center gap-4 pointer-events-auto">
                <div class="flex items-center gap-1.5">
                    @foreach($slides as $idx => $slide)
                        <button type="button" @click="go({{ $idx }})"
                                :class="current === {{ $idx }} ? 'w-8 bg-accent' : 'w-2 bg-white/40 hover:bg-white/60'"
                                class="h-1.5 rounded-full transition-all"
                                aria-label="Ir para slide {{ $idx + 1 }}"></button>
                    @endforeach
                </div>

                {{-- Contador --}}
                <div class="font-mono text-[11px] text-white/70 uppercase tracking-[0.08em] drop-shadow-md">
                    <span x-text="String(current + 1).padStart(2,'0')"></span>
                    <span class="opacity-60">/ {{ str_pad($slides->count(), 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                {{-- Setas (desktop) --}}
                <div class="ml-auto hidden md:flex items-center gap-2">
                    <button type="button" @click="prev(); start()"
                            class="w-10 h-10 rounded-lg border border-white/30 bg-black/30 backdrop-blur hover:bg-black/50 flex items-center justify-center transition"
                            aria-label="Slide anterior">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button type="button" @click="next(); start()"
                            class="w-10 h-10 rounded-lg border border-white/30 bg-black/30 backdrop-blur hover:bg-black/50 flex items-center justify-center transition"
                            aria-label="Próximo slide">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</section>

{{-- ═══════════ 2B. BIKE SELECTOR ═══════════ --}}
<section class="relative border-b border-line overflow-hidden"
         style="background: linear-gradient(180deg, #EDECE7 0%, #F5F4F0 100%);">
    {{-- Borda superior azul fina (signal) --}}
    <div class="absolute top-0 left-0 right-0 h-[2px]" style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    <div class="container-tmac py-10 md:py-14 relative">
        @livewire('site.bike-selector')
    </div>
</section>

{{-- ═══════════ 2C. BANDA INSTITUCIONAL — Centro de Distribuição ═══════════ --}}
<section class="relative bg-ink text-white overflow-hidden">
    {{-- Foto full-bleed industrial (CD TMAC) --}}
    <img src="{{ asset('storage/images/cd-tmac.jpg') }}"
         alt="Centro de distribuição TMAC" loading="lazy"
         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=2400&q=70'"
         class="absolute inset-0 w-full h-full object-cover opacity-35">

    {{-- Overlay vertical pra legibilidade --}}
    <div class="absolute inset-0"
         style="background: linear-gradient(180deg, rgba(15,16,20,0.65) 0%, rgba(15,16,20,0.35) 50%, rgba(15,16,20,0.85) 100%);"></div>

    {{-- Trilha azul fina no topo --}}
    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    <div class="container-tmac relative py-16 md:py-24 lg:py-28">
        <div class="grid lg:grid-cols-12 gap-8 items-end">
            <div class="lg:col-span-7">
                <div class="section-marker section-marker--on-dark">
                    <span class="section-marker__num">01·B</span>
                    <span class="section-marker__label">Centro de distribuição próprio</span>
                </div>
                <h2 class="font-display font-black text-[36px] md:text-[56px] lg:text-[68px] leading-[0.92] tracking-tightest text-white mt-4 uppercase max-w-[14ch]">
                    Estoque <span class="text-signal">imediato</span>,<br>envio nacional.
                </h2>
                <p class="mt-5 text-white/75 text-[15px] md:text-[17px] leading-relaxed max-w-[52ch]">
                    Operamos com CD próprio e mais de 12 mil SKUs em prateleira. Picking, separação e expedição no mesmo dia útil para pedidos aprovados até 14h.
                </p>
            </div>

            <div class="lg:col-span-5">
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white/[0.06] backdrop-blur border border-white/10 rounded-lg p-4 text-center">
                        <div class="font-display font-black text-[28px] md:text-[34px] leading-none tracking-tighter text-white">12k</div>
                        <div class="mt-2 font-mono text-[9px] uppercase tracking-[0.08em] text-white/55 leading-tight">SKUs em prateleira</div>
                    </div>
                    <div class="bg-signal/15 backdrop-blur border border-signal/30 rounded-lg p-4 text-center">
                        <div class="font-display font-black text-[28px] md:text-[34px] leading-none tracking-tighter text-white">24h</div>
                        <div class="mt-2 font-mono text-[9px] uppercase tracking-[0.08em] text-white/65 leading-tight">Para expedição</div>
                    </div>
                    <div class="bg-white/[0.06] backdrop-blur border border-white/10 rounded-lg p-4 text-center">
                        <div class="font-display font-black text-[28px] md:text-[34px] leading-none tracking-tighter text-white">26</div>
                        <div class="mt-2 font-mono text-[9px] uppercase tracking-[0.08em] text-white/55 leading-tight">Estados ativos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Trilha azul fina na base --}}
    <div class="absolute bottom-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>
</section>

{{-- ═══════════ 2D. UNIVERSO TMAC — LINHAS PRÓPRIAS ═══════════ --}}
@if($tmacBrands->isNotEmpty())
<section class="relative bg-bg overflow-hidden">
    {{-- Faixa diagonal sutil de fundo --}}
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
         style="background-image: repeating-linear-gradient(115deg, transparent, transparent 80px, #1A1B1F 80px, #1A1B1F 82px);"></div>

    {{-- Mega "UNIVERSO" decorativo --}}
    <div class="absolute -top-4 right-4 hidden md:block pointer-events-none select-none opacity-[0.04] leading-none">
        <span class="font-display font-black tracking-tightest text-ink" style="font-size: clamp(120px, 18vw, 260px); letter-spacing: -0.06em;">TMAC</span>
    </div>

    <div class="container-tmac py-14 md:py-20 relative">
        {{-- Header da seção --}}
        <div class="grid lg:grid-cols-12 gap-6 items-end mb-10">
            <div class="lg:col-span-7">
                <div class="section-marker section-marker--lg">
                    <span class="section-marker__num">02·C</span>
                    <span class="section-marker__label">Universo TMAC · Linhas próprias</span>
                </div>
                <h2 class="font-display font-black text-[34px] md:text-[52px] leading-[0.95] tracking-tightest text-ink mt-4 uppercase">
                    <span class="text-accent">Marcas próprias</span><br>
                    desenvolvidas pela TMAC.
                </h2>
                <p class="mt-4 text-ink-soft text-[15px] md:text-[17px] leading-relaxed max-w-[58ch]">
                    Linhas exclusivas pensadas pro mercado brasileiro. Importação direta, controle de qualidade próprio e identidade visual única — só na TMAC.
                </p>
            </div>
            <div class="lg:col-span-5 lg:text-right">
                <a href="{{ route('site.tmac-brands') }}" class="btn-dark">
                    Conheça o Universo TMAC
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Grid de linhas (cards SÓLIDOS na cor da marca, estilo brand-blocks) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($tmacBrands as $idx => $tb)
                @php
                    $bg = $tb->resolved_brand_color;
                    $fg = $tb->resolved_text_color;
                    $dark = $tb->darkened_color;
                @endphp
                <a href="{{ $tb->link_url ?: route('site.tmac-brand', $tb->slug) }}"
                   class="tmac-brand-card group"
                   style="--brand-bg: {{ $bg }}; --brand-fg: {{ $fg }}; --brand-dark: {{ $dark }};">

                    {{-- Foto de fundo opcional (bem sutil, multiply) --}}
                    @if($tb->image_url)
                        <img src="{{ $tb->image_url }}" alt="" aria-hidden="true" loading="lazy"
                             class="tmac-brand-card__bg">
                    @endif

                    {{-- Overlay diagonal sutil pra textura --}}
                    <div class="tmac-brand-card__texture"></div>

                    {{-- Logo / Nome no topo --}}
                    <div class="tmac-brand-card__head">
                        @if($tb->logo_url)
                            <img src="{{ $tb->logo_url }}" alt="{{ $tb->name }}" class="h-9 object-contain object-left {{ $tb->text_theme === 'light' ? 'brightness-0 invert' : '' }}">
                        @else
                            <span class="tmac-brand-card__name">{{ $tb->name }}</span>
                        @endif
                    </div>

                    {{-- Descrição --}}
                    <p class="tmac-brand-card__desc">
                        {{ \Illuminate\Support\Str::limit($tb->description, 130) }}
                    </p>

                    {{-- Footer: badge + arrow --}}
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
@endif

{{-- ═══════════ 3. DESTAQUES PARA COTAÇÃO ═══════════ --}}
@if($featuredProducts->isNotEmpty())
<section class="bg-bg relative overflow-hidden">
    {{-- Mega "02" decorativo --}}
    <div class="absolute -top-6 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">02</span>
    </div>

    <div class="container-tmac py-12 md:py-16 grid lg:grid-cols-12 gap-8 relative">
        <div class="lg:col-span-4">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">02</span>
                <span class="section-marker__label">Produtos em destaque</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[44px] leading-[1] tracking-tightest text-ink mt-3">
                Destaques para <span class="text-signal">cotação</span>.
            </h2>
            <p class="mt-4 text-ink-soft text-[15px] leading-relaxed">
                Confira alguns dos produtos mais procurados pelos nossos clientes lojistas e oficinas.
            </p>
            <a href="{{ route('site.products') }}" class="btn-ghost mt-6">
                Ver todos os produtos
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="lg:col-span-8 grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($featuredProducts->take(4) as $i => $product)
                @php $badge = $featuredBadges[$i] ?? null; @endphp
                <article class="p-card relative">
                    @if($badge)
                        <span class="absolute top-2 left-2 z-10 text-[9px] font-mono font-semibold uppercase tracking-[0.06em] text-white px-2 py-1 rounded-sm {{ $badge[1] }}">
                            {{ $badge[0] }}
                        </span>
                    @endif
                    <a href="{{ route('site.product', $product->slug) }}" class="p-card__img block">
                        @if($product->main_image)
                            <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-full object-contain p-3">
                        @else
                            <span class="opacity-50">{{ $product->brand?->name }}</span>
                        @endif
                    </a>
                    <div class="p-card__body">
                        <h3 class="p-card__title">
                            <a href="{{ route('site.product', $product->slug) }}" class="hover:text-accent transition">{{ $product->name }}</a>
                        </h3>
                        @if($product->brand)<div class="p-card__brand">{{ $product->brand->name }}</div>@endif
                        <button type="button"
                                onclick="TMAC.addToQuote({{ $product->id }}, 1, { sku: '{{ $product->sku }}' })"
                                class="mt-2 w-full bg-bg-sunken hover:bg-ink hover:text-white border border-line text-ink text-[12px] font-semibold py-2 rounded transition flex items-center justify-center gap-1.5">
                            Solicitar cotação
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════ 4. CATEGORIAS — encontre tudo ═══════════ --}}
@if($featuredCategories->isNotEmpty())
<section class="bg-ink text-white relative overflow-hidden">
    {{-- Mega "03" decorativo --}}
    <div class="absolute -top-8 -left-4 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--on-dark">03</span>
    </div>

    <div class="container-tmac py-12 md:py-16 relative">
        <div class="text-center">
            <div class="section-marker section-marker--lg section-marker--on-dark justify-center inline-flex">
                <span class="section-marker__num">03</span>
                <span class="section-marker__label">Principais categorias</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[44px] leading-[1] tracking-tightest text-white mt-3">
                Encontre tudo que sua <span class="text-signal">loja precisa</span>.
            </h2>
        </div>

        <div class="mt-10 grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($featuredCategories->take(8) as $cat)
                <a href="{{ route('site.products', ['cat' => [$cat->slug]]) }}" class="cat-tile-v2 group">
                    {{-- Área da imagem — fundo claro, imagem contida (não estoura) --}}
                    <div class="cat-tile-v2__media">
                        @if($cat->image_path)
                            <img src="{{ asset('storage/'.$cat->image_path) }}"
                                 alt="{{ $cat->name }}" loading="lazy" decoding="async">
                        @else
                            <svg class="w-10 h-10 text-ink-faint/40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/></svg>
                        @endif
                    </div>

                    {{-- Faixa com o nome --}}
                    <div class="cat-tile-v2__label">
                        <span>{{ $cat->name }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-200"
                             fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('site.products') }}" class="btn bg-white/10 backdrop-blur text-white border border-white/20 hover:bg-white/15">
                Ver todas as categorias
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════════ 5. VANTAGENS — 8 cards de features ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    {{-- Mega "04" decorativo --}}
    <div class="absolute -top-8 -right-8 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">04</span>
    </div>

    <div class="container-tmac py-12 md:py-16 relative">
        <div class="text-center">
            <div class="section-marker section-marker--lg justify-center inline-flex">
                <span class="section-marker__num">04</span>
                <span class="section-marker__label">Por que comprar com a TMAC?</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[44px] leading-[1] tracking-tightest text-ink mt-3">
                Vantagens que fazem <span class="text-signal">a diferença</span>.
            </h2>
        </div>

        <div class="mt-10 grid grid-cols-2 md:grid-cols-4 gap-3">
            @php
                $advantages = [
                    ['box',       'Importação direta',  'Produtos originais com os melhores preços.'],
                    ['clock',     'Estoque imediato',   'Mais de 12 mil itens prontos para envio.'],
                    ['headset',   'Atendimento técnico','Suporte especializado para sua loja.'],
                    ['chart',     'Tabela por volume',  'Condições especiais conforme sua compra.'],
                    ['plane',     'Envio nacional',     'Entrega para todo o Brasil com agilidade.'],
                    ['flash',     'Cotação rápida',     'Receba sua tabela sem burocracia.'],
                    ['users',     'Representantes',     'Atendimento em 26 estados.'],
                    ['shield',    'Qualidade garantida','Trabalhamos apenas com marcas de confiança.'],
                ];
            @endphp
            @foreach($advantages as $idx => [$icon, $title, $sub])
                @php
                    // Alterna cores: vermelho/azul/preto para criar ritmo visual
                    $iconBg = match($idx % 3) {
                        0 => 'bg-accent-soft text-accent',
                        1 => 'bg-signal-soft text-signal',
                        2 => 'bg-ink text-white',
                    };
                @endphp
                <div class="card p-5 hover:border-ink/40 transition relative overflow-hidden group">
                    <div class="w-10 h-10 rounded-lg {{ $iconBg }} flex items-center justify-center mb-4 relative z-10">
                        @switch($icon)
                            @case('box')     <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg> @break
                            @case('clock')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> @break
                            @case('headset') <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 14v-2a9 9 0 0 1 18 0v2M3 14v3a2 2 0 0 0 2 2h2v-7H5a2 2 0 0 0-2 2zm18 0v3a2 2 0 0 1-2 2h-2v-7h2a2 2 0 0 1 2 2z"/></svg> @break
                            @case('chart')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v18h18M7 14l4-4 4 4 5-5"/></svg> @break
                            @case('plane')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 12h20M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg> @break
                            @case('flash')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg> @break
                            @case('users')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg> @break
                            @case('shield')  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg> @break
                        @endswitch
                    </div>
                    <div class="font-display font-bold text-[15px] tracking-tightish text-ink leading-tight">{{ $title }}</div>
                    <p class="mt-1.5 text-[13px] text-ink-soft leading-relaxed">{{ $sub }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 6. MARCAS — carrossel preto ═══════════ --}}
@if($featuredBrands->isNotEmpty())
<section class="bg-ink text-white border-y border-white/10">
    <div class="container-tmac py-10 md:py-12">
        <div class="text-center mb-6">
            <div class="font-mono text-[10px] uppercase tracking-[0.12em] text-white/55">
                Trabalhamos com as principais marcas do setor
            </div>
        </div>
        <div class="flex items-center gap-6 overflow-x-auto scrollbar-none py-4">
            @foreach($featuredBrands as $brand)
                <a href="{{ route('site.brand', $brand->slug) }}"
                   class="flex-shrink-0 px-6 py-3 flex items-center justify-center min-w-[140px] hover:opacity-100 opacity-70 transition">
                    @if($brand->logo_path)
                        <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }}" loading="lazy" class="h-9 object-contain brightness-0 invert">
                    @else
                        <span class="font-display font-extrabold text-[18px] tracking-tightish text-white">{{ strtoupper($brand->name) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="text-center font-mono text-[10px] uppercase tracking-[0.06em] text-white/40 mt-2">
            Distribuição oficial e homologada
        </div>
    </div>
</section>
@endif

{{-- ═══════════ 7. COMO FUNCIONA — 4 passos ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    {{-- Mega "05" decorativo --}}
    <div class="absolute top-8 -left-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">05</span>
    </div>

    <div class="container-tmac py-12 md:py-16 grid lg:grid-cols-12 gap-8 items-start relative">
        <div class="lg:col-span-4">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">05</span>
                <span class="section-marker__label">Como funciona</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[40px] leading-[1] tracking-tightest text-ink mt-3">
                Do pedido à <span class="text-signal">entrega</span>,<br>simples e rápido.
            </h2>
            <p class="mt-4 text-ink-soft text-[15px] leading-relaxed">
                Processo descomplicado para você focar no que realmente importa: suas vendas.
            </p>
            <a href="{{ route('site.quote') }}" class="btn-primary mt-6">
                Solicitar cotação
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="lg:col-span-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['Solicite sua cotação',   'Preencha o formulário e envie sua solicitação.',     'doc'],
                ['Receba sua tabela',      'Nossa equipe envia sua tabela personalizada.',       'mail'],
                ['Aprove seu pedido',      'Confirme os produtos e condições.',                  'check'],
                ['Receba em sua loja',     'Enviamos para todo o Brasil com agilidade.',         'truck'],
            ] as $i => [$title, $sub, $icon])
                <div class="relative text-center">
                    @if(!$loop->last)
                        <div class="hidden md:block absolute top-7 left-[60%] right-[-40%] border-t-2 border-dashed border-signal/30"></div>
                    @endif
                    <div class="relative inline-flex items-center justify-center w-14 h-14 rounded-full bg-bg-elev border border-line text-ink">
                        @switch($icon)
                            @case('doc')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8Z"/><path d="M14 3v5h5M8 13h8M8 17h5"/></svg> @break
                            @case('mail')  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg> @break
                            @case('check') <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg> @break
                            @case('truck') <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h13v10H3zM16 10h4l1 3v3h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg> @break
                        @endswitch
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-signal text-white font-mono text-[11px] font-bold flex items-center justify-center border-2 border-bg">{{ $i + 1 }}</span>
                    </div>
                    <div class="font-display font-bold text-[15px] tracking-tightish text-ink mt-4 leading-tight">{{ $title }}</div>
                    <p class="mt-1.5 text-[13px] text-ink-soft leading-relaxed">{{ $sub }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 8. SOCIAL PROOF / DEPOIMENTOS ═══════════ --}}
<section class="bg-bg-sunken">
    <div class="container-tmac py-12 md:py-16 grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 relative rounded-xl overflow-hidden bg-ink min-h-[320px] flex items-end p-6">
            {{-- Foto de parceria TMAC (lojista + operação) --}}
            <img src="{{ asset('storage/images/parceria-tmac.jpg') }}"
                 alt="Parceria TMAC com lojistas em todo o Brasil" loading="lazy"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1632823469850-2f77dd9c7f93?auto=format&fit=crop&w=1600&q=70'"
                 class="absolute inset-0 w-full h-full object-cover">

            {{-- Overlay escuro + acento vermelho --}}
            <div class="absolute inset-0"
                 style="background: linear-gradient(160deg, rgba(15,16,20,0.92) 0%, rgba(26,27,31,0.55) 45%, rgba(42,20,23,0.85) 100%);"></div>

            {{-- Sticker no canto superior --}}
            <div class="absolute top-4 left-4 right-4 flex items-start justify-between gap-3 z-10">
                <span class="speed-badge bg-white/[0.08] border-white/15 text-white">
                    <span class="speed-badge__dot"></span>
                    Em números
                </span>
                <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
                    06 · TMAC
                </span>
            </div>

            <div class="grid grid-cols-1 gap-3 w-full relative z-10">
                <div class="bg-accent text-white rounded-lg px-4 py-3 shadow-lg">
                    <div class="font-display font-black text-[26px] leading-none tracking-tighter">+12 mil</div>
                    <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-white/80 mt-1">SKUs em estoque</div>
                </div>
                <div class="bg-white/[0.08] backdrop-blur border border-white/15 text-white rounded-lg px-4 py-3">
                    <div class="font-display font-black text-[22px] leading-none tracking-tighter">+3,2k</div>
                    <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-white/65 mt-1">Lojistas ativos</div>
                </div>
                <div class="bg-white/[0.08] backdrop-blur border border-white/15 text-white rounded-lg px-4 py-3">
                    <div class="font-display font-black text-[22px] leading-none tracking-tighter">26</div>
                    <div class="text-[10px] font-mono uppercase tracking-[0.08em] text-white/65 mt-1">Estados atendidos</div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">06</span>
                <span class="section-marker__label">Quem confia, recomenda</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[36px] leading-[1] tracking-tightest text-ink mt-3">
                Parceria que gera <span class="text-signal">resultados</span>.
            </h2>

            <div class="mt-6 grid sm:grid-cols-3 gap-3">
                @php
                    // Prioriza depoimentos cadastrados no admin; se nenhum, usa fallback demo (para não deixar seção vazia).
                    $items = ($testimonials ?? collect())->isNotEmpty()
                        ? $testimonials->map(fn ($t) => [
                            'name'   => $t->name,
                            'role'   => $t->role,
                            'quote'  => $t->quote,
                            'rating' => $t->rating,
                        ])->all()
                        : [
                            ['name' => 'João Silva',    'role' => 'Auto Peças Silva — SP', 'quote' => '"A TMAC é nossa principal fornecedora. Produtos de qualidade e atendimento sempre rápido."', 'rating' => 5],
                            ['name' => 'Carlos Santos', 'role' => 'Motos Center — MG',     'quote' => '"Variedade incrível de produtos e preços muito competitivos. Recomendo!"',                     'rating' => 5],
                            ['name' => 'Roberto Lima',  'role' => 'Racing Parts — PR',     'quote' => '"Atendimento técnico excelente e entrega sempre dentro do prazo."',                          'rating' => 5],
                        ];
                @endphp
                @foreach($items as $item)
                    <div class="card p-4">
                        <div class="flex gap-0.5 text-warn mb-2">
                            @for($s = 0; $s < ($item['rating'] ?? 5); $s++)<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4.5L18 22l-6-4-6 4 1.5-8.5L2 9h7z"/></svg>@endfor
                        </div>
                        <p class="text-[13px] text-ink-soft leading-relaxed">{{ $item['quote'] }}</p>
                        <div class="mt-3 pt-3 border-t border-line-soft">
                            <div class="font-display font-bold text-[13px] tracking-tightish">{{ $item['name'] }}</div>
                            @if(!empty($item['role']))
                                <div class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint mt-0.5">{{ $item['role'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 9. CTA FINAL VERMELHO ═══════════ --}}
<section class="relative overflow-hidden bg-accent text-white">
    {{-- Foto de moto de fundo (bem sutil, multiply pra integrar com o vermelho) --}}
    <img src="https://images.unsplash.com/photo-1547549700-bf12d5e5ce8a?auto=format&fit=crop&w=2000&q=70"
         alt="" aria-hidden="true" loading="lazy"
         class="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-multiply pointer-events-none">

    {{-- Glow azul radial canto direito --}}
    <div class="absolute -top-20 -right-20 w-80 h-80 pointer-events-none"
         style="background: radial-gradient(circle, rgba(0,77,255,0.35), transparent 70%);"></div>
    {{-- Trail luminosa no topo --}}
    <div class="speed-trail absolute top-0 left-0 right-0 h-[3px] bg-black/20"></div>

    <div class="container-tmac relative py-12 md:py-16 grid lg:grid-cols-2 gap-6 items-center">
        <div>
            <div class="section-marker">
                <span class="section-marker__num" style="background: #fff; color: #E11D2A;">07</span>
                <span class="section-marker__label" style="color: rgba(255,255,255,0.8);">Receba sua tabela atacadista</span>
            </div>
            <h2 class="font-display font-black text-[28px] md:text-[44px] leading-[0.95] tracking-tightest text-white mt-3 uppercase">
                Preços especiais<br>para <span class="underline decoration-4 underline-offset-4" style="text-decoration-color: rgba(0,77,255,0.9);">sua loja</span>.
            </h2>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-[12px]">
                @foreach([
                    ['flash',  'Atendimento rápido',    'e sem burocracia'],
                    ['chart',  'Tabela por volume',     'e condições exclusivas'],
                    ['users',  'Suporte comercial',     'especializado'],
                ] as [$icon, $title, $sub])
                    <div class="flex items-start gap-2">
                        <div class="w-7 h-7 rounded-md bg-white/15 border border-white/15 flex items-center justify-center flex-shrink-0">
                            @switch($icon)
                                @case('flash') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg> @break
                                @case('chart') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v18h18M7 14l4-4 4 4 5-5"/></svg> @break
                                @case('users') <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> @break
                            @endswitch
                        </div>
                        <div class="leading-tight">
                            <div class="font-semibold">{{ $title }}</div>
                            <div class="text-white/75 mt-0.5">{{ $sub }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex lg:justify-end">
            <a href="{{ route('site.quote') }}" class="btn bg-white text-ink hover:bg-white/90 text-base px-6">
                Solicitar cotação agora
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
