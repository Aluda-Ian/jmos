<?php

namespace Tests\Feature;

use App\Mail\ContractInvitationMail;
use App\Mail\ContractSignedMail;
use App\Models\AppNotification;
use App\Models\Contract;
use App\Models\Quote;
use App\Models\User;
use App\Services\Contracts\ContractTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContractsTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE_PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'title' => 'Brand Content Retainer',
            'client_name' => 'Safari Park Hotel',
            'client_email' => 'events@safaripark.co.ke',
            'client_phone' => '0712345678',
            'signatory_name' => 'Jane Wanjiku',
            'signatory_position' => 'Marketing Director',
            'fields' => [
                'services' => ['photography_event', 'social_management'],
                'fee' => 250000,
                'deposit_percent' => 50,
                'photo_count' => 60,
                'reel_count' => 4,
                'platforms' => 'Instagram, TikTok',
            ],
        ], $overrides);
    }

    public function test_owner_can_generate_a_contract_from_the_template(): void
    {
        $response = $this->actingAs($this->owner())->postJson('/api/contracts', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'Draft')
            ->assertJsonPath('data.client_name', 'Safari Park Hotel');

        $contract = Contract::firstOrFail();
        $this->assertMatchesRegularExpression('/^CT-\d{4}-001$/', $contract->contract_number);
        $this->assertSame(48, strlen($contract->access_token));
        $this->assertStringContainsString('Two Hundred and Fifty Thousand Only', $contract->body);
        $this->assertStringContainsString('Social Media Account Management', $contract->body);
        $this->assertStringContainsString('Instagram, TikTok', $contract->body);
        // Videography was not selected, so its appendix is left out
        $this->assertStringNotContainsString('Videography Services including', $contract->body);
    }

    public function test_unfilled_template_values_are_marked_as_blanks(): void
    {
        $this->actingAs($this->owner())->postJson('/api/contracts', $this->payload([
            'fields' => ['services' => ['videography_project'], 'fee' => 0],
        ]))->assertCreated()
            ->assertJsonPath('data.blanks', fn (int $blanks) => $blanks >= 4);
    }

    public function test_contract_fee_is_prefilled_from_a_linked_quotation(): void
    {
        $quote = Quote::create([
            'quote_number' => 'QT-2026-050',
            'recipient_name' => 'Safari Park Hotel',
            'recipient_email' => 'quotes@safaripark.co.ke',
            'title' => 'Event Coverage',
            'items' => [['description' => 'Full-day event photography', 'quantity' => 1, 'rate' => 90000, 'amount' => 90000]],
            'total_amount' => 90000,
        ]);

        $payload = $this->payload(['quote_id' => $quote->id]);
        unset($payload['fields']['fee'], $payload['client_email']);

        $this->actingAs($this->owner())->postJson('/api/contracts', $payload)->assertCreated();

        $contract = Contract::firstOrFail();
        $this->assertSame(90000.0, (float) $contract->fields['fee']);
        $this->assertSame('quotes@safaripark.co.ke', $contract->client_email);
        $this->assertStringContainsString('Full-day event photography', $contract->body);
    }

    public function test_only_the_owner_can_manage_contracts(): void
    {
        $this->getJson('/api/contracts')->assertUnauthorized();

        $sales = User::factory()->create(['role' => 'sales']);

        $this->actingAs($sales)->postJson('/api/contracts', $this->payload())->assertForbidden();
        $this->actingAs($sales)->getJson('/api/contracts')->assertForbidden();
    }

    public function test_manual_edits_are_sanitised(): void
    {
        $contract = Contract::factory()->create();

        $this->actingAs($this->owner())->putJson("/api/contracts/{$contract->id}", [
            'body' => '<h2 onclick="steal()">1. TERMS</h2><script>alert(1)</script><p>Fair <a href="javascript:x">terms</a></p><img src=x onerror=alert(1)>',
        ])->assertOk();

        $body = $contract->fresh()->body;
        $this->assertStringContainsString('<h2>1. TERMS</h2>', $body);
        $this->assertStringContainsString('<p>Fair terms</p>', $body);
        $this->assertStringNotContainsString('script', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('<img', $body);
    }

    public function test_ai_revision_replaces_the_body_with_sanitised_output(): void
    {
        config(['ai.anthropic.api_key' => 'test-key']);
        $aiBody = '<h2>1. INTERPRETATION</h2><p>'.str_repeat('Tailored clause text for the hotel. ', 20).'</p><h2>2. EXCLUSIVITY</h2><p>The Service Provider shall be the exclusive media partner.</p><script>bad()</script>';
        Http::fake(['api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => "```html\n{$aiBody}\n```"]],
            'stop_reason' => 'end_turn',
        ])]);

        $contract = Contract::factory()->create();

        $this->actingAs($this->owner())->postJson("/api/contracts/{$contract->id}/ai-revise", [
            'instructions' => 'Add an exclusivity clause for hotel events.',
        ])->assertOk();

        $contract->refresh();
        $this->assertTrue($contract->ai_generated);
        $this->assertStringContainsString('EXCLUSIVITY', $contract->body);
        $this->assertStringNotContainsString('script', $contract->body);
        Http::assertSent(fn ($request) => str_contains($request['messages'][0]['content'], 'exclusivity clause'));
    }

    public function test_ai_revision_reports_when_ai_is_not_configured(): void
    {
        config(['ai.anthropic.api_key' => '']);
        $contract = Contract::factory()->create(['body' => '<p>Original</p>']);

        $this->actingAs($this->owner())->postJson("/api/contracts/{$contract->id}/ai-revise", [
            'instructions' => 'Make it a 6 month retainer.',
        ])->assertStatus(422)->assertJsonPath('status', 'error');

        $this->assertSame('<p>Original</p>', $contract->fresh()->body);
    }

    public function test_sending_emails_the_client_and_applies_the_company_signature(): void
    {
        Mail::fake();
        $contract = Contract::factory()->create(['client_email' => 'client@example.com']);

        $this->actingAs($this->owner())->postJson("/api/contracts/{$contract->id}/send-email")
            ->assertOk()
            ->assertJsonPath('data.status', 'Sent');

        Mail::assertSent(ContractInvitationMail::class, fn ($mail) => $mail->hasTo('client@example.com'));
        $this->assertNotNull($contract->fresh()->provider_signed_at);
    }

    public function test_client_can_view_and_sign_a_sent_contract(): void
    {
        Mail::fake();
        $owner = $this->owner();
        $contract = Contract::factory()->sent()->create(['client_email' => 'client@example.com']);

        $this->get("/contracts/sign/{$contract->access_token}")
            ->assertOk()
            ->assertSee('SERVICES AGREEMENT')
            ->assertSee('barny-kiome-signature.png')
            ->assertSee('Sign this agreement');
        $this->assertSame(Contract::STATUS_VIEWED, $contract->fresh()->status);

        $this->post("/contracts/sign/{$contract->access_token}", [
            'signer_name' => 'Jane Wanjiku',
            'signer_position' => 'Director',
            'signature' => self::SIGNATURE_PNG,
            'agree' => '1',
            'data_consent' => '1',
        ])->assertRedirect("/contracts/sign/{$contract->access_token}");

        $contract->refresh();
        $this->assertSame(Contract::STATUS_SIGNED, $contract->status);
        $this->assertSame('Jane Wanjiku', $contract->client_signed_name);
        $this->assertTrue($contract->data_consent);
        $this->assertSame(hash('sha256', $contract->body), $contract->body_hash);
        $this->assertTrue(AppNotification::where('user_id', $owner->id)->where('title', 'like', 'Contract signed%')->exists());
        Mail::assertSent(ContractSignedMail::class, fn ($mail) => $mail->hasTo('client@example.com'));

        // Signed contracts are locked
        $this->actingAs($owner)->putJson("/api/contracts/{$contract->id}", ['body' => '<p>Changed</p>'])->assertStatus(422);
        $this->actingAs($owner)->deleteJson("/api/contracts/{$contract->id}")->assertStatus(422);
    }

    public function test_draft_or_signed_contracts_cannot_be_signed_again(): void
    {
        $draft = Contract::factory()->create();

        $this->post("/contracts/sign/{$draft->access_token}", [
            'signer_name' => 'Someone',
            'signature' => self::SIGNATURE_PNG,
            'agree' => '1',
        ])->assertSessionHas('error');

        $this->assertSame(Contract::STATUS_DRAFT, $draft->fresh()->status);
    }

    public function test_signing_requires_a_valid_signature_and_agreement(): void
    {
        $contract = Contract::factory()->sent()->create();

        $this->post("/contracts/sign/{$contract->access_token}", [
            'signer_name' => 'Jane',
            'signature' => self::SIGNATURE_PNG,
        ])->assertSessionHasErrors('agree');

        $this->post("/contracts/sign/{$contract->access_token}", [
            'signer_name' => 'Jane',
            'signature' => 'data:image/png;base64,bm90LWEtcG5n',
            'agree' => '1',
        ])->assertSessionHasErrors('signature');

        $this->assertNotSame(Contract::STATUS_SIGNED, $contract->fresh()->status);
    }

    public function test_amount_in_words(): void
    {
        $service = new ContractTemplateService;

        $this->assertSame('One Hundred and Fifty Thousand Only', $service->amountInWords(150000));
        $this->assertSame('One Million Two Hundred and Fifty Thousand and Five Only', $service->amountInWords(1250005));
        $this->assertSame('Forty-Five Thousand Five Hundred and Fifty Cents Only', $service->amountInWords(45500.50));
    }
}
