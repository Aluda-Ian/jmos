<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskProjectProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_task_can_be_attached_to_project_and_calculates_completion_percentage(): void
    {
        $project = Project::create([
            'project_name' => 'Safari Rally Docuseries',
            'client' => 'Safari Rally Kenya',
            'stage' => 'production',
            'status' => 'On track',
            'progress_pct' => 0,
        ]);

        $task1 = Task::create([
            'project_id' => $project->id,
            'title' => 'Day 1 Drone Filming',
            'stage' => 'todo',
            'assigned_to' => 'Stephen Otieno',
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'title' => 'Audio Recording and Foley',
            'stage' => 'todo',
            'assigned_to' => 'Barny Kiome',
        ]);

        $project->refresh();
        $this->assertEquals(0, $project->progress_pct);

        // Mark task1 done -> 1 of 2 done = 50%
        $task1->update(['stage' => 'done']);
        $project->refresh();
        $this->assertEquals(50, $project->progress_pct);

        // Mark task2 done -> 2 of 2 done = 100%
        $task2->update(['stage' => 'done']);
        $project->refresh();
        $this->assertEquals(100, $project->progress_pct);
    }

    public function test_moving_task_back_from_done_decreases_project_completion_percentage(): void
    {
        $project = Project::create([
            'project_name' => 'Commercial Ad Spot',
            'client' => 'Safaricom PLC',
            'stage' => 'production',
            'status' => 'On track',
        ]);

        $task1 = Task::create([
            'project_id' => $project->id,
            'title' => 'Color Grading',
            'stage' => 'done',
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'title' => 'Sound Engineering',
            'stage' => 'done',
        ]);

        $project->refresh();
        $this->assertEquals(100, $project->progress_pct);

        // Move task2 back to review_client
        $task2->update(['stage' => 'review_client']);
        $project->refresh();
        $this->assertEquals(50, $project->progress_pct);
    }

    public function test_deleting_a_task_recalculates_project_progress(): void
    {
        $project = Project::create([
            'project_name' => 'Music Video Production',
            'client' => 'Sony Music Africa',
            'stage' => 'brief',
            'status' => 'On track',
        ]);

        $task1 = Task::create([
            'project_id' => $project->id,
            'title' => 'Pre-viz Storyboard',
            'stage' => 'done',
        ]);

        $task2 = Task::create([
            'project_id' => $project->id,
            'title' => 'Extra B-Roll Pass',
            'stage' => 'todo',
        ]);

        $project->refresh();
        $this->assertEquals(50, $project->progress_pct);

        // Delete the todo task -> now 1/1 = 100%
        $task2->delete();
        $project->refresh();
        $this->assertEquals(100, $project->progress_pct);
    }

    public function test_reassigning_task_to_different_project_updates_both_projects(): void
    {
        $projectA = Project::create([
            'project_name' => 'Project Alpha',
            'client' => 'Client A',
            'stage' => 'brief',
        ]);

        $projectB = Project::create([
            'project_name' => 'Project Beta',
            'client' => 'Client B',
            'stage' => 'brief',
        ]);

        $taskA = Task::create([
            'project_id' => $projectA->id,
            'title' => 'Alpha Task 1',
            'stage' => 'done',
        ]);

        $taskB = Task::create([
            'project_id' => $projectB->id,
            'title' => 'Beta Task 1',
            'stage' => 'todo',
        ]);

        $projectA->refresh();
        $projectB->refresh();
        $this->assertEquals(100, $projectA->progress_pct);
        $this->assertEquals(0, $projectB->progress_pct);

        // Move taskA from project A to project B
        $taskA->update(['project_id' => $projectB->id]);

        $projectA->refresh();
        $projectB->refresh();

        // Project A now has 0 tasks -> 0%
        $this->assertEquals(0, $projectA->progress_pct);
        // Project B now has 2 tasks (1 done, 1 todo) -> 50%
        $this->assertEquals(50, $projectB->progress_pct);
    }

    public function test_api_can_create_and_update_task_with_project_id(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $project = Project::create([
            'project_name' => 'Brand Activation',
            'client' => 'KBL Kenya',
            'stage' => 'brief',
        ]);

        // 1. Create task attached to project
        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/tasks', [
                'project_id' => $project->id,
                'title' => 'Key Visual Design',
                'stage' => 'todo',
                'assigned_to' => 'Barny Kiome',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.project_id', $project->id)
            ->assertJsonPath('data.project.project_name', 'Brand Activation');

        $taskId = $response->json('data.id');
        $project->refresh();
        $this->assertEquals(0, $project->progress_pct);

        // 2. Advance task to done via API
        $updateResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/tasks/{$taskId}", [
                'stage' => 'done',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.stage', 'done');

        $project->refresh();
        $this->assertEquals(100, $project->progress_pct);
    }
}
