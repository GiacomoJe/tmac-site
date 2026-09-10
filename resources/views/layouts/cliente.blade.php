<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#004F9F">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    {!! app(\App\Services\SeoMeta::class)->render() !!}

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=archivo:400,500,600,700,800|ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-bg font-sans text-ink antialiased">

    <header class="sticky top-0 z-40 bg-signal text-white">
        <div class="container-tmac flex items-center justify-between h-14 gap-3">
            <a href="{{ route('cliente.tabela') }}" class="flex items-center gap-2 shrink-0 font-display font-extrabold tracking-tightish">
                <span class="text-white">TMAC</span>
                <span class="hidden xs:inline text-[11px] font-mono uppercase tracking-mono-up bg-white/15 rounded px-1.5 py-0.5">Área do Cliente</span>
            </a>

            @auth('cliente')
                <div class="flex items-center gap-3 min-w-0">
                    <span class="hidden sm:block text-sm text-white/85 truncate max-w-[220px]">
                        {{ Auth::guard('cliente')->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('cliente.logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-semibold uppercase tracking-mono-up bg-white/15 hover:bg-white/25 rounded-sm px-3 py-2 transition-colors">
                            Sair
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="flex-1 pb-24">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
