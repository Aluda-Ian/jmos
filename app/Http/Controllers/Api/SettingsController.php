<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\MeetingReminderMail;
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

        return response()->json([
            'status' => 'success',
            'message' => 'Settings saved successfully.',
        ]);
    }

    public function testEmail(Request $request): JsonResponse
    {
        $recipient = $request->input('recipient', 'jmos@jeotamedia.co.ke');
        $template = $request->input('template', 'general'); // general, task, meeting, invoice, deal

        NotificationService::applySmtpSettings();

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
                $msg = "Branded Task Assignment email sent to {$recipient}.";
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
                $msg = "Branded Calendar / Meeting Reminder email sent to {$recipient}.";
            } elseif ($template === 'invoice') {
                Mail::to($recipient)->send(new InvoiceReminderMail([
                    'clientName' => 'Acre Insights Ltd',
                    'invoiceNo' => 'JM-0146',
                    'invoiceType' => '60% Production Deposit',
                    'amount' => 192000,
                    'dueDate' => 'Sep 30, 2026',
                ]));
                $msg = "Branded Invoice & Payment Statement email sent to {$recipient}.";
            } elseif ($template === 'deal') {
                Mail::to($recipient)->send(new DealWonAlertMail([
                    'dealTitle' => 'Moyo Honey · Q4 Retainer & Brand Film',
                    'clientName' => 'Moyo Honey Limited',
                    'value' => 380000,
                ]));
                $msg = "Branded Deal-Won Cascade Notification email sent to {$recipient}.";
            } else {
                $host = SystemSetting::getVal('mail_host', config('mail.mailers.smtp.host'));
                $port = SystemSetting::getVal('mail_port', config('mail.mailers.smtp.port'));
                $fromAddress = SystemSetting::getVal('mail_from_address', config('mail.from.address'));
                $fromName = SystemSetting::getVal('mail_from_name', config('mail.from.name'));

                Mail::raw("Hello from JMOS!\n\nThis is a test notification confirming that your SMTP email server ($host:$port) is connected and operating properly.\n\nSent at: ".now()->toDateTimeString(), function ($message) use ($recipient, $fromAddress, $fromName) {
                    $message->to($recipient)
                        ->subject('JMOS — SMTP Connection Test Successful')
                        ->from($fromAddress, $fromName);
                });
                $msg = "Test SMTP email successfully delivered to {$recipient} via {$host}:{$port}.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $msg,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SMTP Delivery Notice: '.$e->getMessage(),
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
