<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_fetch_rich_client_details_with_projects_and_invoices(): void
    {
        $user = User::where('email', 'barny@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $client = Client::create([
            'client_name' => 'Safari Park Hotel',
            'client_type' => 'Hospitality',
            'contact_person' => 'Peter Karanja',
            'email' => 'peter@safaripark.co.ke',
            'phone' => '+254712345678',
            'address' => 'Thika Road, Nairobi',
            'website' => 'https://safariparkhotel.co.ke',
            'owner' => 'Barny Kiome',
            'service' => 'Brand film',
            'project_status' => 'Active',
            'project_value' => 450000,
            'notes' => 'Key hospitality stakeholder for annual corporate documentary series.',
        ]);

        $project1 = Project::create([
            'project_name' => 'Safari Park Brand Showcase',
            'client' => 'Safari Park Hotel',
            'status' => 'On track',
            'budget' => 300000,
        ]);

        $project2 = Project::create([
            'project_name' => 'Safari Park Chef Special Promo',
            'client' => 'Safari Park Hotel',
            'status' => 'Completed',
            'budget' => 150000,
        ]);

        $invoice = Invoice::create([
            'invoice_no' => 'JM-0991',
            'client' => 'Safari Park Hotel',
            'type' => 'Deposit',
            'amount' => 200000,
            'status' => 'Paid',
        ]);

        $event = CalendarEvent::create([
            'title' => 'Shoot: Safari Park Hotel Grounds',
            'description' => 'Commercial 4K drone & interview shoot for Safari Park Hotel',
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHours(4),
            'event_type' => 'shoot',
        ]);

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/clients/{$client->id}");

        $res->assertStatus(200)
            ->assertJsonPath('client_name', 'Safari Park Hotel')
            ->assertJsonPath('email', 'peter@safaripark.co.ke')
            ->assertJsonPath('phone', '+254712345678')
            ->assertJsonPath('address', 'Thika Road, Nairobi')
            ->assertJsonPath('website', 'https://safariparkhotel.co.ke')
            ->assertJsonPath('stats.total_projects', 2)
            ->assertJsonPath('stats.active_projects', 1)
            ->assertJsonPath('stats.completed_projects', 1)
            ->assertJsonPath('stats.total_project_value', 450000)
            ->assertJsonPath('stats.total_invoiced', 200000)
            ->assertJsonPath('stats.total_paid', 200000);

        $data = $res->json();
        $this->assertCount(2, $data['projects']);
        $this->assertCount(1, $data['invoices']);
        $this->assertCount(1, $data['events']);
    }

    public function test_can_update_client_details_and_sync_related_records(): void
    {
        $user = User::where('email', 'barny@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $client = Client::create([
            'client_name' => 'Original Brand Ltd',
            'client_type' => 'Corporate',
            'contact_person' => 'Jane Doe',
            'project_value' => 100000,
        ]);

        $project = Project::create([
            'project_name' => 'Brand Launch Video',
            'client' => 'Original Brand Ltd',
            'budget' => 100000,
        ]);

        $invoice = Invoice::create([
            'invoice_no' => 'JM-0992',
            'client' => 'Original Brand Ltd',
            'amount' => 50000,
            'status' => 'Pending',
        ]);

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/clients/{$client->id}", [
                'client_name' => 'Renamed Brand Holdings',
                'contact_person' => 'Jane Smith',
                'email' => 'jane@brandholdings.com',
                'phone' => '+254700112233',
                'address' => 'Westlands Commercial Center',
                'website' => 'https://brandholdings.com',
                'notes' => 'Updated contract terms for Q4 2026',
            ]);

        $res->assertStatus(200);

        $client->refresh();
        $this->assertEquals('Renamed Brand Holdings', $client->client_name);
        $this->assertEquals('jane@brandholdings.com', $client->email);
        $this->assertEquals('+254700112233', $client->phone);
        $this->assertEquals('Westlands Commercial Center', $client->address);
        $this->assertEquals('https://brandholdings.com', $client->website);
        $this->assertEquals('Updated contract terms for Q4 2026', $client->notes);

        // Verify cascading update to project and invoice
        $project->refresh();
        $this->assertEquals('Renamed Brand Holdings', $project->client);

        $invoice->refresh();
        $this->assertEquals('Renamed Brand Holdings', $invoice->client);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'Client',
            'action' => 'UPDATE',
        ]);
    }
}
