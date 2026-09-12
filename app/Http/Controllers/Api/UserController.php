<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'title' => 'nullable|string|max:255',
            'role' => 'required|in:owner,finance,sales,team',
            'type' => 'required|string|max:100',
            'pay' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:4',
            'color' => 'nullable|string',
            'initials' => 'nullable|string',
        ]);

        $colors = ['#C52523', '#2B6E8A', '#8A5A2B', '#5A7A2B', '#6E2B8A', '#2B8A5A', '#B4780F', '#7A2B5A'];
        $userCount = User::count();

        // compute initials if missing
        $words = preg_split('/\s+/', trim($validated['name']));
        $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password'] ?? 'jeota2024'),
            'title' => $validated['title'] ?? 'Team',
            'role' => $validated['role'],
            'type' => $validated['type'],
            'pay' => $validated['pay'] ?? '—',
            'color' => $validated['color'] ?? $colors[$userCount % count($colors)],
            'initials' => $validated['initials'] ?? $initials,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Person added to team.',
            'data' => $user
        ], 201);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // Safety checks
        if ($user->role === 'owner' && User::where('role', 'owner')->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot remove the last owner account.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User removed.'
        ]);
    }
}
