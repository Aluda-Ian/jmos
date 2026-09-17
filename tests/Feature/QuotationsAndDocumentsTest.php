<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationsAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a quote, sending email & generating WhatsApp link
     */
    public function test_quotation_creation_and_messaging(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $lead = Lead::create([
            'first_name' => 'Ian',
            'last_name' => 'Aluda',
            'lead_name' => 'Ian Aluda',
            'company' => 'Venda Technologies',
            'email' => 'ianaluda27@students.uonbi.ac.ke',
            'phone' => '+254712345678',
            'lead_status' => 'Qualified',
            'rating' => 'Hot',
            'lead_source' => 'Direct',
            'lead_owner' => 'Patrick Mwendwa',
        ]);

        // 1. Create Quote
        $response = $this->actingAs($user)->postJson('/api/quotes', [
            'lead_id' => $lead->id,
            'title' => 'Commercial Video Production',
            'recipient_name' => 'Ian Aluda',
            'recipient_email' => 'ianaluda27@students.uonbi.ac.ke',
            'recipient_phone' => '+254712345678',
            'subtotal' => 125000.0,
            'tax' => 20000.0,
            'discount' => 5000.0,
            'total_amount' => 140000.0,
            'validity_days' => 14,
            'notes' => 'Includes 2 days on-set shooting and color grading.',
            'terms' => '60% deposit upon confirmation.',
            'items' => [
                [
                    'description' => '2-Day 4K Video Production',
                    'quantity' => 1,
                    'rate' => 100000.0,
                    'amount' => 100000.0,
                ],
                [
                    'description' => 'Motion Graphics & Sound Design',
                    'quantity' => 1,
                    'rate' => 25000.0,
                    'amount' => 25000.0,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'title' => 'Commercial Video Production',
                    'recipient_name' => 'Ian Aluda',
                    'total_amount' => 140000.0,
                ],
            ]);

        $quoteId = $response->json('data.id');
        $this->assertDatabaseHas('quotes', ['id' => $quoteId, 'total_amount' => 140000.0]);

        // 2. Fetch WhatsApp link
        $waResponse = $this->actingAs($user)->getJson("/api/quotes/{$quoteId}/whatsapp");
        $waResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'whatsapp_url', 'message']);

        $this->assertStringContainsString('254712345678', $waResponse->json('whatsapp_url'));

        // 3. Send Quote via Email
        $emailResponse = $this->actingAs($user)->postJson("/api/quotes/{$quoteId}/send-email");
        $emailResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('quotes', ['id' => $quoteId, 'status' => 'Sent']);

        // 4. Upgrade Quote to Invoice after client negotiation
        $upgradeResponse = $this->actingAs($user)->postJson("/api/quotes/{$quoteId}/upgrade-invoice", [
            'invoice_type' => 'Deposit 60%',
            'amount' => 84000.0,
            'due_date' => now()->addDays(7)->format('M d'),
        ]);

        $upgradeResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'invoice' => [
                    'client' => 'Ian Aluda',
                    'amount' => 84000.0,
                    'type' => 'Deposit 60%',
                    'status' => 'Sent',
                ],
            ]);

        $this->assertDatabaseHas('quotes', [
            'id' => $quoteId,
            'status' => 'Invoiced',
        ]);
        $this->assertDatabaseHas('invoices', [
            'client' => 'Ian Aluda',
            'amount' => 84000.0,
        ]);
    }

    /**
     * Test Documents upload, link storage and listing
     */
    public function test_documents_management(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'owner']);

        // 1. Upload a file document
        $file = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $docResponse = $this->actingAs($user)->postJson('/api/documents', [
            'title' => 'Master Services Agreement 2026',
            'folder' => 'contracts',
            'notes' => 'Standard MSA template for enterprise clients',
            'file' => $file,
        ]);

        $docResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'title' => 'Master Services Agreement 2026',
                    'folder' => 'contracts',
                    'file_name' => 'contract.pdf',
                ],
            ]);

        $docId = $docResponse->json('data.id');
        $this->assertDatabaseHas('documents', ['id' => $docId, 'folder' => 'contracts']);

        // 2. Add an external cloud link document
        $linkResponse = $this->actingAs($user)->postJson('/api/documents', [
            'title' => 'Jeota Brand Guidelines & Assets',
            'folder' => 'brand_guides',
            'notes' => 'Canva brand kit with typography, colors, and logos',
            'external_url' => 'https://canva.com/brand/jeota-2026',
        ]);

        $linkResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'title' => 'Jeota Brand Guidelines & Assets',
                    'folder' => 'brand_guides',
                    'external_url' => 'https://canva.com/brand/jeota-2026',
                ],
            ]);

        // 3. List documents
        $listResponse = $this->actingAs($user)->getJson('/api/documents');
        $listResponse->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test Fundraising impact opportunities pipeline
     */
    public function test_fundraising_pipeline_crud(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $createResponse = $this->actingAs($user)->postJson('/api/fundraising', [
            'organization' => 'Ford Foundation',
            'program_title' => 'Climate & Creative Economy Grant',
            'category' => 'open_calls',
            'status' => 'Proposal Drafting',
            'amount_kes' => 15000000.0,
            'deadline' => '2026-10-31',
            'funding_type' => 'Youth Employment & Digital Storytelling',
            'partner_organization' => 'Barny Kiome',
            'application_link' => 'https://fordfoundation.org/grants/apply',
            'notes' => 'Full draft underway with partner NGOs.',
        ]);

        $createResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'organization' => 'Ford Foundation',
                    'program_title' => 'Climate & Creative Economy Grant',
                    'category' => 'open_calls',
                    'status' => 'Proposal Drafting',
                ],
            ]);

        // Verify index metrics
        $listResponse = $this->actingAs($user)->getJson('/api/fundraising');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'stats' => [
                    'total_opportunities',
                    'total_pipeline_kes',
                    'open_calls_count',
                    'partnerships_count',
                    'submitted_count',
                    'active_pipeline_count',
                    'won_count',
                ],
            ]);
    }
}
