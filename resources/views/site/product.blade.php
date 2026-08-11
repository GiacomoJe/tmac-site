@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4">

    {{-- Breadcrumb --}}
    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <a href="{{ route('site.products') }}">Produtos</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">{{ $product->sku }}</span>
    </nav>

    <div class="mt-4 grid md:grid-cols-2 gap-8">
        {{-- ── Galeria ── --}}
        <div>
            <div class="relative aspect-square border border-line rounded-xl overflow-hidden flex items-center justify-center bg-bg-elev"
                 style="background-image:repeating-linear-gradient(135deg,#ECEAE3,#ECEAE3 8px,#E4E2DB 8px,#E4E2DB 16px)">
                <span class="absolute top-3 left-3 font-mono text-[11px] uppercase tracking-[0.06em] bg-bg border border-line px-2 py-1 rounded text-ink-soft">
                    {{ $product->sku }}
                </span>
                @if($product->main_image)
                    <img src="{{ asset('storage/'.$product->main_image) }}" alt="{{ $product->name }}"
                         fetchpriority="high" class="max-w-full max-h-full object-contain p-6 relative">
                @else
                    <span class="font-mono text-[11px] uppercase tracking-[0.06em] text-ink-faint">{{ $product->brand?->name }} · imagem técnica</span>
                @endif
            </div>

            @if($product->images->isNotEmpty())
                <div class="mt-3 flex gap-2 overflow-x-auto scrollbar-none">
                    @foreach($product->images as $img)
                        <button class="flex-shrink-0 w-14 h-14 rounded border border-line overflow-hidden">
                            <img src="{{ asset('storage/'.$img->path) }}" alt="{{ $img->alt }}" loading="lazy" class="w-full h-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Info ── --}}
        <div>
            <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint mb-1.5">
                @if($product->brand)
                    <a href="{{ route('site.brand', $product->brand->slug) }}" class="hover:text-accent transition">{{ $product->brand->name }}</a>
                @endif
            </div>
            <h1 class="font-display font-extrabold text-[26px] md:text-[32px] tracking-tighter leading-[1.08] text-ink">{{ $product->name }}</h1>

            @if($product->short_description)
                <p class="mt-3 text-ink-soft text-[14px] leading-relaxed">{{ $product->short_description }}</p>
            @endif

            {{-- Quantidade + CTA --}}
            <div class="mt-4 py-4 border-y border-line flex items-center gap-3"
                 x-data="{
                    qty: 1,
                    clamp() { if (!Number.isFinite(this.qty) || this.qty < 1) this.qty = 1; if (this.qty > 9999) this.qty = 9999; }
                 }">
                <span class="font-mono text-[11px] uppercase tracking-[0.06em] text-ink-soft">Qtd.</span>
                <div class="inline-flex items-center bg-bg-sunken border border-line rounded h-10 focus-within:border-signal focus-within:ring-2 focus-within:ring-signal/20 transition">
                    <button type="button" @click="qty = Math.max(1, qty-1)" aria-label="Diminuir" class="w-10 h-10 flex items-center justify-center text-ink hover:bg-line/40 transition">−</button>
                    <input x-model.number="qty"
                           @blur="clamp()"
                           @focus="$el.select()"
                           @keydown.enter.prevent="$el.blur()"
                           @input="$el.value = $el.value.replace(/[^0-9]/g,'')"
                           type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4"
                           aria-label="Quantidade"
                           class="w-16 h-full bg-transparent border-x border-line text-center font-mono text-[13px] tabular-nums focus:outline-none focus:bg-white">
                    <button type="button" @click="qty++; clamp()" aria-label="Aumentar" class="w-10 h-10 flex items-center justify-center text-ink hover:bg-line/40 transition">+</button>
                </div>
                <button type="button"
                        @click="TMAC.addToQuote({{ $product->id }}, qty, { sku: '{{ $product->sku }}' })"
                        class="btn-dark btn-sm ml-auto">Adicionar à cotação</button>
            </div>

            {{-- CTA WhatsApp --}}
            @if($wpp = \App\Models\Setting::get('whatsapp_number'))
                <a href="https://wa.me/{{ $wpp }}?text={{ urlencode('Olá! Tenho interesse no produto '.$product->name.' (SKU '.$product->sku.').') }}"
                   target="_blank" rel="noopener"
                   onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'product',sku:'{{ $product->sku }}'})"
                   class="btn-whatsapp btn-block mt-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Zm4.1-5.6c-.22-.11-1.32-.65-1.53-.72-.2-.07-.35-.11-.5.11-.14.22-.56.71-.7.86-.13.14-.25.16-.47.05a6.1 6.1 0 0 1-1.8-1.1 6.74 6.74 0 0 1-1.25-1.54c-.13-.22-.01-.34.1-.45.1-.1.22-.27.33-.4.11-.14.15-.24.22-.4.07-.15.04-.28-.02-.4-.05-.11-.5-1.2-.68-1.64-.18-.43-.36-.37-.5-.38h-.42a.8.8 0 0 0-.58.27 2.45 2.45 0 0 0-.78 1.85c0 1.1.8 2.15.91 2.3.11.14 1.56 2.38 3.78 3.34.53.23.94.36 1.26.46.53.16 1.02.14 1.4.09.43-.07 1.32-.54 1.5-1.06.19-.52.19-.97.13-1.06-.06-.1-.2-.15-.43-.26Z"/></svg>
                    Falar pelo WhatsApp
                </a>
            @endif

            {{-- Compatibilidade peça → motos --}}
            @if($product->fitments->isNotEmpty())
                <div class="mt-6 pt-5 border-t border-line">
                    <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint mb-3 flex items-center justify-between">
                        <span>Motos compatíveis</span>
                        <span class="text-ink-soft normal-case tracking-normal">{{ $product->fitments->count() }} aplicações</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($product->fitments as $fit)
                            @php $m = $fit->model; @endphp
                            @if($m)
                                <a href="{{ route('site.parts.model', ['marca' => $m->make->slug, 'modelo' => $m->slug]) }}"
                                   class="fitment-chip hover:border-ink hover:text-ink transition">
                                    <span class="fitment-chip__make">{{ $m->make->name }}</span>
                                    <span class="font-medium">{{ $m->name }}</span>
                                    @if($fit->year_from)
                                        <span class="fitment-chip__year">
                                            · {{ $fit->year_from }}{{ $fit->year_to && $fit->year_to !== $fit->year_from ? '–'.$fit->year_to : ($fit->year_to ? '' : '+') }}
                                        </span>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                    @if($wpp = \App\Models\Setting::get('whatsapp_number'))
                        <p class="mt-3 font-mono text-[11px] text-ink-faint">
                            Não viu sua moto?
                            <a href="https://wa.me/{{ $wpp }}?text={{ urlencode('Olá! Esta peça '.$product->name.' serve na minha moto?') }}"
                               target="_blank" class="text-accent hover:underline">Pergunte pelo WhatsApp</a>.
                        </p>
                    @endif
                </div>
            @endif

            {{-- Especificações técnicas --}}
            @if(!empty($product->specifications))
                <div class="mt-6 pt-5 border-t border-line">
                    <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint mb-3">Especificações técnicas</div>
                    <ul class="divide-y divide-line-soft">
                        @foreach($product->specifications as $key => $value)
                            <li class="flex justify-between gap-4 py-2.5 text-[13px]">
                                <span class="text-ink-soft">{{ $key }}</span>
                                <span class="text-ink font-medium text-right">{{ $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Descrição completa --}}
            @if($product->description)
                <div class="mt-6 pt-5 border-t border-line">
                    <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint mb-3">Descrição</div>
                    <div class="prose prose-sm max-w-none text-ink-soft">{!! $product->description !!}</div>
                </div>
            @endif

            <p class="mt-4 font-mono text-[11px] text-ink-faint leading-relaxed">
                * Confira ano, versão e part number antes de adquirir. Em dúvida, envie foto da peça pelo WhatsApp.
            </p>
        </div>
    </div>

    {{-- Relacionados --}}
    @if($related->isNotEmpty())
        <div class="mt-12 md:mt-16">
            <x-site.eyebrow num="→" text="Quem viu isso, também cotou"/>
            <h2 class="section-title mt-1.5">Itens relacionados.</h2>
            <div class="mt-5 grid grid-cols-2 md:grid-cols-4 gap-2.5">
                @foreach($related as $p)
                    <x-site.product-card :product="$p" />
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
