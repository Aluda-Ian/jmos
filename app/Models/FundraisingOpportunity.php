<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundraisingOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization',
        'program_title',
        'application_link',
        'amount_kes',
        'amount_display',
        'funding_type',
        'deadline',
        'status',
        'category',
        'partnership_entity_type',
        'partner_organization',
        'lead_owner',
        'notes',
    ];

    protected $casts = [
        'amount_kes' => 'decimal:2',
    ];
}
