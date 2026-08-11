@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4">

    {{-- Breadcrumb --}}
    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <a href="{{ route('site.products') }}">Peças</a>
        <span class="crumb__sep">/</span>
        @if($model)
            <a href="{{ route('site.parts.make', $make->slug) }}">{{ $make->name }}</a>
            <span class="crumb__sep">/</span>
            <span class="crumb__current">{{ $model->name }}{{ $year ? ' '.$year : '' }}</span>
        @else
            <span class="crumb__current">{{ $make->name }}</span>
        @endif
    </nav>

    {{-- Section header --}}
    <div class="mt-6">
        <x-site.eyebrow text="Peças compatíveis"/>
        <h1 class="section-title mt-1.5">
            @if($model)
                Peças para {{ $make->name }} {{ $model->name }}{{ $year ? ' '.$year : '' }}.
            @else
                Linha {{ $make->name }}.
            @endif
        </h1>
        <p class="section-sub">
            {{ $products->total() }} {{ $products->total() === 1 ? 'item' : 'itens' }} compatíveis encontrados
            @if($model && $model->displacement) · {{ $model->displacement }} cc @endif
        </p>
    </div>

    {{-- Refinar filtro: mudar modelo ou ano --}}
    <div class="mt-6">
        @livewire('site.bike-selector',
            ['makeSlug' => $make->slug, 'modelSlug' => $model?->slug, 'year' => $year]
        )
    </div>

    {{-- Sub-nav de modelos da marca (quando só marca selecionada) --}}
    @if(! $model && $models->isNotEmpty())
        <div class="mt-6 flex gap-2 overflow-x-auto scrollbar-none pb-1 -mx-4 px-4">
            @foreach($models as $m)
                <a href="{{ route('site.parts.model', ['marca' => $make->slug, 'modelo' => $m->slug]) }}"
                   class="chip">
                    {{ $m->name }}
                    @if($m->displacement)<span class="chip__count">{{ $m->displacement }}cc</span>@endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Grid de produtos --}}
    <div class="mt-7 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
        @forelse($products as $p)
            <x-site.product-card :product="$p"/>
        @empty
            <div class="col-span-full bg-bg-elev border border-line rounded-xl p-10 text-center">
                <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint">→ Sem resultados</div>
                <h3 class="font-display font-extrabold text-[22px] mt-2 text-ink">Ainda não temos peças cadastradas para essa combinação.</h3>
                <p class="mt-2 text-[14px] text-ink-soft max-w-prose mx-auto">
                    Mas é provável que tenhamos em estoque. Mande seu pedido pelo WhatsApp ou cotação.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('site.quote') }}" class="btn-primary">Solicitar cotação</a>
                    <a href="{{ route('site.products') }}" class="btn">Ver catálogo completo</a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</section>
@endsection
