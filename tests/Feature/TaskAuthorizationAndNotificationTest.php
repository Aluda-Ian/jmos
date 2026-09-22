<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAuthorizationAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_task_assignee_can_advance_task_stage(): void
    {
        $owner = User::where('role', 'owner')->first();
        $teamUser = User::where('role', 'team')->first();

        $project = Project::first();

        // Create task assigned by owner to team user
        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Color Grading & Sound Design',
            'stage' => 'todo',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $teamUser->id,
            'assigned_to' => $teamUser->name,
        ]);

        $token = $teamUser->createToken('team_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/tasks/{$task->id}", [
                'stage' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Task updated.',
            ]);

        $task->refresh();
        $this->assertEquals('in_progress', $task->stage);

        // Assigner should have received a notification
        $assignerNotif = AppNotification::where('user_id', $owner->id)
            ->where('title', 'Task Stage Updated')
            ->first();

        $this->assertNotNull($assignerNotif);
        $this->assertStringContainsString('In Progress', $assignerNotif->message);
    }

    public function test_task_assigner_can_advance_task_stage(): void
    {
        $owner = User::where('role', 'owner')->first();
        $teamUser = User::where('role', 'team')->first();
        $project = Project::first();

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Rough Cut Assembly',
            'stage' => 'in_progress',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $teamUser->id,
            'assigned_to' => $teamUser->name,
        ]);

        $token = $owner->createToken('owner_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/tasks/{$task->id}", [
                'stage' => 'review_internal',
            ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals('review_internal', $task->stage);

        // Assignee should have received a notification
        $assigneeNotif = AppNotification::where('user_id', $teamUser->id)
            ->where('title', 'Task Stage Updated')
            ->first();

        $this->assertNotNull($assigneeNotif);
        $this->assertStringContainsString('Internal Review', $assigneeNotif->message);
    }

    public function test_unrelated_team_member_cannot_advance_task_stage(): void
    {
        $owner = User::where('role', 'owner')->first();
        $teamUser1 = User::where('role', 'team')->first();

        // Create another distinct team member
        $teamUser2 = User::create([
            'name' => 'Stranger Member',
            'email' => 'stranger@jeotamedia.co.ke',
            'password' => bcrypt('secret123'),
            'role' => 'team',
            'type' => 'Full-time',
        ]);

        $project = Project::first();

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'Private Video Render',
            'stage' => 'todo',
            'assigned_by_id' => $owner->id,
            'assigned_to_id' => $teamUser1->id,
            'assigned_to' => $teamUser1->name,
        ]);

        $token = $teamUser2->createToken('stranger_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/tasks/{$task->id}", [
                'stage' => 'in_progress',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized. Only the assigned team member or the task assigner can advance this task.',
            ]);

        $task->refresh();
        $this->assertEquals('todo', $task->stage);
    }

    public function test_notification_isolation_prevents_leaks_between_users(): void
    {
        $userA = User::where('role', 'owner')->first();
        $userB = User::where('role', 'team')->first();

        AppNotification::create([
            'user_id' => $userA->id,
            'type' => 'secret',
            'title' => 'Confidential Financial Alert',
            'message' => 'Confidential figures for Executive only',
            'link' => 'finance',
        ]);

        $tokenB = $userB->createToken('token_b')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenB)
            ->getJson('/api/notifications');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();

        $this->assertNotContains('Confidential Financial Alert', $titles);
    }
}
