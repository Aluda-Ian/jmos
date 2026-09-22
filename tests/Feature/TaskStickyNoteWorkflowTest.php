<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStickyNoteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_tasks_visibility_isolation_between_team_members_and_managers(): void
    {
        $owner = User::where('role', 'owner')->first();
        $manager = User::where('role', 'manager')->first() ?? User::create([
            'name' => 'IT Ops Manager',
            'email' => 'itops@jeotamedia.co.ke',
            'password' => bcrypt('password123'),
            'role' => 'it_manager',
            'type' => 'Full-time',
        ]);

        $member1 = User::create([
            'name' => 'Alice Editor',
            'email' => 'alice@jeotamedia.co.ke',
            'password' => bcrypt('password123'),
            'role' => 'editor',
            'type' => 'Full-time',
        ]);

        $member2 = User::create([
            'name' => 'Bob Sound',
            'email' => 'bob@jeotamedia.co.ke',
            'password' => bcrypt('password123'),
            'role' => 'crew',
            'type' => 'Contract',
        ]);

        $project1 = Project::create([
            'project_name' => 'Alpha Documentary',
            'client' => 'UNEP Kenya',
            'project_type' => 'Documentary',
            'project_manager' => 'Barny Kiome',
        ]);

        $project2 = Project::create([
            'project_name' => 'Beta Commercial',
            'client' => 'Safaricom',
            'project_type' => 'Commercial TVC',
            'project_manager' => 'Amos Muthama',
        ]);

        // Task in Project 1 assigned to Alice
        $task1 = Task::create([
            'project_id' => $project1->id,
            'title' => 'Color Grading Scene 1',
            'stage' => 'in_progress',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $member1->id,
            'assigned_to' => $member1->name,
        ]);

        // Task in Project 2 assigned to Bob
        $task2 = Task::create([
            'project_id' => $project2->id,
            'title' => 'Foley Sound Effects',
            'stage' => 'todo',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $member2->id,
            'assigned_to' => $member2->name,
        ]);

        // 1. Alice queries tasks -> only sees Task 1
        $token1 = $member1->createToken('alice_token')->plainTextToken;
        $resAlice = $this->withHeader('Authorization', 'Bearer '.$token1)->getJson('/api/tasks');
        $resAlice->assertStatus(200);
        $aliceTaskIds = collect($resAlice->json())->pluck('id')->toArray();
        $this->assertContains($task1->id, $aliceTaskIds);
        $this->assertNotContains($task2->id, $aliceTaskIds);

        // 2. Bob queries tasks -> only sees Task 2
        $token2 = $member2->createToken('bob_token')->plainTextToken;
        $resBob = $this->withHeader('Authorization', 'Bearer '.$token2)->getJson('/api/tasks');
        $resBob->assertStatus(200);
        $bobTaskIds = collect($resBob->json())->pluck('id')->toArray();
        $this->assertContains($task2->id, $bobTaskIds);
        $this->assertNotContains($task1->id, $bobTaskIds);

        // 3. Owner queries tasks -> sees all tasks
        $tokenOwner = $owner->createToken('owner_token')->plainTextToken;
        $resOwner = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)->getJson('/api/tasks');
        $resOwner->assertStatus(200);
        $ownerTaskIds = collect($resOwner->json())->pluck('id')->toArray();
        $this->assertContains($task1->id, $ownerTaskIds);
        $this->assertContains($task2->id, $ownerTaskIds);
    }

    public function test_can_add_review_links_and_comments_to_task(): void
    {
        $owner = User::where('role', 'owner')->first();
        $teamUser = User::where('role', 'team')->first();
        $project = Project::first();

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Rough Cut Video Edit',
            'stage' => 'in_progress',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $teamUser->id,
            'assigned_to' => $teamUser->name,
            'sticky_color' => '#FFFBEB',
        ]);

        $tokenTeam = $teamUser->createToken('team_token')->plainTextToken;

        // 1. Add review link
        $resLink = $this->withHeader('Authorization', 'Bearer '.$tokenTeam)
            ->postJson("/api/tasks/{$task->id}/links", [
                'title' => 'Rough Cut v1 Review',
                'url' => 'https://drive.google.com/file/d/123456789/view',
            ]);

        $resLink->assertStatus(200);
        $task->refresh();
        $this->assertCount(1, $task->links);
        $this->assertEquals('Google Drive', $task->links[0]['provider']);
        $this->assertEquals('Rough Cut v1 Review', $task->links[0]['title']);

        // Check owner received notification about deliverable review link
        $notif = AppNotification::where('user_id', $owner->id)
            ->where('title', 'Deliverable Review Link Added')
            ->first();
        $this->assertNotNull($notif);

        // 2. Add recommendation comment
        $resComment = $this->withHeader('Authorization', 'Bearer '.$tokenTeam)
            ->postJson("/api/tasks/{$task->id}/comments", [
                'message' => 'Please review transition at 02:45 mark.',
                'type' => 'recommendation',
            ]);

        $resComment->assertStatus(200);
        $task->refresh();
        $this->assertNotEmpty($task->comments);
        $comments = $task->comments;
        $lastComment = end($comments);
        $this->assertEquals('recommendation', $lastComment['type']);
        $this->assertEquals('Please review transition at 02:45 mark.', $lastComment['message']);
    }

    public function test_project_manager_workflow_send_back_for_changes_and_proceed(): void
    {
        $owner = User::where('role', 'owner')->first();
        $teamUser = User::where('role', 'team')->first();
        $project = Project::first();

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Brand Identity Assets',
            'stage' => 'review_internal',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $teamUser->id,
            'assigned_to' => $teamUser->name,
        ]);

        $tokenOwner = $owner->createToken('pm_token')->plainTextToken;

        // 1. Send back for revisions
        $resRevert = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->postJson("/api/tasks/{$task->id}/workflow", [
                'action' => 'send_back',
                'recommendation' => 'Please adjust typography contrast and export PNGs at 2x resolution.',
            ]);

        $resRevert->assertStatus(200);
        $task->refresh();
        $this->assertEquals('in_progress', $task->stage);

        // Assignee received changes requested notification
        $revNotif = AppNotification::where('user_id', $teamUser->id)
            ->where('title', '⚠️ Changes Requested on Task')
            ->first();
        $this->assertNotNull($revNotif);
        $this->assertStringContainsString('typography contrast', $revNotif->message);

        // 2. Proceed to next stage
        $resProceed = $this->withHeader('Authorization', 'Bearer '.$tokenOwner)
            ->postJson("/api/tasks/{$task->id}/workflow", [
                'action' => 'proceed',
                'recommendation' => 'Revisions verified and approved.',
            ]);

        $resProceed->assertStatus(200);
        $task->refresh();
        $this->assertEquals('review_internal', $task->stage);
    }
}
