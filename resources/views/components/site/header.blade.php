@php
    $current = request()->route()?->getName() ?? '';
    $currentSlug = request()->route('slug') ?? '';

    // Formulário externo de cadastro de distribuidor (editável em Configurações → distributor_form_url)
    $distributorFormUrl = \App\Models\Setting::get('distributor_form_url')
        ?: 'https://tsgmrsomo1jj.sg.larksuite.com/share/base/form/shrlgNTTXRvcR6JaDOqBRJnrXDd';

    $navItems = [
        ['route' => 'site.products',        'label' => 'Produtos'],
        ['route' => 'site.tmac-brands',     'label' => 'Universo TMAC', 'highlight' => true],
        ['route' => 'site.representatives', 'label' => 'Representantes'],
        ['route' => 'site.page', 'param' => 'quem-somos',  'label' => 'Quem somos'],
        ['route' => 'site.page', 'param' => 'contato',     'label' => 'Contato'],
        ['route' => 'cliente.login', 'label' => 'Área do Cliente'],
    ];
@endphp
<header x-data="{ open: false, search: false, scrolled: false }"
        @scroll.window="scrolled = window.scrollY > 8"
        :class="scrolled ? 'shadow-[0_4px_24px_-12px_rgba(0,0,0,0.12)] border-line' : 'border-transparent'"
        class="sticky top-0 z-40 bg-bg/90 backdrop-blur-md border-b transition-shadow duration-300">

    {{-- Trilha azul fininha no topo (detalhe) --}}
    <div class="absolute top-0 left-0 right-0 h-[2px] pointer-events-none"
         style="background: linear-gradient(90deg, transparent 0%, rgba(0,77,255,0.35) 30%, rgba(0,77,255,0.35) 70%, transparent 100%);"></div>

    <div class="w-full px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-4 lg:gap-6 xl:gap-8 h-[68px] relative">
        {{-- Menu mobile (só aparece em telas < lg, mantém à esquerda no mobile) --}}
        <button @click="open = true" class="icon-btn lg:hidden absolute left-4 top-1/2 -translate-y-1/2" aria-label="Menu">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
        </button>

        {{-- Logo --}}
        <a href="{{ route('site.home') }}" class="logo-mark shrink-0">
            <img src="{{ asset('storage/images/logo-tmac.svg') }}" alt="TMAC Import" class="h-10" onerror="this.onerror=null;this.src='{{ asset('storage/images/logo-tmac.png') }}'">
            <span class="logo-mark__tick"></span>
            <span class="logo-mark__sub">
                <span>Atacado</span>
                <span class="logo-mark__sub-val">B2B · CNPJ</span>
            </span>
        </a>

        {{-- Nav desktop --}}
        <nav class="hidden lg:flex items-center gap-5 xl:gap-7 shrink-0">
            @foreach($navItems as $item)
                @php
                    $href   = isset($item['param']) ? route($item['route'], $item['param']) : route($item['route']);
                    $active = isset($item['param'])
                        ? ($current === $item['route'] && $currentSlug === $item['param'])
                        : str_starts_with($current, $item['route']);
                @endphp
                <a href="{{ $href }}" class="nav-link {{ $active ? 'nav-link--active' : '' }} {{ !empty($item['highlight']) ? 'nav-link--highlight' : '' }}">
                    @if(!empty($item['highlight']))
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-accent mr-1.5 align-middle"></span>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- CTA Seja Distribuidor (desktop) --}}
        <a href="{{ $distributorFormUrl }}" target="_blank" rel="noopener"
           onclick="window.dataLayer&&dataLayer.push({event:'distributor_cta',source:'header'})"
           class="btn-distributor hidden lg:inline-flex shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M3 6h13v10H3zM16 10h4l1 3v3h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>
            </svg>
            Seja distribuidor
        </a>

        {{-- Busca + cotação --}}
        <div class="flex items-center gap-2 shrink-0">
            <button @click="search = !search" class="icon-btn" aria-label="Buscar"
                    :class="search ? 'bg-bg-elev border-ink/30' : ''">
                <svg class="w-[18px] h-[18px] transition-transform duration-200" :class="search ? 'rotate-90' : ''"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
            </button>

            <div class="quote-cart-wrap">
                <livewire:site.quote-cart-button />
            </div>
        </div>
    </div>

    {{-- Busca expandida --}}
    <div x-show="search" x-cloak
         x-transition:enter="transition-all ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-all ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="border-t border-line bg-bg-elev">
        <div class="container-tmac py-3.5">
            <form action="{{ route('site.products') }}" method="GET"
                  class="search-bar focus-within:border-signal focus-within:ring-2 focus-within:ring-signal/15 transition">
                <svg class="w-[18px] h-[18px] text-ink-faint" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="q" placeholder="Buscar por moto, peça ou part number…" autofocus>
                <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint hidden sm:inline-flex items-center gap-1.5">
                    <span class="w-1 h-1 bg-signal rounded-full"></span>
                    12k+ peças
                </span>
                <button type="submit" class="hidden sm:inline-flex items-center gap-1.5 ml-2 h-8 px-3 rounded-md bg-ink text-white text-[12px] font-semibold hover:bg-signal transition">
                    Buscar
                    <kbd class="font-mono text-[9px] uppercase tracking-[0.08em] bg-white/15 px-1 py-0.5 rounded">↵</kbd>
                </button>
            </form>
        </div>
    </div>

    {{-- Drawer mobile (TELEPORTED to body para escapar do backdrop-filter do header) --}}
    <template x-teleport="body">
    <div x-show="open" x-cloak
         @keydown.escape.window="open = false"
         :class="open ? 'overflow-hidden' : ''"
         class="fixed inset-0 z-[60] lg:hidden">
        <div @click="open=false"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <aside x-transition:enter="transition-transform ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition-transform ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="absolute left-0 top-0 bottom-0 w-[82%] max-w-[340px] bg-bg shadow-2xl flex flex-col overflow-y-auto">

            {{-- Header drawer --}}
            <div class="relative flex items-center gap-3 px-4 py-4 border-b border-line">
                <div class="absolute top-0 left-0 right-0 h-[2px]"
                     style="background: linear-gradient(90deg, #004DFF, transparent);"></div>
                <span class="font-display font-black text-xl tracking-tightest">TMAC</span>
                <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint">Import</span>
                <button @click="open=false" class="ml-auto icon-btn" aria-label="Fechar">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <nav class="flex-1 py-1.5">
                @foreach([
                    ['site.home', 'Início'],
                    ['site.products', 'Produtos'],
                    ['site.tmac-brands', 'Universo TMAC'],
                    ['site.representatives', 'Representantes'],
                    ['site.quote', 'Solicitar cotação'],
                    ['cliente.login', 'Área do Cliente'],
                ] as [$route, $label])
                    <a href="{{ route($route) }}"
                       class="group flex items-center justify-between px-4 py-4 border-b border-line-soft font-display font-bold text-[18px] tracking-tightish text-ink hover:bg-bg-sunken hover:pl-5 transition-all duration-200">
                        <span class="flex items-center gap-3">
                            <span class="w-1 h-5 bg-signal rounded-sm scale-y-0 group-hover:scale-y-100 transition-transform duration-200 origin-center"></span>
                            {{ $label }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-ink-faint group-hover:text-signal group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                @endforeach
                @foreach([
                    ['quem-somos', 'Quem somos'],
                    ['como-comprar', 'Como comprar'],
                    ['contato', 'Contato'],
                ] as [$slug, $label])
                    <a href="{{ route('site.page', $slug) }}"
                       class="group flex items-center justify-between px-4 py-4 border-b border-line-soft font-display font-bold text-[18px] tracking-tightish text-ink hover:bg-bg-sunken hover:pl-5 transition-all duration-200">
                        <span class="flex items-center gap-3">
                            <span class="w-1 h-5 bg-signal rounded-sm scale-y-0 group-hover:scale-y-100 transition-transform duration-200 origin-center"></span>
                            {{ $label }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-ink-faint group-hover:text-signal group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                @endforeach
            </nav>

            @php $wppNumber = \App\Models\Setting::get('whatsapp_number'); @endphp
            <div class="p-4 border-t border-line space-y-2">
                {{-- CTA Seja Distribuidor --}}
                <a href="{{ $distributorFormUrl }}" target="_blank" rel="noopener"
                   onclick="window.dataLayer&&dataLayer.push({event:'distributor_cta',source:'menu_mobile'})"
                   class="btn-distributor btn-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M3 6h13v10H3zM16 10h4l1 3v3h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>
                    </svg>
                    Seja distribuidor
                </a>

                <a href="{{ route('site.quote') }}" class="btn-primary btn-block">
                    Solicitar cotação
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
                @if($wppNumber)
                    <a href="https://wa.me/{{ $wppNumber }}" target="_blank" rel="noopener" class="btn-whatsapp btn-block">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Zm4.1-5.6c-.22-.11-1.32-.65-1.53-.72-.2-.07-.35-.11-.5.11-.14.22-.56.71-.7.86-.13.14-.25.16-.47.05a6.1 6.1 0 0 1-1.8-1.1 6.74 6.74 0 0 1-1.25-1.54c-.13-.22-.01-.34.1-.45.1-.1.22-.27.33-.4.11-.14.15-.24.22-.4.07-.15.04-.28-.02-.4-.05-.11-.5-1.2-.68-1.64-.18-.43-.36-.37-.5-.38h-.42a.8.8 0 0 0-.58.27 2.45 2.45 0 0 0-.78 1.85c0 1.1.8 2.15.91 2.3.11.14 1.56 2.38 3.78 3.34.53.23.94.36 1.26.46.53.16 1.02.14 1.4.09.43-.07 1.32-.54 1.5-1.06.19-.52.19-.97.13-1.06-.06-.1-.2-.15-.43-.26Z"/></svg>
                        Falar no WhatsApp
                    </a>
                @endif
            </div>
        </aside>
    </div>
    </template>
</header>
