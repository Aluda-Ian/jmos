<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Emails every attendee of a calendar event 1 day and 1 hour before it starts,
 * with the Google Meet link pasted on the event.
 */
class MeetingReminderService
{
    /**
     * Send all reminders that are due now.
     *
     * @return array{day: int, hour: int}
     */
    public function sendDue(?Carbon $now = null): array
    {
        $tz = config('jeota.timezone', 'Africa/Nairobi');
        // Event times are stored as local wall-clock times, so compare in local time.
        $local = ($now ?? now())->copy()->timezone($tz);
        $nowStr = $local->format('Y-m-d H:i:s');
        $sent = ['day' => 0, 'hour' => 0];

        $upcoming = CalendarEvent::query()
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '>', $nowStr)
            ->where('start_time', '<=', $local->copy()->addDay()->format('Y-m-d H:i:s'))
            ->where(fn ($q) => $q->whereNull('reminder_day_sent_at')->orWhereNull('reminder_hour_sent_at'))
            ->get();

        foreach ($upcoming as $event) {
            $start = Carbon::parse($event->start_time->format('Y-m-d H:i:s'), $tz);
            $minutesLeft = (int) round($local->diffInMinutes($start, false));

            if ($minutesLeft <= 60 && $event->reminder_hour_sent_at === null) {
                $this->send($event, 'hour', $start, $minutesLeft);
                // The 1-hour reminder replaces a 1-day reminder that never went out.
                $event->forceFill(['reminder_hour_sent_at' => now(), 'reminder_day_sent_at' => $event->reminder_day_sent_at ?? now()])->saveQuietly();
                $sent['hour']++;
            } elseif ($minutesLeft > 60 && $event->reminder_day_sent_at === null) {
                $this->send($event, 'day', $start, $minutesLeft);
                $event->forceFill(['reminder_day_sent_at' => now()])->saveQuietly();
                $sent['day']++;
            }
        }

        return $sent;
    }

    /**
     * Run sendDue() at most once every few minutes, after the response is sent.
     * Keeps reminders going even if the server cron job has not been set up.
     */
    public function sendDueThrottled(): void
    {
        if (! Cache::add('jmos:meeting-reminders:lock', true, now()->addMinutes(5))) {
            return;
        }

        try {
            $this->sendDue();
        } catch (\Throwable $e) {
            Log::warning('Meeting reminders run failed: '.$e->getMessage());
        }
    }

    /**
     * Everyone who should receive the reminder: email => name.
     *
     * @return array<string, string>
     */
    public function recipients(CalendarEvent $event): array
    {
        $recipients = [];

        foreach ((array) ($event->attendee_emails ?? []) as $email) {
            $email = strtolower(trim((string) $email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients[$email] = $recipients[$email] ?? '';
            }
        }

        // Older events only stored names: match them to team members, clients or typed emails.
        foreach (array_filter(array_map('trim', explode(',', (string) $event->attendees))) as $token) {
            if (filter_var($token, FILTER_VALIDATE_EMAIL)) {
                $recipients[strtolower($token)] = $recipients[strtolower($token)] ?? '';

                continue;
            }
            $user = User::whereRaw('LOWER(name) = ?', [strtolower($token)])->first();
            if ($user?->email) {
                $recipients[strtolower($user->email)] = $user->name;

                continue;
            }
            $client = Client::whereRaw('LOWER(client_name) = ?', [strtolower($token)])->first();
            if ($client?->email) {
                $recipients[strtolower($client->email)] = $client->contact_person ?: $client->client_name;
            }
        }

        // Fill in names for team members found by email
        foreach ($recipients as $email => $name) {
            if ($name === '') {
                $recipients[$email] = User::where('email', $email)->value('name') ?? '';
            }
        }

        return $recipients;
    }

    private function send(CalendarEvent $event, string $kind, Carbon $start, int $minutesLeft): void
    {
        $when = $kind === 'hour'
            ? ($minutesLeft <= 5 ? 'starting now' : "starts in {$minutesLeft} minutes")
            : ($start->isTomorrow() ? 'tomorrow at '.$start->format('g:i A') : $start->format('l').' at '.$start->format('g:i A'));

        foreach ($this->recipients($event) as $email => $name) {
            NotificationService::sendMeetingReminder([
                'recipientName' => $name ?: 'there',
                'eventTitle' => $event->title,
                'eventType' => str_replace('_', ' ', $event->event_type),
                'eventDateTime' => $start->format('l, j F Y \a\t g:i A').' (EAT)',
                'whenLabel' => $when,
                'reminderKind' => $kind,
                'location' => $event->location,
                'meetLink' => $event->meet_link,
                'attendees' => $event->attendees,
                'description' => $event->description,
            ], $email);
        }
    }
}
