@extends('layouts.site')

@section('content')
<section class="container-tmac pt-4 max-w-3xl">

    <nav class="crumb">
        <a href="{{ route('site.home') }}">Início</a>
        <span class="crumb__sep">/</span>
        <span class="crumb__current">Como comprar</span>
    </nav>

    <div class="mt-6">
        <x-site.eyebrow text="Processo de compra"/>
        <h1 class="section-title mt-1.5">5 passos. Para CNPJ.</h1>
        <p class="section-sub">Atacado direto do importador. Sem revenda para consumidor final.</p>
    </div>

    {{-- 5 passos --}}
    <div class="mt-8">
        @php
            $steps = [
                ['Cadastre seu CNPJ', 'Atendimento exclusivo para empresas. Envie razão social, CNPJ e tipo de negócio. Aprovação em até 1 dia útil.'],
                ['Monte sua lista', 'Pesquise por part number, marca ou modelo de moto. Adicione tudo no carrinho de cotação.'],
                ['Receba a tabela', 'Nossa equipe envia a tabela escalonada com prazo e frete para o seu estado.'],
                ['Aprove e pague', 'PIX, boleto, depósito ou faturado em 28/30/45 dias (lojistas com cadastro aprovado).'],
                ['Despachamos hoje', 'Pedidos aprovados até 17h despacham no mesmo dia. CD próprio em Guarulhos / SP.'],
            ];
        @endphp

        @foreach($steps as $i => [$title, $desc])
            <div class="flex gap-4 py-4 border-t border-line {{ $loop->last ? 'border-b' : '' }}">
                <div class="flex-shrink-0 w-11 h-11 rounded-lg border border-line flex items-center justify-center font-display font-black text-[18px] tracking-tighter
                            {{ $i === 0 ? 'bg-accent text-white border-accent' : 'bg-bg-elev text-ink' }}">
                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div class="flex-1 pt-1">
                    <div class="font-display font-bold text-[17px] tracking-tightish leading-tight">{{ $title }}</div>
                    <p class="text-[14px] text-ink-soft mt-1.5 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagamento --}}
    <div class="mt-10">
        <x-site.eyebrow num="$" text="Pagamento"/>
        <div class="mt-3 grid grid-cols-2 gap-2">
            @foreach(['PIX', 'Boleto', 'Depósito', 'Faturado 28d'] as $method)
                <div class="card p-4 flex items-center justify-between font-display font-bold text-[15px] tracking-tightish">
                    {{ $method }}
                    <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
                </div>
            @endforeach
        </div>
        <p class="mt-3 font-mono text-[11px] text-ink-faint leading-relaxed">
            * Pagamento faturado disponível mediante análise de cadastro com CNPJ ativo há mais de 12 meses.
        </p>
    </div>

    {{-- Entrega --}}
    <div class="mt-10">
        <x-site.eyebrow num="→" text="Entrega"/>
        <p class="mt-2 text-[14px] text-ink-soft leading-relaxed">
            Despacho no mesmo dia útil para pedidos aprovados até 16h. Envio por transportadora própria (SP, RJ, MG) ou parceiros (demais estados). Retirada possível no CD de Guarulhos.
        </p>
    </div>

    <a href="{{ route('site.quote') }}" class="btn-primary btn-block mt-8">
        Começar uma cotação
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
    </a>
</section>
@endsection
