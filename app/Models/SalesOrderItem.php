<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id', 'cod', 'descricao', 'marca',
        'preco_tabela', 'desconto_percent', 'quantidade', 'valor_final', 'total', 'bloqueado',
    ];

    protected $casts = [
        'preco_tabela' => 'decimal:2',
        'desconto_percent' => 'decimal:2',
        'valor_final' => 'decimal:2',
        'total' => 'decimal:2',
        'bloqueado' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }
}
