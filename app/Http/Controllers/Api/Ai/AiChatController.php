<?php

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiChatConversation;
use App\Models\AiOpportunity;
use App\Services\Ai\ClaudeAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __construct(
        protected ClaudeAssistantService $assistantService
    ) {}

    /**
     * Send a user query to the AI Assistant.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|string|max:100',
            'mode' => 'nullable|string|in:general_help,lead_gen,fundraising_partner',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $result = $this->assistantService->sendMessage(
            user: $user,
            message: $validated['message'],
            sessionId: $validated['session_id'] ?? null,
            mode: $validated['mode'] ?? 'general_help'
        );

        return response()->json($result);
    }

    /**
     * Get active session conversation history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $sessionId = $request->query('session_id');

        $conversation = AiChatConversation::where('user_id', $user->id)
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->latest('updated_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'session_id' => $conversation?->session_id ?? $sessionId,
            'mode' => $conversation?->mode ?? 'general_help',
            'escalated' => $conversation?->escalated ?? false,
            'messages' => $conversation?->messages ?? [],
        ]);
    }

    /**
     * Clear user chat history for a session.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $sessionId = $request->input('session_id');
        if ($sessionId) {
            $this->assistantService->clearConversation($user, $sessionId);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Chat conversation cleared.',
        ]);
    }

    /**
     * Sourced opportunities list.
     */
    public function getOpportunities(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $query = AiOpportunity::latest('created_at')->take(50);
        if ($user->role !== 'owner') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhereNull('user_id');
            });
        }

        return response()->json([
            'status' => 'success',
            'opportunities' => $query->get(),
        ]);
    }
}
