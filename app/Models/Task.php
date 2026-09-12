<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'stage',
        'assigned_to',
        'assigned_initials',
        'assigned_color',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
