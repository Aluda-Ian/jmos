<?php

namespace Tests\Feature;

use App\Mail\MeetingReminderMail;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Services\MeetingReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MeetingRemindersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Event times are local (Nairobi) wall-clock times.
     */
    private function eventAt(string $localStart, array $extra = []): CalendarEvent
    {
        return CalendarEvent::create(array_merge([
            'title' => 'Client Briefing',
            'event_type' => 'meeting',
            'start_time' => $localStart,
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
            'attendees' => 'Amina Otieno, client@brand.co.ke',
            'attendee_emails' => ['client@brand.co.ke'],
            'status' => 'confirmed',
        ], $extra));
    }

    public function test_attendees_get_a_one_day_and_a_one_hour_reminder_with_the_meet_link(): void
    {
        Mail::fake();
        User::factory()->create(['name' => 'Amina Otieno', 'email' => 'amina@jeotamedia.co.ke']);
        // Now = 10:00 Nairobi (07:00 UTC); meeting tomorrow 09:00 Nairobi
        $now = Carbon::parse('2026-10-08 07:00:00', 'UTC');
        $event = $this->eventAt('2026-10-09 09:00:00');
        $service = app(MeetingReminderService::class);

        $this->assertSame(['day' => 1, 'hour' => 0], $service->sendDue($now));
        Mail::assertSent(MeetingReminderMail::class, 2);
        Mail::assertSent(MeetingReminderMail::class, fn ($m) => $m->hasTo('client@brand.co.ke') && $m->data['meetLink'] === 'https://meet.google.com/abc-defg-hij' && $m->data['reminderKind'] === 'day');
        Mail::assertSent(MeetingReminderMail::class, fn ($m) => $m->hasTo('amina@jeotamedia.co.ke'));

        // Running again does not repeat the day reminder
        $this->assertSame(['day' => 0, 'hour' => 0], $service->sendDue($now->copy()->addMinutes(5)));

        // 08:10 Nairobi next day = 50 minutes before
        $this->assertSame(['day' => 0, 'hour' => 1], $service->sendDue(Carbon::parse('2026-10-09 05:10:00', 'UTC')));
        Mail::assertSent(MeetingReminderMail::class, fn ($m) => $m->data['reminderKind'] === 'hour' && str_contains($m->data['whenLabel'], '50 minutes'));
        Mail::assertSent(MeetingReminderMail::class, 4);

        $event->refresh();
        $this->assertNotNull($event->reminder_day_sent_at);
        $this->assertNotNull($event->reminder_hour_sent_at);
    }

    public function test_past_cancelled_and_far_future_events_are_skipped(): void
    {
        Mail::fake();
        $now = Carbon::parse('2026-10-08 07:00:00', 'UTC'); // 10:00 Nairobi
        $this->eventAt('2026-10-08 09:00:00');                         // already started
        $this->eventAt('2026-10-12 09:00:00');                         // more than a day away
        $this->eventAt('2026-10-08 15:00:00', ['status' => 'cancelled']);

        $this->assertSame(['day' => 0, 'hour' => 0], app(MeetingReminderService::class)->sendDue($now));
        Mail::assertNothingSent();
    }

    public function test_the_reminder_command_runs(): void
    {
        Mail::fake();
        $this->artisan('jmos:meeting-reminders')->assertSuccessful();
    }
}
