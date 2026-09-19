<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_can_be_listed_with_permission_catalog(): void
    {
        $response = $this->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'catalog',
            ]);

        $this->assertDatabaseHas('roles', ['slug' => 'owner']);
        $this->assertDatabaseHas('roles', ['slug' => 'manager']);
        $this->assertDatabaseHas('roles', ['slug' => 'team']);
    }

    public function test_admin_can_create_custom_role_with_specific_permissions(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'admin@jeotamedia.co.ke',
        ]);

        $payload = [
            'name' => 'Lead Video Producer',
            'description' => 'Oversees project timelines, deliverables, and quotations',
            'color' => '#2B8A5A',
            'permissions' => [
                'dashboard.view',
                'projects.view',
                'projects.manage',
                'quotes.view',
                'quotes.manage',
                'chat.access',
            ],
        ];

        $response = $this->actingAs($owner)->postJson('/api/roles', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Lead Video Producer',
            'slug' => 'lead-video-producer',
            'is_system' => false,
        ]);
    }

    public function test_custom_role_permissions_can_be_updated(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $role = Role::create([
            'name' => 'Field Director',
            'slug' => 'field-director',
            'description' => 'Shoots and field production',
            'permissions' => ['projects.view', 'tasks.manage'],
            'is_system' => false,
        ]);

        $response = $this->actingAs($owner)->postJson("/api/roles/{$role->id}", [
            'permissions' => ['projects.view', 'tasks.manage', 'calendar.view', 'calendar.manage'],
        ]);

        $response->assertStatus(200);

        $role->refresh();
        $this->assertContains('calendar.manage', $role->permissions);
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        $response = $this->actingAs($owner)->deleteJson("/api/roles/{$ownerRole->id}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);

        $this->assertDatabaseHas('roles', ['slug' => 'owner']);
    }

    public function test_custom_role_can_be_deleted_and_users_reassigned(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $customRole = Role::create([
            'name' => 'Intern Editor',
            'slug' => 'intern-editor',
            'permissions' => ['projects.view'],
            'is_system' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'intern-editor',
            'role_id' => $customRole->id,
        ]);

        $response = $this->actingAs($owner)->deleteJson("/api/roles/{$customRole->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);

        $user->refresh();
        $this->assertEquals('team', $user->role);
    }

    public function test_user_has_permission_evaluates_role_and_wildcards(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->assertTrue($owner->hasPermission('finance.view'));
        $this->assertTrue($owner->hasPermission('system.upgrade'));
        $this->assertTrue($owner->hasPermission('anything.arbitrary'));

        $manager = User::factory()->create(['role' => 'manager']);
        $this->assertTrue($manager->hasPermission('projects.manage'));
        $this->assertTrue($manager->hasPermission('settings.manage'));

        $customRole = Role::create([
            'name' => 'Copywriter',
            'slug' => 'copywriter',
            'permissions' => ['projects.view', 'chat.access'],
            'is_system' => false,
        ]);

        $copywriter = User::factory()->create([
            'role' => 'copywriter',
            'role_id' => $customRole->id,
        ]);

        $this->assertTrue($copywriter->hasPermission('projects.view'));
        $this->assertTrue($copywriter->hasPermission('chat.access'));
        $this->assertFalse($copywriter->hasPermission('finance.view'));
        $this->assertFalse($copywriter->hasPermission('system.upgrade'));
    }

    public function test_admin_can_assign_custom_permissions_override_to_user(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $teamUser = User::factory()->create(['role' => 'team']);

        $response = $this->actingAs($owner)->postJson("/api/users/{$teamUser->id}/permissions", [
            'permissions' => ['quotes.view', 'quotes.manage'],
        ]);

        $response->assertStatus(200);

        $teamUser->refresh();
        $this->assertTrue($teamUser->hasPermission('quotes.manage'));
    }
}
