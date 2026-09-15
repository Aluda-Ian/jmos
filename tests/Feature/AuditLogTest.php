<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_can_be_recorded_programmatically(): void
    {
        $user = User::factory()->create([
            'name' => 'Barny Kiome',
            'role' => 'owner',
            'email' => 'barny@jeotamedia.co.ke',
        ]);

        $log = AuditLog::record(
            'CREATE',
            "Created live project 'Brand Film Moyo'",
            'Project',
            101,
            ['stage' => 'brief', 'budget' => 350000],
            null,
            $user
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'user_name' => 'Barny Kiome',
            'user_role' => 'owner',
            'action' => 'CREATE',
            'entity_type' => 'Project',
            'entity_id' => '101',
        ]);
    }

    public function test_owner_and_it_manager_can_access_audit_logs_api(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Barny Kiome',
            'email' => 'barny@jeotamedia.co.ke',
        ]);

        AuditLog::record('SYSTEM', 'Database snapshot generated', 'System', null, [], null, $owner);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'stats' => ['total', 'today', 'creates', 'updates', 'deletes', 'auth', 'system'],
            ]);
    }

    public function test_unauthorized_role_cannot_access_audit_logs(): void
    {
        $teamUser = User::factory()->create([
            'role' => 'team',
            'name' => 'Editor Team',
            'email' => 'editor@jeotamedia.co.ke',
        ]);

        Sanctum::actingAs($teamUser);

        $response = $this->getJson('/api/audit-logs');

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_audit_log_filter_by_action(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'name' => 'Ian Aluda',
            'email' => 'ian@jeotamedia.co.ke',
        ]);

        AuditLog::record('CREATE', 'Created project A', 'Project', 1, [], null, $manager);
        AuditLog::record('DELETE', 'Deleted project B', 'Project', 2, [], null, $manager);

        Sanctum::actingAs($manager);

        $response = $this->getJson('/api/audit-logs?action=DELETE');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertEquals('DELETE', $item['action']);
        }
    }

    public function test_client_side_audit_event_can_be_stored(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'name' => 'Ian Aluda',
        ]);

        Sanctum::actingAs($manager);

        $response = $this->postJson('/api/audit-logs', [
            'action' => 'AUTH',
            'description' => 'User enabled browser push notifications on Desktop',
            'entity_type' => 'System',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'AUTH',
            'description' => 'User enabled browser push notifications on Desktop',
        ]);
    }

    public function test_project_creation_automatically_records_audit_log(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Barny Kiome',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/projects', [
            'project_name' => 'New Commercial Campaign',
            'client' => 'Pankaj Social Service',
            'stage' => 'brief',
            'status' => 'On track',
            'deadline' => '2026-10-15',
            'budget' => 250000,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE',
            'entity_type' => 'Project',
        ]);
    }

    public function test_schedule_shoot_event_automatically_records_audit_log(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Barny Kiome',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/calendar/events', [
            'title' => 'Commercial Shoot — Nairobi West',
            'event_type' => 'shoot',
            'start_time' => '2026-09-20 09:00:00',
            'end_time' => '2026-09-20 17:00:00',
            'generate_meet' => false,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE',
            'entity_type' => 'Shoot',
        ]);
    }
}
