<?php

namespace App\Services\Ai;

use App\Models\AiReport;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiReportService
{
    public function __construct(
        protected AiAuthorizationService $authService
    ) {}

    /**
     * Generate an AI-powered role-gated report.
     *
     * @return array{status: string, report_type: string, summary: string, cached: bool, model: string, scope: string, data: array}
     */
    public function generateReport(User $user, string $reportType = 'executive_digest', bool $forceRefresh = false): array
    {
        // 1. Feature Flag Verification
        if (! config('ai.enabled', true) || ! config('ai.reports_enabled', true)) {
            return [
                'status' => 'disabled',
                'report_type' => $reportType,
                'summary' => 'AI Reporting is currently disabled by system administration.',
                'cached' => false,
                'model' => 'disabled',
                'scope' => 'none',
                'data' => [],
            ];
        }

        // 2. Authorization Check
        if (! $this->authService->canViewReports($user)) {
            abort(403, 'You do not have permission to view AI reports.');
        }

        if ($reportType === 'financial_summary' && ! $this->authService->canViewFinancialReports($user)) {
            abort(403, 'You do not have financial permission to generate financial reports.');
        }

        $cacheKey = $this->authService->getReportCacheKey($user, $reportType);
        $cacheTtl = config('ai.cache_ttl_minutes', 30);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // 3. Cache Layer
        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult && is_array($cachedResult)) {
            $cachedResult['cached'] = true;

            return $cachedResult;
        }

        // 4. Permission Scoping (Only authorized records reach this point)
        $scopedData = $this->authService->scopeDataForUser($user, $reportType);
        $dataScopeSummary = 'projects:'.($scopedData['permissions_applied']['project_data_included'] ? 'authorized' : 'hidden')
            .',finance:'.($scopedData['permissions_applied']['financial_data_included'] ? 'authorized' : 'hidden');

        // 5. Generate Report (Gemini API with Fallback)
        $aiResult = $this->callGeminiApi($scopedData, $reportType, $user);

        // 6. Persist Report & Audit Trail
        $aiReport = AiReport::create([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'report_type' => $reportType,
            'data_scope' => $dataScopeSummary,
            'summary_content' => $aiResult['summary'],
            'structured_data' => $scopedData,
            'model_used' => $aiResult['model'],
            'cache_key' => $cacheKey,
        ]);

        AuditLog::record(
            action: 'AI_REPORT_GENERATED',
            description: "Generated AI {$reportType} report for {$user->name} ({$user->role})",
            entityType: 'AiReport',
            entityId: $aiReport->id,
            details: [
                'report_type' => $reportType,
                'data_scope' => $dataScopeSummary,
                'model' => $aiResult['model'],
                'user_id' => $user->id,
            ],
            user: $user
        );

        $responsePayload = [
            'status' => 'success',
            'report_type' => $reportType,
            'summary' => $aiResult['summary'],
            'cached' => false,
            'model' => $aiResult['model'],
            'scope' => $dataScopeSummary,
            'data' => $scopedData,
            'generated_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $responsePayload, now()->addMinutes($cacheTtl));

        return $responsePayload;
    }

    /**
     * Invoke Gemini API with strict timeout and structured prompt.
     * Degrades gracefully to deterministic analytic engine if API key is not configured or fails.
     */
    protected function callGeminiApi(array $scopedData, string $reportType, User $user): array
    {
        $apiKey = config('ai.gemini.api_key');
        $model = config('ai.gemini.model', 'gemini-1.5-flash');
        $timeout = config('ai.gemini.timeout', 15);

        if (empty($apiKey)) {
            return [
                'summary' => $this->generateLocalAnalyticalSummary($scopedData, $reportType, $user),
                'model' => 'deterministic-analytics-engine',
            ];
        }

        $systemPrompt = 'You are the Executive Intelligence Engine for JMOS (Jeota Media Operating System). '
            .'Generate a professional, structured executive briefing strictly using the JSON data provided. '
            .'Do not invent numbers, dates, or projects not in the payload. '
            .'Format your response in clean Markdown with sections: Executive Overview, Key Operational Highlights, '
            .'Financial Status (only if financial data is present in payload), and Recommended Next Actions.';

        $userPrompt = "Generate a {$reportType} report for {$user->name} (Role: {$user->role}).\n"
            ."Scoped Dataset (pre-filtered by user permissions):\n"
            .json_encode($scopedData, JSON_PRETTY_PRINT);

        try {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout($timeout)
                ->retry(2, 500, throw: false)
                ->post($endpoint, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt."\n\n".$userPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 1200,
                    ],
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $generatedText = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (! empty($generatedText)) {
                    return [
                        'summary' => trim($generatedText),
                        'model' => $model,
                    ];
                }
            }

            Log::warning('Gemini API call returned unsuccessful response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gemini API request failed with exception', [
                'error' => $e->getMessage(),
            ]);
        }

        // Graceful fallback to deterministic analytical summary
        return [
            'summary' => $this->generateLocalAnalyticalSummary($scopedData, $reportType, $user),
            'model' => 'fallback-analytical-engine',
        ];
    }

    /**
     * Local deterministic analyzer when AI is offline or key is unconfigured.
     */
    protected function generateLocalAnalyticalSummary(array $data, string $reportType, User $user): string
    {
        $hasFinance = $data['permissions_applied']['financial_data_included'] ?? false;
        $hasProjects = $data['permissions_applied']['project_data_included'] ?? false;

        $lines = [];
        $lines[] = '### JMOS Executive Briefing — '.ucwords(str_replace('_', ' ', $reportType));
        $lines[] = "**Prepared For:** {$user->name} | **Role:** ".ucfirst($user->role).' | **Generated:** '.now()->format('d M Y, H:i');
        $lines[] = '';

        if ($hasProjects && isset($data['projects'])) {
            $p = $data['projects'];
            $lines[] = '#### 🎬 Production & Project Operations';
            $lines[] = "- **Active Productions in Flight:** {$p['total_active']}";
            $lines[] = "- **Completed Deliverables:** {$p['completed']}";
            if (! empty($p['recent_projects'])) {
                $lines[] = '- **Top Deliverables Status:**';
                foreach (array_slice($p['recent_projects']->toArray(), 0, 4) as $proj) {
                    $lines[] = "  * **{$proj['title']}** ({$proj['client']}): {$proj['status']} [{$proj['progress']} complete]";
                }
            }
            $lines[] = '';
        }

        if ($hasFinance && isset($data['finance'])) {
            $f = $data['finance'];
            $lines[] = '#### 💰 Financial Performance & Cashflow';
            $lines[] = '- **Collected Revenue:** KES '.number_format($f['total_revenue_collected'], 2);
            $lines[] = '- **Total Operating Expenses:** KES '.number_format($f['total_expenses'], 2);
            $lines[] = '- **Net Operating Cashflow:** KES '.number_format($f['net_operating_cashflow'], 2);
            $lines[] = '- **Accounts Receivable (Outstanding):** KES '.number_format($f['total_outstanding'], 2);
            if ($f['total_overdue'] > 0) {
                $lines[] = '- ⚠️ **Overdue Invoices Alert:** KES '.number_format($f['total_overdue'], 2).' requires immediate collection follow-up.';
            }
            $lines[] = '';
        }

        if (isset($data['leads'])) {
            $l = $data['leads'];
            $lines[] = '#### 📈 Business Development & Pipeline';
            $lines[] = "- **Active Pipeline Leads:** {$l['active_pipeline_leads']}";
            $lines[] = "- **Converted Clients:** {$l['converted_leads']}";
            $lines[] = "- **Active Grants & Funding Opportunities:** {$l['active_opportunities_count']}";
            $lines[] = '';
        }

        $lines[] = '#### 🎯 Recommended Action Items';
        $lines[] = '1. Review pending project milestones approaching due dates in the Projects view.';
        if ($hasFinance) {
            $lines[] = '2. Dispatch automated reminders for unpaid invoices via the Quotations & Finance engine.';
        }
        $lines[] = '3. Check the Lead Journey Pipeline to advance qualified prospective accounts.';

        return implode("\n", $lines);
    }
}
