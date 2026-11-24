@extends('layouts.cliente')

@section('content')
<section class="container-tmac pt-12 pb-16">
    <div class="max-w-md mx-auto text-center">
        <img src="{{ asset('images/logo-tmac.svg') }}" alt="TMAC Import" class="h-10 mx-auto mb-6"
             onerror="this.onerror=null;this.src='{{ asset('images/logo-tmac.png') }}'">

        <span class="inline-flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.12em] text-signal bg-signal-soft rounded-full px-3 py-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-signal" style="animation: tmac-pulse 1.8s ease-in-out infinite"></span>
            Em construção
        </span>

        <h1 class="mt-4 font-display font-extrabold text-2xl md:text-3xl tracking-tighter text-ink">Área do Cliente</h1>
        <p class="mt-3 text-[15px] text-ink-soft leading-relaxed">
            Estamos preparando um espaço exclusivo para nossos clientes. Em breve você poderá acessar por aqui.
        </p>
        <p class="mt-2 text-[14px] text-ink-soft leading-relaxed">
            Enquanto isso, solicite sua cotação pelo site ou fale com o nosso time comercial.
        </p>

        <div class="mt-7 grid sm:grid-cols-2 gap-2">
            <a href="{{ route('site.home') }}" class="btn-dark justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M11 19l-7-7 7-7"/></svg>
                Voltar para o site
            </a>
            <a href="{{ route('site.quote') }}" class="btn-primary justify-center">Solicitar cotação</a>
        </div>
    </div>
</section>
@endsection
