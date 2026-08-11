<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = ['from_path', 'to_path', 'http_code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'http_code' => 'integer',
    ];
}
