@extends('layouts.site')

@section('content')
<article class="container-tmac pt-4 max-w-3xl">

    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">{{ $page->title }}</span>
    </nav>

    <div class="mt-6">
        <x-site.eyebrow text="Institucional"/>
        <h1 class="section-title mt-1.5">{{ $page->title }}.</h1>
    </div>

    @if($page->hero_image)
        <img src="{{ asset('storage/'.$page->hero_image) }}" alt="{{ $page->title }}"
             class="mt-6 rounded-xl w-full object-cover max-h-80 border border-line">
    @endif

    <div class="mt-6 prose max-w-none text-ink-soft prose-headings:font-display prose-headings:text-ink prose-headings:font-extrabold prose-headings:tracking-tightish prose-strong:text-ink prose-a:text-accent prose-a:no-underline hover:prose-a:underline">
        {!! $page->content !!}
    </div>

    {{-- CTA fim de página --}}
    <div class="mt-10">
        <a href="{{ route('site.quote') }}" class="info-banner bg-ink text-white border-ink hover:bg-ink/95 transition">
            <div class="info-banner__num text-accent">2h</div>
            <div class="flex-1">
                <div class="font-display font-bold text-[15px] tracking-tightish leading-tight">Solicite cotação agora</div>
                <div class="text-xs text-white/65 mt-0.5 leading-snug">Atendimento exclusivo CNPJ.</div>
            </div>
            <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
    </div>
</article>
@endsection
