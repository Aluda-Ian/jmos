<?php

namespace App\Services\Ai;

use App\Models\Contract;
use App\Services\Contracts\ContractTemplateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Uses Claude to tailor a contract generated from a Jeota Media template to one client.
 */
class ContractDraftingService
{
    public function __construct(
        protected ContractTemplateService $templates
    ) {}

    public function isAvailable(): bool
    {
        return (bool) config('ai.enabled', true) && ! empty(config('ai.anthropic.api_key'));
    }

    /**
     * Revise the contract body according to the given instructions.
     *
     * @return array{ok: bool, message: string, body?: string, model?: string}
     */
    public function revise(Contract $contract, string $instructions): array
    {
        if (! $this->isAvailable()) {
            return [
                'ok' => false,
                'message' => 'AI drafting is not configured. Add ANTHROPIC_API_KEY to the server .env to enable it — the template draft is still available.',
            ];
        }

        $model = config('ai.anthropic.model');

        try {
            $response = Http::timeout((int) config('ai.anthropic.contract_timeout', 120))
                ->withHeaders([
                    'x-api-key' => config('ai.anthropic.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post(config('ai.anthropic.endpoint', 'https://api.anthropic.com/v1/messages'), [
                    'model' => $model,
                    'max_tokens' => 12000,
                    'system' => $this->systemPrompt(),
                    'messages' => [
                        ['role' => 'user', 'content' => $this->userPrompt($contract, $instructions)],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::error('Contract AI drafting request failed', ['contract' => $contract->id, 'error' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Could not reach the AI service. Please try again in a moment.'];
        }

        if (! $response->successful()) {
            Log::warning('Contract AI drafting returned an error', ['status' => $response->status(), 'body' => $response->body()]);

            return ['ok' => false, 'message' => 'The AI service returned an error ('.$response->status().'). Check the ANTHROPIC_MODEL and API key settings.'];
        }

        if ($response->json('stop_reason') === 'max_tokens') {
            return ['ok' => false, 'message' => 'The AI response was cut off before the end of the agreement, so the current draft was kept.'];
        }

        $text = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        $text = trim(preg_replace('/^```(?:html)?\s*|\s*```$/i', '', trim($text)));
        $body = $this->templates->sanitize($text);

        if (mb_strlen(strip_tags($body)) < 400 || ! str_contains($body, '<h2')) {
            return ['ok' => false, 'message' => 'The AI response did not look like a complete agreement, so the current draft was kept.'];
        }

        return ['ok' => true, 'message' => 'Agreement revised by AI.', 'body' => $body, 'model' => (string) $model];
    }

    protected function systemPrompt(): string
    {
        $company = config('jeota.company');

        return <<<PROMPT
You are the contracts drafting assistant for {$company}, a media production company in Nairobi, Kenya.
You tailor the company's standard services agreement to one specific client, following the instructions you are given.

Rules:
1. Keep the agreement governed by the Laws of Kenya and keep the Kenya Data Protection Act consent appendix.
2. Preserve the Service Provider's protections (non-refundable deposit, late-payment interest, right to suspend services, intellectual property, termination and confidentiality clauses) unless an instruction explicitly asks to change them.
3. Never invent names, fees, dates, quantities or other facts. If information is missing, leave <span class="blank">________</span> in its place. Keep any existing blanks the instructions do not fill.
4. Keep clause numbering sequential (1, 2, 3 …) and keep Appendix 1 (Deliverables) and Appendix 2 (Consent) at the end.
5. Do not add a title page, parties paragraph or signature blocks — the system adds those.
6. Write in clear, formal British English.
7. Output ONLY the full revised agreement body as HTML using only these tags: h2, h3, p, ol, ul, li, strong, em, u, br, table, thead, tbody, tr, th, td. Use <h2> for clause headings and <ol type="a"> for lettered sub-clauses. No markdown, no code fences, no commentary before or after.
PROMPT;
    }

    protected function userPrompt(Contract $contract, string $instructions): string
    {
        $fields = $this->templates->normalize($contract->fields ?? []);

        $context = [
            'Client' => $contract->client_name,
            'Client registration / ID' => $contract->client_registration ?: 'not provided',
            'Client signatory' => trim(($contract->signatory_name ?? '').' '.($contract->signatory_position ? '('.$contract->signatory_position.')' : '')) ?: 'not provided',
            'Services' => $fields['services_label'] ?: 'not specified',
            'Total fee (KES, exclusive of taxes)' => $fields['fee'] > 0 ? number_format($fields['fee'], 2) : 'not provided',
            'Deposit' => $fields['deposit_percent'].'%',
            'Start date' => $fields['start_date'] ?: 'not provided',
            'End date' => $fields['end_date'] ?: 'not provided',
        ];

        if ($contract->quote && ! empty($contract->quote->items)) {
            $context['Linked quotation'] = $contract->quote->quote_number.' — '.collect($contract->quote->items)
                ->map(fn ($item) => ($item['description'] ?? 'Item').' (KES '.number_format((float) ($item['amount'] ?? 0), 2).')')
                ->implode('; ');
        }

        $contextText = collect($context)->map(fn ($value, $key) => "- {$key}: {$value}")->implode("\n");

        return "Client details:\n{$contextText}\n\n"
            ."Instructions from Jeota Media:\n".trim($instructions)."\n\n"
            ."Current agreement body:\n<agreement>\n".$contract->body."\n</agreement>\n\n"
            .'Return the complete revised agreement body as HTML.';
    }
}
