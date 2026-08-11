@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4 max-w-4xl">

    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">Representantes</span>
    </nav>

    <div class="mt-6">
        <x-site.eyebrow text="Atendimento regional"/>
        <h1 class="section-title mt-1.5">Representantes por estado.</h1>
        <p class="section-sub">Time comercial dedicado em 26 estados + DF. Suporte técnico em peças de moto, atendimento no seu fuso.</p>
    </div>

    {{-- Grid de UFs selecionável --}}
    <div class="mt-6">
        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint mb-2">Selecione seu estado</div>
        <div class="grid grid-cols-5 sm:grid-cols-7 md:grid-cols-9 gap-1.5">
            @foreach($states as $st)
                <a href="{{ route('site.representatives', ['uf' => $st->uf]) }}"
                   class="aspect-square border rounded flex items-center justify-center font-display font-extrabold text-[14px] tracking-tightish transition
                          {{ $uf === $st->uf ? 'bg-ink text-white border-ink' : 'bg-bg-elev text-ink border-line hover:border-ink/40' }}">
                    {{ $st->uf }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Resultado --}}
    @if($representatives->isEmpty())
        <div class="mt-8 p-8 text-center border border-dashed border-line rounded-xl bg-bg-elev">
            @if($uf)
                <div class="font-display font-bold text-lg text-ink">Nenhum representante para {{ $uf }}</div>
                <p class="text-sm text-ink-soft mt-2">Atendemos todo o Brasil pela central comercial em São Paulo. Solicite cotação online e nossa equipe encaminha.</p>
                <a href="{{ route('site.quote') }}" class="btn-primary mt-4 inline-flex">Falar com a central</a>
            @else
                <p class="text-sm text-ink-soft">Selecione um estado acima para ver os representantes.</p>
            @endif
        </div>
    @else
        <div class="mt-6 grid sm:grid-cols-2 gap-3">
            @foreach($representatives as $rep)
                <div class="card p-5">
                    <div class="flex items-center gap-3">
                        <span class="font-display font-black text-[14px] tracking-tightish bg-ink text-white w-9 h-9 flex items-center justify-center rounded">
                            {{ $rep->states->first()?->uf }}
                        </span>
                        <div>
                            <div class="font-display font-bold text-[16px] tracking-tightish">{{ $rep->name }}</div>
                            <div class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint">{{ $rep->states->pluck('uf')->join(' · ') }}</div>
                        </div>
                    </div>

                    @if($rep->bio)
                        <p class="text-sm text-ink-soft mt-3 leading-relaxed">{{ $rep->bio }}</p>
                    @endif

                    <div class="pt-4 mt-4 border-t border-line">
                        <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint mb-1.5">Contato</div>
                        @if($rep->phone)<div class="text-[14px]">{{ $rep->phone }}</div>@endif
                        @if($rep->email)<div class="text-[13px] text-ink-soft">{{ $rep->email }}</div>@endif

                        <div class="flex gap-2 mt-3">
                            @if($rep->phone)
                                <a href="tel:{{ $rep->phone }}" class="btn-dark btn-sm flex-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
                                    Ligar
                                </a>
                            @endif
                            @if($rep->whatsapp)
                                <a href="https://wa.me/{{ $rep->whatsapp }}" target="_blank" rel="noopener"
                                   onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'rep'})"
                                   class="btn-whatsapp btn-sm flex-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07Z"/></svg>
                                    WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-10">
        <x-site.eyebrow num="i" text="Não encontrou seu estado?"/>
        <p class="mt-2 text-sm text-ink-soft leading-relaxed">
            Atendemos todo o Brasil pela central comercial em São Paulo. Solicite cotação online e nossa equipe encaminha para o representante mais próximo.
        </p>
        <a href="{{ route('site.quote') }}" class="btn-primary btn-block mt-4 max-w-sm">Falar com a central</a>
    </div>
</section>
@endsection
