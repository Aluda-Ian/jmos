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
        'due_date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Task $task): void {
            if ($task->wasChanged('project_id')) {
                $oldProjectId = $task->getOriginal('project_id');
                if ($oldProjectId && $oldProjectId !== $task->project_id) {
                    Project::find($oldProjectId)?->recalculateProgress();
                }
            }

            if ($task->project_id && ($task->wasChanged('stage') || $task->wasChanged('project_id') || $task->wasRecentlyCreated)) {
                Project::find($task->project_id)?->recalculateProgress();
            }
        });

        static::deleted(function (Task $task): void {
            if ($task->project_id) {
                Project::find($task->project_id)?->recalculateProgress();
            }
        });
    }
}
