<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_id',
        'message',
        'attachment_name',
        'attachment_url',
    ];

    public function thread()
    {
        return $this->belongsTo(ChatThread::class, 'thread_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
