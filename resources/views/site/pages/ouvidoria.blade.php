@extends('layouts.site')

@section('content')

{{-- ═══════════ 1. HERO ═══════════ --}}
<section class="relative bg-ink text-white overflow-hidden" style="min-height: clamp(340px, 48vh, 500px);">
    @php $heroImg = $page?->hero_image ? asset('storage/' . $page->hero_image) : 'https://images.unsplash.com/photo-1591537220787-5f7d63d2e1a1?auto=format&fit=crop&w=2400&q=70'; @endphp
    <img src="{{ $heroImg }}"
         alt="{{ $page->title ?? 'Ouvidoria — TMAC Import' }}"
         fetchpriority="high"
         class="absolute inset-0 w-full h-full object-cover opacity-40">

    <div class="absolute inset-0"
         style="background:
            linear-gradient(180deg, rgba(15,16,20,0.60) 0%, rgba(15,16,20,0.35) 40%, rgba(15,16,20,0.95) 100%),
            radial-gradient(ellipse at 20% 100%, rgba(0,77,255,0.28), transparent 50%);"></div>

    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    <nav class="container-tmac relative z-10 pt-4">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em] text-white/60">
            <a href="{{ route('site.home') }}" class="hover:text-white transition">Início</a>
            <span class="text-white/30">/</span>
            <span class="text-white">Ouvidoria</span>
        </div>
    </nav>

    <div class="container-tmac absolute bottom-0 left-1/2 -translate-x-1/2 right-0 z-10 pb-10 md:pb-14">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
            <span class="speed-badge">
                <span class="speed-badge__dot"></span>
                {{ $page?->hero_eyebrow ?: 'Ouvidoria TMAC' }}
            </span>
            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
                Resposta em até 2 dias úteis
            </div>
        </div>

        <h1 class="font-display font-black uppercase leading-[0.92] tracking-tightest text-white
                   text-[40px] sm:text-[56px] md:text-[76px] lg:text-[88px] max-w-[14ch]">
            @php
                $heroTitle = $page?->hero_title ?: 'Sua voz importa.';
                $heroHighlight = $page?->hero_highlight ?: 'importa.';
            @endphp
            {!! preg_replace('/(' . preg_quote($heroHighlight, '/') . ')/i', '<span class="text-signal">$1</span>', e($heroTitle), 1) !!}
        </h1>

        <p class="mt-5 text-white/75 text-[15px] md:text-[18px] leading-relaxed max-w-[58ch]">
            {{ $page?->hero_description ?: 'Sugestões, reclamações ou elogios. Canal direto com nossa ouvidoria, lido por gente real — não vai pra um robô.' }}
        </p>
    </div>
</section>

{{-- ═══════════ 2. CARDS DE ORIENTAÇÃO ═══════════ --}}
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-8 md:py-10">
        @php
            $defaultCards = [
                ['title' => 'Sugestão',    'description' => 'Tem uma ideia pra melhorar nosso atendimento, catálogo ou serviço?', 'accent' => 'signal'],
                ['title' => 'Reclamação',  'description' => 'Algo deu errado? Nos conta pra que possamos corrigir e ajustar.',    'accent' => 'accent'],
                ['title' => 'Elogio',      'description' => 'Atendimento bom merece reconhecimento. Compartilha sua experiência.', 'accent' => 'whatsapp'],
            ];
            $cards = $page?->data('cards') ?: $defaultCards;
        @endphp

        <div class="grid sm:grid-cols-3 gap-3">
            @foreach($cards as $i => $card)
                @php
                    $accent = $card['accent'] ?? ['signal', 'accent', 'whatsapp'][$i % 3];
                    $iconClass = match($accent) {
                        'accent'   => 'bg-accent-soft text-accent',
                        'whatsapp' => 'bg-whatsapp/15 text-whatsapp',
                        default    => 'bg-signal-soft text-signal',
                    };
                @endphp
                <div class="card p-4 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $iconClass }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2l2.5 5 5.5 1-4 4 1 5.5L12 15l-5 2.5 1-5.5-4-4 5.5-1z"/></svg>
                    </div>
                    <div>
                        <div class="font-display font-bold text-[14px] text-ink leading-tight">{{ $card['title'] }}</div>
                        <p class="text-[12.5px] text-ink-soft mt-1 leading-relaxed">{{ $card['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 3. FORMULÁRIO ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="absolute -top-10 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">01</span>
    </div>

    <div class="container-tmac py-14 md:py-20 grid lg:grid-cols-12 gap-10 lg:gap-16 relative">
        <div class="lg:col-span-5">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">01</span>
                <span class="section-marker__label">Manifeste-se · Ouvidoria</span>
            </div>
            <h2 class="font-display font-black text-[32px] md:text-[44px] leading-[0.95] tracking-tightest text-ink mt-4 uppercase">
                Sua mensagem<br>
                <span class="text-signal">vai direto pro time</span>.
            </h2>
            <p class="mt-5 text-ink-soft text-[15px] md:text-[16px] leading-relaxed max-w-[48ch]">
                Aqui não tem ticket nem URA. Todo retorno é feito por pessoa real do nosso time, com prazo de até 2 dias úteis.
            </p>

            <div class="mt-7 space-y-3">
                <div class="flex items-start gap-3 p-3.5 rounded-lg bg-bg-elev border border-line">
                    <div class="w-8 h-8 rounded-md bg-ink text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[13px] text-ink leading-tight">Confidencialidade</div>
                        <div class="text-[12px] text-ink-soft mt-0.5">Suas informações ficam restritas à equipe responsável.</div>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-lg bg-bg-elev border border-line">
                    <div class="w-8 h-8 rounded-md bg-signal text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[13px] text-ink leading-tight">Resposta em até 2 dias úteis</div>
                        <div class="text-[12px] text-ink-soft mt-0.5">Para urgências, use o WhatsApp ou o canal comercial.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="card p-6 md:p-8">
                <livewire:site.lead-form
                    source="{{ \App\Models\Lead::SOURCE_FEEDBACK }}"
                    variant="full"
                    recipient-setting-key="feedback_email"
                    title="Envie sua manifestação"
                    :ask-subject-choice="true"
                    :subject-choices="['Sugestão', 'Reclamação', 'Elogio', 'Dúvida geral']"
                    :ask-company="false"
                    :ask-state="false"
                    cta-label="Enviar manifestação"
                    success-message="Recebemos sua manifestação. A equipe de ouvidoria entrará em contato em até 2 dias úteis." />
            </div>
        </div>
    </div>
</section>

@endsection
