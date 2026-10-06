<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContractInvitationMail;
use App\Mail\ContractSignedMail;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Quote;
use App\Models\User;
use App\Services\Ai\ContractDraftingService;
use App\Services\Contracts\ContractTemplateService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        protected ContractTemplateService $templates,
        protected ContractDraftingService $drafting
    ) {}

    /**
     * Templates, service options and default values for the "New contract" form.
     */
    public function templates(Request $request): JsonResponse
    {
        $this->authorizeManager($request);

        return response()->json([
            'status' => 'success',
            'data' => [
                'templates' => collect($this->templates->templates())->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
                'services' => collect(ContractTemplateService::SERVICES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
                'defaults' => $this->templates->defaults(),
                'ai_available' => $this->drafting->isAvailable(),
                'signatory' => [
                    'name' => config('jeota.signatory.name'),
                    'title' => config('jeota.signatory.title'),
                    'signature_url' => asset(config('jeota.signatory.signature')),
                ],
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeManager($request);

        $query = Contract::with(['client:id,client_name', 'quote:id,quote_number,total_amount'])->latest();

        if ($request->filled('search')) {
            $s = $request->string('search')->trim();
            $query->where(function ($q) use ($s) {
                $q->where('contract_number', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('client_name', 'like', "%{$s}%")
                    ->orWhere('client_email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        $contracts = $query->get()->each(function (Contract $contract) {
            $contract->setAttribute('blanks', $this->templates->countBlanks($contract->body));
            $contract->makeHidden('body');
        });

        return response()->json(['status' => 'success', 'data' => $contracts]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManager($request);

        $validated = $request->validate($this->rules() + [
            'use_ai' => 'nullable|boolean',
        ]);

        $contract = new Contract($this->partyAttributes($validated));
        $contract->template = $validated['template'] ?? 'photo-video-social';
        $contract->fields = $this->templates->normalizeInput($validated['fields'] ?? []);
        $contract->ai_instructions = $validated['ai_instructions'] ?? null;
        $contract->status = Contract::STATUS_DRAFT;
        $contract->created_by = $request->user()?->name ?? 'Barny Kiome';
        $this->prefillFromLinks($contract);

        $contract->body = $this->templates->render($contract);
        $contract->save();

        $aiMessage = null;
        if ($request->boolean('use_ai') && filled($contract->ai_instructions)) {
            $aiMessage = $this->applyAiRevision($contract, $contract->ai_instructions);
        }

        AuditLog::record('CREATE', "Created contract '{$contract->contract_number}' for {$contract->client_name}", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Contract {$contract->contract_number} drafted.".($aiMessage ? " {$aiMessage}" : ''),
            'ai_message' => $aiMessage,
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ], 201);
    }

    public function show(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);

        return response()->json([
            'status' => 'success',
            'data' => $this->presentContract($contract->load(['client', 'quote'])),
        ]);
    }

    /**
     * Update party details / variables. Pass `regenerate` to rebuild the text from the template,
     * or `body` to save manual edits to the agreement text.
     */
    public function update(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);
        $this->ensureEditable($contract);

        $validated = $request->validate(array_merge($this->rules(true), [
            'body' => 'nullable|string|max:400000',
            'regenerate' => 'nullable|boolean',
        ]));

        $contract->fill($this->partyAttributes($validated));

        if (array_key_exists('template', $validated) && $validated['template']) {
            $contract->template = $validated['template'];
        }
        if (array_key_exists('fields', $validated)) {
            $contract->fields = $this->templates->normalizeInput(array_merge($contract->fields ?? [], $validated['fields'] ?? []));
        }
        if (array_key_exists('ai_instructions', $validated)) {
            $contract->ai_instructions = $validated['ai_instructions'];
        }

        if ($request->boolean('regenerate')) {
            $contract->body = $this->templates->render($contract);
            $contract->ai_generated = false;
        } elseif (array_key_exists('body', $validated) && $validated['body'] !== null) {
            $contract->body = $this->templates->sanitize($validated['body']);
        }

        $contract->save();

        AuditLog::record('UPDATE', "Updated contract '{$contract->contract_number}'", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => $request->boolean('regenerate') ? 'Agreement text rebuilt from the template.' : 'Contract saved.',
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    /**
     * Ask AI to tailor the current agreement text.
     */
    public function aiRevise(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);
        $this->ensureEditable($contract);

        $validated = $request->validate(['instructions' => 'required|string|min:5|max:4000']);

        $result = $this->drafting->revise($contract->loadMissing('quote'), $validated['instructions']);

        if (! $result['ok']) {
            return response()->json(['status' => 'error', 'message' => $result['message']], 422);
        }

        $contract->update([
            'body' => $result['body'],
            'ai_generated' => true,
            'ai_instructions' => trim(($contract->ai_instructions ? $contract->ai_instructions."\n---\n" : '').$validated['instructions']),
        ]);

        AuditLog::record('AI_REVISE', "AI revised contract '{$contract->contract_number}'", 'Contract', $contract->id, ['model' => $result['model'] ?? null], $request);

        return response()->json([
            'status' => 'success',
            'message' => $result['message'].' Please review the changes before sending.',
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    /**
     * Email the signing link to the client. This applies the company signature.
     */
    public function sendEmail(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);
        $this->ensureSendable($contract);

        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        $email = $validated['email'] ?? $contract->client_email;
        if (empty($email)) {
            return response()->json(['status' => 'error', 'message' => 'Add the client\'s email address before sending.'], 422);
        }

        try {
            NotificationService::applySmtpSettings();
            Mail::to($email)->send(new ContractInvitationMail($contract, $validated['message'] ?? null));
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Email dispatch failed: '.$e->getMessage()], 500);
        }

        $this->markSent($contract, ['client_email' => $email]);
        AuditLog::record('EMAIL', "Sent contract {$contract->contract_number} for signature to {$email}", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Contract {$contract->contract_number} sent to {$email} for signature.",
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    /**
     * WhatsApp share link with the signing URL. This applies the company signature.
     */
    public function whatsapp(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);
        $this->ensureSendable($contract);

        $phone = preg_replace('/[^0-9]/', '', (string) $contract->client_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254'.$phone;
        }

        $greeting = $contract->signatory_name ?: $contract->client_name;
        $message = "Hello *{$greeting}*,\n\n"
            ."Please find our services agreement *{$contract->contract_number}* — {$contract->title}. "
            ."It is already signed on behalf of *Jeota Media Limited*.\n\n"
            ."👉 *Review & sign online:*\n{$contract->signUrl()}\n\n"
            ."Any questions, just reply here.\n\n"
            ."Best regards,\n*Jeota Media*\n".config('jeota.email').' · '.config('jeota.phone');

        $this->markSent($contract);
        AuditLog::record('WHATSAPP', "Shared contract {$contract->contract_number} via WhatsApp", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'whatsapp_url' => 'https://api.whatsapp.com/send?'.($phone ? "phone={$phone}&" : '').'text='.urlencode($message),
            'message' => $message,
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    /**
     * Mark as sent without emailing (e.g. the link was copied and shared manually).
     */
    public function markAsSent(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);
        $this->ensureSendable($contract);

        $this->markSent($contract);
        AuditLog::record('SEND', "Issued contract {$contract->contract_number} signing link", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Signing link is live and the company signature has been applied.',
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    public function void(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);

        if ($contract->status === Contract::STATUS_SIGNED) {
            return response()->json(['status' => 'error', 'message' => 'A signed contract cannot be voided here. Record a termination instead.'], 422);
        }

        $contract->update(['status' => Contract::STATUS_VOID]);
        AuditLog::record('VOID', "Voided contract {$contract->contract_number}", 'Contract', $contract->id, [], $request);

        return response()->json([
            'status' => 'success',
            'message' => "Contract {$contract->contract_number} voided. The signing link no longer accepts signatures.",
            'data' => $this->presentContract($contract->fresh(['client', 'quote'])),
        ]);
    }

    public function destroy(Request $request, Contract $contract): JsonResponse
    {
        $this->authorizeManager($request);

        if ($contract->status === Contract::STATUS_SIGNED) {
            return response()->json(['status' => 'error', 'message' => 'Signed contracts are kept as records and cannot be deleted.'], 422);
        }

        $number = $contract->contract_number;
        $id = $contract->id;
        $contract->delete();

        AuditLog::record('DELETE', "Deleted contract '{$number}'", 'Contract', $id, [], $request);

        return response()->json(['status' => 'success', 'message' => "Contract {$number} deleted."]);
    }

    /*
    |--------------------------------------------------------------------------
    | Public client pages (link sent by email / WhatsApp)
    |--------------------------------------------------------------------------
    */

    public function publicShow(Request $request, string $token): View
    {
        $contract = Contract::where('access_token', $token)->with('quote')->firstOrFail();

        if ($contract->status === Contract::STATUS_SENT && ! $request->boolean('preview')) {
            $contract->update(['status' => Contract::STATUS_VIEWED, 'viewed_at' => now()]);
        }

        return view('pages.public-contract', [
            'contract' => $contract,
            'fields' => $this->templates->normalize($contract->fields ?? []),
            'isPreview' => $request->boolean('preview'),
        ]);
    }

    public function publicSign(Request $request, string $token): RedirectResponse
    {
        $contract = Contract::where('access_token', $token)->firstOrFail();

        if (! $contract->canBeSignedByClient()) {
            return redirect()->route('contracts.public.show', $token)
                ->with('error', $contract->status === Contract::STATUS_SIGNED
                    ? 'This agreement has already been signed.'
                    : 'This agreement is not open for signature. Please contact Jeota Media.');
        }

        $validated = $request->validate([
            'signer_name' => 'required|string|max:255',
            'signer_position' => 'nullable|string|max:255',
            'signature' => ['required', 'string', 'max:700000', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/'],
            'agree' => 'accepted',
            'data_consent' => 'nullable|boolean',
        ], [
            'signature.required' => 'Please draw your signature in the box.',
            'signature.regex' => 'The signature could not be read. Please draw it again.',
            'agree.accepted' => 'Please confirm that you have read and agree to the agreement.',
        ]);

        $png = base64_decode(substr($validated['signature'], strlen('data:image/png;base64,')), true);
        if ($png === false || ! str_starts_with($png, "\x89PNG\r\n\x1a\n")) {
            return back()->withInput()->withErrors(['signature' => 'The signature could not be read. Please draw it again.']);
        }

        $contract->update([
            'status' => Contract::STATUS_SIGNED,
            'signed_at' => now(),
            'client_signature' => $validated['signature'],
            'client_signed_name' => $validated['signer_name'],
            'client_signed_position' => $validated['signer_position'] ?? null,
            'client_signed_ip' => $request->ip(),
            'client_signed_user_agent' => substr((string) $request->userAgent(), 0, 500),
            'data_consent' => $request->boolean('data_consent'),
            'body_hash' => hash('sha256', (string) $contract->body),
        ]);

        AuditLog::record('CLIENT_SIGN', "{$contract->client_signed_name} signed contract {$contract->contract_number} for {$contract->client_name}", 'Contract', $contract->id, [
            '_user_name' => $contract->client_signed_name,
            '_user_role' => 'client',
        ], $request);

        $this->notifyTeamOfSignature($contract);

        return redirect()->route('contracts.public.show', $token)
            ->with('success', 'Thank you! The agreement has been signed. A confirmation has been sent by email.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    protected function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return [
            'template' => ['nullable', 'string', Rule::in(array_keys($this->templates->templates()))],
            'title' => "{$required}|string|max:255",
            'client_id' => 'nullable|exists:clients,id',
            'quote_id' => 'nullable|exists:quotes,id',
            'client_name' => "{$required}|string|max:255",
            'client_registration' => 'nullable|string|max:255',
            'client_po_box' => 'nullable|string|max:255',
            'client_address' => 'nullable|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'signatory_name' => 'nullable|string|max:255',
            'signatory_position' => 'nullable|string|max:255',
            'ai_instructions' => 'nullable|string|max:4000',
            'fields' => 'nullable|array',
            'fields.services' => 'nullable|array',
            'fields.services.*' => ['string', Rule::in(array_keys(ContractTemplateService::SERVICES))],
            'fields.fee' => 'nullable|numeric|min:0',
            'fields.deposit_percent' => 'nullable|integer|min:0|max:100',
            'fields.payment_days' => 'nullable|integer|min:0|max:365',
            'fields.late_interest' => 'nullable|numeric|min:0|max:100',
            'fields.feedback_days' => 'nullable|integer|min:0|max:60',
            'fields.revision_rounds' => 'nullable|integer|min:0|max:20',
            'fields.reschedule_days' => 'nullable|integer|min:0|max:365',
            'fields.termination_days' => 'nullable|integer|min:0|max:365',
            'fields.*' => 'nullable',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function partyAttributes(array $validated): array
    {
        return collect($validated)->only([
            'title', 'client_id', 'quote_id', 'client_name', 'client_registration', 'client_po_box',
            'client_address', 'client_email', 'client_phone', 'signatory_name', 'signatory_position',
        ])->all();
    }

    /**
     * Fill gaps from the linked quotation / client record.
     */
    protected function prefillFromLinks(Contract $contract): void
    {
        $fields = $contract->fields ?? [];

        if ($contract->quote_id && ($quote = Quote::find($contract->quote_id))) {
            if (empty($fields['fee'])) {
                $fields['fee'] = (float) $quote->total_amount;
            }
            $contract->client_id = $contract->client_id ?: $quote->client_id;
            $contract->client_email = $contract->client_email ?: $quote->recipient_email;
            $contract->client_phone = $contract->client_phone ?: $quote->recipient_phone;
        }

        if ($contract->client_id && ($client = Client::find($contract->client_id))) {
            $contract->client_email = $contract->client_email ?: $client->email;
            $contract->client_phone = $contract->client_phone ?: $client->phone;
            $contract->client_address = $contract->client_address ?: $client->address;
            $contract->signatory_name = $contract->signatory_name ?: $client->contact_person;
        }

        $contract->fields = $fields;
    }

    protected function applyAiRevision(Contract $contract, string $instructions): string
    {
        $result = $this->drafting->revise($contract->loadMissing('quote'), $instructions);

        if (! $result['ok']) {
            return 'AI customisation skipped: '.$result['message'];
        }

        $contract->update(['body' => $result['body'], 'ai_generated' => true]);

        return 'AI has tailored the agreement — please review it before sending.';
    }

    protected function markSent(Contract $contract, array $extra = []): void
    {
        $contract->update(array_merge([
            'status' => in_array($contract->status, [Contract::STATUS_SENT, Contract::STATUS_VIEWED], true) ? $contract->status : Contract::STATUS_SENT,
            'sent_at' => now(),
            'provider_signed_at' => $contract->provider_signed_at ?? now(),
        ], $extra));
    }

    protected function notifyTeamOfSignature(Contract $contract): void
    {
        $allowedRoles = config('jeota.contracts.allowed_roles', ['owner']);

        User::whereIn('role', $allowedRoles)->get()->each(function (User $user) use ($contract) {
            AppNotification::create([
                'user_id' => $user->id,
                'title' => 'Contract signed ✓',
                'message' => "{$contract->client_signed_name} signed {$contract->contract_number} ({$contract->client_name}).",
                'type' => 'success',
                'link' => '/contracts',
            ]);
        });

        try {
            NotificationService::applySmtpSettings();
            $recipients = array_values(array_filter([$contract->client_email, config('jeota.email')]));
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new ContractSignedMail($contract));
            }
        } catch (\Throwable $e) {
            Log::warning('Contract signed confirmation email failed: '.$e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentContract(Contract $contract): array
    {
        return array_merge($contract->toArray(), [
            'blanks' => $this->templates->countBlanks($contract->body),
            'services_label' => $this->templates->normalize($contract->fields ?? [])['services_label'],
            'has_client_signature' => ! empty($contract->client_signature),
        ]);
    }

    protected function authorizeManager(Request $request): void
    {
        $role = $request->user()?->role;

        abort_unless(
            $role && in_array($role, config('jeota.contracts.allowed_roles', ['owner']), true),
            403,
            'Only the account owner can manage client contracts.'
        );
    }

    protected function ensureEditable(Contract $contract): void
    {
        abort_if($contract->isLocked(), 422, "Contract {$contract->contract_number} is {$contract->status} and can no longer be edited.");
    }

    protected function ensureSendable(Contract $contract): void
    {
        abort_if($contract->isLocked(), 422, "Contract {$contract->contract_number} is {$contract->status} and cannot be sent.");
        abort_if(blank(strip_tags((string) $contract->body)), 422, 'The agreement text is empty.');
    }
}
