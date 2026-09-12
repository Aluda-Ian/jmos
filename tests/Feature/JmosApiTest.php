<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
