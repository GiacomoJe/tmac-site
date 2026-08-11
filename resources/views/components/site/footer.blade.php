@php
    $socials       = \App\Models\SocialLink::visible();
    $catalogUrl    = \App\Models\Setting::get('catalog_pdf_url');
    $contactPhone  = \App\Models\Setting::get('contact_phone');
    $contactEmail  = \App\Models\Setting::get('contact_email');
    $whatsApp      = \App\Models\Setting::get('whatsapp_number');
    $address       = \App\Models\Setting::get('address');
@endphp

<footer class="mt-16 bg-ink text-white relative overflow-hidden">
    {{-- Trilha azul fina no topo --}}
    <div class="absolute top-0 left-0 right-0 h-[2px] pointer-events-none"
         style="background: linear-gradient(90deg, transparent 0%, rgba(0,77,255,0.4) 30%, rgba(0,77,255,0.4) 70%, transparent 100%);"></div>

    {{-- Mega "TMAC" decorativo no fundo --}}
    <div class="absolute bottom-0 right-0 pointer-events-none select-none opacity-[0.03] leading-none translate-y-[20%]">
        <span class="font-display font-black tracking-tightest text-white" style="font-size: clamp(180px, 28vw, 360px); letter-spacing: -0.06em;">TMAC</span>
    </div>

    {{-- ─── Bloco superior: CTA + assinatura ─── --}}
    <div class="container-tmac py-10 md:py-12 relative">
        <div class="grid md:grid-cols-12 gap-8 items-end">
            <div class="md:col-span-7">
                <div class="section-marker section-marker--on-dark">
                    <span class="section-marker__num">END</span>
                    <span class="section-marker__label">Atendimento exclusivo CNPJ</span>
                </div>
                <h2 class="mt-3 font-display font-black text-[28px] md:text-[40px] leading-[0.95] tracking-tightest text-white uppercase">
                    Sua loja precisa de <span class="text-signal">peças</span>?<br>
                    A gente <span class="text-accent">resolve</span>.
                </h2>
                <p class="mt-3 text-white/70 text-[14px] md:text-[15px] leading-relaxed max-w-[52ch]">
                    Mais de 12 mil itens em estoque, condições especiais por volume e logística para todo o Brasil. Solicite sua tabela atacadista em minutos.
                </p>
            </div>
            <div class="md:col-span-5 flex flex-col gap-2.5">
                <a href="{{ route('site.quote') }}" class="btn-primary btn-block">
                    Solicitar cotação
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                @if($whatsApp)
                    <a href="https://wa.me/{{ $whatsApp }}" target="_blank" rel="noopener"
                       onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'footer'})"
                       class="btn-whatsapp btn-block">
                        <x-site.social-icon platform="whatsapp" class="w-5 h-5"/>
                        Falar no WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Linhas de info: grid principal ─── --}}
    <div class="border-t border-white/10 relative">
        <div class="container-tmac py-10 md:py-12 grid grid-cols-2 md:grid-cols-12 gap-y-8 gap-x-6">

            {{-- Logo + descrição (col-span maior) --}}
            <div class="col-span-2 md:col-span-4">
                <div class="flex items-end gap-2">
                    <span class="font-display font-black text-3xl tracking-tightest leading-none">TMAC</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/45 pb-1">Import</span>
                </div>
                <p class="mt-4 text-[13px] text-white/55 leading-relaxed">
                    Distribuidor atacadista de peças para motocicleta. Importação direta para lojistas, oficinas e revendedores autorizados em todo o Brasil.
                </p>

                {{-- Ícones sociais --}}
                @if($socials->isNotEmpty())
                    <div class="mt-5">
                        <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/40 mb-2.5">Siga a gente</div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($socials as $s)
                                <a href="{{ $s->url }}" target="_blank" rel="noopener"
                                   aria-label="{{ $s->display_label }}"
                                   title="{{ $s->display_label }}"
                                   class="social-btn-dark group"
                                   onclick="window.dataLayer&&dataLayer.push({event:'social_click',platform:'{{ $s->platform }}',source:'footer'})">
                                    <x-site.social-icon :platform="$s->platform" class="w-[15px] h-[15px]"/>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Coluna: Catálogo --}}
            <div class="col-span-1 md:col-span-2">
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/40 mb-3 flex items-center gap-2">
                    <span class="w-1 h-1 bg-signal rounded-full"></span>
                    Catálogo
                </div>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('site.products') }}" class="footer-link">Produtos</a></li>
                    <li><a href="{{ route('site.brands') }}" class="footer-link">Marcas</a></li>
                    @if($catalogUrl)
                        <li><a href="{{ $catalogUrl }}" target="_blank" rel="noopener" class="footer-link">Catálogo PDF ↗</a></li>
                    @endif
                </ul>
            </div>

            {{-- Coluna: Empresa --}}
            <div class="col-span-1 md:col-span-2">
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/40 mb-3 flex items-center gap-2">
                    <span class="w-1 h-1 bg-signal rounded-full"></span>
                    Empresa
                </div>
                <ul class="space-y-2.5">
                    @foreach([
                        'quem-somos' => 'Quem somos',
                        'como-comprar' => 'Como comprar',
                        'logistica' => 'Logística',
                        'qualidade' => 'Qualidade',
                        'contato' => 'Contato',
                        'ouvidoria' => 'Ouvidoria',
                    ] as $slug => $label)
                        <li><a href="{{ route('site.page', $slug) }}" class="footer-link">{{ $label }}</a></li>
                    @endforeach
                    <li><a href="{{ route('site.representatives') }}" class="footer-link">Representantes</a></li>
                </ul>
            </div>

            {{-- Coluna: Atendimento --}}
            <div class="col-span-2 md:col-span-4">
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-white/40 mb-3 flex items-center gap-2">
                    <span class="w-1 h-1 bg-signal rounded-full"></span>
                    Atendimento
                </div>
                <div class="space-y-3">
                    @if($contactPhone)
                        <div class="flex items-center gap-2.5">
                            <span class="footer-iconwrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.35 1.84.59 2.8.72A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <span class="text-[14px] text-white">{{ $contactPhone }}</span>
                        </div>
                    @endif
                    @if($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="flex items-center gap-2.5 group">
                            <span class="footer-iconwrap group-hover:bg-signal/20 group-hover:border-signal/40 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            </span>
                            <span class="text-[14px] text-white group-hover:text-signal transition">{{ $contactEmail }}</span>
                        </a>
                    @endif
                    @if($address)
                        <div class="flex items-start gap-2.5">
                            <span class="footer-iconwrap mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <span class="text-[13px] text-white/70 leading-snug">{{ $address }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2.5">
                        <span class="footer-iconwrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        </span>
                        <span class="text-[13px] text-white/70">Seg–Sex · 08h às 18h</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Newsletter strip (captação de lead) ─── --}}
    <div class="border-t border-white/10 relative bg-white/[0.02]">
        <div class="container-tmac py-8 md:py-10 grid lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-5">
                <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-signal mb-2 flex items-center gap-2">
                    <span class="w-1 h-1 bg-signal rounded-full"></span>
                    Newsletter TMAC
                </div>
                <div class="font-display font-black text-[20px] md:text-[26px] leading-tight tracking-tightish text-white">
                    Receba novidades e ofertas no seu e-mail.
                </div>
                <p class="mt-1.5 text-[12px] text-white/55 leading-relaxed">
                    Sem spam. Cancelamento em 1 clique.
                </p>
            </div>
            <div class="lg:col-span-7">
                <livewire:site.lead-form
                    source="{{ \App\Models\Lead::SOURCE_FOOTER }}"
                    variant="inline"
                    :ask-company="false"
                    :ask-state="false"
                    :ask-message="false"
                    cta-label="Inscrever"
                    success-message="Pronto! Você está na nossa lista." />
            </div>
        </div>
    </div>

    {{-- ─── Stat strip (números chamativos) ─── --}}
    <div class="border-t border-white/10 relative">
        <div class="container-tmac py-5 grid grid-cols-3 md:grid-cols-4 gap-4 text-center md:text-left">
            <div class="flex md:items-center gap-2 flex-col md:flex-row">
                <span class="font-display font-black text-[20px] md:text-[24px] tracking-tighter leading-none">12k+</span>
                <span class="font-mono text-[9px] md:text-[10px] uppercase tracking-[0.08em] text-white/45 leading-tight">SKUs</span>
            </div>
            <div class="flex md:items-center gap-2 flex-col md:flex-row">
                <span class="font-display font-black text-[20px] md:text-[24px] tracking-tighter leading-none">3,2k</span>
                <span class="font-mono text-[9px] md:text-[10px] uppercase tracking-[0.08em] text-white/45 leading-tight">Lojistas</span>
            </div>
            <div class="flex md:items-center gap-2 flex-col md:flex-row">
                <span class="font-display font-black text-[20px] md:text-[24px] tracking-tighter leading-none">26</span>
                <span class="font-mono text-[9px] md:text-[10px] uppercase tracking-[0.08em] text-white/45 leading-tight">Estados</span>
            </div>
            <div class="hidden md:flex md:items-center gap-2 flex-col md:flex-row">
                <span class="font-display font-black text-[20px] md:text-[24px] tracking-tighter leading-none text-signal">2h</span>
                <span class="font-mono text-[9px] md:text-[10px] uppercase tracking-[0.08em] text-white/45 leading-tight">Cotação<br>atendida</span>
            </div>
        </div>
    </div>

    {{-- ─── Linha final (copyright + legal) ─── --}}
    <div class="border-t border-white/10 relative">
        <div class="container-tmac py-5 flex flex-col md:flex-row gap-2 md:gap-6 items-start md:items-center justify-between font-mono text-[10px] uppercase tracking-[0.08em] text-white/40">
            <div class="flex items-center gap-2">
                <span>&copy; {{ date('Y') }} TMAC Import</span>
                <span class="opacity-50">·</span>
                <span>CNPJ 00.000.000/0001-00</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('site.page', 'contato') }}" class="hover:text-signal transition">Contato</a>
                <span class="opacity-50">·</span>
                <a href="#" class="hover:text-signal transition">Política de privacidade</a>
                <span class="opacity-50">·</span>
                <a href="#" class="hover:text-signal transition">Termos de uso</a>
            </div>
        </div>
    </div>
</footer>
