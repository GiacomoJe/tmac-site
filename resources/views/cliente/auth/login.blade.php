@extends('layouts.cliente')

@section('content')
<section class="container-tmac pt-10 pb-16">
    <div class="max-w-sm mx-auto">
        <div class="text-center mb-8">
            <img src="{{ asset('storage/images/logo-tmac.svg') }}" alt="TMAC Import" class="h-10 mx-auto mb-4"
                 onerror="this.onerror=null;this.src='{{ asset('storage/images/logo-tmac.png') }}'">
            <h1 class="font-display font-extrabold text-xl text-ink">Área do Cliente</h1>
            <p class="text-sm text-ink-soft mt-1">Entre para consultar a tabela de preços por estado e montar seu pedido.</p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-DEFAULT bg-signal-soft text-signal-dark text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cliente.login.submit') }}" class="bg-bg-elev border border-line rounded-lg p-5 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-mono-up text-signal mb-1">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-sm border border-line px-3 py-2.5 text-sm focus:border-signal focus:ring-1 focus:ring-signal">
                @error('email')
                    <p class="text-accent text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-mono-up text-signal mb-1">Senha</label>
                <input id="password" type="password" name="password" required
                       class="w-full rounded-sm border border-line px-3 py-2.5 text-sm focus:border-signal focus:ring-1 focus:ring-signal">
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input type="checkbox" name="remember" class="rounded border-line">
                Manter conectado
            </label>

            <button type="submit" class="w-full bg-accent hover:bg-accent-dark text-white font-bold uppercase tracking-mono-up text-sm rounded-sm py-3 transition-colors">
                Entrar
            </button>

            <p class="text-xs text-ink-faint text-center pt-2">
                Ainda não tem acesso? Fale com o time comercial da TMAC para criar seu login.
            </p>
        </form>
    </div>
</section>
@endsection
