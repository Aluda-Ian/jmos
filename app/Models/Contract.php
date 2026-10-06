<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Contract extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_SENT = 'Sent';

    public const STATUS_VIEWED = 'Viewed';

    public const STATUS_SIGNED = 'Signed';

    public const STATUS_VOID = 'Void';

    protected $fillable = [
        'contract_number',
        'template',
        'title',
        'client_id',
        'quote_id',
        'client_name',
        'client_registration',
        'client_po_box',
        'client_address',
        'client_email',
        'client_phone',
        'signatory_name',
        'signatory_position',
        'fields',
        'body',
        'ai_instructions',
        'ai_generated',
        'status',
        'access_token',
        'sent_at',
        'viewed_at',
        'provider_signed_at',
        'signed_at',
        'client_signature',
        'client_signed_name',
        'client_signed_position',
        'client_signed_ip',
        'client_signed_user_agent',
        'data_consent',
        'body_hash',
        'created_by',
    ];

    /**
     * The signature image and signing metadata are never sent to the dashboard list.
     *
     * @var list<string>
     */
    protected $hidden = [
        'client_signature',
        'client_signed_user_agent',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'sign_url',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'ai_generated' => 'boolean',
            'data_consent' => 'boolean',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'provider_signed_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Contract $contract): void {
            if (empty($contract->contract_number)) {
                $contract->contract_number = static::nextContractNumber();
            }
            if (empty($contract->access_token)) {
                $contract->access_token = Str::random(48);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Signed or voided agreements can no longer be edited.
     */
    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_SIGNED, self::STATUS_VOID], true);
    }

    /**
     * The client may sign only after the agreement has been sent and before it is signed or voided.
     */
    public function canBeSignedByClient(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_VIEWED], true);
    }

    public function signUrl(): string
    {
        return route('contracts.public.show', $this->access_token);
    }

    public function getSignUrlAttribute(): ?string
    {
        return $this->access_token ? $this->signUrl() : null;
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->isLocked();
    }

    /**
     * Generate the next contract sequence number, e.g. CT-2026-004.
     */
    public static function nextContractNumber(): string
    {
        $year = date('Y');
        $prefix = "CT-{$year}-";
        $maxSeq = 0;

        foreach (static::where('contract_number', 'like', "{$prefix}%")->pluck('contract_number') as $number) {
            if (preg_match('/CT-\d{4}-(\d+)/', (string) $number, $m)) {
                $maxSeq = max($maxSeq, (int) $m[1]);
            }
        }

        return sprintf('CT-%s-%03d', $year, $maxSeq + 1);
    }
}
