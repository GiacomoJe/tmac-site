<?php

use App\Models\Cliente;

/*
|--------------------------------------------------------------------------
| Guard extra: "cliente" (Área do Cliente / Tabela de Vendas)
|--------------------------------------------------------------------------
| Este projeto roda no esquema "config mínimo" do Laravel 11: como não há
| config/auth.php publicado, o framework já assume por baixo dos panos os
| guards padrão (web/sanctum com o provider "users" -> App\Models\User,
| usados pelo painel Filament). Os blocos abaixo (guards/providers/passwords)
| são MESCLADOS automaticamente com esses padrões — não é preciso repeti-los
| aqui. Só declaramos o que é novo: o guard "cliente", usado pela Área do
| Cliente (login em /area-cliente/entrar), completamente separado dos
| usuários administrativos do Filament.
*/

return [

    'guards' => [
        'cliente' => [
            'driver' => 'session',
            'provider' => 'clientes',
        ],
    ],

    'providers' => [
        'clientes' => [
            'driver' => 'eloquent',
            'model' => Cliente::class,
        ],
    ],

    'passwords' => [
        'clientes' => [
            'provider' => 'clientes',
            'table' => 'clientes_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

];
