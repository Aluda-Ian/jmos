<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'client_name',
        'stage',
        'value',
        'meta_text',
        'is_won',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'value' => 'decimal:2',
    ];
}
