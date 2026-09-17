<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'client_id',
        'deal_id',
        'call_type',
        'call_status',
        'purpose',
        'outcome',
        'duration_minutes',
        'call_time',
        'logged_by',
        'notes',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'call_time' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
