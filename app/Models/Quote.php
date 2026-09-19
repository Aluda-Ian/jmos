<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'lead_id',
        'client_id',
        'deal_id',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'title',
        'items',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'validity_days',
        'status',
        'converted_invoice_id',
        'notes',
        'terms',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'validity_days' => 'integer',
        'sent_at' => 'datetime',
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

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    /**
     * Generate next quote sequence number
     */
    public static function nextQuoteNumber(): string
    {
        $year = date('Y');
        $prefix = "QT-{$year}-";
        $maxSeq = 0;

        $existingQuotes = static::where('quote_number', 'like', "{$prefix}%")->pluck('quote_number');
        foreach ($existingQuotes as $qNum) {
            if (preg_match('/QT-\d{4}-(\d+)/', (string) $qNum, $m)) {
                $seq = (int) $m[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = $maxSeq + 1;

        return sprintf('QT-%s-%03d', $year, $nextSeq);
    }
}
