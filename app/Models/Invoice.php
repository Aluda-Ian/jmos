<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'client',
        'client_id',
        'project_id',
        'title',
        'type',
        'items',
        'amount',
        'subtotal',
        'discount',
        'tax',
        'method',
        'etims',
        'status',
        'due_date',
        'notes',
        'quote_id',
    ];

    protected $casts = [
        'items' => 'array',
        'etims' => 'boolean',
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    public function clientModel(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            if (empty($invoice->invoice_no)) {
                $invoice->invoice_no = static::nextInvoiceNo();
            }
        });
    }

    public static function nextInvoiceNo(): string
    {
        $maxNum = 145;
        $allInvoices = self::pluck('invoice_no');
        foreach ($allInvoices as $no) {
            if (preg_match('/(\d+)/', (string) $no, $m)) {
                $n = (int) $m[1];
                if ($n > $maxNum) {
                    $maxNum = $n;
                }
            }
        }
        $next = $maxNum + 1;

        return sprintf('JM-%04d', $next);
    }
}
