@extends('layouts.site')

@section('content')

{{-- ═══════════ 1. HERO FULL-BLEED CINEMATOGRÁFICO ═══════════ --}}
<section class="relative overflow-hidden bg-ink text-white"
         style="min-height: clamp(560px, 78vh, 760px);">
    {{-- Foto wide moody --}}
    @php $heroImg = $page->hero_image ? asset('storage/' . $page->hero_image) : 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=2400&q=75'; @endphp
    <img src="{{ $heroImg }}"
         alt="{{ $page->title ?? 'TMAC Import' }}"
         fetchpriority="high"
         class="absolute inset-0 w-full h-full object-cover opacity-55">

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

    {{-- Conteúdo do hero (parte inferior) --}}
    <div class="container-tmac absolute bottom-0 left-1/2 -translate-x-1/2 right-0 z-10 pb-10 md:pb-14">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
            <span class="speed-badge">
                <span class="speed-badge__dot"></span>
                {{ $page->hero_eyebrow ?: 'Ao universo TMAC' }}
            </span>
            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
                Desde 2016 · B2B · CNPJ
            </div>
        </div>

        <h1 class="font-display font-black uppercase leading-[0.92] tracking-tightest text-white
                   text-[44px] sm:text-[64px] md:text-[88px] lg:text-[108px] max-w-[14ch]">
            @php
                $heroTitle = $page->hero_title ?: 'Quem somos.';
                $heroHighlight = $page->hero_highlight ?: 'somos';
            @endphp
            {!! preg_replace('/(' . preg_quote($heroHighlight, '/') . ')/i', '<span class="text-accent">$1</span>', e($heroTitle), 1) !!}
        </h1>

        <p class="mt-5 text-white/75 text-[15px] md:text-[18px] leading-relaxed max-w-[58ch]">
            {{ $page->hero_description ?: 'Distribuidor atacadista de peças e acessórios para motocicleta. Importação direta da China, atendimento exclusivo CNPJ, logística para todo o Brasil.' }}
        </p>
    </div>

    {{-- Indicador de scroll --}}
    <div class="hidden md:flex absolute bottom-6 right-6 z-10 items-center gap-2 font-mono text-[10px] uppercase tracking-[0.12em] text-white/50 animate-pulse">
        <span>Role</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </div>
</section>

