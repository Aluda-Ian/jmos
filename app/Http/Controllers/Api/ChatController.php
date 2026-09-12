<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatThread;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Resolve current authenticated user (Sanctum or Session or fallback).
     */
    protected function resolveUser(Request $request): User
    {
        if ($user = $request->user()) {
            return $user;
        }

        if (Auth::check()) {
            return Auth::user();
        }

        // Check for custom header or fallback to first user
        $userId = $request->header('X-User-Id') ?? $request->input('user_id');
        if ($userId && $found = User::find($userId)) {
            return $found;
        }

        return User::first() ?? User::create([
            'name' => 'Barny Kiome',
            'email' => 'barny@jeotamedia.co.ke',
            'role' => 'owner',
            'initials' => 'BK',
            'color' => '#C52523',
        ]);
    }

    /**
     * List all chat threads for the current user and team directory.
     */
    public function index(Request $request)
    {
        $user = $this->resolveUser($request);

        // Ensure default group channels exist and user is subscribed
        $this->ensureDefaultChannels($user);

        // Fetch all threads where user is a participant
        $threads = ChatThread::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['participants.user', 'lastMessage.sender'])
            ->get()
            ->map(function ($thread) use ($user) {
                $participant = $thread->participants->firstWhere('user_id', $user->id);
                $lastReadAt = $participant ? $participant->last_read_at : null;

                // Unread messages count
                $unreadQuery = ChatMessage::where('thread_id', $thread->id)
                    ->where('sender_id', '!=', $user->id);

                if ($lastReadAt) {
                    $unreadQuery->where('created_at', '>', $lastReadAt);
                }

                $unreadCount = $unreadQuery->count();

                // Thread display metadata
                $title = $thread->title;
                $subtitle = $thread->description;
                $avatar = 'JM';
                $color = '#C52523';
                $otherUser = null;

                if ($thread->type === 'direct') {
                    $otherParticipant = $thread->participants->firstWhere('user_id', '!=', $user->id);
                    if ($otherParticipant && $otherParticipant->user) {
                        $otherUser = $otherParticipant->user;
                        $title = $otherUser->name;
                        $subtitle = $otherUser->title ?? $otherUser->role;
                        $avatar = $otherUser->initials ?? substr($otherUser->name, 0, 2);
                        $color = $otherUser->color ?? '#C52523';
                    }
                }

                return [
                    'id' => $thread->id,
                    'type' => $thread->type,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'avatar' => $avatar,
                    'color' => $color,
                    'unread_count' => $unreadCount,
                    'last_message' => $thread->lastMessage ? [
                        'message' => $thread->lastMessage->message,
                        'sender_name' => $thread->lastMessage->sender ? $thread->lastMessage->sender->name : 'Team',
                        'created_at' => $thread->lastMessage->created_at->toIso8601String(),
                        'time_formatted' => $thread->lastMessage->created_at->diffForHumans(null, true, true),
                    ] : null,
                    'other_user' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
                        'title' => $otherUser->title,
                        'initials' => $otherUser->initials,
                        'color' => $otherUser->color,
                    ] : null,
                    'updated_at' => $thread->updated_at,
                ];
            })
            ->sortByDesc(function ($t) {
                return $t['last_message']['created_at'] ?? $t['updated_at'];
            })
            ->values();

        // Team directory list for direct messaging
        $allUsers = User::all()->map(function ($u) use ($user) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'title' => $u->title,
                'initials' => $u->initials ?? substr($u->name, 0, 2),
                'color' => $u->color ?? '#C52523',
                'is_current' => $u->id === $user->id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'current_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'title' => $user->title,
                'initials' => $user->initials,
                'color' => $user->color,
            ],
            'threads' => $threads,
            'team_members' => $allUsers,
        ]);
    }

    /**
     * Get or create a 1-on-1 direct message thread with a target user.
     */
    public function getDirectThread(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $user = $this->resolveUser($request);
        $recipientId = (int) $validated['recipient_id'];

        if ($user->id === $recipientId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot create direct conversation with yourself',
            ], 422);
        }

        // Look for existing direct thread with both participants
        $thread = ChatThread::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($recipientId) {
                $q->where('user_id', $recipientId);
            })
            ->first();

        if (! $thread) {
            $thread = ChatThread::create([
                'type' => 'direct',
                'created_by' => $user->id,
            ]);

            ChatParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'last_read_at' => now(),
                'notified_initial_email' => true,
            ]);

            ChatParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $recipientId,
                'last_read_at' => null,
                'notified_initial_email' => false,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * Create a new group channel.
     */
    public function storeGroupThread(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $user = $this->resolveUser($request);

        $channelName = trim($validated['title']);
        if (! str_starts_with($channelName, '#')) {
            $channelName = '#'.ltrim($channelName, '#');
        }

        $thread = ChatThread::create([
            'type' => 'group',
            'title' => $channelName,
            'description' => $validated['description'] ?? null,
            'created_by' => $user->id,
        ]);

        // Default to all users if no specific participants selected
        $participantIds = ! empty($validated['participant_ids'])
            ? array_unique(array_merge([$user->id], $validated['participant_ids']))
            : User::pluck('id')->toArray();

        foreach ($participantIds as $pId) {
            ChatParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $pId,
                'last_read_at' => $pId === $user->id ? now() : null,
                'notified_initial_email' => $pId === $user->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'thread_id' => $thread->id,
            'message' => "Channel {$channelName} created",
        ], 201);
    }

    /**
    /**
     * Get message history for a specific thread.
     */
    public function getMessages(ChatThread $thread, Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        // Direct messages are strictly restricted to involved participants
        if ($thread->type === 'direct') {
            $isParticipant = ChatParticipant::where('thread_id', $thread->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $isParticipant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Direct messages are private to involved participants.',
                ], 403);
            }
        }

        // Auto join user if thread is a group channel
        $participant = ChatParticipant::firstOrCreate(
            ['thread_id' => $thread->id, 'user_id' => $user->id],
            ['last_read_at' => now(), 'notified_initial_email' => true]
        );

        // Mark as read
        $participant->update(['last_read_at' => now()]);

        $messages = $thread->messages()
            ->with('sender:id,name,role,title,initials,color,email')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'thread_id' => $msg->thread_id,
                    'sender_id' => $msg->sender_id,
                    'is_me' => $msg->sender_id === $user->id,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'Unknown',
                    'sender_role' => $msg->sender ? ($msg->sender->title ?? $msg->sender->role) : '',
                    'sender_initials' => $msg->sender ? ($msg->sender->initials ?? substr($msg->sender->name, 0, 2)) : 'JM',
                    'sender_color' => $msg->sender ? ($msg->sender->color ?? '#C52523') : '#C52523',
                    'message' => $msg->message,
                    'attachment_name' => $msg->attachment_name,
                    'attachment_url' => $msg->attachment_url,
                    'created_at' => $msg->created_at->toIso8601String(),
                    'time_formatted' => $msg->created_at->format('H:i · M j'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'thread' => [
                'id' => $thread->id,
                'type' => $thread->type,
                'title' => $thread->title,
                'description' => $thread->description,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a new message and trigger email & in-app notifications.
     */
    public function sendMessage(ChatThread $thread, Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        // Direct messages are strictly restricted to involved participants
        if ($thread->type === 'direct') {
            $isParticipant = ChatParticipant::where('thread_id', $thread->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $isParticipant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Direct messages are private to involved participants.',
                ], 403);
            }
        }

        $validated = $request->validate([
            'message' => 'nullable|string',
            'attachment_name' => 'nullable|string|max:255',
            'attachment_url' => 'nullable|string|max:500',
        ]);

        $messageText = trim($validated['message'] ?? '');
        if ($messageText === '' && empty($validated['attachment_url'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message text or attachment is required.',
            ], 422);
        }

        if ($messageText === '') {
            $ext = pathinfo($validated['attachment_url'] ?? '', PATHINFO_EXTENSION);
            $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            $messageText = $isImg ? '📷 Photo' : '📎 '.($validated['attachment_name'] ?? 'Attachment');
        }

        // Ensure user is participant
        ChatParticipant::firstOrCreate(
            ['thread_id' => $thread->id, 'user_id' => $user->id],
            ['last_read_at' => now(), 'notified_initial_email' => true]
        );

        $chatMsg = ChatMessage::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'message' => $messageText,
            'attachment_name' => $validated['attachment_name'] ?? null,
            'attachment_url' => $validated['attachment_url'] ?? null,
        ]);

        // Touch thread timestamp
        $thread->touch();

        // Update sender's last read timestamp
        ChatParticipant::where('thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        // Seamless In-App & Email Notifications for Recipients
        $recipients = ChatParticipant::where('thread_id', $thread->id)
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->get();

        foreach ($recipients as $recipientPart) {
            if ($recipientPart->user) {
                $recipientUser = $recipientPart->user;
                $threadTitle = $thread->type === 'direct' ? "1-on-1 Direct Chat with {$user->name}" : ($thread->title ?? 'Team Channel');
                $notifTitle = $thread->type === 'direct' ? "Message from {$user->name}" : "{$user->name} in {$thread->title}";

                // 1. In-app notification record
                AppNotification::create([
                    'user_id' => $recipientUser->id,
                    'type' => 'chat',
                    'title' => $notifTitle,
                    'message' => Str::limit($chatMsg->message, 85),
                    'link' => 'chat',
                ]);

                // 2. Initial email notification for first-time contacts
                if (! $recipientPart->notified_initial_email && $recipientUser->email) {
                    NotificationService::sendNewChatMessage([
                        'recipientName' => $recipientUser->name,
                        'senderName' => $user->name,
                        'senderRole' => $user->title ?? $user->role,
                        'threadTitle' => $threadTitle,
                        'isDirect' => $thread->type === 'direct',
                        'messageText' => $chatMsg->message,
                        'sentAt' => now()->format('M j, Y H:i'),
                    ], $recipientUser->email);

                    $recipientPart->update(['notified_initial_email' => true]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => [
                'id' => $chatMsg->id,
                'thread_id' => $chatMsg->thread_id,
                'sender_id' => $chatMsg->sender_id,
                'is_me' => true,
                'sender_name' => $user->name,
                'sender_role' => $user->title ?? $user->role,
                'sender_initials' => $user->initials ?? substr($user->name, 0, 2),
                'sender_color' => $user->color ?? '#C52523',
                'message' => $chatMsg->message,
                'attachment_name' => $chatMsg->attachment_name,
                'attachment_url' => $chatMsg->attachment_url,
                'created_at' => $chatMsg->created_at->toIso8601String(),
                'time_formatted' => $chatMsg->created_at->format('H:i · M j'),
            ],
        ], 201);
    }

    /**
     * Upload an attachment file (documents, images, or camera snapshots).
     */
    public function uploadAttachment(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:25600', // 25MB max
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        $isImage = str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

        $uploadDir = public_path('uploads/chat');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = uniqid('chat_', true).'.'.($extension ?: 'bin');
        $file->move($uploadDir, $safeName);

        $url = asset('uploads/chat/'.$safeName);

        return response()->json([
            'status' => 'success',
            'attachment_name' => $originalName,
            'attachment_url' => $url,
            'is_image' => $isImage,
            'size' => $file->getSize() ?: 0,
        ], 201);
    }

    /**
     * Mark thread as read.
     */
    public function markRead(ChatThread $thread, Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if ($thread->type === 'direct') {
            $isParticipant = ChatParticipant::where('thread_id', $thread->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $isParticipant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized.',
                ], 403);
            }
        }

        ChatParticipant::where('thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Return total unread messages count for badges.
     */
    public function unreadCount(Request $request)
    {
        $user = $this->resolveUser($request);

        $threads = ChatThread::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('participants')->get();

        $totalUnread = 0;
        foreach ($threads as $thread) {
            $p = $thread->participants->firstWhere('user_id', $user->id);
            $query = ChatMessage::where('thread_id', $thread->id)->where('sender_id', '!=', $user->id);
            if ($p && $p->last_read_at) {
                $query->where('created_at', '>', $p->last_read_at);
            }
            $totalUnread += $query->count();
        }

        return response()->json([
            'status' => 'success',
            'unread_count' => $totalUnread,
        ]);
    }

    /**
     * Helper: Ensure default group channels exist and all users are subscribed.
     */
    protected function ensureDefaultChannels(User $currentUser): void
    {
        $defaultChannels = [
            ['title' => '#general', 'description' => 'General team discussion, announcements & all-hands updates.'],
            ['title' => '#production', 'description' => 'Shoots, camera gear, on-location crew coordination & editing.'],
            ['title' => '#client-projects', 'description' => 'Live client deliverables, reviews, feedback & status briefs.'],
        ];

        $allUserIds = User::pluck('id')->toArray();

        foreach ($defaultChannels as $ch) {
            $thread = ChatThread::firstOrCreate(
                ['type' => 'group', 'title' => $ch['title']],
                ['description' => $ch['description'], 'created_by' => $currentUser->id]
            );

            foreach ($allUserIds as $uId) {
                ChatParticipant::firstOrCreate(
                    ['thread_id' => $thread->id, 'user_id' => $uId],
                    ['last_read_at' => now(), 'notified_initial_email' => true]
                );
            }
        }
    }
}
