<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_type',
        'contact_person',
        'owner',
        'projects',
        'service',
        'project_status',
        'project_value',
    ];
}
