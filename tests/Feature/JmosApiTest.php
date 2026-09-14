<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class JmosApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_login_and_get_token(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'ian@jeotamedia.co.ke',
            'password' => 'jeota2024',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role', 'title'],
            ]);
    }

    public function test_user_can_fetch_me_with_valid_token(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Reset auth guard cache for the subsequent request
        $this->app['auth']->forgetGuards();

        // Subsequent call with revoked token should fail with 401
        $subsequent = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $subsequent->assertStatus(401);
    }

    public function test_clients_crud_endpoints(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        // List clients
        $listResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/clients');
        $listResponse->assertStatus(200);

        // Create new client
        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/clients', [
                'client_name' => 'Safari Park Hotel',
                'client_type' => 'Hospitality',
                'contact_person' => 'Peter Karanja',
                'owner' => 'Ian Aluda',
                'projects' => 1,
                'service' => 'Brand Film',
                'project_status' => 'Active',
                'project_value' => 120000,
            ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.client_name', 'Safari Park Hotel');

        $clientId = $createResponse->json('data.id');

        // Update client
        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/clients/{$clientId}", [
                'client_name' => 'Safari Park Resort & Hotel',
            ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.client_name', 'Safari Park Resort & Hotel');

        // Delete client
        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/clients/{$clientId}");
        $deleteResponse->assertStatus(200);
    }

    public function test_pipeline_win_cascade_creates_project_and_invoice(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $deal = Deal::create([
            'title' => 'Acre Insights · Brand Film',
            'client_name' => 'Acre Insights',
            'stage' => 'negotiation',
            'value' => 320000,
            'meta_text' => 'KES 320K',
        ]);

        $winResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/pipeline/{$deal->id}/win");

        $winResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'project',
                'invoice',
            ]);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'stage' => 'won',
            'is_won' => 1,
        ]);
    }

    public function test_invoice_pay_endpoint(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $invoice = Invoice::create([
            'invoice_no' => 'JM-0145',
            'client' => 'Pankaj Productions',
            'type' => 'Deposit 60%',
            'amount' => 132000,
            'method' => null,
            'etims' => true,
            'status' => 'Sent',
        ]);

        $payResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/invoices/{$invoice->id}/pay", [
                'method' => 'M-Pesa',
            ]);

        $payResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'Paid');
    }

    public function test_invoice_update_endpoint(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $invoice = Invoice::create([
            'invoice_no' => 'JM-0146',
            'client' => 'Pankaj Productions',
            'type' => 'Deposit 60%',
            'amount' => 150000,
            'method' => null,
            'etims' => false,
            'status' => 'Sent',
            'due_date' => 'Sep 25',
        ]);

        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/invoices/{$invoice->id}", [
                'invoice_no' => 'JM-0146-REV',
                'client' => 'Pankaj Films International',
                'amount' => 175000,
                'status' => 'Paid',
                'method' => 'Bank Transfer',
                'etims' => true,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.invoice_no', 'JM-0146-REV')
            ->assertJsonPath('data.client', 'Pankaj Films International')
            ->assertJsonPath('data.status', 'Paid')
            ->assertJsonPath('data.method', 'Bank Transfer');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'invoice_no' => 'JM-0146-REV',
            'client' => 'Pankaj Films International',
        ]);
    }

    public function test_invoice_delete_endpoint(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $invoice = Invoice::create([
            'invoice_no' => 'JM-0147',
            'client' => 'Old Client',
            'type' => 'Balance 40%',
            'amount' => 85000,
            'status' => 'Sent',
        ]);

        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/invoices/{$invoice->id}");

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id,
        ]);
    }

    public function test_finance_overview(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $overview = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/finance/overview');

        $overview->assertStatus(200)
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

    public function test_invoice_numbers_are_assigned_automatically_ascending(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        // Invoice 1 created without explicit invoice_no -> should be JM-0146
        $res1 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/invoices', [
                'client' => 'Client Alpha',
                'type' => 'Deposit 60%',
                'amount' => 100000,
            ]);
        $res1->assertStatus(201)
            ->assertJsonPath('data.invoice_no', 'JM-0146');

        // Invoice 2 created without explicit invoice_no -> should be JM-0147
        $res2 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/invoices', [
                'client' => 'Client Beta',
                'type' => 'Deposit 60%',
                'amount' => 120000,
            ]);
        $res2->assertStatus(201)
            ->assertJsonPath('data.invoice_no', 'JM-0147');

        // Delete Invoice 2
        $inv2Id = $res2->json('data.id');
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/invoices/{$inv2Id}")
            ->assertStatus(200);

        // Next number endpoint should still return JM-0147 (or ascending based on highest number)
        $nextRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/invoices/next-number');
        $nextRes->assertStatus(200);

        // Invoice 3 created without explicit invoice_no -> ascending sequentially
        $res3 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/invoices', [
                'client' => 'Client Gamma',
                'type' => 'Full 100%',
                'amount' => 50000,
            ]);
        $res3->assertStatus(201);
        $this->assertNotEmpty($res3->json('data.invoice_no'));
    }

    public function test_can_log_update_and_delete_expense_with_etims_and_notes(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        // 1. Log an expense with eTIMS CU number
        $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/expenses', [
                'name' => '4K Drone Gimbal Hire',
                'category' => 'Equipment',
                'project' => 'Acre Insights Brand Film',
                'amount' => 45000,
                'etr' => 'yes',
                'etims_number' => 'KRA-ETIMS-008129',
                'notes' => 'Hired from Skylark Studios for shoot Day 1',
                'date' => 'Sep 14',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', '4K Drone Gimbal Hire')
            ->assertJsonPath('data.etims_number', 'KRA-ETIMS-008129');

        $expenseId = $createResponse->json('data.id');

        $this->assertDatabaseHas('expenses', [
            'id' => $expenseId,
            'name' => '4K Drone Gimbal Hire',
            'etims_number' => 'KRA-ETIMS-008129',
        ]);

        // 2. Update the expense
        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/expenses/{$expenseId}", [
                'amount' => 42000,
                'receipt_url' => 'http://localhost/uploads/expenses/exp_sample.pdf',
                'receipt_name' => 'Skylark_Invoice_KRA.pdf',
                'notes' => 'Discount applied',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.amount', '42000.00')
            ->assertJsonPath('data.receipt_name', 'Skylark_Invoice_KRA.pdf');

        $this->assertDatabaseHas('expenses', [
            'id' => $expenseId,
            'amount' => 42000,
            'receipt_name' => 'Skylark_Invoice_KRA.pdf',
        ]);

        // 3. Delete the expense
        $deleteResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/expenses/{$expenseId}");

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expenseId,
        ]);
    }

    public function test_expense_receipt_upload_endpoint(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $fakeFile = UploadedFile::fake()->create('etims_vat_invoice.pdf', 120, 'application/pdf');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/expenses/upload-receipt', [
                'file' => $fakeFile,
            ], ['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('receipt_name', 'etims_vat_invoice.pdf');

        $this->assertNotEmpty($response->json('receipt_url'));

        // Cleanup uploaded test file
        $url = $response->json('receipt_url');
        $fileName = basename($url);
        $filePath = public_path('uploads/expenses/'.$fileName);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    public function test_web_routes_render_jmos_app(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200)
            ->assertSee('JEOTA MEDIA')
            ->assertSee('JMOS · Jeota Media Operating System');

        $budgetResponse = $this->get('/budget-calculator');
        $budgetResponse->assertStatus(200)
            ->assertSee('Production Budget Calculator');
    }
}
