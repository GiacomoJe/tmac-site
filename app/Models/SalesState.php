<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesState extends Model
{
    protected $fillable = ['sales_price_list_id', 'code', 'sort_order'];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(SalesPriceList::class, 'sales_price_list_id');
    }
}
