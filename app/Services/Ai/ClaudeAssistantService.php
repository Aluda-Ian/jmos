<?php

namespace App\Services\Ai;

use App\Models\AiChatConversation;
use App\Models\AiOpportunity;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClaudeAssistantService
{
    public function __construct(
        protected AiAuthorizationService $authService
    ) {}

    /**
     * Send a message to the AI Assistant and receive an answer with context & navigation guidance.
     *
     * @return array{status: string, message: string, conversation_id: string, escalated: bool, navigation_links: array, opportunities_found: array}
     */
    public function sendMessage(User $user, string $message, ?string $sessionId = null, string $mode = 'general_help'): array
    {
        // 1. Feature Flag Check
        if (! config('ai.enabled', true) || ! config('ai.chat_enabled', true)) {
            return [
                'status' => 'disabled',
                'message' => 'AI Assistant is currently turned off in this workspace.',
                'conversation_id' => $sessionId ?? (string) Str::uuid(),
                'escalated' => false,
                'navigation_links' => [],
                'opportunities_found' => [],
            ];
        }

        // 2. Mode-based Permission Check
        if (in_array($mode, ['lead_gen', 'fundraising_partner'], true) && ! $this->authService->canUseLeadGenChat($user)) {
            $mode = 'general_help'; // Fall back gracefully to general help if unauthorized for lead gen
        }

        $sessionId = $sessionId ?: (string) Str::uuid();

        // 3. Load or Create User Conversation History
        $conversation = AiChatConversation::firstOrCreate(
            ['user_id' => $user->id, 'session_id' => $sessionId],
            ['mode' => $mode, 'messages' => [], 'escalated' => false]
        );

        $messages = $conversation->messages ?? [];

        // 4. Testable Escalation / "Stuck" Detection
        $escalationCheck = $this->detectStuckOrEscalation($message, $messages, $mode);

        // 5. Append User Turn
        $messages[] = [
            'role' => 'user',
            'content' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // 6. Generate Response (Claude API or Deterministic Engine)
        $aiResult = $this->callClaudeApi($messages, $mode, $user, $escalationCheck);

        // 7. Opportunity Sourcing Check (if in lead/fundraising mode)
        $opportunities = [];
        if (in_array($mode, ['lead_gen', 'fundraising_partner'], true)) {
            $opportunities = $this->extractOpportunitiesFromText($message, $aiResult['text'], $user);
        }

        // 8. Handle Escalation Alert Notification
        if ($escalationCheck['is_escalated'] && ! $conversation->escalated) {
            $conversation->escalated = true;
            $conversation->escalation_reason = $escalationCheck['reason'];

            // Dispatch notification to admins
            AppNotification::create([
                'user_id' => $user->id,
                'title' => 'AI Assistant Support Handoff',
                'message' => "User {$user->name} requested assistance or became stuck: {$escalationCheck['reason']}",
                'type' => 'alert',
                'link' => '/chat',
            ]);
        }

        // 9. Append Assistant Turn & Save Conversation
        $messages[] = [
            'role' => 'assistant',
            'content' => $aiResult['text'],
            'timestamp' => now()->toIso8601String(),
            'escalated' => $escalationCheck['is_escalated'],
        ];

        $conversation->messages = $messages;
        $conversation->mode = $mode;
        $conversation->save();

        AuditLog::record(
            action: 'AI_CHAT_MESSAGE',
            description: "AI chat message processed in mode: {$mode} for {$user->name}",
            entityType: 'AiChatConversation',
            entityId: $conversation->id,
            details: [
                'session_id' => $sessionId,
                'mode' => $mode,
                'escalated' => $escalationCheck['is_escalated'],
            ],
            user: $user
        );

        return [
            'status' => 'success',
            'message' => $aiResult['text'],
            'conversation_id' => $sessionId,
            'escalated' => $escalationCheck['is_escalated'],
            'escalation_reason' => $escalationCheck['reason'],
            'navigation_links' => $escalationCheck['links'],
            'opportunities_found' => $opportunities,
        ];
    }

    /**
     * Clear user conversation session.
     */
    public function clearConversation(User $user, string $sessionId): bool
    {
        return (bool) AiChatConversation::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->delete();
    }

    /**
     * Testable, deterministic escalation triggers:
     * 1. Explicit request for a human/support
     * 2. Frustration / stuck keywords
     * 3. Asking for out-of-scope legal / binding commitments
     */
    public function detectStuckOrEscalation(string $currentMessage, array $history, string $mode): array
    {
        $normalized = strtolower(trim($currentMessage));

        // 1. Explicit request for human / support
        $humanKeywords = ['talk to a human', 'human agent', 'speak to someone', 'customer support', 'contact support', 'talk to admin', 'real person', 'help desk'];
        foreach ($humanKeywords as $kw) {
            if (str_contains($normalized, $kw)) {
                return [
                    'is_escalated' => true,
                    'reason' => 'Explicit human support request',
                    'links' => [
                        ['label' => 'Open Team Chat', 'view' => 'chat', 'url' => '/chat'],
                        ['label' => 'System Settings & Support', 'view' => 'settings', 'url' => '/settings'],
                    ],
                ];
            }
        }

        // 2. Frustration or repeated failure signals
        $frustrationKeywords = ['i am stuck', "i'm stuck", 'not working', 'system is broken', 'cannot find where', 'confused about this', 'repeatedly failing'];
        foreach ($frustrationKeywords as $kw) {
            if (str_contains($normalized, $kw)) {
                return [
                    'is_escalated' => true,
                    'reason' => 'User reported being stuck or experiencing friction',
                    'links' => [
                        ['label' => 'Project Workspace', 'view' => 'projects', 'url' => '/projects'],
                        ['label' => 'Documents & Guides', 'view' => 'documents', 'url' => '/documents'],
                        ['label' => 'Lead Pipeline', 'view' => 'pipeline', 'url' => '/pipeline'],
                    ],
                ];
            }
        }

        // 3. Check for out-of-scope contract / financial guarantee queries
        if (str_contains($normalized, 'promise payment') || str_contains($normalized, 'guarantee grant') || str_contains($normalized, 'sign contract')) {
            return [
                'is_escalated' => true,
                'reason' => 'Out-of-scope binding commitment query',
                'links' => [
                    ['label' => 'Quotations & Invoicing', 'view' => 'quotes', 'url' => '/quotes'],
                    ['label' => 'Documents & Legal Contracts', 'view' => 'documents', 'url' => '/documents'],
                ],
            ];
        }

        // 4. Multi-turn friction check (last 3 user messages having questions without resolution)
        $recentUserCount = 0;
        foreach (array_reverse($history) as $turn) {
            if (($turn['role'] ?? '') === 'user') {
                $recentUserCount++;
            }
        }

        if ($recentUserCount >= 4 && (str_contains($normalized, 'how') || str_contains($normalized, 'why') || str_contains($normalized, 'where'))) {
            return [
                'is_escalated' => true,
                'reason' => 'Multi-turn navigation assistance needed',
                'links' => [
                    ['label' => 'Dashboard Overview', 'view' => 'dashboard', 'url' => '/dashboard'],
                    ['label' => 'Documents Repository', 'view' => 'documents', 'url' => '/documents'],
                ],
            ];
        }

        return [
            'is_escalated' => false,
            'reason' => null,
            'links' => [],
        ];
    }

    /**
     * Call Anthropic Claude API or fallback to JMOS contextual assistant.
     */
    protected function callClaudeApi(array $messages, string $mode, User $user, array $escalation): array
    {
        $apiKey = config('ai.anthropic.api_key');
        $model = config('ai.anthropic.model', 'claude-3-5-sonnet-20241022');
        $timeout = config('ai.anthropic.timeout', 20);

        if (empty($apiKey)) {
            return [
                'text' => $this->generateLocalAssistantReply(end($messages)['content'] ?? '', $mode, $user, $escalation),
                'model' => 'deterministic-assistant-engine',
            ];
        }

        $systemPrompt = 'You are the JMOS AI Operating Partner for Jeota Media. '
            ."Mode: {$mode}. User: {$user->name} ({$user->role}). "
            .'Guardrails: '
            .'1. Never invent financial figures or promise funds. '
            .'2. Direct users to the appropriate JMOS modules (Projects, Leads, Quotes, Invoices, Documents, Calendar). '
            .'3. If user is stuck, provide structured step-by-step guidance.';

        // Convert messages format for Claude API
        $claudeMessages = [];
        foreach ($messages as $msg) {
            $claudeMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->retry(2, 500, throw: false)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 1000,
                    'system' => $systemPrompt,
                    'messages' => $claudeMessages,
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $reply = $body['content'][0]['text'] ?? null;
                if (! empty($reply)) {
                    return [
                        'text' => trim($reply),
                        'model' => $model,
                    ];
                }
            }

            Log::warning('Claude API returned non-success response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Claude API request encountered exception', [
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'text' => $this->generateLocalAssistantReply(end($messages)['content'] ?? '', $mode, $user, $escalation),
            'model' => 'fallback-assistant-engine',
        ];
    }

    /**
     * Local contextual assistant when Claude API key is omitted or during offline simulation.
     */
    protected function generateLocalAssistantReply(string $input, string $mode, User $user, array $escalation): string
    {
        $normalized = strtolower($input);

        if ($escalation['is_escalated']) {
            return "I've detected you may need direct assistance or navigation support. "
                ."I've alerted the workspace team lead. In the meantime, you can jump directly to "
                .'the relevant section using the quick links below, or check **Team Chat** to reach an administrator directly.';
        }

        if ($mode === 'lead_gen') {
            if (str_contains($normalized, 'lead') || str_contains($normalized, 'prospect') || str_contains($normalized, 'qualif')) {
                return 'For lead qualification in JMOS, use the **CRM Lead Journey** view (`/leads`). '
                    .'You can log calls, track interest level (Hot/Warm/Cold), and convert qualified prospects directly into active projects and client accounts.';
            }

            return 'To accelerate client acquisition, check out the **Pipeline & Deals** view. '
                .'Would you like me to guide you on how to log a new prospective account or prepare a tailored commercial proposal?';
        }

        if ($mode === 'fundraising_partner') {
            return 'In JMOS, all institutional grants, open calls, and donor funding tracks are centralized in **Grants & Open Calls** (`/fundraising`). '
                .'You can import CSV databases, track application deadlines, and attach draft proposals in the **Documents** repository.';
        }

        // General Help
        if (str_contains($normalized, 'quote') || str_contains($normalized, 'budget')) {
            return 'You can build accurate commercial estimates via the **Commercial Production Budget Calculator** and the **Quotations Engine** (`/quotes`). '
                .'Approved quotes can be automatically upgraded to client invoices in one click.';
        }

        if (str_contains($normalized, 'project') || str_contains($normalized, 'task')) {
            return 'You can manage deliverables, production shoot milestones, and assignee timelines directly under the **Projects** (`/projects`) and **Tasks** views.';
        }

        if (str_contains($normalized, 'invoice') || str_contains($normalized, 'expense') || str_contains($normalized, 'finance')) {
            return 'Financial ledgers, receipts, and invoice status trackers are available in the **Finance & Invoicing** section (`/finance`). '
                .'Users with financial permissions can also generate automated AI Financial Summaries.';
        }

        return "Hello {$user->name}! I'm **J- ai**, your operating assistant for Jeota Media. I can help you navigate projects, lead qualification, quotation workflows, and funding opportunities. What would you like to explore?";
    }

    /**
     * Opportunistically extract lead / funding entities from assistant conversations.
     */
    protected function extractOpportunitiesFromText(string $userMsg, string $aiReply, User $user): array
    {
        $combined = $userMsg.' '.$aiReply;
        $opportunities = [];

        // Check for grant / funder keywords
        if (preg_match('/grant|funding|funder|donor|open call/i', $combined) && preg_match('/(?:titled|named|call for)\s+["\']?([^"\'\n\.\,]+)["\']?/i', $combined, $matches)) {
            $title = trim($matches[1]);
            $opportunity = AiOpportunity::create([
                'user_id' => $user->id,
                'type' => 'grant',
                'title' => $title,
                'organization' => 'Identified via AI Assistant',
                'summary' => Str::limit($userMsg, 250),
                'confidence_score' => 85,
                'status' => 'discovered',
            ]);
            $opportunities[] = $opportunity->toArray();
        }

        return $opportunities;
    }
}
