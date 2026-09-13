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
