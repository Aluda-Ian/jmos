<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Task::with('project')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'stage' => 'required|in:todo,in_progress,review_internal,review_client,done',
            'assigned_to' => 'nullable|string',
            'assigned_initials' => 'nullable|string',
            'assigned_color' => 'nullable|string',
            'due_date' => 'nullable|string',
        ]);

        $task = Task::create($validated);
        $task->load('project');

        $this->syncTaskCalendarEvent($task);
        $this->notifyTaskAssignee($task);

        AuditLog::record('CREATE', "Created task '{$task->title}'".($task->project ? " on {$task->project->project_name}" : ''), 'Task', $task->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created.',
            'data' => $task,
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'sometimes|required|string|max:255',
            'stage' => 'nullable|in:todo,in_progress,review_internal,review_client,done',
            'assigned_to' => 'nullable|string',
            'assigned_initials' => 'nullable|string',
            'assigned_color' => 'nullable|string',
            'due_date' => 'nullable|string',
        ]);

        $oldAssignee = $task->assigned_to;
        $task->update($validated);
        $task->load('project');

        if (! empty($validated['due_date'])) {
            $this->syncTaskCalendarEvent($task);
        }

        if (! empty($validated['assigned_to']) && $validated['assigned_to'] !== $oldAssignee) {
            $this->notifyTaskAssignee($task);
        }

        AuditLog::record('UPDATE', "Updated task '{$task->title}' (Stage: {$task->stage})", 'Task', $task->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated.',
            'data' => $task,
        ]);
    }

    protected function syncTaskCalendarEvent(Task $task): void
    {
        if (empty($task->due_date)) {
            return;
        }

        try {
            $date = Carbon::parse($task->due_date);
            $projName = $task->project ? $task->project->project_name : 'General Task';
            CalendarEvent::updateOrCreate(
                [
                    'related_type' => 'task',
                    'related_id' => $task->id,
                ],
                [
                    'title' => 'Task Due: '.$task->title,
                    'description' => "Project: {$projName} | Assignee: ".($task->assigned_to ?? 'Unassigned')." | Stage: {$task->stage}",
                    'event_type' => 'deadline',
                    'start_time' => $date->copy()->startOfDay(),
                    'end_time' => $date->copy()->endOfDay(),
                    'all_day' => true,
                    'status' => 'confirmed',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to parse task due date for calendar event: '.$e->getMessage());
        }
    }

    protected function notifyTaskAssignee(Task $task): void
    {
        if (empty($task->assigned_to)) {
            return;
        }

        $assigneeName = trim($task->assigned_to);
        $user = User::where('name', 'like', '%'.$assigneeName.'%')->first();

        if ($user) {
            $projName = $task->project ? $task->project->project_name : 'Internal Operations';
            AppNotification::create([
                'user_id' => $user->id,
                'type' => 'task',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned task: {$task->title} on project {$projName}",
                'link' => 'tasks',
                'read' => false,
            ]);

            if ($user->email) {
                NotificationService::sendTaskAssigned([
                    'assigneeName' => $user->name,
                    'userName' => $user->name,
                    'taskTitle' => $task->title,
                    'projectName' => $projName,
                    'stage' => $task->stage,
                    'deadline' => $task->due_date ?? 'Immediate',
                    'role' => 'Assignee',
                    'assignedBy' => auth('sanctum')->user()?->name ?? 'Production Lead',
                    'actionUrl' => url('/'),
                ], $user->email);
            }
        }
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $title = $task->title;
        $id = $task->id;

        CalendarEvent::where('related_type', 'task')->where('related_id', $task->id)->delete();
        $task->delete();

        AuditLog::record('DELETE', "Removed task '{$title}'", 'Task', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Task removed.',
        ]);
    }
}
