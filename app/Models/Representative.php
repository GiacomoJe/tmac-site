<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Representative extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'whatsapp', 'email',
        'photo_path', 'bio', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class)
            ->withPivot('is_primary');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
