<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Project::with('tasks')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'client' => 'required|string|max:255',
            'project_type' => 'nullable|string|max:100',
            'project_manager' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'deadline' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric',
            'progress_pct' => 'nullable|integer',
            'waiting_on' => 'nullable|string|max:50',
        ]);

        $project = Project::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully.',
            'data' => $project
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
            'project_type' => 'nullable|string|max:100',
            'project_manager' => 'nullable|string|max:255',
            'stage' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'priority' => 'nullable|string|max:50',
            'deadline' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric',
            'progress_pct' => 'nullable|integer',
            'waiting_on' => 'nullable|string|max:50',
        ]);

        $project->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully.',
            'data' => $project
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully.'
        ]);
    }
}
