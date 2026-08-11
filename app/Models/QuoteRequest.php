<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'customer_name', 'company', 'cnpj', 'segment',
        'email', 'phone',
        'state_id', 'city', 'message', 'status',
        'assigned_representative_id', 'forwarded_to_representative_at', 'forwarded_count',
        'source', 'utm_payload',
        'ip', 'user_agent',
    ];

    protected $casts = [
        'utm_payload'                    => 'array',
        'forwarded_to_representative_at' => 'datetime',
    ];

    public const SEGMENTS = [
        'loja' => 'Loja de peças',
        'oficina' => 'Oficina mecânica',
        'concessionaria' => 'Concessionária',
        'frota' => 'Frota / locadora',
        'outro' => 'Outro',
    ];

    protected static function booted(): void
    {
        static::creating(function (QuoteRequest $request) {
            if (empty($request->code)) {
                $request->code = 'COT-'.date('Y').'-'.strtoupper(Str::random(5));
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(Representative::class, 'assigned_representative_id');
    }
}
