<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roleModel')->orderBy('name')->get()->map(function ($u) {
            $data = $u->toArray();
            $data['permissions'] = $u->allPermissions();

            return $data;
        });

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'required|string|max:100',
            'role_id' => 'nullable|exists:roles,id',
            'custom_permissions' => 'nullable|array',
            'type' => 'required|string|max:100',
            'pay' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|string',
            'password' => 'nullable|string|min:4',
            'color' => 'nullable|string',
            'initials' => 'nullable|string',
            'avatar' => 'nullable|file|image|max:10240',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension());
            $uploadDir = public_path('uploads/avatars');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = 'user_'.uniqid().'.'.($extension ?: 'jpg');
            $file->move($uploadDir, $safeName);
            $validated['avatar_url'] = asset('uploads/avatars/'.$safeName);
            unset($validated['avatar']);
        }

        $colors = ['#C52523', '#2B6E8A', '#8A5A2B', '#5A7A2B', '#6E2B8A', '#2B8A5A', '#B4780F', '#7A2B5A'];
        $userCount = User::count();

        // compute initials if missing
        $words = preg_split('/\s+/', trim($validated['name']));
        $initials = strtoupper(substr($words[0] ?? '', 0, 1).substr($words[1] ?? '', 0, 1));

        $roleSlug = $validated['role'];
        $roleId = $validated['role_id'] ?? null;
        if (! $roleId) {
            $matchedRole = Role::where('slug', $roleSlug)->first();
            $roleId = $matchedRole?->id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password'] ?? 'jeota2024'),
            'title' => $validated['title'] ?? 'Team',
            'department' => $validated['department'] ?? 'Production',
            'role' => $roleSlug,
            'role_id' => $roleId,
            'custom_permissions' => $validated['custom_permissions'] ?? null,
            'type' => $validated['type'],
            'pay' => $validated['pay'] ?? '—',
            'phone' => $validated['phone'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'color' => $validated['color'] ?? $colors[$userCount % count($colors)],
            'initials' => $validated['initials'] ?? $initials,
        ]);

        AuditLog::record('CREATE', "Added team member '{$user->name}' ({$user->role})", 'User', $user->id, ['role' => $user->role, 'department' => $user->department], $request);

        $userData = $user->toArray();
        $userData['permissions'] = $user->allPermissions();

        return response()->json([
            'status' => 'success',
            'message' => 'Person added to team.',
            'data' => $userData,
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role' => 'sometimes|required|string|max:100',
            'role_id' => 'nullable|exists:roles,id',
            'custom_permissions' => 'nullable|array',
            'type' => 'sometimes|required|string|max:100',
            'pay' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|string',
            'password' => 'nullable|string|min:4',
            'color' => 'nullable|string',
            'initials' => 'nullable|string',
            'avatar' => 'nullable|file|image|max:10240',
        ]);

        // Safety check: Prevent demoting the last owner
        if (isset($validated['role']) && $validated['role'] !== 'owner' && $user->role === 'owner') {
            if (User::where('role', 'owner')->count() <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot demote the last owner account. Promote another owner first.',
                ], 422);
            }
        }

        if (isset($validated['role']) && empty($validated['role_id'])) {
            $matchedRole = Role::where('slug', $validated['role'])->first();
            if ($matchedRole) {
                $validated['role_id'] = $matchedRole->id;
            }
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension());
            $uploadDir = public_path('uploads/avatars');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = 'user_'.$user->id.'_'.uniqid().'.'.($extension ?: 'jpg');
            $file->move($uploadDir, $safeName);
            $validated['avatar_url'] = asset('uploads/avatars/'.$safeName);
            unset($validated['avatar']);
        }

        if (! empty($validated['name']) && empty($validated['initials'])) {
            $words = preg_split('/\s+/', trim($validated['name']));
            $validated['initials'] = strtoupper(substr($words[0] ?? '', 0, 1).substr($words[1] ?? '', 0, 1));
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (isset($validated['email'])) {
            $validated['email'] = strtolower($validated['email']);
        }

        $user->update($validated);

        AuditLog::record('UPDATE', "Updated team member '{$user->name}'", 'User', $user->id, $request->except(['password', 'avatar']), $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Team member updated successfully.',
            'data' => $user,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // Safety checks
        if ($user->role === 'owner' && User::where('role', 'owner')->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot remove the last owner account.',
            ], 422);
        }

        $name = $user->name;
        $id = $user->id;
        $user->delete();

        AuditLog::record('DELETE', "Removed team member '{$name}'", 'User', $id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'User removed.',
        ]);
    }
}
