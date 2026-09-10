<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCatalogItem extends Model
{
    protected $fillable = [
        'sales_price_list_id', 'tabela', 'cod', 'descricao', 'marca', 'grupo',
        'caixa_master', 'sub_embalagem', 'tag', 'promo', 'status', 'bloqueado',
        'preco', 'achou_preco',
    ];

    protected $casts = [
        'bloqueado' => 'boolean',
        'achou_preco' => 'boolean',
        'preco' => 'decimal:2',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(SalesPriceList::class, 'sales_price_list_id');
    }

    public function scopeForTabela(Builder $query, string $tabela): Builder
    {
        return $query->where('tabela', $tabela);
    }

    public function scopeSearch(Builder $query, ?string $q1, ?string $q2): Builder
    {
        return $query
            ->when($q1, fn (Builder $qr) => $qr->where(function (Builder $w) use ($q1) {
                $w->where('cod', 'like', "%{$q1}%")->orWhere('descricao', 'like', "%{$q1}%");
            }))
            ->when($q2, fn (Builder $qr) => $qr->where(function (Builder $w) use ($q2) {
                $w->where('cod', 'like', "%{$q2}%")->orWhere('descricao', 'like', "%{$q2}%");
            }));
    }

    public function scopePromoFilter(Builder $query, string $filtro): Builder
    {
        return match ($filtro) {
            'promo' => $query->where('promo', 'PROMOÇÃO'),
            'extra' => $query->where('promo', 'PROMOÇÃO EXTRA'),
            default => $query,
        };
    }

    /** Valor final considerando desconto manual (itens em promoção não aceitam desconto). */
    public function valorFinal(float $descontoPercent): float
    {
        if ($this->promo) {
            return (float) $this->preco;
        }

        $d = max(0, min(100, $descontoPercent));

        return round(((float) $this->preco) * (1 - $d / 100), 2);
    }
}
