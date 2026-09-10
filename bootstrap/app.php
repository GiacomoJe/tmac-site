<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Visitante sem sessão tentando acessar algo protegido por "auth":
        // - dentro da Área do Cliente -> login do cliente
        // - em qualquer outra rota (ex.: o upload da Tabela de Vendas no painel,
        //   que usa o guard "web" padrão do admin) -> login do painel
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('area-cliente*')
                ? route('cliente.login')
                : route('filament.admin.auth.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
