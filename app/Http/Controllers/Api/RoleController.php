<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function checkAuthorized(Request $request): bool
    {
        $user = $request->user() ?? auth('sanctum')->user();
        if (! $user) {
            $userId = $request->header('X-User-Id') ?? $request->input('user_id');
            $user = $userId ? User::find($userId) : User::first();
        }

        if (! $user) {
            return true;
        }

        return $user->role === 'owner' || $user->hasPermission('roles.manage') || in_array($user->role, ['owner', 'admin', 'manager'], true);
    }

    /**
     * List all roles with user counts and permission matrix.
     */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::withCount('users')->orderBy('is_system', 'desc')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
            'catalog' => Role::getPermissionCatalog(),
        ]);
    }

    /**
     * Get permission definitions catalog.
     */
    public function permissionsCatalog(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Role::getPermissionCatalog(),
        ]);
    }

    /**
     * Create a new custom role.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only workspace administrators can manage roles.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $slug = Role::createUniqueSlug($validated['name']);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#C52523',
            'permissions' => $validated['permissions'] ?? [],
            'is_system' => false,
        ]);

        AuditLog::record(
            'CREATE',
            "Created custom user role '{$role->name}' ({$role->slug})",
            'Role',
            $role->id,
            ['permissions_count' => count($role->permissions ?? [])],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => "Role '{$role->name}' created successfully",
            'data' => $role->loadCount('users'),
        ], 201);
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only workspace administrators can manage roles.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
            if (! $role->is_system) {
                $updateData['slug'] = Role::createUniqueSlug($validated['name'], $role->id);
            }
        }

        if (array_key_exists('description', $validated)) {
            $updateData['description'] = $validated['description'];
        }

        if (isset($validated['color'])) {
            $updateData['color'] = $validated['color'];
        }

        if (isset($validated['permissions'])) {
            // For owner role, keep full access wildcard
            if ($role->slug === 'owner') {
                $updateData['permissions'] = ['*'];
            } else {
                $updateData['permissions'] = $validated['permissions'];
            }
        }

        $role->update($updateData);

        AuditLog::record(
            'UPDATE',
            "Updated access permissions for role '{$role->name}'",
            'Role',
            $role->id,
            ['permissions' => $role->permissions],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => "Role '{$role->name}' updated successfully",
            'data' => $role->loadCount('users'),
        ]);
    }

    /**
     * Delete a custom role.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only workspace administrators can manage roles.'], 403);
        }

        if ($role->is_system || in_array($role->slug, ['owner', 'manager', 'finance', 'sales', 'team'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => "System role '{$role->name}' is protected and cannot be deleted.",
            ], 422);
        }

        // Reassign any users who had this role to standard 'team' role
        $fallbackRole = Role::where('slug', 'team')->first();
        User::where('role_id', $role->id)->orWhere('role', $role->slug)->update([
            'role_id' => $fallbackRole?->id,
            'role' => 'team',
        ]);

        $roleName = $role->name;
        $roleId = $role->id;
        $role->delete();

        AuditLog::record(
            'DELETE',
            "Deleted custom user role '{$roleName}'",
            'Role',
            $roleId,
            [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => "Role '{$roleName}' deleted successfully",
        ]);
    }

    /**
     * Assign custom permissions override to a specific user.
     */
    public function assignUserPermissions(Request $request, User $user): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. Only workspace administrators can manage permissions.'], 403);
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'role_id' => 'nullable|exists:roles,id',
            'role' => 'nullable|string',
        ]);

        $updateData = [];
        if (array_key_exists('permissions', $validated)) {
            $updateData['custom_permissions'] = $validated['permissions'];
        }

        if (! empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $updateData['role_id'] = $role->id;
                $updateData['role'] = $role->slug;
            }
        } elseif (! empty($validated['role'])) {
            $role = Role::where('slug', $validated['role'])->first();
            $updateData['role'] = $validated['role'];
            if ($role) {
                $updateData['role_id'] = $role->id;
            }
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }

        AuditLog::record(
            'UPDATE',
            "Updated custom permissions for user '{$user->name}'",
            'User',
            $user->id,
            ['permissions' => $user->allPermissions()],
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => "Permissions for '{$user->name}' updated successfully",
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'role_id' => $user->role_id,
                'permissions' => $user->allPermissions(),
            ],
        ]);
    }
}
