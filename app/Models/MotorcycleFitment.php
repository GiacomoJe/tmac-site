<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotorcycleFitment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'motorcycle_model_id', 'year_from', 'year_to', 'notes',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(MotorcycleModel::class, 'motorcycle_model_id');
    }

    public function getYearRangeAttribute(): string
    {
        if (! $this->year_from) {
            return 'Todos os anos';
        }

        $to = $this->year_to ?: 'atual';
        return $this->year_from === $this->year_to
            ? (string) $this->year_from
            : $this->year_from.'–'.$to;
    }
}
