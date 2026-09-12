<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'created_by',
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'thread_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'thread_id', 'user_id')
                    ->withPivot('last_read_at', 'notified_initial_email')
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'thread_id')->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'thread_id')->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
