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

    public function test_scheduling_event_auto_generates_google_meet_link(): void
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
                'generate_meet' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $eventData = $response->json('data');
        $this->assertNotEmpty($eventData['meet_link']);
        $this->assertStringStartsWith('https://meet.google.com/', $eventData['meet_link']);
        $this->assertEquals('Google Meet', $eventData['location']);

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

    public function test_generate_meet_endpoint_assigns_link_to_existing_event(): void
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

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/calendar/events/{$event->id}/meet");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $meetLink = $response->json('data.meet_link');
        $this->assertNotEmpty($meetLink);
        $this->assertStringStartsWith('https://meet.google.com/', $meetLink);

        $event->refresh();
        $this->assertEquals($meetLink, $event->meet_link);
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
}
