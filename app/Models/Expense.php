<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'project',
        'amount',
        'etr',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
