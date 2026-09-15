<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Check if the requesting user has Admin, Owner, or IT Manager privileges.
     */
    protected function checkAuthorized(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return true; // Local development or token-fallback
        }

        return in_array($user->role, ['owner', 'admin', 'manager'], true);
    }

    /**
     * List recent audit logs with filtering and search support.
     */
    public function index(Request $request): JsonResponse
    {
        if (! $this->checkAuthorized($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Only Administrators and IT Managers can access the audit trail.',
            ], 403);
        }

        $query = AuditLog::query()->latest();

        // Filter by action (e.g. CREATE, UPDATE, DELETE, AUTH, SYSTEM)
        if ($request->filled('action') && $request->input('action') !== 'all') {
            $query->where('action', strtoupper($request->input('action')));
        }

        // Filter by entity type
        if ($request->filled('entity_type') && $request->input('entity_type') !== 'all') {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Free-text search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('entity_id', 'like', "%{$search}%");
            });
        }

        $limit = min((int) $request->input('limit', 50), 200);
        $logs = $query->take($limit)->get();

        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', now()->today())->count(),
            'creates' => AuditLog::where('action', 'CREATE')->count(),
            'updates' => AuditLog::where('action', 'UPDATE')->count(),
            'deletes' => AuditLog::where('action', 'DELETE')->count(),
            'auth' => AuditLog::where('action', 'AUTH')->count(),
            'system' => AuditLog::where('action', 'SYSTEM')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * Record a client-side audit event (e.g. app installed, push enabled).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'entity_type' => 'nullable|string|max:100',
            'entity_id' => 'nullable|string|max:100',
            'details' => 'nullable|array',
        ]);

        $log = AuditLog::record(
            $validated['action'],
            $validated['description'],
            $validated['entity_type'] ?? null,
            $validated['entity_id'] ?? null,
            $validated['details'] ?? [],
            $request
        );

        return response()->json([
            'status' => 'success',
            'data' => $log,
        ], 201);
    }
}
