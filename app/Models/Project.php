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
        'drive_link',
        'brief_link',
        'treatment_link',
        'playbook_link',
        'notes',
        'files',
        'comments',
    ];

    protected $casts = [
        'files' => 'array',
        'comments' => 'array',
        'budget' => 'float',
        'progress_pct' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function recalculateProgress(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            $this->update(['progress_pct' => 0]);

            return 0;
        }

        $done = $this->tasks()->where('stage', 'done')->count();
        $pct = (int) round(($done / $total) * 100);

        $this->update(['progress_pct' => $pct]);

        return $pct;
    }
}
