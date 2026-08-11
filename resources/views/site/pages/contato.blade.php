@extends('layouts.site')

@section('content')

@php
    $phone   = \App\Models\Setting::get('contact_phone');
    $email   = \App\Models\Setting::get('contact_email');
    $wpp     = \App\Models\Setting::get('whatsapp_number');
    $address = \App\Models\Setting::get('address');
@endphp

{{-- ═══════════ 1. HERO ═══════════ --}}
<section class="relative bg-ink text-white overflow-hidden" style="min-height: clamp(360px, 50vh, 520px);">
    @php $heroImg = $page?->hero_image ? asset('storage/' . $page->hero_image) : 'https://images.unsplash.com/photo-1632823469850-2f77dd9c7f93?auto=format&fit=crop&w=2400&q=70'; @endphp
    <img src="{{ $heroImg }}"
         alt="{{ $page->title ?? 'Contato — TMAC Import' }}"
         fetchpriority="high"
         class="absolute inset-0 w-full h-full object-cover opacity-45">

    <div class="absolute inset-0"
         style="background:
            linear-gradient(180deg, rgba(15,16,20,0.55) 0%, rgba(15,16,20,0.30) 40%, rgba(15,16,20,0.95) 100%),
            radial-gradient(ellipse at 20% 100%, rgba(0,77,255,0.28), transparent 50%);"></div>

    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    <nav class="container-tmac relative z-10 pt-4">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em] text-white/60">
            <a href="{{ route('site.home') }}" class="hover:text-white transition">Início</a>
            <span class="text-white/30">/</span>
            <span class="text-white">Contato</span>
        </div>
    </nav>

    <div class="container-tmac absolute bottom-0 left-1/2 -translate-x-1/2 right-0 z-10 pb-10 md:pb-14">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
            <span class="speed-badge">
                <span class="speed-badge__dot"></span>
                {{ $page?->hero_eyebrow ?: 'Fale com a TMAC' }}
            </span>
            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
                Resposta em até 1 dia útil
            </div>
        </div>

        <h1 class="font-display font-black uppercase leading-[0.92] tracking-tightest text-white
                   text-[40px] sm:text-[56px] md:text-[76px] lg:text-[92px] max-w-[14ch]">
            @php
                $heroTitle = $page?->hero_title ?: 'Atendimento direto.';
                $heroHighlight = $page?->hero_highlight ?: 'direto.';
            @endphp
            {!! preg_replace('/(' . preg_quote($heroHighlight, '/') . ')/i', '<span class="text-signal">$1</span>', e($heroTitle), 1) !!}
        </h1>

        <p class="mt-5 text-white/75 text-[15px] md:text-[18px] leading-relaxed max-w-[58ch]">
            {{ $page?->hero_description ?: 'Comercial, técnico ou administrativo. Sem fila de bot, sem URA. Preenche o formulário ou escolhe seu canal preferido.' }}
        </p>
    </div>
</section>

{{-- ═══════════ 2. CANAIS RÁPIDOS ═══════════ --}}
<section class="bg-bg border-b border-line">
    <div class="container-tmac py-8 md:py-10">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if($wpp)
                <a href="https://wa.me/{{ $wpp }}" target="_blank" rel="noopener"
                   onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'contact'})"
                   class="group card p-4 flex items-center gap-3 hover:border-whatsapp transition">
                    <div class="w-11 h-11 rounded-lg bg-whatsapp text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">WhatsApp</div>
                        <div class="font-semibold text-[13px] truncate">{{ $wpp }}</div>
                    </div>
                </a>
            @endif

            @if($phone)
                <a href="tel:{{ $phone }}" class="group card p-4 flex items-center gap-3 hover:border-ink/40 transition">
                    <div class="w-11 h-11 rounded-lg bg-ink text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">Telefone</div>
                        <div class="font-semibold text-[13px] truncate">{{ $phone }}</div>
                    </div>
                </a>
            @endif

            @if($email)
                <a href="mailto:{{ $email }}" class="group card p-4 flex items-center gap-3 hover:border-signal transition">
                    <div class="w-11 h-11 rounded-lg bg-signal text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">E-mail</div>
                        <div class="font-semibold text-[13px] truncate">{{ $email }}</div>
                    </div>
                </a>
            @endif

            @if($address)
                <div class="card p-4 flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg bg-accent text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">Endereço</div>
                        <div class="font-semibold text-[13px] truncate">{{ $address }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ═══════════ 3. FORMULÁRIO PRINCIPAL ═══════════ --}}
