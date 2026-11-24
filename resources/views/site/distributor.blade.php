@extends('layouts.site')

@section('content')
<section class="bg-bg">
    <div class="container-tmac pt-4 pb-10 md:pb-14 max-w-5xl">

        <nav class="crumb">
            <a href="{{ route('site.home') }}">Início</a>
            <span class="crumb__sep">/</span>
            <span class="crumb__current">Seja distribuidor</span>
        </nav>

        <div class="mt-5 grid md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-8">
                <x-site.eyebrow text="Distribuição autorizada"/>
                <h1 class="section-title mt-1.5">Seja um distribuidor TMAC.</h1>
                <p class="section-sub">Preencha o cadastro abaixo. Nosso time comercial analisa e retorna com as condições para a sua região.</p>
            </div>
            <div class="md:col-span-4 md:text-right">
                <a href="{{ $formUrl }}" target="_blank" rel="noopener"
                   onclick="window.dataLayer&&dataLayer.push({event:'distributor_form_new_tab',source:'distributor_page'})"
                   class="inline-flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-[0.08em] text-ink-soft hover:text-accent transition">
                    Abrir formulário em nova aba
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 4h6v6M20 4l-9 9M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                </a>
            </div>
        </div>

        {{-- Formulário do Lark embutido. Se o navegador bloquear o iframe, o link acima/abaixo abre em nova aba. --}}
        <div class="mt-6 card overflow-hidden relative bg-white"
             x-data="{ loaded: false }">
            <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center text-[13px] text-ink-faint font-mono uppercase tracking-[0.08em]">
                Carregando formulário…
            </div>
            <iframe src="{{ $formUrl }}"
                    title="Formulário de cadastro de distribuidor TMAC"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    @load="loaded = true; window.dataLayer&&dataLayer.push({event:'distributor_form_view'})"
                    class="block w-full border-0 relative"
                    style="height: clamp(720px, 160vh, 1400px);"></iframe>
        </div>

        <p class="mt-4 text-[13px] text-ink-soft">
            O formulário não carregou?
            <a href="{{ $formUrl }}" target="_blank" rel="noopener" class="font-semibold text-ink underline underline-offset-2 hover:text-accent">Clique aqui para abrir em nova aba</a>.
        </p>
    </div>
</section>
@endsection
