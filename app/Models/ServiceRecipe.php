<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'stages',
        'deliverables',
    ];

    protected $casts = [
        'stages' => 'array',
    ];
}
