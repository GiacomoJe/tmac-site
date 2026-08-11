@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4">

    {{-- Breadcrumb --}}
    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">Solicitar cotação</span>
    </nav>

    <div class="mt-6 max-w-2xl">
        <x-site.eyebrow text="Pedido de cotação"/>
        <h1 class="section-title mt-1.5">Pedido de atacado.</h1>
        <p class="section-sub">Atendimento exclusivo para CNPJ ativo.</p>

        <livewire:site.quote-form />
    </div>
</section>
@endsection
