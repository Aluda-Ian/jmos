<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(public array $data) {}

    public function build()
    {
        $otp = $this->data['otp'] ?? '000000';

        return $this->subject("JMOS Security: Your Verification Code ({$otp})")
            ->view('emails.password-reset-otp')
            ->with($this->data);
    }
}