<section class="bg-bg relative overflow-hidden">
    <div class="absolute -top-10 -right-6 hidden md:block pointer-events-none">
        <span class="mega-num mega-num--blue">01</span>
    </div>

    <div class="container-tmac py-14 md:py-20 grid lg:grid-cols-12 gap-10 lg:gap-16 relative">
        <div class="lg:col-span-5">
            <div class="section-marker section-marker--lg">
                <span class="section-marker__num">01</span>
                <span class="section-marker__label">Fale conosco · Formulário</span>
            </div>
            <h2 class="font-display font-black text-[34px] md:text-[48px] leading-[0.95] tracking-tightest text-ink mt-4 uppercase">
                Conta pra gente<br>
                <span class="text-signal">o que você precisa</span>.
            </h2>
            <p class="mt-5 text-ink-soft text-[15px] md:text-[16px] leading-relaxed max-w-[48ch]">
                Preenche o formulário ao lado. A gente responde em até 1 dia útil — geralmente no mesmo dia.
            </p>

            {{-- Pílulas de canal --}}
            <div class="mt-7 space-y-3">
                <div class="flex items-start gap-3 p-3.5 rounded-lg bg-bg-elev border border-line">
                    <div class="w-8 h-8 rounded-md bg-signal-soft text-signal flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[13px] text-ink leading-tight">Atendimento prioritário</div>
                        <div class="text-[12px] text-ink-soft mt-0.5">Resposta no mesmo dia útil pra cadastros aprovados.</div>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-lg bg-bg-elev border border-line">
                    <div class="w-8 h-8 rounded-md bg-accent-soft text-accent flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[13px] text-ink leading-tight">Equipe comercial dedicada</div>
                        <div class="text-[12px] text-ink-soft mt-0.5">Direcionamento automático para o representante da sua região.</div>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-lg bg-bg-elev border border-line">
                    <div class="w-8 h-8 rounded-md bg-ink text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-[13px] text-ink leading-tight">Seg–Sex 8h–18h · Sáb 8h–12h</div>
                        <div class="text-[12px] text-ink-soft mt-0.5">Fora desse horário, WhatsApp continua disponível.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="card p-6 md:p-8">
                <livewire:site.lead-form
                    source="{{ \App\Models\Lead::SOURCE_CONTACT }}"
                    variant="full"
                    title="Envie sua mensagem"
                    cta-label="Enviar mensagem"
                    success-message="Recebemos! A equipe comercial entra em contato em breve." />
            </div>
        </div>
    </div>
</section>


{{-- ═══════════ 5. NEWSLETTER STRIP ═══════════ --}}
<section class="relative overflow-hidden bg-ink text-white">
    <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=2000&q=70"
         alt="" aria-hidden="true" loading="lazy"
         class="absolute inset-0 w-full h-full object-cover opacity-25">

    <div class="absolute inset-0"
         style="background: linear-gradient(180deg, rgba(15,16,20,0.85) 0%, rgba(15,16,20,0.55) 50%, rgba(15,16,20,0.95) 100%);"></div>

    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004DFF, transparent);"></div>

    <div class="container-tmac py-14 md:py-20 relative">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="section-marker section-marker--lg section-marker--on-dark">
                    <span class="section-marker__num">02</span>
                    <span class="section-marker__label">Newsletter TMAC</span>
                </div>
                <h2 class="font-display font-black text-[28px] md:text-[40px] leading-[0.95] tracking-tightest text-white mt-4 uppercase">
                    Lançamentos,<br>
                    <span class="text-signal">ofertas e bastidores</span>.
                </h2>
                <p class="mt-4 text-white/70 text-[15px] leading-relaxed max-w-[44ch]">
                    Cadastre seu e-mail e receba novidades de catálogo, condições especiais e bastidores da operação.
                </p>
            </div>

            <div class="bg-white/[0.04] backdrop-blur border border-white/10 rounded-xl p-5 md:p-6">
                <livewire:site.lead-form
                    source="{{ \App\Models\Lead::SOURCE_NEWSLETTER }}"
                    variant="inline"
                    :ask-company="false"
                    :ask-state="false"
                    :ask-message="false"
                    cta-label="Inscrever"
                    success-message="Você foi inscrito na newsletter da TMAC." />
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 6. CONTEÚDO EXTRA EDITÁVEL ═══════════ --}}
@if($page?->content && trim(strip_tags($page->content)))
    <section class="bg-bg">
        <div class="container-tmac py-12 md:py-16 max-w-3xl">
            <div class="prose max-w-none text-ink-soft prose-headings:font-display prose-headings:text-ink prose-headings:font-extrabold prose-strong:text-ink prose-a:text-signal">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endif

@endsection
