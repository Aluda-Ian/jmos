<?php

namespace App\Services;

use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\MeetingReminderMail;
use App\Mail\NewChatMessageMail;
use App\Mail\TaskAssignedMail;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function applySmtpSettings(): void
    {
        $host = SystemSetting::getVal('mail_host');
        $port = SystemSetting::getVal('mail_port');
        $username = SystemSetting::getVal('mail_username');
        $password = SystemSetting::getVal('mail_password');
        $encryption = SystemSetting::getVal('mail_encryption');
        $fromAddress = SystemSetting::getVal('mail_from_address');
        $fromName = SystemSetting::getVal('mail_from_name');

        if ($host) {
            Config::set('mail.mailers.smtp.host', $host);
        }
        if ($port) {
            Config::set('mail.mailers.smtp.port', $port);
        }
        if ($username) {
            Config::set('mail.mailers.smtp.username', $username);
        }
        if ($password) {
            Config::set('mail.mailers.smtp.password', $password);
        }
        if ($encryption) {
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
        }
        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }

        Mail::purge('smtp');
    }

    public static function sendTaskAssigned(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            Mail::to($recipientEmail)->send(new TaskAssignedMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('Task assignment email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendMeetingReminder(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            Mail::to($recipientEmail)->send(new MeetingReminderMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('Meeting reminder email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendInvoiceReminder(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            Mail::to($recipientEmail)->send(new InvoiceReminderMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('Invoice reminder email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendDealWonAlert(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            Mail::to($recipientEmail)->send(new DealWonAlertMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('Deal won alert email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendNewChatMessage(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            Mail::to($recipientEmail)->send(new NewChatMessageMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('New chat message email notice: '.$e->getMessage());

            return false;
        }
    }
}
