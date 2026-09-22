<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiFeatureIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default basic role models
        Role::updateOrCreate(['slug' => 'owner'], [
            'name' => 'Owner',
            'description' => 'System Owner',
            'permissions' => ['*'],
            'is_system' => true,
        ]);

        Role::updateOrCreate(['slug' => 'finance'], [
            'name' => 'Finance Manager',
            'description' => 'Finance & Accounting',
            'permissions' => ['dashboard.view', 'dashboard.financials', 'finance.view', 'invoices.manage'],
            'is_system' => true,
        ]);

        Role::updateOrCreate(['slug' => 'member'], [
            'name' => 'Production Crew',
            'description' => 'Projects & Deliverables Only',
            'permissions' => ['dashboard.view', 'projects.view', 'tasks.manage'],
            'is_system' => true,
        ]);

        Role::updateOrCreate(['slug' => 'restricted'], [
            'name' => 'Restricted Guest',
            'description' => 'No reports access',
            'permissions' => ['chat.access'],
            'is_system' => false,
        ]);
    }

    public function test_ai_report_generation_respects_feature_flags(): void
    {
        Config::set('ai.enabled', false);

        $user = User::factory()->create(['role' => 'owner']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'executive_digest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'disabled',
        ]);
    }

    public function test_unauthorized_user_is_denied_from_generating_reports(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.reports_enabled', true);

        $user = User::factory()->create([
            'role' => 'restricted',
            'custom_permissions' => [],
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'executive_digest',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_financial_role_gets_project_data_with_financials_stripped(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.reports_enabled', true);

        // Create a project and financial data
        Project::create([
            'project_name' => 'Commercial Shoot Nairobi',
            'client' => 'Acme Brand',
            'status' => 'In Progress',
            'progress_pct' => 45,
            'budget' => 250000,
        ]);

        Invoice::create([
            'invoice_no' => 'INV-2026-001',
            'client' => 'Acme Brand',
            'amount' => 250000,
            'status' => 'Paid',
        ]);

        Expense::create([
            'name' => 'Camera Gear Rental',
            'category' => 'Equipment',
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);

        $memberUser = User::factory()->create([
            'role' => 'member',
            'custom_permissions' => ['dashboard.view', 'projects.view'],
        ]);
        Sanctum::actingAs($memberUser);

        $response = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'project_status',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('success', $data['status']);
        $this->assertTrue($data['data']['permissions_applied']['project_data_included']);
        $this->assertFalse($data['data']['permissions_applied']['financial_data_included']);
        $this->assertArrayNotHasKey('finance', $data['data']);
        $this->assertArrayHasKey('projects', $data['data']);
    }

    public function test_financial_role_gets_full_financial_analytics(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.reports_enabled', true);

        Invoice::create([
            'invoice_no' => 'INV-2026-099',
            'client' => 'Safaricom Ltd',
            'amount' => 850000,
            'status' => 'Paid',
        ]);

        Expense::create([
            'name' => 'Studio Lighting Rig',
            'category' => 'Production Cost',
            'amount' => 120000,
            'date' => now()->toDateString(),
        ]);

        $financeUser = User::factory()->create([
            'role' => 'finance',
        ]);
        Sanctum::actingAs($financeUser);

        $response = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'financial_summary',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals('success', $data['status']);
        $this->assertTrue($data['data']['permissions_applied']['financial_data_included']);
        $this->assertArrayHasKey('finance', $data['data']);
        $this->assertEquals(850000, $data['data']['finance']['total_revenue_collected']);
        $this->assertEquals(120000, $data['data']['finance']['total_expenses']);
    }

    public function test_reports_are_cached_and_can_be_force_refreshed(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.reports_enabled', true);

        $owner = User::factory()->create(['role' => 'owner']);
        Sanctum::actingAs($owner);

        // First generation
        $first = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'executive_digest',
        ]);
        $first->assertStatus(200);
        $this->assertFalse($first->json('cached'));

        // Second generation (cached)
        $second = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'executive_digest',
        ]);
        $second->assertStatus(200);
        $this->assertTrue($second->json('cached'));

        // Force refresh
        $third = $this->postJson('/api/ai/reports/generate', [
            'report_type' => 'executive_digest',
            'refresh' => true,
        ]);
        $third->assertStatus(200);
        $this->assertFalse($third->json('cached'));
    }

    public function test_ai_chat_detects_escalation_and_alerts_support(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.chat_enabled', true);

        $user = User::factory()->create(['name' => 'Ian Aluda', 'role' => 'owner']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ai/chat/message', [
            'message' => 'I need to talk to a human agent please, I am stuck',
            'mode' => 'general_help',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['escalated']);
        $this->assertNotEmpty($data['navigation_links']);

        // Assert notification created for support handoff
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'AI Assistant Support Handoff',
        ]);
    }

    public function test_ai_chat_conversation_history_is_isolated_per_user(): void
    {
        Config::set('ai.enabled', true);
        Config::set('ai.chat_enabled', true);

        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        Sanctum::actingAs($user1);
        $this->postJson('/api/ai/chat/message', [
            'message' => 'Confidential prompt for User One',
            'session_id' => 'session-123',
        ]);

        // Query history as User Two
        Sanctum::actingAs($user2);
        $response = $this->getJson('/api/ai/chat/history?session_id=session-123');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('messages'));
    }
}
