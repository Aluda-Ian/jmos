<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineProjectWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_can_update_deal_stage_and_delete_deal(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $deal = Deal::create([
            'title' => 'Stanbic Kenya Commercial',
            'client_name' => 'Stanbic Bank',
            'stage' => 'lead',
            'value' => 750000,
        ]);

        // Move stage to negotiation
        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/deals/{$deal->id}", [
                'stage' => 'negotiation',
            ]);

        $res->assertStatus(200);
        $deal->refresh();
        $this->assertEquals('negotiation', $deal->stage);

        // Delete deal
        $delRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/deals/{$deal->id}");

        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }

    public function test_can_save_project_workspace_links_and_sync_calendar(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $project = Project::create([
            'project_name' => 'Chiwo Connect',
            'client' => 'Chiwo Brands',
            'stage' => 'concept',
            'status' => 'In progress',
            'deadline' => '2026-10-15',
            'budget' => 450000,
        ]);

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/projects/{$project->id}", [
                'drive_link' => 'https://drive.google.com/drive/folders/chiwo-connect-assets',
                'brief_link' => 'https://docs.google.com/document/d/brief',
                'treatment_link' => 'https://docs.google.com/presentation/d/treatment',
                'playbook_link' => 'https://notion.so/playbook',
                'notes' => 'Complete documentary style shoot in Nairobi & Naivasha.',
                'comments' => [
                    ['user_name' => 'Ian Aluda', 'text' => 'Drive assets linked', 'created_at' => '10:00 AM'],
                ],
                'project_manager' => 'Amos Muthama',
                'deadline' => '2026-10-20',
            ]);

        $res->assertStatus(200);
        $project->refresh();
        $this->assertEquals('https://drive.google.com/drive/folders/chiwo-connect-assets', $project->drive_link);
        $this->assertEquals('https://docs.google.com/document/d/brief', $project->brief_link);
        $this->assertEquals('Amos Muthama', $project->project_manager);
        $this->assertCount(1, $project->comments);

        // Verify calendar event was synced
        $this->assertDatabaseHas('calendar_events', [
            'related_type' => 'project',
            'related_id' => $project->id,
            'event_type' => 'deadline',
        ]);
    }

    public function test_task_creation_with_due_date_creates_calendar_event_and_notification(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $project = Project::create([
            'project_name' => 'Petale Atelier Launch',
            'client' => 'Petale',
        ]);

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/tasks', [
                'project_id' => $project->id,
                'title' => 'Color Grading Master Pass',
                'stage' => 'in_progress',
                'assigned_to' => 'Stephen Otieno',
                'due_date' => '2026-10-05',
            ]);

        $res->assertStatus(201);
        $taskId = $res->json('data.id');

        // Calendar event created
        $this->assertDatabaseHas('calendar_events', [
            'related_type' => 'task',
            'related_id' => $taskId,
            'event_type' => 'deadline',
        ]);

        // In-app notification created for assignee (Stephen Otieno)
        $stephen = User::where('email', 'stephen@jeotamedia.co.ke')->first();
        $this->assertDatabaseHas('notifications', [
            'user_id' => $stephen->id,
            'type' => 'task',
        ]);
    }

    public function test_kra_gava_settings_persistence(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/settings', [
                'settings' => [
                    ['group' => 'kra', 'key' => 'kra_pin', 'value' => 'P099887766Z'],
                    ['group' => 'kra', 'key' => 'kra_status', 'value' => 'connected'],
                ],
            ]);

        $res->assertStatus(200);
        $this->assertEquals('P099887766Z', SystemSetting::getVal('kra_pin'));
        $this->assertEquals('connected', SystemSetting::getVal('kra_status'));
    }

    public function test_can_create_and_update_internal_project_category(): void
    {
        $user = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $token = $user->createToken('test')->plainTextToken;

        // Create internal project
        $res = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/projects', [
                'project_name' => 'JMOS v2.5 Core Architecture',
                'client' => 'Jeota Media (Internal)',
                'project_type' => 'Internal System Development',
                'category' => 'internal',
                'stage' => 'concept',
                'status' => 'In progress',
                'deadline' => '2026-11-01',
            ]);

        $res->assertStatus(201);
        $projectData = $res->json('data');
        $this->assertEquals('internal', $projectData['category']);
        $this->assertTrue($projectData['is_internal']);

        $projectId = $projectData['id'];

        // Retrieve and filter by category
        $filterRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/projects?category=internal');
        $filterRes->assertStatus(200);
        $internalList = collect($filterRes->json());
        $this->assertTrue($internalList->contains('id', $projectId));

        // Update category
        $updateRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/projects/{$projectId}", [
                'category' => 'internal',
                'project_type' => 'Internal Operations & R&D',
            ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Internal Operations & R&D', $updateRes->json('data.project_type'));
    }
}
