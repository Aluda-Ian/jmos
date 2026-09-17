<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'client_id',
        'title',
        'client_name',
        'contact_person',
        'deal_owner',
        'stage',
        'value',
        'expected_close_date',
        'meta_text',
        'is_won',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(LeadCall::class)->latest();
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'related_id')
            ->where('related_type', 'Deal')
            ->orderBy('start_time', 'asc');
    }
}
