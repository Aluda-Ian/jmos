<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRecipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ServiceRecipe::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'stages' => 'nullable|array',
            'deliverables' => 'nullable|string',
        ]);

        $service = ServiceRecipe::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Service recipe created successfully.',
            'data' => $service,
        ], 201);
    }

    public function destroy(ServiceRecipe $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service recipe deleted successfully.',
        ]);
    }
}

