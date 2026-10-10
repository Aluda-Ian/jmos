<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarMeetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_scheduling_event_saves_the_pasted_google_meet_link_and_attendee_emails(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/calendar/events', [
                'title' => 'Executive Client Briefing',
                'event_type' => 'meeting',
                'start_time' => '2026-09-20T10:00:00',
                'end_time' => '2026-09-20T11:00:00',
                'location' => '',
                'attendees' => 'Barny Kiome, Client Lead',
                'description' => 'Initial scope discussion',
                'meet_link' => 'https://meet.google.com/abc-defg-hij',
                'attendee_emails' => ['Client@Brand.co.ke', 'barny@jeotamedia.co.ke'],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $eventData = $response->json('data');
        $this->assertSame('https://meet.google.com/abc-defg-hij', $eventData['meet_link']);
        $this->assertEquals('Google Meet', $eventData['location']);
        $this->assertSame(['client@brand.co.ke', 'barny@jeotamedia.co.ke'], $eventData['attendee_emails']);

        // Verify stored in DB
        $dbEvent = CalendarEvent::find($eventData['id']);
        $this->assertNotNull($dbEvent);
        $this->assertEquals($eventData['meet_link'], $dbEvent->meet_link);
    }

    public function test_calendar_index_includes_meet_links(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        CalendarEvent::create([
            'title' => 'Team Sync',
            'event_type' => 'meeting',
            'start_time' => now()->addDay(),
            'location' => 'Google Meet',
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/calendar/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'meet_link',
                    ],
                ],
            ]);

        $events = $response->json('data');
        $matching = collect($events)->firstWhere('title', 'Team Sync');
        $this->assertNotNull($matching);
        $this->assertEquals('https://meet.google.com/abc-defg-hij', $matching['meet_link']);
    }

    public function test_a_pasted_meet_link_can_be_added_to_an_existing_event(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $event = CalendarEvent::create([
            'title' => 'Shoot Location Scouting',
            'event_type' => 'shoot',
            'start_time' => now()->addDays(2),
            'location' => 'Nairobi Studio',
            'meet_link' => null,
        ]);

        // Without a Google connection JMOS must not invent a fake meet.google.com code
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/calendar/events/{$event->id}/meet")
            ->assertStatus(422);
        $this->assertNull($event->fresh()->meet_link);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/calendar/events/{$event->id}/meet", ['meet_link' => 'https://meet.google.com/xyz-abcd-efg']);

        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertEquals('https://meet.google.com/xyz-abcd-efg', $event->fresh()->meet_link);
    }

    public function test_sync_calendar_with_personal_google_account(): void
    {
        $user = User::where('role', 'team')->first() ?? User::first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/calendar/sync', [
                'account_email' => 'personal.stephen@gmail.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'account' => 'personal.stephen@gmail.com',
                'status_label' => 'connected',
            ]);

        $user->refresh();
        $this->assertEquals('personal.stephen@gmail.com', $user->google_calendar_email);
        $this->assertEquals('connected', $user->google_calendar_status);
        $this->assertNotNull($user->google_calendar_synced_at);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'google_connected_account',
            'value' => 'personal.stephen@gmail.com',
        ]);
    }

    public function test_can_check_personal_calendar_sync_status(): void
    {
        $user = User::where('role', 'team')->first() ?? User::first();
        $user->update([
            'google_calendar_email' => 'personal.lead@gmail.com',
            'google_calendar_status' => 'connected',
            'google_calendar_synced_at' => now(),
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/calendar/sync-status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'connected' => true,
                'account' => 'personal.lead@gmail.com',
            ]);
    }

    public function test_can_disconnect_personal_google_calendar(): void
    {
        $user = User::where('role', 'team')->first() ?? User::first();
        $user->update([
            'google_calendar_email' => 'personal.lead@gmail.com',
            'google_calendar_status' => 'connected',
            'google_calendar_synced_at' => now(),
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/calendar/disconnect');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'status_label' => 'disconnected',
            ]);

        $user->refresh();
        $this->assertNull($user->google_calendar_email);
        $this->assertEquals('disconnected', $user->google_calendar_status);
        $this->assertNull($user->google_calendar_synced_at);
    }

    public function test_can_schedule_status_meeting_without_a_meet_link(): void
    {
        $user = User::where('role', 'owner')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/calendar/events', [
                'title' => 'Weekly Ops Status Meeting',
                'event_type' => 'status_meeting',
                'start_time' => '2026-09-22T09:00:00',
                'end_time' => '2026-09-22T10:00:00',
                'location' => 'Boardroom / Meet',
                'attendees' => 'All Team',
                'description' => 'Weekly operational review and blockers',
                'generate_meet' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'event_type' => 'status_meeting',
                    'title' => 'Weekly Ops Status Meeting',
                ],
            ]);

        $eventData = $response->json('data');
        $this->assertNull($eventData['meet_link']);

        $dbEvent = CalendarEvent::find($eventData['id']);
        $this->assertNotNull($dbEvent);
        $this->assertEquals('status_meeting', $dbEvent->event_type);
        $this->assertEquals('Weekly Ops Status Meeting', $dbEvent->title);
    }
}
