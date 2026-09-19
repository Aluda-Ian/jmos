<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarVisibilityRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_invoices_are_only_visible_to_owner_and_finance_on_calendar(): void
    {
        // 1. Create an unpaid invoice with a due date
        $invoice = Invoice::create([
            'invoice_no' => 'JM-TEST-INV',
            'client' => 'Acme Global',
            'type' => 'Retainer',
            'amount' => 250000,
            'due_date' => '2026-09-25',
            'status' => 'Pending',
            'etims' => true,
        ]);

        $owner = User::where('role', 'owner')->first();
        $finance = User::where('role', 'finance')->first() ?? User::factory()->create(['role' => 'finance', 'name' => 'Finance Lead', 'email' => 'finance-test@jeotamedia.co.ke']);
        $manager = User::where('role', 'manager')->first(); // Ian Aluda
        $team = User::where('role', 'team')->first(); // Stephen Otieno

        $ownerToken = $owner->createToken('test_owner')->plainTextToken;
        $financeToken = $finance->createToken('test_finance')->plainTextToken;
        $managerToken = $manager->createToken('test_manager')->plainTextToken;
        $teamToken = $team->createToken('test_team')->plainTextToken;

        // Owner sees invoice
        $ownerRes = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->getJson('/api/calendar/events');
        $ownerRes->assertStatus(200);
        $ownerEvents = collect($ownerRes->json('data'));
        $this->assertTrue(
            $ownerEvents->contains(fn ($e) => ($e['event_type'] ?? '') === 'invoice' && str_contains($e['title'] ?? '', 'JM-TEST-INV')),
            'Owner should see invoice deadlines on calendar'
        );

        // Finance sees invoice
        $financeRes = $this->withHeader('Authorization', 'Bearer '.$financeToken)
            ->getJson('/api/calendar/events');
        $financeRes->assertStatus(200);
        $financeEvents = collect($financeRes->json('data'));
        $this->assertTrue(
            $financeEvents->contains(fn ($e) => ($e['event_type'] ?? '') === 'invoice' && str_contains($e['title'] ?? '', 'JM-TEST-INV')),
            'Finance team should see invoice deadlines on calendar'
        );

        // Manager (Ian Aluda - IT specialist) CANNOT see invoice
        $managerRes = $this->withHeader('Authorization', 'Bearer '.$managerToken)
            ->getJson('/api/calendar/events');
        $managerRes->assertStatus(200);
        $managerEvents = collect($managerRes->json('data'));
        $this->assertFalse(
            $managerEvents->contains(fn ($e) => ($e['event_type'] ?? '') === 'invoice'),
            'IT Specialist / System Manager must not see invoice deadlines on calendar'
        );

        // Team member CANNOT see invoice
        $teamRes = $this->withHeader('Authorization', 'Bearer '.$teamToken)
            ->getJson('/api/calendar/events');
        $teamRes->assertStatus(200);
        $teamEvents = collect($teamRes->json('data'));
        $this->assertFalse(
            $teamEvents->contains(fn ($e) => ($e['event_type'] ?? '') === 'invoice'),
            'Regular team members must not see invoice deadlines on calendar'
        );
    }

    public function test_team_members_only_see_projects_involving_them_on_calendar(): void
    {
        // Project A managed by Stephen Otieno
        $projA = Project::create([
            'project_name' => 'Stephen Video Doc',
            'client' => 'Documentary Africa',
            'project_type' => 'Documentary',
            'project_manager' => 'Stephen Otieno',
            'stage' => 'edit',
            'status' => 'On track',
            'priority' => 'High',
            'deadline' => '2026-09-28',
            'budget' => 300000,
        ]);

        // Project B managed by Amos Muthama (no relation to Stephen)
        $projB = Project::create([
            'project_name' => 'Amos Drone Commercial',
            'client' => 'Safari Park',
            'project_type' => 'Brand film',
            'project_manager' => 'Amos Muthama',
            'stage' => 'shoot',
            'status' => 'On track',
            'priority' => 'Medium',
            'deadline' => '2026-09-29',
            'budget' => 450000,
        ]);

        $stephen = User::where('email', 'stephen@jeotamedia.co.ke')->first();
        $stephenToken = $stephen->createToken('test_stephen')->plainTextToken;

        $stephenRes = $this->withHeader('Authorization', 'Bearer '.$stephenToken)
            ->getJson('/api/calendar/events');
        $stephenRes->assertStatus(200);
        $stephenEvents = collect($stephenRes->json('data'));

        // Stephen should see Project A
        $this->assertTrue(
            $stephenEvents->contains(fn ($e) => ($e['id'] ?? '') === 'proj_'.$projA->id),
            'Stephen Otieno should see his own managed project deadline'
        );

        // Stephen should NOT see Project B
        $this->assertFalse(
            $stephenEvents->contains(fn ($e) => ($e['id'] ?? '') === 'proj_'.$projB->id),
            'Stephen Otieno should NOT see projects managed by others that do not involve him'
        );

        // Owner Barny Kiome sees both
        $owner = User::where('role', 'owner')->first();
        $ownerToken = $owner->createToken('test_owner')->plainTextToken;
        $ownerRes = $this->withHeader('Authorization', 'Bearer '.$ownerToken)
            ->getJson('/api/calendar/events');
        $ownerEvents = collect($ownerRes->json('data'));

        $this->assertTrue($ownerEvents->contains(fn ($e) => ($e['id'] ?? '') === 'proj_'.$projA->id));
        $this->assertTrue($ownerEvents->contains(fn ($e) => ($e['id'] ?? '') === 'proj_'.$projB->id));
    }

    public function test_project_with_task_assigned_to_user_is_visible_on_their_calendar(): void
    {
        // Project managed by Barny Kiome
        $proj = Project::create([
            'project_name' => 'Platform Infrastructure Overhaul',
            'client' => 'Jeota Internal',
            'project_type' => 'System',
            'project_manager' => 'Barny Kiome',
            'stage' => 'pre-pro',
            'status' => 'On track',
            'priority' => 'High',
            'deadline' => '2026-10-05',
            'budget' => 100000,
        ]);

        // Task assigned to Ian Aluda
        Task::create([
            'project_id' => $proj->id,
            'title' => 'Configure SMTP & Server Backups',
            'stage' => 'in_progress',
            'assigned_to' => 'Ian Aluda',
            'assigned_initials' => 'IA',
            'assigned_color' => '#2B8A5A',
        ]);

        $ian = User::where('email', 'ian@jeotamedia.co.ke')->first();
        $ianToken = $ian->createToken('test_ian')->plainTextToken;

        $ianRes = $this->withHeader('Authorization', 'Bearer '.$ianToken)
            ->getJson('/api/calendar/events');
        $ianRes->assertStatus(200);
        $ianEvents = collect($ianRes->json('data'));

        $this->assertTrue(
            $ianEvents->contains(fn ($e) => ($e['id'] ?? '') === 'proj_'.$proj->id),
            'Ian Aluda should see project deadline when assigned to a task inside that project'
        );
    }

    public function test_calendar_events_and_meetings_only_visible_to_involved_users(): void
    {
        // Private meeting between Amos and Lesley
        $privateMeeting = CalendarEvent::create([
            'title' => 'Drone Reel Scripting',
            'event_type' => 'meeting',
            'start_time' => '2026-09-22T14:00:00',
            'end_time' => '2026-09-22T15:00:00',
            'attendees' => 'Amos Muthama, Lesley Chacha',
            'created_by' => 'Amos Muthama',
            'status' => 'confirmed',
        ]);

        // Company-wide all-hands meeting
        $allHands = CalendarEvent::create([
            'title' => 'Jeota All-Hands General Meeting',
            'event_type' => 'meeting',
            'start_time' => '2026-09-23T09:00:00',
            'end_time' => '2026-09-23T10:00:00',
            'attendees' => 'All Team',
            'created_by' => 'Barny Kiome',
            'status' => 'confirmed',
        ]);

        $amos = User::where('email', 'amos@jeotamedia.co.ke')->first();
        $ian = User::where('email', 'ian@jeotamedia.co.ke')->first();

        $amosToken = $amos->createToken('test_amos')->plainTextToken;
        $ianToken = $ian->createToken('test_ian')->plainTextToken;

        // Amos sees private meeting and all-hands
        $amosRes = $this->withHeader('Authorization', 'Bearer '.$amosToken)
            ->getJson('/api/calendar/events');
        $amosEvents = collect($amosRes->json('data'));
        $this->assertTrue($amosEvents->contains(fn ($e) => ($e['id'] ?? '') === 'evt_'.$privateMeeting->id));
        $this->assertTrue($amosEvents->contains(fn ($e) => ($e['id'] ?? '') === 'evt_'.$allHands->id));

        // Ian sees all-hands, but does NOT see private meeting
        $ianRes = $this->withHeader('Authorization', 'Bearer '.$ianToken)
            ->getJson('/api/calendar/events');
        $ianEvents = collect($ianRes->json('data'));
        $this->assertTrue($ianEvents->contains(fn ($e) => ($e['id'] ?? '') === 'evt_'.$allHands->id));
        $this->assertFalse(
            $ianEvents->contains(fn ($e) => ($e['id'] ?? '') === 'evt_'.$privateMeeting->id),
            'Ian should not see private meetings where he is not an attendee or creator'
        );
    }
}
