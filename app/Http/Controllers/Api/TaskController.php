<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class TaskController extends Controller
{
    /**
     * Palette of sticky note colors for tasks.
     */
    protected array $stickyColors = [
        '#FFFBEB', // Sunny Amber / Cream
        '#FDF2F8', // Rose Blush
        '#ECFDF5', // Mint Frost
        '#F0F9FF', // Sky Azure
        '#F5F3FF', // Lavender Soft
        '#FFF7ED', // Warm Peach
        '#FEF9C3', // Lemon Yellow
    ];

    protected function resolveCurrentUser(Request $request): ?User
    {
        if ($user = $request->user()) {
            return $user;
        }

        if ($bearer = $request->bearerToken()) {
            if ($accessToken = PersonalAccessToken::findToken($bearer)) {
                return $accessToken->tokenable;
            }
        }

        if ($user = auth('sanctum')->user()) {
            return $user;
        }

        $userId = $request->header('X-User-Id') ?? $request->input('user_id');
        if ($userId && $found = User::find($userId)) {
            return $found;
        }

        return null;
    }

    /**
     * Check if a user is authorized to move / advance / review a task.
     */
    public function canMoveTask(?User $user, Task $task): bool
    {
        if (! $user) {
            return false;
        }

        // 1. Owner has full workspace access
        if ($user->role === 'owner' || $user->hasPermission('*')) {
            return true;
        }

        // 2. Task Assigner (Creator)
        if ($task->assigned_by_id && (int) $task->assigned_by_id === (int) $user->id) {
            return true;
        }

        // 3. Task Assignee (by ID)
        if ($task->assigned_to_id && (int) $task->assigned_to_id === (int) $user->id) {
            return true;
        }

        // 4. Task Assignee (by name match fallback)
        if (! empty($task->assigned_to)) {
            $nameLower = strtolower(trim($task->assigned_to));
            $userNameLower = strtolower(trim($user->name));
            $userFirstLower = strtolower(explode(' ', trim($user->name))[0] ?? '');

            if ($nameLower === $userNameLower || (strlen($userFirstLower) >= 3 && str_contains($nameLower, $userFirstLower))) {
                return true;
            }
        }

        // 5. Project Manager of the associated project
        if ($task->project && ! empty($task->project->project_manager)) {
            $pmLower = strtolower(trim($task->project->project_manager));
            $userNameLower = strtolower(trim($user->name));
            if ($pmLower === $userNameLower || str_contains($pmLower, $userNameLower)) {
                return true;
            }
        }

        // 6. Managers / IT Administrators with tasks.manage permission
        if ($user->hasPermission('tasks.manage') && in_array($user->role, ['manager', 'admin', 'it_manager'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Determine cloud provider from URL.
     */
    protected function detectProvider(string $url): string
    {
        $urlLower = strtolower($url);

        if (str_contains($urlLower, 'drive.google.com') || str_contains($urlLower, 'docs.google.com')) {
            return 'Google Drive';
        }
        if (str_contains($urlLower, 'dropbox.com')) {
            return 'Dropbox';
        }
        if (str_contains($urlLower, 'playbook.com')) {
            return 'Playbook';
        }
        if (str_contains($urlLower, 'frame.io')) {
            return 'Frame.io';
        }
        if (str_contains($urlLower, 'figma.com')) {
            return 'Figma';
        }
        if (str_contains($urlLower, 'notion.so') || str_contains($urlLower, 'notion.site')) {
            return 'Notion';
        }
        if (str_contains($urlLower, 'canva.com')) {
            return 'Canva';
        }
        if (str_contains($urlLower, 'onedrive') || str_contains($urlLower, 'sharepoint.com')) {
            return 'OneDrive';
        }
        if (str_contains($urlLower, 'youtube.com') || str_contains($urlLower, 'youtu.be')) {
            return 'YouTube';
        }
        if (str_contains($urlLower, 'vimeo.com')) {
            return 'Vimeo';
        }

        return 'Review Link';
    }

    public function index(Request $request): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);
        $query = Task::with(['project', 'assignedUser', 'assignedBy'])->latest();

        if ($currentUser) {
            $isOwnerOrSuper = $currentUser->role === 'owner' || $currentUser->hasPermission('*');
            $isItOrManager = in_array($currentUser->role, ['admin', 'manager', 'it_manager'], true)
                || $currentUser->hasPermission('system.upgrade')
                || $currentUser->hasPermission('roles.manage');

            // Requirement 1: Tasks should only be visible to people involved in project, IT manager and owner view all tasks
            if (! $isOwnerOrSuper && ! $isItOrManager) {
                $userId = $currentUser->id;
                $userName = trim($currentUser->name);
                $first = explode(' ', $userName)[0] ?? '';

                $query->where(function ($q) use ($userId, $userName, $first) {
                    // 1. Directly assigned to or created by user
                    $q->where('tasks.assigned_to_id', $userId)
                        ->orWhere('tasks.assigned_by_id', $userId);

                    if (! empty($userName)) {
                        $q->orWhere('tasks.assigned_to', 'like', "%{$userName}%");
                        if (strlen($first) >= 3) {
                            $q->orWhere('tasks.assigned_to', 'like', "%{$first}%");
                        }
                    }

                    // 2. Tasks in projects where current user is the Project Manager
                    $q->orWhereHas('project', function ($pq) use ($userName, $first) {
                        if (! empty($userName)) {
                            $pq->where('projects.project_manager', 'like', "%{$userName}%");
                            if (strlen($first) >= 3) {
                                $pq->where('projects.project_manager', 'like', "%{$first}%");
                            }
                        }
                    });

                    // 3. Tasks in projects where current user is involved as a team member
                    $q->orWhereHas('project', function ($pq) use ($userId, $userName) {
                        $pq->whereHas('tasks', function ($ptq) use ($userId, $userName) {
                            $ptq->where('tasks.assigned_to_id', $userId);
                            if (! empty($userName)) {
                                $ptq->orWhere('tasks.assigned_to', 'like', "%{$userName}%");
                            }
                        });
                    });
                });
            }
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'assigned_by_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stage' => 'required|in:todo,in_progress,review_internal,review_client,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|string',
            'assigned_initials' => 'nullable|string',
            'assigned_color' => 'nullable|string',
            'sticky_color' => 'nullable|string',
            'links' => 'nullable|array',
            'comments' => 'nullable|array',
            'due_date' => 'nullable|string',
        ]);

        if (empty($validated['assigned_by_id']) && $currentUser) {
            $validated['assigned_by_id'] = $currentUser->id;
        }

        // Auto assign a random sticky note color if not provided
        if (empty($validated['sticky_color'])) {
            $validated['sticky_color'] = $this->stickyColors[array_rand($this->stickyColors)];
        }

        // Auto resolve assignee details
        if (! empty($validated['assigned_to_id'])) {
            $assignee = User::find($validated['assigned_to_id']);
            if ($assignee) {
                $validated['assigned_to'] = $validated['assigned_to'] ?? $assignee->name;
                $validated['assigned_initials'] = $validated['assigned_initials'] ?? $assignee->initials;
                $validated['assigned_color'] = $validated['assigned_color'] ?? $assignee->color;
            }
        } elseif (! empty($validated['assigned_to'])) {
            $assignee = User::where('name', 'like', '%'.trim($validated['assigned_to']).'%')->first();
            if ($assignee) {
                $validated['assigned_to_id'] = $assignee->id;
                $validated['assigned_initials'] = $validated['assigned_initials'] ?? $assignee->initials;
                $validated['assigned_color'] = $validated['assigned_color'] ?? $assignee->color;
            }
        }

        $task = Task::create($validated);
        $task->load(['project', 'assignedUser', 'assignedBy']);

        $this->syncTaskCalendarEvent($task);
        $this->notifyTaskAssignee($task, $currentUser);

        AuditLog::record('CREATE', "Created task '{$task->title}'".($task->project ? " on {$task->project->project_name}" : ''), 'Task', $task->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created.',
            'data' => $task,
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load(['project', 'assignedUser', 'assignedBy']);

        return response()->json([
            'status' => 'success',
            'data' => $task,
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to_id' => 'nullable|exists:users,id',
            'assigned_by_id' => 'nullable|exists:users,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'stage' => 'nullable|in:todo,in_progress,review_internal,review_client,done',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|string',
            'assigned_initials' => 'nullable|string',
            'assigned_color' => 'nullable|string',
            'sticky_color' => 'nullable|string',
            'links' => 'nullable|array',
            'comments' => 'nullable|array',
            'due_date' => 'nullable|string',
        ]);

        // Enforce authorization for stage advancing or updates
        if (isset($validated['stage']) && $validated['stage'] !== $task->stage) {
            if (! $this->canMoveTask($currentUser, $task)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only the assigned team member or the task assigner can advance this task.',
                ], 403);
            }
        } elseif (! empty($validated) && ! $this->canMoveTask($currentUser, $task)) {
            if ($currentUser && ! $currentUser->hasPermission('tasks.manage') && $currentUser->role !== 'owner') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to modify this task.',
                ], 403);
            }
        }

        $oldStage = $task->stage;
        $oldAssignee = $task->assigned_to;
        $oldAssigneeId = $task->assigned_to_id;

        // Auto sync assignee if changed
        if (isset($validated['assigned_to_id']) && $validated['assigned_to_id'] !== $oldAssigneeId) {
            $newAssignee = User::find($validated['assigned_to_id']);
            if ($newAssignee) {
                $validated['assigned_to'] = $newAssignee->name;
                $validated['assigned_initials'] = $newAssignee->initials;
                $validated['assigned_color'] = $newAssignee->color;
            }
        } elseif (isset($validated['assigned_to']) && $validated['assigned_to'] !== $oldAssignee) {
            $matched = User::where('name', 'like', '%'.trim($validated['assigned_to']).'%')->first();
            if ($matched) {
                $validated['assigned_to_id'] = $matched->id;
                $validated['assigned_initials'] = $matched->initials;
                $validated['assigned_color'] = $matched->color;
            }
        }

        $task->update($validated);
        $task->load(['project', 'assignedUser', 'assignedBy']);

        if (! empty($validated['due_date'])) {
            $this->syncTaskCalendarEvent($task);
        }

        // Notify new assignee if assignee changed
        if (! empty($validated['assigned_to']) && $validated['assigned_to'] !== $oldAssignee) {
            $this->notifyTaskAssignee($task, $currentUser);
        }

        // Notify counterpart if stage moved forward/changed
        if (isset($validated['stage']) && $validated['stage'] !== $oldStage) {
            $this->notifyTaskStageTransition($task, $oldStage, $validated['stage'], $currentUser);
        }

        AuditLog::record('UPDATE', "Updated task '{$task->title}' (Stage: {$task->stage})", 'Task', $task->id, $validated, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated.',
            'data' => $task,
        ]);
    }

    /**
     * Requirement 2: Add deliverable / review link to task (e.g. video review, report, designs).
     */
    public function addLink(Request $request, Task $task): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'provider' => 'nullable|string|max:100',
        ]);

        $provider = ! empty($validated['provider']) ? $validated['provider'] : $this->detectProvider($validated['url']);

        $newLink = [
            'id' => 'lnk_'.Str::random(10),
            'title' => $validated['title'],
            'url' => $validated['url'],
            'provider' => $provider,
            'created_by' => $currentUser?->name ?? 'Team Member',
            'created_by_id' => $currentUser?->id,
            'created_at' => now()->toIso8601String(),
        ];

        $existingLinks = is_array($task->links) ? $task->links : [];
        $existingLinks[] = $newLink;
        $task->update(['links' => $existingLinks]);
        $task->load(['project', 'assignedUser', 'assignedBy']);

        // Automatically record an activity comment
        $this->appendTaskComment($task, [
            'id' => 'cmt_'.Str::random(10),
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Team Member',
            'user_role' => $currentUser?->role ?? 'team',
            'user_color' => $currentUser?->color ?? '#C52523',
            'message' => "Attached deliverable review link: {$validated['title']} ({$provider})",
            'type' => 'link_attached',
            'created_at' => now()->toIso8601String(),
        ]);

        // Notify Project Manager and Owner that a review link was added
        $this->notifyReviewLinkAdded($task, $newLink, $currentUser);

        AuditLog::record('CREATE', "Added review link '{$validated['title']}' on task '{$task->title}'", 'Task', $task->id, $newLink, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Review link added successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Remove deliverable / review link from task.
     */
    public function removeLink(Request $request, Task $task, string $linkId): JsonResponse
    {
        $existingLinks = is_array($task->links) ? $task->links : [];
        $filtered = array_values(array_filter($existingLinks, fn ($l) => ($l['id'] ?? '') !== $linkId));

        $task->update(['links' => $filtered]);
        $task->load(['project', 'assignedUser', 'assignedBy']);

        return response()->json([
            'status' => 'success',
            'message' => 'Review link removed.',
            'data' => $task,
        ]);
    }

    /**
     * Requirement 3: Add comment / recommendation to a task.
     */
    public function addComment(Request $request, Task $task): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'type' => 'nullable|in:comment,recommendation,change_request,approval',
        ]);

        $type = $validated['type'] ?? 'comment';

        $commentObj = [
            'id' => 'cmt_'.Str::random(10),
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Team Member',
            'user_role' => $currentUser?->role ?? 'team',
            'user_color' => $currentUser?->color ?? '#C52523',
            'message' => $validated['message'],
            'type' => $type,
            'created_at' => now()->toIso8601String(),
        ];

        $this->appendTaskComment($task, $commentObj);
        $task->load(['project', 'assignedUser', 'assignedBy']);

        // Notify counterparts about the new comment / recommendation
        $this->notifyCommentAdded($task, $commentObj, $currentUser);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment recorded.',
            'data' => $task,
        ]);
    }

    /**
     * Requirement 3: Project Manager workflow action (Send back for changes or Proceed to next level).
     */
    public function workflowAction(Request $request, Task $task): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        $validated = $request->validate([
            'action' => 'required|in:send_back,proceed',
            'recommendation' => 'nullable|string|max:5000',
        ]);

        $action = $validated['action'];
        $recommendation = trim((string) ($validated['recommendation'] ?? ''));

        if (! $this->canMoveTask($currentUser, $task)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to perform workflow review on this task.',
            ], 403);
        }

        $stagesOrder = ['todo', 'in_progress', 'review_internal', 'review_client', 'done'];
        $currentIdx = array_search($task->stage, $stagesOrder, true);
        if ($currentIdx === false) {
            $currentIdx = 0;
        }

        if ($action === 'send_back') {
            // Revert to in_progress (or todo if already in progress/todo)
            $newStage = ($task->stage === 'in_progress' || $task->stage === 'todo') ? 'todo' : 'in_progress';

            $commentMessage = 'Sent back for revisions'.($recommendation ? ": {$recommendation}" : '. Please update deliverable and resubmit.');

            $commentObj = [
                'id' => 'cmt_'.Str::random(10),
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? 'Project Lead',
                'user_role' => $currentUser?->role ?? 'manager',
                'user_color' => $currentUser?->color ?? '#C52523',
                'message' => $commentMessage,
                'type' => 'change_request',
                'created_at' => now()->toIso8601String(),
            ];

            $this->appendTaskComment($task, $commentObj);
            $task->update(['stage' => $newStage]);
            $task->load(['project', 'assignedUser', 'assignedBy']);

            // Notify Assignee that task was sent back for changes
            $this->notifyTaskSentBack($task, $commentMessage, $currentUser);

            AuditLog::record('UPDATE', "Task '{$task->title}' sent back for changes to {$newStage}", 'Task', $task->id, ['recommendation' => $recommendation], $request);

            return response()->json([
                'status' => 'success',
                'message' => 'Task sent back for changes.',
                'data' => $task,
            ]);
        }

        if ($action === 'proceed') {
            $nextIdx = min($currentIdx + 1, count($stagesOrder) - 1);
            $newStage = $stagesOrder[$nextIdx];

            $stageLabels = [
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'review_internal' => 'Internal Review',
                'review_client' => 'Client Review',
                'done' => 'Completed',
            ];

            $newStageLabel = $stageLabels[$newStage] ?? $newStage;
            $commentMessage = "Approved & advanced to {$newStageLabel}".($recommendation ? " (Note: {$recommendation})" : '');

            $commentObj = [
                'id' => 'cmt_'.Str::random(10),
                'user_id' => $currentUser?->id,
                'user_name' => $currentUser?->name ?? 'Project Lead',
                'user_role' => $currentUser?->role ?? 'manager',
                'user_color' => $currentUser?->color ?? '#C52523',
                'message' => $commentMessage,
                'type' => 'approval',
                'created_at' => now()->toIso8601String(),
            ];

            $this->appendTaskComment($task, $commentObj);
            $task->update(['stage' => $newStage]);
            $task->load(['project', 'assignedUser', 'assignedBy']);

            $this->notifyTaskStageTransition($task, $stagesOrder[$currentIdx], $newStage, $currentUser);

            AuditLog::record('UPDATE', "Task '{$task->title}' advanced to {$newStage}", 'Task', $task->id, [], $request);

            return response()->json([
                'status' => 'success',
                'message' => "Task advanced to {$newStageLabel}.",
                'data' => $task,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action'], 422);
    }

    protected function appendTaskComment(Task $task, array $comment): void
    {
        $existing = is_array($task->comments) ? $task->comments : [];
        $existing[] = $comment;
        $task->update(['comments' => $existing]);
    }

    protected function notifyReviewLinkAdded(Task $task, array $link, ?User $actor): void
    {
        $actorName = $actor?->name ?? 'Team Member';
        $projName = $task->project ? " on {$task->project->project_name}" : '';

        // Target Owner and Project Manager
        $targets = collect();

        // 1. Owner
        $owner = User::where('role', 'owner')->first();
        if ($owner && (! $actor || $owner->id !== $actor->id)) {
            $targets->push($owner);
        }

        // 2. Project Manager
        if ($task->project && ! empty($task->project->project_manager)) {
            $pmName = trim($task->project->project_manager);
            $pm = User::where('name', 'like', "%{$pmName}%")->first();
            if ($pm && (! $actor || $pm->id !== $actor->id)) {
                $targets->push($pm);
            }
        }

        // 3. Assigner
        if ($task->assigned_by_id && (! $actor || (int) $task->assigned_by_id !== (int) $actor->id)) {
            $assigner = User::find($task->assigned_by_id);
            if ($assigner) {
                $targets->push($assigner);
            }
        }

        foreach ($targets->unique('id') as $recipient) {
            AppNotification::create([
                'user_id' => $recipient->id,
                'type' => 'task',
                'title' => 'Deliverable Review Link Added',
                'message' => "{$actorName} attached review link '{$link['title']}' ({$link['provider']}) for task: {$task->title}{$projName}",
                'link' => 'tasks',
                'read' => false,
            ]);
        }
    }

    protected function notifyCommentAdded(Task $task, array $comment, ?User $actor): void
    {
        $actorName = $actor?->name ?? 'Team Member';
        $projName = $task->project ? " on {$task->project->project_name}" : '';

        $targets = collect();

        // If Assignee commented -> notify Assigner and PM
        if ($task->assigned_by_id && (! $actor || (int) $task->assigned_by_id !== (int) $actor->id)) {
            $assigner = User::find($task->assigned_by_id);
            if ($assigner) {
                $targets->push($assigner);
            }
        }

        // If someone else commented -> notify Assignee
        if ($task->assigned_to_id && (! $actor || (int) $task->assigned_to_id !== (int) $actor->id)) {
            $assignee = User::find($task->assigned_to_id);
            if ($assignee) {
                $targets->push($assignee);
            }
        } elseif (! empty($task->assigned_to)) {
            $assignee = User::where('name', 'like', '%'.trim($task->assigned_to).'%')->first();
            if ($assignee && (! $actor || (int) $assignee->id !== (int) $actor->id)) {
                $targets->push($assignee);
            }
        }

        // Always notify PM if distinct
        if ($task->project && ! empty($task->project->project_manager)) {
            $pm = User::where('name', 'like', '%'.trim($task->project->project_manager).'%')->first();
            if ($pm && (! $actor || $pm->id !== $actor->id)) {
                $targets->push($pm);
            }
        }

        $typeLabel = match ($comment['type'] ?? 'comment') {
            'recommendation' => '💡 New Recommendation',
            'change_request' => '⚠️ Revision Requested',
            'approval' => '✅ Stage Approval',
            default => '💬 New Task Comment',
        };

        foreach ($targets->unique('id') as $recipient) {
            AppNotification::create([
                'user_id' => $recipient->id,
                'type' => 'task',
                'title' => $typeLabel,
                'message' => "{$actorName}: \"".Str::limit($comment['message'], 120)."\" on task {$task->title}{$projName}",
                'link' => 'tasks',
                'read' => false,
            ]);
        }
    }

    protected function notifyTaskSentBack(Task $task, string $commentMessage, ?User $actor): void
    {
        $actorName = $actor?->name ?? 'Project Manager';
        $projName = $task->project ? " on {$task->project->project_name}" : '';

        $assigneeUser = $task->assigned_to_id ? User::find($task->assigned_to_id) : null;
        if (! $assigneeUser && ! empty($task->assigned_to)) {
            $assigneeUser = User::where('name', 'like', '%'.trim($task->assigned_to).'%')->first();
        }

        if ($assigneeUser && (! $actor || (int) $assigneeUser->id !== (int) $actor->id)) {
            AppNotification::create([
                'user_id' => $assigneeUser->id,
                'type' => 'task',
                'title' => '⚠️ Changes Requested on Task',
                'message' => "{$actorName} sent back task '{$task->title}' for revisions{$projName}. Note: {$commentMessage}",
                'link' => 'tasks',
                'read' => false,
            ]);

            if ($assigneeUser->email) {
                NotificationService::sendTaskAssigned([
                    'assigneeName' => $assigneeUser->name,
                    'userName' => $assigneeUser->name,
                    'taskTitle' => $task->title,
                    'projectName' => $task->project ? $task->project->project_name : 'Operations',
                    'stage' => 'Revisions Required (In Progress)',
                    'deadline' => $task->due_date ?? 'Immediate',
                    'role' => 'Assignee',
                    'assignedBy' => $actorName,
                    'actionUrl' => url('/'),
                ], $assigneeUser->email);
            }
        }
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

    protected function notifyTaskAssignee(Task $task, ?User $actor = null): void
    {
        $assigneeUser = $task->assigned_to_id ? User::find($task->assigned_to_id) : null;
        if (! $assigneeUser && ! empty($task->assigned_to)) {
            $assigneeName = trim($task->assigned_to);
            $assigneeUser = User::where('name', 'like', '%'.$assigneeName.'%')->first();
        }

        if ($assigneeUser) {
            $projName = $task->project ? $task->project->project_name : 'Internal Operations';
            $assignerName = $actor?->name ?? ($task->assignedBy?->name ?? 'Production Lead');

            AppNotification::create([
                'user_id' => $assigneeUser->id,
                'type' => 'task',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned task: {$task->title} on project {$projName}",
                'link' => 'tasks',
                'read' => false,
            ]);

            if ($assigneeUser->email) {
                NotificationService::sendTaskAssigned([
                    'assigneeName' => $assigneeUser->name,
                    'userName' => $assigneeUser->name,
                    'taskTitle' => $task->title,
                    'projectName' => $projName,
                    'stage' => $task->stage,
                    'deadline' => $task->due_date ?? 'Immediate',
                    'role' => 'Assignee',
                    'assignedBy' => $assignerName,
                    'actionUrl' => url('/'),
                ], $assigneeUser->email);
            }
        }
    }

    protected function notifyTaskStageTransition(Task $task, string $oldStage, string $newStage, ?User $actor): void
    {
        $stageLabels = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'review_internal' => 'Internal Review',
            'review_client' => 'Client Review',
            'done' => 'Completed',
        ];

        $newStageLabel = $stageLabels[$newStage] ?? ucwords(str_replace('_', ' ', $newStage));
        $actorName = $actor?->name ?? 'Team Member';
        $projName = $task->project ? " on {$task->project->project_name}" : '';

        $recipientUserId = null;

        $isAssignee = ($actor && $task->assigned_to_id && (int) $actor->id === (int) $task->assigned_to_id);
        if (! $isAssignee && $actor && ! empty($task->assigned_to)) {
            $isAssignee = str_contains(strtolower($actor->name), strtolower(explode(' ', trim($task->assigned_to))[0] ?? ''));
        }

        if ($isAssignee) {
            $recipientUserId = $task->assigned_by_id;
        } else {
            $recipientUserId = $task->assigned_to_id;
            if (! $recipientUserId && ! empty($task->assigned_to)) {
                $assignee = User::where('name', 'like', '%'.trim($task->assigned_to).'%')->first();
                $recipientUserId = $assignee?->id;
            }
        }

        if ($recipientUserId && (! $actor || (int) $recipientUserId !== (int) $actor->id)) {
            $recipient = User::find($recipientUserId);
            if ($recipient) {
                AppNotification::create([
                    'user_id' => $recipient->id,
                    'type' => 'task',
                    'title' => 'Task Stage Updated',
                    'message' => "{$actorName} moved task '{$task->title}' to {$newStageLabel}{$projName}",
                    'link' => 'tasks',
                    'read' => false,
                ]);
            }
        }
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $currentUser = $this->resolveCurrentUser($request);

        if (! $this->canMoveTask($currentUser, $task) && (! $currentUser || ! $currentUser->hasPermission('tasks.manage'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to remove this task.',
            ], 403);
        }

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
