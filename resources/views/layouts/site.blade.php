<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#E11D2A">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO dinâmico --}}
    {!! app(\App\Services\SeoMeta::class)->render() !!}

    {{-- Tipografia: Archivo (display) + IBM Plex Sans (texto) + IBM Plex Mono (técnico) --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=archivo:400,500,600,700,800,900|ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600&display=swap" rel="stylesheet">

    {{-- Google Tag Manager --}}
    @if($gtmId = \App\Models\Setting::get('gtm_id'))
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');
        </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col">
    @if($gtmId = \App\Models\Setting::get('gtm_id'))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @include('components.site.header')

    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('components.site.footer')
    <x-site.social-rail />
    @include('components.site.whatsapp-float')
    @include('components.site.quote-float')

    {{-- Modal de seleção de UF (obrigatório antes da cotação) --}}
    <livewire:site.state-selector-modal />

    @livewireScripts

    {{-- Helper global de carrinho de cotação --}}
    <script>
        window.TMAC = window.TMAC || {};
        window.TMAC.routes = {
            cartAdd:    "{{ route('site.cart.add') }}",
            cartUpdate: "{{ route('site.cart.update') }}",
            cartRemove: "{{ route('site.cart.remove') }}",
        };

        window.TMAC.addToQuote = async function (productId, quantity = 1, opts = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const res = await fetch(window.TMAC.routes.cartAdd, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: productId, quantity }),
                });
                if (!res.ok) throw new Error('HTTP '+res.status);
                const data = await res.json();

                // Servidor diz que precisa selecionar UF antes de adicionar
                if (data.needs_state) {
                    if (window.Livewire) {
                        window.Livewire.dispatch('quote-cart:require-state', {
                            productId: productId,
                            qty: quantity,
                        });
                    }
                    return data;
                }

                // 1) Atualiza imediatamente o DOM (não depende do Livewire)
                window.TMAC.updateCartUI(data);

                // 2) Notifica todos os componentes Livewire (re-hydratam da sessão)
                if (window.Livewire) {
                    window.Livewire.dispatch('quote-cart:updated');
                    window.Livewire.dispatch('cart-updated', { count: data.count });

                    // 3) Fallback: força refresh de TODOS componentes Livewire na página
                    //    Garante atualização mesmo se algum listener falhar.
                    setTimeout(() => {
                        document.querySelectorAll('[wire\\:id]').forEach(el => {
                            const wire = window.Livewire.find(el.getAttribute('wire:id'));
                            if (wire) wire.$refresh();
                        });
                    }, 50);
                }
                // Evento DOM para qualquer outro listener (Alpine, GTM)
                window.dispatchEvent(new CustomEvent('quote-cart-updated', { detail: data }));
                if (opts.sku) {
                    window.dataLayer && dataLayer.push({ event: 'add_to_quote', sku: opts.sku });
                }
                // Toast leve
                window.TMAC.toast(`Adicionado à cotação (${data.count} ${data.count===1?'item':'itens'})`);
                return data;
            } catch (e) {
                console.error('addToQuote failed', e);
                window.TMAC.toast('Erro ao adicionar. Tente novamente.', 'error');
            }
        };

        /**
         * Atualiza badges/textos do carrinho diretamente no DOM.
         * Não depende do Livewire — funciona como fonte da verdade imediata.
         * O servidor sempre retorna { count, distinct, progress, meets_min, state_uf }.
         */
        window.TMAC.updateCartUI = function (data) {
            if (!data) return;
            const count = data.count ?? 0;
            const progress = Math.min(100, Math.max(0, data.progress ?? 0));
            const meets = !!data.meets_min;

            // Badges com contador — número de itens
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                el.textContent = count;
                el.classList.toggle('hidden', count === 0);
            });

            // Botão do carrinho: estado "liberado" (verde) ao atingir o mínimo
            document.querySelectorAll('[data-cart-root]').forEach(el => {
                const wasReady = el.classList.contains('quote-cart--ready');
                const isReady  = meets && count > 0;

                el.classList.toggle('quote-cart--has-items', count > 0);
                el.classList.toggle('quote-cart--ready', isReady);

                // Acabou de destravar → re-dispara a animação + toast
                if (isReady && !wasReady) {
                    el.style.animation = 'none';
                    void el.offsetWidth;               // força reflow
                    el.style.animation = '';
                    window.TMAC.toast('Valor mínimo atingido — cotação liberada!', 'ok');
                }
            });

            // Float mobile — mostra/esconde + estado liberado
            document.querySelectorAll('[data-cart-float]').forEach(el => {
                el.classList.toggle('hidden', count === 0);
                el.classList.toggle('quote-float--ready', meets && count > 0);
            });

            // Indicador "X itens"
            document.querySelectorAll('[data-cart-items-label]').forEach(el => {
                el.textContent = count === 1 ? '1 item' : count + ' itens';
            });

            // % no botão do header
            document.querySelectorAll('[data-cart-progress-label-pct]').forEach(el => {
                el.textContent = progress + '%';
            });

            // Barras de progresso
            document.querySelectorAll('[data-cart-progress-bar]').forEach(el => {
                el.style.width = progress + '%';
                el.classList.toggle('bg-whatsapp', meets);
                el.classList.toggle('bg-signal', !meets);
            });

            // Label "faltam X%" / "mínimo atingido"
            document.querySelectorAll('[data-cart-progress-label]').forEach(el => {
                el.textContent = meets
                    ? 'mínimo atingido'
                    : 'faltam ' + (100 - progress) + '%';
                el.classList.toggle('text-whatsapp', meets);
                el.classList.toggle('text-signal', !meets);
            });
        };

        /* Livewire dispara 'cart-ui-sync' quando a quantidade muda no formulário
           de cotação — sincroniza cores/badges/barras imediatamente. */
        document.addEventListener('livewire:init', () => {
            Livewire.on('cart-ui-sync', (e) => {
                const data = Array.isArray(e) ? e[0] : e;
                window.TMAC.updateCartUI(data?.payload ?? data);
            });
        });

        window.TMAC.toast = function (msg, type = 'ok') {
            const el = document.createElement('div');
            el.textContent = msg;
            el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-lg text-sm font-medium shadow-lg transition-opacity '
                + (type === 'error' ? 'bg-accent text-white' : 'bg-ink text-white');
            el.style.opacity = '0';
            document.body.appendChild(el);
            requestAnimationFrame(() => { el.style.opacity = '1'; });
            setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 250); }, 1800);
        };
    </script>
</body>
</html>
