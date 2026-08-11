<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    /** Fontes de captação (uma por formulário/widget) */
    public const SOURCE_CONTACT     = 'contact';
    public const SOURCE_NEWSLETTER  = 'newsletter';
    public const SOURCE_REPRESENT   = 'represent';   // "quero ser representante"
    public const SOURCE_CATALOG     = 'catalog';     // "baixar catálogo"
    public const SOURCE_PRODUCT     = 'product';     // "tirar dúvida sobre produto"
    public const SOURCE_FOOTER      = 'footer';
    public const SOURCE_FEEDBACK    = 'feedback';    // ouvidoria (sugestões/reclamações/elogios)

    public const SOURCES = [
        self::SOURCE_CONTACT    => 'Contato',
        self::SOURCE_NEWSLETTER => 'Newsletter',
        self::SOURCE_REPRESENT  => 'Quer ser representante',
        self::SOURCE_CATALOG    => 'Download catálogo',
        self::SOURCE_PRODUCT    => 'Dúvida em produto',
        self::SOURCE_FOOTER     => 'Footer',
        self::SOURCE_FEEDBACK   => 'Ouvidoria',
    ];

    public const STATUSES = [
        'new'        => 'Novo',
        'contacted'  => 'Contatado',
        'qualified'  => 'Qualificado',
        'converted'  => 'Convertido',
        'discarded'  => 'Descartado',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'city', 'state_id',
        'subject', 'message', 'interest', 'source', 'source_url',
        'utm_payload', 'ip', 'user_agent',
        'status', 'admin_notes', 'rd_synced_at', 'contacted_at',
    ];

    protected $casts = [
        'utm_payload'  => 'array',
        'rd_synced_at' => 'datetime',
        'contacted_at' => 'datetime',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
