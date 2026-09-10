<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Conta de cliente (dealer/revenda) com acesso à Área do Cliente / Tabela de Vendas.
 * Guard próprio ('cliente', ver config/auth.php) — totalmente separado dos usuários
 * do painel administrativo (App\Models\User / Filament).
 *
 * Por enquanto as contas são criadas manualmente pelo time comercial no painel
 * (Filament > Tabela de Vendas > Clientes). O auto-cadastro fica para uma fase futura.
 */
class Cliente extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'clientes';

    protected $fillable = [
        'name', 'company', 'cnpj', 'email', 'phone', 'whatsapp',
        'uf', 'city', 'tabela_padrao', 'password', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
