<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function resolveUser(Request $request): ?User
    {
        if ($user = $request->user()) {
            return $user;
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

    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $userId = $user?->id;
        $filter = $request->query('filter', 'all');

        $baseQuery = AppNotification::query()->forUser($userId);

        $unreadCount = (clone $baseQuery)->unread()->count();
        $totalCount = (clone $baseQuery)->count();

        $query = clone $baseQuery;
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }

        $notifications = $query->orderBy('created_at', 'desc')->limit(50)->get();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'total_count' => $totalCount,
            'filter' => $filter,
            'data' => $notifications,
        ]);
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        $user = $this->resolveUser($request);
        $userId = $user?->id;

        if ($notification->user_id !== null && $userId !== null && $notification->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }

        $unreadCount = AppNotification::query()->forUser($userId)->unread()->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
            'unread_count' => $unreadCount,
            'data' => $notification,
        ]);
    }

    public function markUnread(Request $request, AppNotification $notification): JsonResponse
    {
        $userId = $request->user()?->id ?? auth('sanctum')->id();

        if ($notification->user_id !== null && $userId !== null && $notification->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (! is_null($notification->read_at)) {
            $notification->read_at = null;
            $notification->save();
        }

        $unreadCount = AppNotification::query()->forUser($userId)->unread()->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as unread',
            'unread_count' => $unreadCount,
            'data' => $notification,
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? auth('sanctum')->id();

        AppNotification::query()
            ->forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'unread_count' => 0,
        ]);
    }

    public function destroy(Request $request, AppNotification $notification): JsonResponse
    {
        $userId = $request->user()?->id ?? auth('sanctum')->id();

        if ($notification->user_id !== null && $userId !== null && $notification->user_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        $unreadCount = AppNotification::query()->forUser($userId)->unread()->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification removed',
            'unread_count' => $unreadCount,
        ]);
    }
}
