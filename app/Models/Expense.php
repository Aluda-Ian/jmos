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
        'etims_number',
        'receipt_url',
        'receipt_name',
        'notes',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
