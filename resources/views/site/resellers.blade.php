@extends('layouts.site')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@section('content')

{{-- ═══════════ HERO ═══════════ --}}
<section class="relative bg-ink text-white overflow-hidden" style="min-height: 280px;">
    <img src="{{ asset('storage/images/parceria-tmac.jpg') }}" alt="" aria-hidden="true"
         onerror="this.style.display='none'"
         class="absolute inset-0 w-full h-full object-cover opacity-25">
    <div class="absolute inset-0"
         style="background: linear-gradient(180deg, rgba(15,16,20,0.75) 0%, rgba(15,16,20,0.55) 50%, rgba(15,16,20,0.92) 100%),
                            radial-gradient(ellipse at 18% 100%, rgba(0,79,159,0.35), transparent 55%);"></div>
    <div class="absolute top-0 left-0 right-0 h-[2px]"
         style="background: linear-gradient(90deg, transparent, #004F9F, transparent);"></div>

    <nav class="container-tmac relative z-10 pt-4">
        <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.10em] text-white/55">
            <a href="{{ route('site.home') }}" class="hover:text-white transition">Início</a>
            <span class="text-white/30">/</span>
            <span class="text-white">Onde comprar</span>
        </div>
    </nav>

    <div class="container-tmac relative z-10 py-8 md:py-10">
        <span class="speed-badge">
            <span class="speed-badge__dot"></span>
            {{ $total }} {{ $total === 1 ? 'revendedor' : 'revendedores' }}
        </span>

        <h1 class="mt-4 font-display font-black uppercase leading-[0.92] tracking-tightest text-white
                   text-[34px] sm:text-[46px] md:text-[60px] max-w-[16ch]">
            Onde comprar<br><span class="text-signal">TMAC</span>.
        </h1>
        <p class="mt-4 text-white/70 text-[15px] md:text-[16px] leading-relaxed max-w-[56ch]">
            Encontre a loja mais próxima de você que revende produtos das nossas linhas.
        </p>
    </div>
</section>

