<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TaskAssignedMail;
use App\Models\AppNotification;
use App\Models\CalendarEvent;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                try {
                    Mail::to($user->email)->send(new TaskAssignedMail([
                        'userName' => $user->name,
                        'taskTitle' => $task->title,
                        'projectName' => $projName,
                        'deadline' => $task->due_date ?? 'Immediate',
                        'role' => 'Assignee',
                        'actionUrl' => url('/'),
                    ]));
                } catch (\Throwable $e) {
                    Log::warning('Failed sending task assignment email: '.$e->getMessage());
                }
            }
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        CalendarEvent::where('related_type', 'task')->where('related_id', $task->id)->delete();
        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task removed.',
        ]);
    }
}
