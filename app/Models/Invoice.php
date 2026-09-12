<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'client',
        'type',
        'amount',
        'method',
        'etims',
        'status',
        'due_date',
    ];

    protected $casts = [
        'etims' => 'boolean',
        'amount' => 'decimal:2',
    ];
}
