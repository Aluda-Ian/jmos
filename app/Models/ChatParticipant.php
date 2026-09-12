<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'user_id',
        'last_read_at',
        'notified_initial_email',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'notified_initial_email' => 'boolean',
        ];
    }

    public function thread()
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
