<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_type',
        'contact_person',
        'email',
        'phone',
        'address',
        'website',
        'owner',
        'projects',
        'service',
        'project_status',
        'project_value',
        'notes',
    ];

    public function projectsList(): HasMany
    {
        return $this->hasMany(Project::class, 'client', 'client_name');
    }

    public function invoicesList(): HasMany
    {
        return $this->hasMany(Invoice::class, 'client', 'client_name');
    }
}
