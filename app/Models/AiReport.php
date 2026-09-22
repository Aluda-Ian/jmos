<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_role',
        'report_type',
        'data_scope',
        'summary_content',
        'structured_data',
        'model_used',
        'cache_key',
    ];

    protected function casts(): array
    {
        return [
            'structured_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
