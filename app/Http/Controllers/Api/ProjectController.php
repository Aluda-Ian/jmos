<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with('tasks')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'client' => 'required|string|max:255',
            'project_type' => 'nullable|string|max:150',
            'category' => 'nullable|string|max:100',
            'project_manager' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:150',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'deadline' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric',
            'progress_pct' => 'nullable|integer',
            'waiting_on' => 'nullable|string|max:50',
            'drive_link' => 'nullable|string',
            'brief_link' => 'nullable|string',
            'treatment_link' => 'nullable|string',
            'playbook_link' => 'nullable|string',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'comments' => 'nullable|array',
        ]);

        if (empty($validated['category'])) {
            $isInternal = str_contains(strtolower((string) ($validated['project_type'] ?? '')), 'internal')
                || str_contains(strtolower((string) ($validated['project_type'] ?? '')), 'system')
                || str_contains(strtolower((string) ($validated['client'] ?? '')), 'internal');
            $validated['category'] = $isInternal ? 'internal' : 'video_production';
        }

        $project = Project::create($validated);

        $this->syncProjectCalendarEvent($project);
        $this->notifyProjectManager($project);

        AuditLog::record(
            'CREATE',
            "Created live project '{$project->project_name}' for client '{$project->client}'",
            'Project',
            $project->id,
            ['stage' => $project->stage, 'status' => $project->status, 'budget' => $project->budget],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully.',
            'data' => $project->load('tasks'),
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('tasks'));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|required|string|max:255',
            'client' => 'nullable|string|max:255',
            'project_type' => 'nullable|string|max:150',
            'category' => 'nullable|string|max:100',
            'project_manager' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:150',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'deadline' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric',
            'progress_pct' => 'nullable|integer',
            'waiting_on' => 'nullable|string|max:50',
            'drive_link' => 'nullable|string',
            'brief_link' => 'nullable|string',
            'treatment_link' => 'nullable|string',
            'playbook_link' => 'nullable|string',
            'notes' => 'nullable|string',
            'files' => 'nullable|array',
            'comments' => 'nullable|array',
        ]);

        $oldManager = $project->project_manager;
        $project->update($validated);

        if (! empty($validated['deadline'])) {
            $this->syncProjectCalendarEvent($project);
        }

        if (! empty($validated['project_manager']) && $validated['project_manager'] !== $oldManager) {
            $this->notifyProjectManager($project);
        }

        AuditLog::record(
            'UPDATE',
            "Updated project '{$project->project_name}' (Stage: {$project->stage}, Status: {$project->status})",
            'Project',
            $project->id,
            $request->except(['files', 'comments']),
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully.',
            'data' => $project->load('tasks'),
        ]);
    }

    protected function syncProjectCalendarEvent(Project $project): void
    {
        if (empty($project->deadline)) {
            return;
        }

        try {
            $date = Carbon::parse($project->deadline);
            CalendarEvent::updateOrCreate(
                [
                    'related_type' => 'project',
                    'related_id' => $project->id,
                ],
                [
                    'title' => 'Project Deadline: '.$project->project_name,
                    'description' => "Client: {$project->client} | Manager: ".($project->project_manager ?? 'Team')." | Priority: {$project->priority}",
                    'event_type' => 'deadline',
                    'start_time' => $date->copy()->startOfDay(),
                    'end_time' => $date->copy()->endOfDay(),
                    'all_day' => true,
                    'status' => 'confirmed',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Unable to parse project deadline for calendar event: '.$e->getMessage());
        }
    }

    protected function notifyProjectManager(Project $project): void
    {
        if (empty($project->project_manager)) {
            return;
        }

        $managerName = trim($project->project_manager);
        $user = User::where('name', 'like', '%'.$managerName.'%')->first();

        if ($user) {
            AppNotification::create([
                'user_id' => $user->id,
                'type' => 'project',
                'title' => 'Project Lead Assigned',
                'message' => "You have been assigned to lead project: {$project->project_name} for {$project->client}",
                'link' => 'projects',
                'read' => false,
            ]);

            if ($user->email) {
                NotificationService::sendTaskAssigned([
                    'assigneeName' => $user->name,
                    'userName' => $user->name,
                    'taskTitle' => 'Project Lead: '.$project->project_name,
                    'projectName' => $project->project_name,
                    'stage' => $project->stage,
                    'deadline' => $project->deadline ?? 'To be scheduled',
                    'role' => 'Project Manager',
                    'assignedBy' => auth('sanctum')->user()?->name ?? 'Leadership',
                    'actionUrl' => url('/'),
                ], $user->email);
            }
        }
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $name = $project->project_name;
        $id = $project->id;

        CalendarEvent::where('related_type', 'project')->where('related_id', $project->id)->delete();
        $project->delete();

        AuditLog::record(
            'DELETE',
            "Deleted project '{$name}'",
            'Project',
            $id,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully.',
        ]);
    }
}
