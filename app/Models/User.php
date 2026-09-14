<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'secondary_email',
        'password',
        'title',
        'department',
        'role',
        'type',
        'pay',
        'phone',
        'color',
        'initials',
        'avatar_url',
        'bio',
        'google_calendar_email',
        'google_calendar_status',
        'google_calendar_synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google_calendar_synced_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
