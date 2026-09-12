<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

        $task = Task::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created.',
            'data' => $task
        ], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'stage' => 'nullable|in:todo,in_progress,review_internal,review_client,done',
            'assigned_to' => 'nullable|string',
            'assigned_initials' => 'nullable|string',
            'assigned_color' => 'nullable|string',
        ]);

        $task->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated.',
            'data' => $task
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task removed.'
        ]);
    }
}