{{-- ═══════════ 2. STORY — POR QUE ESCOLHER A TMAC? ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    {{-- Mega "01" decorativo --}}
    <div class="absolute -top-10 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">01</span>
    </div>

    <div class="container-tmac py-16 md:py-24 grid lg:grid-cols-12 gap-10 lg:gap-16 relative">
        <div class="lg:col-span-5">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">01</span>
                <span class="section-marker__label">Por que escolher a TMAC?</span>
            </div>
            <h2 class="font-display font-black text-[36px] md:text-[52px] leading-[0.95] tracking-tightest text-ink mt-4 uppercase">
                Distribuidor.<br>
                <span class="text-signal">Não revenda final.</span>
            </h2>

            <div class="mt-8 flex items-baseline gap-3">
                <div class="font-display font-black text-[64px] md:text-[80px] leading-none tracking-tightest text-ink">
                    2016
                </div>
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint leading-tight">
                    Início das<br>operações
                </div>
            </div>

            {{-- Stats compactos --}}
            <div class="mt-8 pt-6 border-t border-line grid grid-cols-3 divide-x divide-line">
                <div class="pr-3">
                    <div class="font-display font-extrabold text-[22px] md:text-[28px] tracking-tighter text-ink leading-none">12k+</div>
                    <div class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint leading-tight">SKUs</div>
                </div>
                <div class="px-3">
                    <div class="font-display font-extrabold text-[22px] md:text-[28px] tracking-tighter text-ink leading-none">3,2k</div>
                    <div class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint leading-tight">Lojistas</div>
                </div>
                <div class="pl-3">
                    <div class="font-display font-extrabold text-[22px] md:text-[28px] tracking-tighter text-ink leading-none">26</div>
                    <div class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint leading-tight">Estados</div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7 space-y-6">
            @if($page->content)
                <div class="prose max-w-none text-ink-soft text-[15px] md:text-[16px] leading-relaxed
                            prose-headings:font-display prose-headings:text-ink prose-headings:font-extrabold
                            prose-strong:text-ink prose-a:text-signal">
                    {!! $page->content !!}
                </div>
            @else
                {{-- Fallback narrativo (espelho do site antigo) --}}
                <p class="text-[16px] md:text-[18px] leading-relaxed text-ink-soft">
                    <span class="float-left font-display font-black text-[68px] leading-none tracking-tightest text-accent mr-3 mt-1">D</span>esde <strong class="text-ink">2016</strong>, a TMAC Import tem se destacado no mercado de motopeças e acessórios, oferecendo produtos de excelência e satisfazendo os clientes com parcerias sólidas e padrões de mercado elevados. Importamos diretamente da indústria chinesa através de nossa filial, facilitando o processo com os renomados fabricantes.
                </p>

                <p class="text-[15px] md:text-[16px] leading-relaxed text-ink-soft">
                    Somos comprometidos com a <strong class="text-ink">inovação, excelência e responsabilidade social</strong>, investimos constantemente na melhoria da infraestrutura logística e na qualidade dos produtos, garantindo que cada peça atenda às exigências dos motociclistas brasileiros.
                </p>

                <div class="p-5 md:p-6 bg-signal-soft/40 border-l-4 border-signal rounded-r-lg">
                    <p class="font-display font-bold text-[17px] md:text-[19px] leading-snug tracking-tightish text-ink">
                        "Estamos determinados a seguir crescendo e convidamos nossos clientes a fazerem parte desta jornada com a TMAC."
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ═══════════ 3. FULL-BLEED IMAGE BREAKER #1 ═══════════ --}}
<section class="relative bg-ink overflow-hidden" style="height: clamp(320px, 50vh, 520px);">
    <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=2400&q=75"
         alt="Detalhe de motor de motocicleta"
         loading="lazy"
         class="absolute inset-0 w-full h-full object-cover opacity-60">

    <div class="absolute inset-0"
         style="background: linear-gradient(90deg, rgba(15,16,20,0.85) 0%, rgba(15,16,20,0.30) 50%, rgba(15,16,20,0.85) 100%);"></div>

    {{-- Quote overlay --}}
    <div class="absolute inset-0 flex items-center">
        <div class="container-tmac">
            <div class="max-w-[680px]">
                <div class="font-mono text-[10px] md:text-[11px] uppercase tracking-[0.12em] text-signal mb-4">
                    Manifesto · TMAC
                </div>
                <p class="font-display font-black uppercase leading-[0.95] tracking-tightest text-white
                          text-[28px] md:text-[44px] lg:text-[56px]">
                    Cada peça atende<br>
                    <span class="text-accent">o motociclista</span><br>
                    brasileiro.
                </p>
            </div>
        </div>
    </div>

    {{-- Trilhas azuis --}}
    <div class="absolute top-0 left-0 right-0 h-[2px]" style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>
    <div class="absolute bottom-0 left-0 right-0 h-[2px]" style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>
</section>

{{-- ═══════════ 4. 4 PILARES — UMA LINHA COMPLETA ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="absolute -top-8 -left-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">02</span>
    </div>

    <div class="container-tmac py-16 md:py-20 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="section-marker section-marker--lg justify-center inline-flex">
                <span class="section-marker__num">02</span>
                <span class="section-marker__label">Nossos pilares</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[44px] leading-[1] tracking-tightest text-ink mt-4 uppercase">
                Uma linha completa<br>
                <span class="text-signal">para o seu negócio</span>.
            </h2>
            <p class="mt-4 text-ink-soft text-[15px] md:text-[16px] leading-relaxed">
                Peças que atendem todas as necessidades de lojistas, oficinas e revendedores. Atendimento de ponta, tecnologia e logística pra você focar no que importa: vender mais.
            </p>
        </div>

        @php
            $pillars = [
                ['globe',  'Atendemos onde você estiver',  'Cobertura nacional com representantes em 26 estados e logística calibrada para o atacado.', 'accent'],
                ['headset','Suporte personalizado',        'Equipe comercial dedicada e atendimento técnico para cada perfil de cliente.',                'signal'],
                ['cpu',    'Tecnologia no dia a dia',      'Plataforma de cotação digital, catálogo em tempo real e acompanhamento pelo WhatsApp.',        'ink'],
                ['boxes',  'Estoque de profundidade',      'Mais de 12 mil SKUs em prateleira para os principais modelos do mercado nacional.',           'accent'],
            ];
        @endphp

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($pillars as $idx => [$icon, $title, $sub, $tone])
                @php
                    $toneClass = match($tone) {
                        'accent' => 'bg-accent text-white border-accent',
                        'signal' => 'bg-signal text-white border-signal',
                        'ink'    => 'bg-ink text-white border-ink',
                    };
                @endphp
                <div class="card p-5 hover:border-ink/40 transition group relative">
                    <span class="absolute top-3 right-4 font-mono text-[10px] text-ink-faint tracking-[0.06em]">
                        0{{ $idx + 1 }}
                    </span>

                    <div class="w-11 h-11 rounded-lg {{ $toneClass }} flex items-center justify-center mb-4 border">
                        @switch($icon)
                            @case('globe')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg> @break
                            @case('headset') <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 14v-2a9 9 0 0 1 18 0v2M3 14v3a2 2 0 0 0 2 2h2v-7H5a2 2 0 0 0-2 2zm18 0v3a2 2 0 0 1-2 2h-2v-7h2a2 2 0 0 1 2 2z"/></svg> @break
                            @case('cpu')     <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg> @break
                            @case('boxes')   <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg> @break
                        @endswitch
                    </div>

                    <div class="font-display font-bold text-[16px] tracking-tightish text-ink leading-tight">
                        {{ $title }}
                    </div>
                    <p class="mt-2 text-[13px] text-ink-soft leading-relaxed">
                        {{ $sub }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 5. UNIVERSO TMAC — MARCAS COM FOTOS ═══════════ --}}
<section class="bg-ink text-white relative overflow-hidden">
    <img src="https://images.unsplash.com/photo-1558981852-426c6c22a060?auto=format&fit=crop&w=2000&q=70"
         alt="" aria-hidden="true" loading="lazy"
         class="absolute inset-0 w-full h-full object-cover opacity-15">

    <div class="absolute inset-0 bg-gradient-to-b from-ink via-ink/85 to-ink"></div>

    <div class="absolute -top-8 -right-4 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--on-dark">03</span>
    </div>

    <div class="container-tmac py-16 md:py-24 relative">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="section-marker section-marker--lg section-marker--on-dark justify-center inline-flex">
                <span class="section-marker__num">03</span>
                <span class="section-marker__label">Marcas · Conheça o Universo TMAC</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[48px] leading-[0.95] tracking-tightest text-white mt-4 uppercase">
                Quatro linhas.<br>
                <span class="text-signal">Um portfólio completo.</span>
            </h2>
        </div>

        @php
            $tmacBrands = \App\Models\TmacBrand::active()->ordered()->take(4)->get();
        @endphp

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

                    @if($tb->image_url)
                        <img src="{{ $tb->image_url }}" alt="" aria-hidden="true" loading="lazy"
                             class="tmac-brand-card__bg">
                    @endif

                    <div class="tmac-brand-card__texture"></div>

                    <div class="tmac-brand-card__head">
                        @if($tb->logo_url)
                            <img src="{{ $tb->logo_url }}" alt="{{ $tb->name }}" class="h-9 object-contain object-left {{ $tb->text_theme === 'light' ? 'brightness-0 invert' : '' }}">
                        @else
                            <span class="tmac-brand-card__name">{{ $tb->name }}</span>
                        @endif
                    </div>

                    <p class="tmac-brand-card__desc">
                        {{ \Illuminate\Support\Str::limit($tb->description, 130) }}
                    </p>

                    <div class="tmac-brand-card__foot">
                        <span class="tmac-brand-card__badge">{{ $tb->badge_label }}</span>
                        <span class="tmac-brand-card__arrow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center mt-10 flex flex-wrap gap-3 justify-center">
            <a href="{{ route('site.tmac-brands') }}" class="btn bg-accent text-white hover:bg-accent/90">
                Explorar Universo TMAC
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('site.brands') }}" class="btn bg-white/10 backdrop-blur text-white border border-white/20 hover:bg-white/15">
                Ver todas as marcas que distribuímos
            </a>
        </div>
    </div>
</section>

{{-- ═══════════ 6. FULL-BLEED IMAGE BREAKER #2 — LOGÍSTICA ═══════════ --}}
<section class="relative bg-ink overflow-hidden" style="height: clamp(280px, 42vh, 440px);">
    <img src="{{ asset('storage/images/quem-somos/qs-logistica.jpg') }}"
         alt="Logística TMAC"
         loading="lazy"
         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=2400&q=75'"
         class="absolute inset-0 w-full h-full object-cover opacity-50">

    <div class="absolute inset-0"
         style="background: linear-gradient(180deg, rgba(15,16,20,0.65) 0%, rgba(15,16,20,0.30) 50%, rgba(15,16,20,0.85) 100%);"></div>

    <div class="absolute inset-0 flex items-center">
        <div class="container-tmac">
            <div class="grid sm:grid-cols-3 gap-4 max-w-4xl">
                @foreach([
                    ['12k+', 'SKUs em prateleira'],
                    ['24h',  'Para expedição'],
                    ['26',   'Estados ativos'],
                ] as [$num, $label])
                    <div>
                        <div class="font-display font-black text-[42px] md:text-[64px] leading-none tracking-tightest text-white">
                            {{ $num }}
                        </div>
                        <div class="mt-2 font-mono text-[10px] md:text-[11px] uppercase tracking-[0.10em] text-white/65 leading-tight">
                            {{ $label }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 7. REPRESENTANTES — NÃO IMPORTA O LUGAR ═══════════ --}}
<section class="bg-bg-sunken relative overflow-hidden">
    <div class="absolute -top-8 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">04</span>
    </div>

    <div class="container-tmac py-16 md:py-24 grid lg:grid-cols-12 gap-10 items-center relative">
        <div class="lg:col-span-6 order-2 lg:order-1">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">04</span>
                <span class="section-marker__label">Representantes em todo o Brasil</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[48px] leading-[0.95] tracking-tightest text-ink mt-4 uppercase">
                Não importa<br>
                <span class="text-signal">o lugar.</span>
            </h2>
            <p class="mt-5 text-ink-soft text-[15px] md:text-[16px] leading-relaxed max-w-[52ch]">
                Selecione seu estado e encontre o representante mais próximo de você. Atendimento regional para responder rápido e entender a realidade do seu mercado.
            </p>

            <a href="{{ route('site.representatives') }}" class="btn-primary mt-6">
                Encontrar representante
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </a>

            <div class="mt-8 pt-6 border-t border-line grid grid-cols-3 gap-4">
                @foreach([
                    ['26', 'Estados'],
                    ['100%', 'Cobertura'],
                    ['24h', 'Resposta'],
                ] as [$n, $l])
                    <div>
                        <div class="font-display font-extrabold text-[26px] md:text-[32px] tracking-tighter text-ink leading-none">{{ $n }}</div>
                        <div class="mt-1.5 font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint leading-tight">{{ $l }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-6 order-1 lg:order-2">
            <div class="relative rounded-xl overflow-hidden aspect-[4/3] bg-ink">
                <img src="{{ asset('storage/images/quem-somos/qs-representantes.jpg') }}"
                     alt="Cobertura nacional TMAC"
                     loading="lazy"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1547549700-bf12d5e5ce8a?auto=format&fit=crop&w=1400&q=75'"
                     class="absolute inset-0 w-full h-full object-cover">

                <div class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/20 to-transparent"></div>

                <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between gap-3">
                    <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/80 bg-white/[0.10] backdrop-blur px-2.5 py-1.5 rounded border border-white/15">
                        Cobertura · Brasil
                    </span>
                    <span class="font-display font-black text-[20px] tracking-tighter text-white">
                        26 / 27
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 8. PRODUTOS CTA — QUALIDADE E TECNOLOGIA ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="absolute -top-8 -left-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">05</span>
    </div>

    <div class="container-tmac py-16 md:py-24 grid lg:grid-cols-12 gap-10 items-center relative">
        <div class="lg:col-span-5">
            <div class="relative rounded-xl overflow-hidden aspect-[4/5] bg-ink">
                <img src="{{ asset('storage/images/quem-somos/qs-produtos.jpg') }}"
                     alt="Linha de produtos TMAC"
                     loading="lazy"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1591537220787-5f7d63d2e1a1?auto=format&fit=crop&w=1200&q=75'"
                     class="absolute inset-0 w-full h-full object-cover">

                <div class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/10 to-transparent"></div>

                <div class="absolute top-4 left-4">
                    <span class="speed-badge bg-white/[0.10] border-white/15 text-white backdrop-blur">
                        <span class="speed-badge__dot"></span>
                        Catálogo TMAC
                    </span>
                </div>

                <div class="absolute bottom-4 left-4 right-4">
                    <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/70 mb-1">
                        Linhas
                    </div>
                    <div class="font-display font-black text-[28px] leading-none tracking-tightest text-white">
                        12k+ SKUs
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">05</span>
                <span class="section-marker__label">Qualidade e tecnologia</span>
            </div>
            <h2 class="font-display font-black text-[36px] md:text-[56px] leading-[0.92] tracking-tightest text-ink mt-4 uppercase">
                Conheça nossas<br>
                <span class="text-accent">linhas de produtos</span>.
            </h2>
            <p class="mt-5 text-ink-soft text-[15px] md:text-[17px] leading-relaxed max-w-[52ch]">
                Catálogo completo com peças, acessórios, iluminação, motor e linha premium. Tudo organizado por categoria e marca, com cotação rápida em poucos cliques.
            </p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('site.products') }}" class="btn-primary">
                    Ver produtos
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('site.quote') }}" class="btn-ghost">
                    Solicitar cotação
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 9. O QUE NÃO FAZEMOS — TRANSPARÊNCIA ═══════════ --}}
<section class="bg-bg-sunken border-t border-line">
    <div class="container-tmac py-12 md:py-16 max-w-3xl">
        <div class="section-marker">
            <span class="section-marker__num">06</span>
            <span class="section-marker__label">Transparência · O que não fazemos</span>
        </div>
        <h2 class="font-display font-black text-[26px] md:text-[36px] leading-tight tracking-tightest text-ink mt-3">
            Atacado é uma <span class="text-accent">escolha</span>.
        </h2>

        <div class="mt-6">
            @php
                $defaultRules = [
                    ['title' => 'Não vendemos para CPF.', 'sub' => 'Atacado exclusivo: somente CNPJ ativo com pedido mínimo do estado atendido.'],
                    ['title' => 'Não revendemos paralelo.', 'sub' => 'Trabalhamos só com fabricante original ou aftermarket homologado.'],
                    ['title' => 'Não atendemos sem retorno.', 'sub' => 'Cotação respondida no mesmo dia útil — sempre.'],
                ];
                $rules = $page->data('rules') ?: $defaultRules;
            @endphp
            @foreach($rules as $i => $rule)
                <div class="flex gap-3 py-4 border-t border-line {{ $loop->last ? 'border-b' : '' }}">
                    <span class="font-mono text-[11px] text-ink-faint pt-0.5">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <div class="font-semibold text-[15px] leading-tight">{{ $rule['title'] }}</div>
                        <div class="text-[13px] text-ink-soft mt-1 leading-relaxed">{{ $rule['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 10. CTA FINAL — FIQUE POR DENTRO ═══════════ --}}
<section class="relative overflow-hidden bg-accent text-white">
    <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=2000&q=70"
         alt="" aria-hidden="true" loading="lazy"
         class="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-multiply pointer-events-none">

    <div class="absolute -top-20 -right-20 w-80 h-80 pointer-events-none"
         style="background: radial-gradient(circle, rgba(0,77,255,0.35), transparent 70%);"></div>

    <div class="speed-trail absolute top-0 left-0 right-0 h-[3px] bg-black/20"></div>

    <div class="container-tmac relative py-14 md:py-20">
        <div class="grid lg:grid-cols-2 gap-8 items-center">
            <div>
                <div class="section-marker">
                    <span class="section-marker__num" style="background: #fff; color: #E11D2A;">07</span>
                    <span class="section-marker__label" style="color: rgba(255,255,255,0.85);">Fique por dentro da TMAC</span>
                </div>
                <h2 class="font-display font-black text-[30px] md:text-[48px] leading-[0.95] tracking-tightest text-white mt-4 uppercase">
                    Siga nas<br>
                    <span class="underline decoration-4 underline-offset-4" style="text-decoration-color: rgba(0,77,255,0.9);">redes sociais</span>.
                </h2>
                <p class="mt-4 text-white/80 text-[15px] md:text-[16px] leading-relaxed max-w-[44ch]">
                    Novidades, lançamentos e bastidores da operação no Instagram, Facebook, YouTube e TikTok da TMAC.
                </p>
            </div>

            <div class="flex flex-col items-start lg:items-end gap-4">
                <div class="flex flex-wrap gap-2.5">
                    @php
                        $socials = \App\Models\SocialLink::visible();
                    @endphp
                    @forelse($socials as $s)
                        <a href="{{ $s->url }}" target="_blank" rel="noopener"
                           class="w-12 h-12 rounded-full bg-white/15 backdrop-blur border border-white/25 flex items-center justify-center text-white hover:bg-white hover:text-accent transition"
                           aria-label="{{ $s->display_label }}">
                            <x-site.social-icon :platform="$s->platform" class="w-5 h-5"/>
                        </a>
                    @empty
                        <span class="font-mono text-[11px] uppercase tracking-[0.08em] text-white/60">
                            @tmacimport
                        </span>
                    @endforelse
                </div>

                <a href="{{ route('site.quote') }}" class="btn bg-white text-ink hover:bg-white/90 mt-2">
                    Solicitar cotação
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