{{-- ═══════════ BUSCA + MAPA ═══════════ --}}
<section class="bg-bg" x-data="resellerMap()" x-init="init()">

    {{-- Barra de busca --}}
    <div class="container-tmac py-5">
        <div class="resellers-search">
            <form @submit.prevent="searchAddress()" class="resellers-search__form">
                <svg class="w-[18px] h-[18px] text-ink-faint shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" x-model="term" placeholder="Digite seu CEP, cidade ou endereço…"
                       class="resellers-search__input">
                <button type="submit" class="btn-dark btn-sm shrink-0" :disabled="loading">
                    <span x-show="!loading">Buscar</span>
                    <span x-show="loading" x-cloak>…</span>
                </button>
            </form>

            <button type="button" @click="useMyLocation()" class="resellers-search__geo" :disabled="loading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="8"/>
                </svg>
                Usar minha localização
            </button>

            <select x-model="radius" @change="refresh()" class="resellers-search__radius">
                <option value="">Qualquer distância</option>
                <option value="10">Até 10 km</option>
                <option value="25">Até 25 km</option>
                <option value="50">Até 50 km</option>
                <option value="100">Até 100 km</option>
                <option value="250">Até 250 km</option>
            </select>
        </div>

        {{-- Mensagem de status --}}
        <div x-show="message" x-cloak class="mt-3 text-[13px]" :class="messageType === 'error' ? 'text-accent' : 'text-ink-soft'">
            <span x-text="message"></span>
        </div>
    </div>

    {{-- Mapa + lista --}}
    <div class="container-tmac pb-12">
        <div class="resellers-layout">

            {{-- Lista --}}
            <div class="resellers-list">
                <div class="resellers-list__head">
                    <span class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint">
                        <span x-text="items.length"></span> <span x-text="items.length === 1 ? 'loja' : 'lojas'"></span>
                        <template x-if="origin"><span> · mais próximas</span></template>
                    </span>
                </div>

                <div class="resellers-list__scroll">
                    <template x-if="!loading && items.length === 0">
                        <div class="p-6 text-center">
                            <p class="text-[14px] text-ink-soft">Nenhum revendedor encontrado nessa área.</p>
                            <button type="button" @click="clearFilters()" class="btn-ghost btn-sm mt-3">Ver todos</button>
                        </div>
                    </template>

                    <template x-for="(r, i) in items" :key="r.id">
                        <article class="reseller-card"
                                 :class="{ 'is-active': activeId === r.id }"
                                 @click="focusOn(r)"
                                 @mouseenter="highlight(r.id)">
                            <div class="reseller-card__head">
                                <span class="reseller-card__pin" x-text="i + 1"></span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="reseller-card__name" x-text="r.name"></h3>
                                    <p class="reseller-card__addr" x-text="r.address"></p>
                                </div>
                                <template x-if="r.distance !== null">
                                    <span class="reseller-card__dist">
                                        <span x-text="r.distance"></span> km
                                    </span>
                                </template>
                            </div>

                            <template x-if="r.hours">
                                <p class="reseller-card__hours" x-text="r.hours"></p>
                            </template>

                            <div class="reseller-card__actions" @click.stop>
                                <template x-if="r.whatsapp">
                                    <a :href="r.whatsapp" target="_blank" rel="noopener" class="reseller-btn reseller-btn--wpp">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Z"/></svg>
                                        WhatsApp
                                    </a>
                                </template>
                                <template x-if="r.phone && !r.whatsapp">
                                    <a :href="'tel:' + r.phone" class="reseller-btn">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 16.9Z"/></svg>
                                        Ligar
                                    </a>
                                </template>
                                <a :href="r.maps" target="_blank" rel="noopener" class="reseller-btn">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 21s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                    Rota
                                </a>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            {{-- Mapa --}}
            <div class="resellers-map">
                <div id="map" class="resellers-map__canvas"></div>
                <div x-show="loading" x-cloak class="resellers-map__loading">
                    <span class="filters-applying__spinner"></span>
                    Carregando…
                </div>
            </div>
        </div>
    </div>

    {{-- CTA para lojistas --}}
    <div class="bg-bg-sunken border-t border-line">
        <div class="container-tmac py-10 md:py-14 text-center max-w-2xl mx-auto">
            <h2 class="font-display font-black text-[24px] md:text-[34px] leading-tight tracking-tightest text-ink uppercase">
                Sua loja ainda não está aqui?
            </h2>
            <p class="mt-3 text-ink-soft text-[15px] leading-relaxed">
                Seja um revendedor autorizado TMAC e apareça neste mapa para milhares de motociclistas.
            </p>
            @php
                $distUrl = \App\Models\Setting::get('distributor_form_url')
                    ?: 'https://tsgmrsomo1jj.sg.larksuite.com/share/base/form/shrlgNTTXRvcR6JaDOqBRJnrXDd';
            @endphp
            <a href="{{ $distUrl }}" target="_blank" rel="noopener" class="btn-distributor mt-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 6h13v10H3zM16 10h4l1 3v3h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>
                Seja distribuidor
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function resellerMap() {
    return {
        map: null,
        markers: {},
        layer: null,
        items: [],
        origin: null,       // {lat, lng}
        originMarker: null,
        term: '',
        radius: '',
        loading: false,
        message: '',
        messageType: 'info',
        activeId: null,

        init() {
            this.map = L.map('map', {
                scrollWheelZoom: false,
                minZoom: 3,                  // impede zoom out excessivo
                maxZoom: 18,
                worldCopyJump: false,
                maxBoundsViscosity: 1.0,     // trava ao arrastar fora dos limites
            }).setView([-14.235, -51.925], 4);   // Brasil

            // Limita a navegação ao mundo (evita área cinza infinita)
            this.map.setMaxBounds([[-85, -180], [85, 180]]);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18,
                minZoom: 3,
                noWrap: true,                // não repete o mundo horizontalmente
                bounds: [[-85, -180], [85, 180]],
            }).addTo(this.map);

            this.map.on('click', () => this.map.scrollWheelZoom.enable());
            this.map.on('mouseout', () => this.map.scrollWheelZoom.disable());

            // Recalcula o tamanho quando o container muda (responsivo / abas)
            setTimeout(() => this.map.invalidateSize(), 200);
            window.addEventListener('resize', () => this.map.invalidateSize());

            this.layer = L.layerGroup().addTo(this.map);
            this.load();
        },

        async load(params = {}) {
            this.loading = true;
            this.message = '';
            try {
                const qs = new URLSearchParams();
                if (this.origin) { qs.set('lat', this.origin.lat); qs.set('lng', this.origin.lng); }
                if (this.radius) qs.set('raio', this.radius);
                Object.entries(params).forEach(([k, v]) => v && qs.set(k, v));

                const res  = await fetch(`{{ route('site.resellers.search') }}?${qs}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.items = data.items || [];
                this.render();

                if (this.items.length === 0) {
                    this.message = 'Nenhum revendedor encontrado. Tente aumentar a distância.';
                    this.messageType = 'info';
                }
            } catch (e) {
                this.message = 'Erro ao carregar revendedores.';
                this.messageType = 'error';
            } finally {
                this.loading = false;
            }
        },

        render() {
            this.layer.clearLayers();
            this.markers = {};
            const bounds = [];

            this.items.forEach((r, i) => {
                const icon = L.divIcon({
                    className: 'reseller-marker' + (r.featured ? ' is-featured' : ''),
                    html: `<span>${i + 1}</span>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15],
                });

                const m = L.marker([r.lat, r.lng], { icon })
                    .bindPopup(`
                        <strong>${this.esc(r.name)}</strong><br>
                        <span style="font-size:12px;color:#52535A">${this.esc(r.address)}</span><br>
                        <a href="${r.maps}" target="_blank" rel="noopener"
                           style="font-size:12px;color:#004F9F;font-weight:600">Como chegar →</a>
                    `)
                    .on('click', () => { this.activeId = r.id; });

                this.layer.addLayer(m);
                this.markers[r.id] = m;
                bounds.push([r.lat, r.lng]);
            });

            if (this.origin) {
                if (this.originMarker) this.map.removeLayer(this.originMarker);
                this.originMarker = L.circleMarker([this.origin.lat, this.origin.lng], {
                    radius: 8, color: '#ED1C24', fillColor: '#ED1C24', fillOpacity: 0.9, weight: 3,
                }).addTo(this.map).bindPopup('Você está aqui');
                bounds.push([this.origin.lat, this.origin.lng]);
            }

            if (bounds.length === 1) {
                // Um único ponto: centraliza sem tentar calcular bounds
                this.map.setView(bounds[0], 14, { animate: true });
            } else if (bounds.length > 1) {
                this.map.fitBounds(bounds, {
                    padding: [40, 40],
                    maxZoom: 14,
                    animate: true,
                });
            }
        },

        async searchAddress() {
            if (!this.term.trim()) { this.origin = null; return this.load(); }
            this.loading = true;
            this.message = '';
            try {
                const res  = await fetch(`{{ route('site.geocode') }}?q=${encodeURIComponent(this.term)}`);
                const data = await res.json();
                if (data.ok) {
                    this.origin = { lat: data.lat, lng: data.lng };
                    await this.load();
                } else {
                    this.message = data.message || 'Endereço não encontrado.';
                    this.messageType = 'error';
                    this.loading = false;
                }
            } catch (e) {
                this.message = 'Erro ao localizar endereço.';
                this.messageType = 'error';
                this.loading = false;
            }
        },

        useMyLocation() {
            if (!navigator.geolocation) {
                this.message = 'Seu navegador não suporta geolocalização.';
                this.messageType = 'error';
                return;
            }
            this.loading = true;
            this.message = 'Obtendo sua localização…';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.origin = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    this.term = '';
                    this.load();
                },
                () => {
                    this.loading = false;
                    this.message = 'Não foi possível obter sua localização. Digite seu CEP ou cidade.';
                    this.messageType = 'error';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        refresh() { this.load(); },

        clearFilters() {
            this.term = '';
            this.radius = '';
            this.origin = null;
            if (this.originMarker) { this.map.removeLayer(this.originMarker); this.originMarker = null; }
            this.load();
        },

        focusOn(r) {
            this.activeId = r.id;
            this.map.setView([r.lat, r.lng], 15, { animate: true });
            this.markers[r.id]?.openPopup();
        },

        highlight(id) { this.activeId = id; },

        esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            })[c]);
        },
    };
}
</script>
@endpush
