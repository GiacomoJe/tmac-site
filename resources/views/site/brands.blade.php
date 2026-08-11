@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4">

    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">Marcas</span>
    </nav>

    <div class="mt-6">
        <x-site.eyebrow text="Originais e aftermarket"/>
        <h1 class="section-title mt-1.5">Marcas que distribuímos.</h1>
        <p class="section-sub">Importação direta dos fabricantes ou via distribuidores autorizados. Nota fiscal, garantia e rastreabilidade em toda peça.</p>
    </div>

    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2 mt-6">
        @foreach($brands as $brand)
            <a href="{{ route('site.brand', $brand->slug) }}" class="brand-tile hover:border-ink/40 transition">
                @if($brand->logo_path)
                    <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }}" loading="lazy" class="max-h-10 object-contain">
                @else
                    {{ $brand->name }}
                @endif
            </a>
        @endforeach
    </div>

    <div class="card mt-10 p-5">
        <x-site.eyebrow num="i" text="Garantia de procedência"/>
        <p class="mt-2 text-[14px] text-ink-soft leading-relaxed">
            Trabalhamos com importação direta e parcerias oficiais com fabricantes de peças para motocicleta.
            Toda peça acompanha nota fiscal, certificado de origem e garantia mínima de 12 meses contra defeito de fabricação.
        </p>
    </div>
</section>
@endsection
