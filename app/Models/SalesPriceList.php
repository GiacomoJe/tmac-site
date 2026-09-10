<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesPriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename', 'file_path', 'uploaded_by', 'is_active',
        'products_count', 'states_count', 'import_notes', 'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function states(): HasMany
    {
        return $this->hasMany(SalesState::class)->orderBy('sort_order');
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(SalesCatalogItem::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest('published_at')->first();
    }
}
