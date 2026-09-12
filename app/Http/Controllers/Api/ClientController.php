<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Client::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'projects' => 'nullable|integer',
            'service' => 'nullable|string|max:255',
            'project_status' => 'nullable|string|max:100',
            'project_value' => 'nullable|numeric',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully.',
            'data' => $client
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json($client);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => 'sometimes|required|string|max:255',
            'client_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'owner' => 'nullable|string|max:255',
            'projects' => 'nullable|integer',
            'service' => 'nullable|string|max:255',
            'project_status' => 'nullable|string|max:100',
            'project_value' => 'nullable|numeric',
        ]);

        $client->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Client updated successfully.',
            'data' => $client
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client removed successfully.'
        ]);
    }
}
