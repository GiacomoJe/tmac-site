@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4">

    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <a href="{{ route('site.brands') }}">Marcas</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">{{ $brand->name }}</span>
    </nav>

    <div class="mt-6 flex flex-col md:flex-row md:items-center gap-5">
        @if($brand->logo_path)
            <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }}" class="h-16 object-contain">
        @endif
        <div>
            <x-site.eyebrow text="Marca"/>
            <h1 class="section-title mt-1.5">{{ $brand->name }}.</h1>
            @if($brand->description)<p class="section-sub">{{ $brand->description }}</p>@endif
        </div>
    </div>

    <livewire:site.quote-cart-summary />

    @if($products->isEmpty())
        <div class="mt-8 p-8 text-center border border-dashed border-line rounded-xl bg-bg-elev">
            <div class="font-display font-bold text-lg text-ink">Nenhum produto cadastrado ainda</div>
            <p class="text-sm text-ink-soft mt-2">Solicite cotação que buscamos pra você.</p>
            <a href="{{ route('site.quote') }}" class="btn-primary mt-4 inline-flex">Solicitar cotação</a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
            @foreach($products as $product)
                <x-site.product-card :product="$product" />
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</section>
@endsection
