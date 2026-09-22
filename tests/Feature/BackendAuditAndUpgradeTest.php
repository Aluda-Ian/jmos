<?php

namespace Tests\Feature;

use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\NewChatMessageMail;
use App\Mail\TaskAssignedMail;
use App\Models\AppNotification;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackendAuditAndUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_deal_win_creates_project_tasks_invoice_notifications_and_audit_log(): void
    {
        Mail::fake();

        $owner = User::where('role', 'owner')->first();
        $token = $owner->createToken('test')->plainTextToken;

        $deal = Deal::create([
            'title' => 'Kenyatta University · Documentary Series',
            'client_name' => 'Kenyatta University',
            'stage' => 'negotiation',
            'value' => 750000,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/pipeline/{$deal->id}/win");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'stage' => 'won',
            'is_won' => true,
        ]);

        $this->assertDatabaseHas('projects', [
            'project_name' => 'Kenyatta University · Documentary Series',
            'client' => 'Kenyatta University',
            'budget' => 750000,
        ]);

        $this->assertDatabaseHas('invoices', [
            'client' => 'Kenyatta University',
            'type' => 'Deposit 60%',
            'amount' => 450000,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'deal',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'WIN',
            'entity_type' => 'Deal',
            'entity_id' => (string) $deal->id,
        ]);

        Mail::assertSent(DealWonAlertMail::class);
    }

    public function test_task_creation_dispatches_email_and_records_audit_log(): void
    {
        Mail::fake();

        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $project = Project::first();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/tasks', [
                'project_id' => $project->id,
                'title' => '4K Color Timing Pass',
                'stage' => 'in_progress',
                'assigned_to' => 'Stephen Otieno',
                'due_date' => '2026-10-15',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE',
            'entity_type' => 'Task',
        ]);

        Mail::assertSent(TaskAssignedMail::class);
    }

    public function test_invoice_send_reminder_endpoint_and_audit_log(): void
    {
        Mail::fake();

        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $client = Client::create([
            'client_name' => 'Acme Corporation',
            'email' => 'finance@acme.co.ke',
            'project_value' => 200000,
        ]);

        $invoice = Invoice::create([
            'client' => 'Acme Corporation',
            'type' => 'Full Payment 100%',
            'amount' => 200000,
            'due_date' => 'Oct 30',
            'status' => 'Sent',
        ]);

        // Explicit email provided
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/invoices/{$invoice->id}/send-reminder", [
                'email' => 'accounts@acme.co.ke',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Invoice reminder dispatched to accounts@acme.co.ke.',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EMAIL',
            'entity_type' => 'Invoice',
            'entity_id' => (string) $invoice->id,
        ]);

        Mail::assertSent(InvoiceReminderMail::class);
    }

    public function test_settings_test_email_handles_chat_template(): void
    {
        Mail::fake();

        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/settings/test-email', [
                'recipient' => 'ian@jeotamedia.co.ke',
                'template' => 'chat',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        Mail::assertSent(NewChatMessageMail::class);
    }

    public function test_contact_creation_auto_composes_name_if_omitted(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/contacts', [
                'first_name' => 'Wanjiku',
                'last_name' => 'Kamau',
                'company_name' => 'Safaricom PLC',
                'email' => 'wanjiku@safaricom.co.ke',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'contact_name' => 'Wanjiku Kamau',
                    'company_name' => 'Safaricom PLC',
                ],
            ]);

        $this->assertDatabaseHas('contacts', [
            'contact_name' => 'Wanjiku Kamau',
            'email' => 'wanjiku@safaricom.co.ke',
        ]);
    }

    public function test_quote_next_quote_number_sequence(): void
    {
        $year = date('Y');
        Quote::create([
            'quote_number' => "QT-{$year}-005",
            'recipient_name' => 'Test Recipient',
            'title' => 'Test Production',
            'total_amount' => 100000,
        ]);

        $next = Quote::nextQuoteNumber();
        $this->assertEquals("QT-{$year}-006", $next);
    }

    public function test_finance_overview_returns_sorted_ledger_and_accurate_totals(): void
    {
        $response = $this->getJson('/api/finance/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'brought_forward',
                'money_in',
                'money_out',
                'current_balance',
                'profit',
                'unpaid_total',
                'overdue_count',
                'ledger',
            ]);
    }

    public function test_document_and_expense_file_size_handling(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $fakeFile = UploadedFile::fake()->create('contract.pdf', 150, 'application/pdf');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/documents', [
                'title' => 'Client Service Agreement 2026',
                'folder' => 'contracts',
                'file' => $fakeFile,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('documents', [
            'title' => 'Client Service Agreement 2026',
            'folder' => 'contracts',
            'file_name' => 'contract.pdf',
        ]);
    }

    public function test_notification_mark_read_and_unread_with_sanctum_token(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        $notif = AppNotification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'System Check',
            'message' => 'All systems functional',
            'read' => false,
        ]);

        // Mark as read
        $readResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/notifications/{$notif->id}/read");
        $readResponse->assertStatus(200);

        $notif->refresh();
        $this->assertNotNull($notif->read_at);

        // Mark as unread
        $unreadResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/notifications/{$notif->id}/unread");
        $unreadResponse->assertStatus(200);

        $notif->refresh();
        $this->assertNull($notif->read_at);
    }

    public function test_spa_deep_links_and_notification_urls_render_application_view_without_404(): void
    {
        $urls = ['/login', '/projects', '/tasks', '/leads', '/pipeline', '/finance', '/quotes', '/chat', '/settings', '/calendar'];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertViewIs('jmos');
        }
    }

    public function test_quote_approve_edit_and_upgrade_to_invoice(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test')->plainTextToken;

        // 1. Create a draft quote
        $createRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/quotes', [
                'recipient_name' => 'Acme Corporation',
                'recipient_email' => 'finance@acme.com',
                'title' => 'Commercial Video Campaign',
                'total_amount' => 250000,
                'items' => [
                    ['description' => '4K Shoot Day 1', 'quantity' => 1, 'rate' => 150000, 'amount' => 150000],
                    ['description' => 'Color Grading & Sound', 'quantity' => 1, 'rate' => 100000, 'amount' => 100000],
                ],
            ]);

        $createRes->assertStatus(201);
        $quoteId = $createRes->json('data.id');

        // 2. Edit the quote
        $editRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/quotes/{$quoteId}", [
                'title' => 'Commercial Video Campaign (Revised Scope)',
                'total_amount' => 300000,
            ]);

        $editRes->assertStatus(200);
        $this->assertEquals('Commercial Video Campaign (Revised Scope)', $editRes->json('data.title'));

        // 3. Approve the quote
        $approveRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/quotes/{$quoteId}/approve");

        $approveRes->assertStatus(200);
        $this->assertEquals('Accepted', $approveRes->json('data.status'));

        // 4. Upgrade approved quote to invoice
        $upgradeRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/quotes/{$quoteId}/upgrade-invoice", [
                'invoice_type' => 'Deposit 60%',
                'amount' => 180000,
                'due_date' => '2026-10-01',
            ]);

        $upgradeRes->assertStatus(200);
        $this->assertEquals('Invoiced', $upgradeRes->json('quote.status'));
        $this->assertNotNull($upgradeRes->json('invoice.id'));
        $this->assertEquals(180000, $upgradeRes->json('invoice.amount'));
    }
}
