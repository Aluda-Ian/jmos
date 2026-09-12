<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'client',
        'project_type',
        'project_manager',
        'stage',
        'status',
        'priority',
        'deadline',
        'budget',
        'progress_pct',
        'waiting_on',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
