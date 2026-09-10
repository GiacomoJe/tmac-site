<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'cliente_id', 'sales_price_list_id', 'tabela',
        'razao_social', 'cnpj', 'responsavel', 'telefone', 'email',
        'cnpj_transportadora', 'transportadora', 'vendedor', 'prazo_pagamento', 'observacoes',
        'total', 'total_bloqueado', 'itens_count', 'pecas_count', 'status', 'ip',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_bloqueado' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->code ??= 'PED-'.strtoupper(Str::random(8));
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(SalesPriceList::class, 'sales_price_list_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
