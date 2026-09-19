<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'lead_name',
        'company',
        'title',
        'email',
        'phone',
        'lead_source',
        'lead_status',
        'lead_owner',
        'rating',
        'industry',
        'annual_revenue',
        'city',
        'address',
        'website',
        'notes',
        'is_converted',
        'converted_at',
        'converted_client_id',
        'converted_contact_id',
        'converted_deal_id',
    ];

    protected $casts = [
        'is_converted' => 'boolean',
        'converted_at' => 'datetime',
        'annual_revenue' => 'decimal:2',
    ];

    /**
     * Associated phone call logs
     */
    public function calls(): HasMany
    {
        return $this->hasMany(LeadCall::class)->latest();
    }

    /**
     * Associated calendar meetings
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'related_id')
            ->where('related_type', 'Lead')
            ->orderBy('start_time', 'asc');
    }

    /**
     * Associated individual contact records
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'lead_id');
    }

    /**
     * Converted client/account record
     */
    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    /**
     * Converted individual contact record
     */
    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    /**
     * Converted pipeline deal
     */
    public function convertedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'converted_deal_id');
    }
}
