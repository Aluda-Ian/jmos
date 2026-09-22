<?php

namespace App\Services;

use App\Mail\DealWonAlertMail;
use App\Mail\InvoiceReminderMail;
use App\Mail\MeetingReminderMail;
use App\Mail\NewChatMessageMail;
use App\Mail\PasswordResetOtpMail;
use App\Mail\TaskAssignedMail;
use App\Mail\UserInvitationMail;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Apply runtime SMTP configuration from system settings or explicit overrides.
     *
     * @param  array<string, mixed>|null  $overrides
     */
    public static function applySmtpSettings(?array $overrides = null): void
    {
        $host = $overrides['mail_host'] ?? SystemSetting::getVal('mail_host', config('mail.mailers.smtp.host'));
        $port = $overrides['mail_port'] ?? SystemSetting::getVal('mail_port', config('mail.mailers.smtp.port', 587));
        $username = $overrides['mail_username'] ?? SystemSetting::getVal('mail_username', config('mail.mailers.smtp.username'));

        $password = null;
        if (isset($overrides['mail_password']) && $overrides['mail_password'] !== '' && $overrides['mail_password'] !== '••••••••') {
            $password = $overrides['mail_password'];
        } else {
            $password = SystemSetting::getVal('mail_password', config('mail.mailers.smtp.password'));
        }

        $encryption = $overrides['mail_encryption'] ?? SystemSetting::getVal('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $fromAddress = $overrides['mail_from_address'] ?? SystemSetting::getVal('mail_from_address', config('mail.from.address'));
        $fromName = $overrides['mail_from_name'] ?? SystemSetting::getVal('mail_from_name', config('mail.from.name'));

        if ($host) {
            Config::set('mail.mailers.smtp.host', $host);
        }
        if ($port) {
            Config::set('mail.mailers.smtp.port', (int) $port);
        }
        if ($username) {
            Config::set('mail.mailers.smtp.username', $username);
        }
        if ($password) {
            Config::set('mail.mailers.smtp.password', $password);
        }
        if ($encryption) {
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : null);
        }
        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
            if (str_contains($fromAddress, '@')) {
                $ehloDomain = substr(strrchr($fromAddress, '@'), 1);
                Config::set('mail.mailers.smtp.local_domain', $ehloDomain);
            }
        }
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }

        Mail::purge('smtp');
    }

    /**
     * Resolve a user's secondary notification email from their primary email.
     */
    public static function resolveSecondaryEmail(string $primaryEmail): ?string
    {
        $user = User::where('email', $primaryEmail)->first();

        return $user?->secondary_email;
    }

    public static function sendTaskAssigned(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new TaskAssignedMail($data));

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
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new MeetingReminderMail($data));

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
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new InvoiceReminderMail($data));

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
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new DealWonAlertMail($data));

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
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new NewChatMessageMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('New chat message email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendPasswordResetOtp(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new PasswordResetOtpMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('Password reset OTP email notice: '.$e->getMessage());

            return false;
        }
    }

    public static function sendUserInvitation(array $data, string $recipientEmail): bool
    {
        self::applySmtpSettings();
        try {
            $mail = Mail::to($recipientEmail);
            $cc = self::resolveSecondaryEmail($recipientEmail);
            if ($cc) {
                $mail->cc($cc);
            }
            $mail->send(new UserInvitationMail($data));

            return true;
        } catch (\Exception $e) {
            \Log::warning('User invitation email notice: '.$e->getMessage());

            return false;
        }
    }
}
