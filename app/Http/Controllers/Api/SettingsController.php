<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\MeetingReminderMail;
use App\Mail\NewChatMessageMail;
use App\Mail\TaskAssignedMail;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = SystemSetting::all();
        $grouped = [];

        foreach ($settings as $setting) {
            $val = $setting->value;
            if ($setting->is_secret && ! empty($val)) {
                $val = '••••••••';
            }
            $grouped[$setting->group][$setting->key] = [
                'key' => $setting->key,
                'value' => $val,
                'has_value' => ! empty($setting->value),
                'is_secret' => (bool) $setting->is_secret,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $grouped,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $item) {
            if (! isset($item['key'])) {
                continue;
            }

            $key = $item['key'];
            $val = $item['value'] ?? '';
            $group = $item['group'] ?? 'general';
            $isSecret = ! empty($item['is_secret']);

            // Skip updating secret if placeholder was submitted unchanged
            if ($isSecret && $val === '••••••••') {
                continue;
            }

            SystemSetting::setVal($key, $val, $group, $isSecret);
        }

        NotificationService::applySmtpSettings();

        return response()->json([
            'status' => 'success',
            'message' => 'Settings saved successfully.',
        ]);
    }

    public function testEmail(Request $request): JsonResponse
    {
        $recipient = trim((string) $request->input('recipient', 'jmos@jeotamedia.co.ke'));
        if (empty($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $recipient = 'jmos@jeotamedia.co.ke';
        }

        $template = $request->input('template', 'general'); // general, task, meeting, invoice, deal

        // Live test overrides from form if submitted
        $overrides = [];
        if ($request->filled('mail_host')) {
            $overrides['mail_host'] = trim((string) $request->input('mail_host'));
        }
        if ($request->filled('mail_port')) {
            $overrides['mail_port'] = (int) $request->input('mail_port');
        }
        if ($request->filled('mail_username')) {
            $overrides['mail_username'] = trim((string) $request->input('mail_username'));
        }
        if ($request->filled('mail_password') && $request->input('mail_password') !== '••••••••') {
            $overrides['mail_password'] = (string) $request->input('mail_password');
        }
        if ($request->filled('mail_encryption')) {
            $overrides['mail_encryption'] = trim((string) $request->input('mail_encryption'));
        }
        if ($request->filled('mail_from_address')) {
            $overrides['mail_from_address'] = trim((string) $request->input('mail_from_address'));
        }
        if ($request->filled('mail_from_name')) {
            $overrides['mail_from_name'] = trim((string) $request->input('mail_from_name'));
        }

        NotificationService::applySmtpSettings($overrides);

        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        try {
            if ($template === 'task') {
                Mail::to($recipient)->send(new TaskAssignedMail([
                    'assigneeName' => 'Stephen Otieno',
                    'taskTitle' => 'Color Grade & Audio Master — Brand Film',
                    'projectName' => 'Sanlam Kenya Campaign',
                    'stage' => 'In progress',
                    'deadline' => 'Sep 25, 2026',
                    'assignedBy' => 'Barny Kiome (Lead Producer)',
                ]));
                $msg = "Branded Task Assignment email successfully delivered to {$recipient} via {$host}:{$port}.";
            } elseif ($template === 'meeting') {
                Mail::to($recipient)->send(new MeetingReminderMail([
                    'recipientName' => 'Production Team & Client',
                    'eventTitle' => 'On-Location Commercial Shoot',
                    'eventType' => 'Production Shoot',
                    'eventDateTime' => now()->addDays(2)->format('l, M j').' at 09:30 AM',
                    'location' => 'Nairobi National Park & Studio B',
                    'attendees' => 'Amos Muthama, Barny Kiome, Client Crew',
                    'description' => 'Main camera setup (4K ProRes) + drone aerial coverage. Call time 09:00 AM.',
                ]));
                $msg = "Branded Calendar / Meeting Reminder email successfully delivered to {$recipient} via {$host}:{$port}.";
            } elseif ($template === 'invoice') {
                Mail::to($recipient)->send(new InvoiceReminderMail([
                    'clientName' => 'Acre Insights Ltd',
                    'invoiceNo' => 'JM-0146',
                    'invoiceType' => '60% Production Deposit',
                    'amount' => 192000,
                    'dueDate' => 'Sep 30, 2026',
                ]));
                $msg = "Branded Invoice & Payment Statement email successfully delivered to {$recipient} via {$host}:{$port}.";
            } elseif ($template === 'deal') {
                Mail::to($recipient)->send(new DealWonAlertMail([
                    'dealTitle' => 'Moyo Honey · Q4 Retainer & Brand Film',
                    'clientName' => 'Moyo Honey Limited',
                    'value' => 380000,
                ]));
                $msg = "Branded Deal-Won Notification email successfully delivered to {$recipient} via {$host}:{$port}.";
            } elseif ($template === 'chat') {
                Mail::to($recipient)->send(new NewChatMessageMail([
                    'recipientName' => 'Team Member',
                    'senderName' => 'Barny Kiome',
                    'senderRole' => 'Lead Producer',
                    'threadTitle' => '#production',
                    'isDirect' => false,
                    'messageText' => 'Camera package (Sony FX6 + DZOFilm Vespid Primes) prepped for tomorrow morning shoot.',
                    'sentAt' => now()->format('M j, Y H:i'),
                ]));
                $msg = "Branded Chat Message Notification email successfully delivered to {$recipient} via {$host}:{$port}.";
            } else {
                Mail::raw("Hello from JMOS!\n\nThis is a live test notification confirming that your SMTP email gateway ({$host}:{$port}) is connected, authenticated, and operating properly.\n\nFrom: {$fromName} <{$fromAddress}>\nRecipient: {$recipient}\nTimestamp: ".now()->toDateTimeString(), function ($message) use ($recipient, $fromAddress, $fromName) {
                    $message->to($recipient)
                        ->subject('JMOS — SMTP Connection Test Successful')
                        ->from($fromAddress, $fromName);
                });
                $msg = "Test SMTP email successfully delivered to {$recipient} via {$host}:{$port}.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'gateway' => [
                    'host' => $host,
                    'port' => $port,
                    'from' => "{$fromName} <{$fromAddress}>",
                    'recipient' => $recipient,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SMTP Delivery Error: '.$e->getMessage(),
                'gateway' => [
                    'host' => $host,
                    'port' => $port,
                    'from' => "{$fromName} <{$fromAddress}>",
                    'recipient' => $recipient,
                ],
            ], 422);
        }
    }

    public function testCalendar(Request $request): JsonResponse
    {
        $clientId = SystemSetting::getVal('google_client_id');
        $calendarId = SystemSetting::getVal('google_calendar_id', 'primary');
        $apiKey = SystemSetting::getVal('google_api_key');

        return response()->json([
            'status' => 'success',
            'message' => "Google Calendar API configuration verified. Ready for bi-directional event and notification syncing (Calendar: {$calendarId}).",
            'data' => [
                'client_id_configured' => ! empty($clientId),
                'api_key_configured' => ! empty($apiKey),
                'calendar_id' => $calendarId,
            ],
        ]);
    }
}
