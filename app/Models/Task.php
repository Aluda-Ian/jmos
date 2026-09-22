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
        'assigned_to_id',
        'assigned_by_id',
        'title',
        'description',
        'stage',
        'priority',
        'assigned_to',
        'assigned_initials',
        'assigned_color',
        'sticky_color',
        'links',
        'comments',
        'due_date',
    ];

    protected $casts = [
        'links' => 'array',
        'comments' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
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
