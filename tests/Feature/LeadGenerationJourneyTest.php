<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadGenerationJourneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test full Zoho CRM Lead Lifecycle & Conversion Journey
     */
    public function test_full_lead_generation_and_conversion_journey(): void
    {
        // 1. Capture new lead
        $leadResponse = $this->postJson('/api/leads', [
            'first_name' => 'Ian',
            'last_name' => 'Aluda',
            'company' => 'Venda Technologies',
            'title' => 'Chief Technology Officer',
            'email' => 'ianaluda27@students.uonbi.ac.ke',
            'phone' => '+254 712 345 678',
            'lead_source' => 'Web Research',
            'lead_owner' => 'Patrick Mwendwa',
            'rating' => 'Hot',
            'lead_status' => 'New',
            'industry' => 'Tech',
            'annual_revenue' => 500000.00,
            'city' => 'Nairobi, Kenya',
            'notes' => 'Looking for complete brand film production.',
        ]);

        $leadResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'lead_name' => 'Ian Aluda',
                    'company' => 'Venda Technologies',
                    'lead_status' => 'New',
                    'rating' => 'Hot',
                ],
            ]);

        $leadId = $leadResponse->json('data.id');
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'lead_name' => 'Ian Aluda']);

        // 2. Log Outbound Discovery Call for the Lead
        $callResponse = $this->postJson('/api/lead-calls', [
            'lead_id' => $leadId,
            'call_type' => 'Outbound',
            'call_status' => 'Completed',
            'purpose' => 'Discovery',
            'outcome' => 'Interested',
            'duration_minutes' => 15,
            'notes' => 'Discussed video requirements, budget range, and timeline.',
        ]);

        $callResponse->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'lead_id' => $leadId,
                    'call_type' => 'Outbound',
                    'purpose' => 'Discovery',
                ],
            ]);

        // Status should automatically bump to Contacted
        $this->assertDatabaseHas('leads', ['id' => $leadId, 'lead_status' => 'Contacted']);

        // 3. Update Lead Status to Qualified
        $updateResponse = $this->putJson("/api/leads/{$leadId}", [
            'lead_status' => 'Qualified',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $leadId,
                    'lead_status' => 'Qualified',
                ],
            ]);

        // 4. Zoho CRM Conversion Engine: Convert Lead to Account, Contact & Deal
        $convertResponse = $this->postJson("/api/leads/{$leadId}/convert", [
            'create_account' => true,
            'account_name' => 'Venda Technologies',
            'create_contact' => true,
            'contact_name' => 'Ian Aluda',
            'contact_title' => 'Chief Technology Officer',
            'create_deal' => true,
            'deal_title' => 'Venda Technologies · Commercial Video',
            'deal_value' => 500000.00,
            'deal_stage' => 'meeting',
        ]);

        $convertResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'lead' => [
                    'id' => $leadId,
                    'is_converted' => true,
                    'lead_status' => 'Converted',
                ],
                'account' => [
                    'client_name' => 'Venda Technologies',
                ],
                'contact' => [
                    'contact_name' => 'Ian Aluda',
                ],
                'deal' => [
                    'title' => 'Venda Technologies · Commercial Video',
                    'stage' => 'meeting',
                ],
            ]);

        $dealId = $convertResponse->json('deal.id');
        $this->assertDatabaseHas('deals', ['id' => $dealId, 'title' => 'Venda Technologies · Commercial Video']);
        $this->assertDatabaseHas('clients', ['client_name' => 'Venda Technologies']);
        $this->assertDatabaseHas('contacts', ['contact_name' => 'Ian Aluda']);

        // 5. Advance Deal to Won in Pipeline and trigger Win cascade
        $winResponse = $this->postJson("/api/deals/{$dealId}/win");
        $winResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'project' => [
                    'project_name' => 'Venda Technologies · Commercial Video',
                    'client' => 'Venda Technologies',
                ],
                'invoice' => [
                    'client' => 'Venda Technologies',
                    'type' => 'Deposit 60%',
                    'amount' => 300000.00,
                ],
            ]);

        // Ensure 8 tasks created
        $this->assertDatabaseCount('tasks', 8);
    }
}
