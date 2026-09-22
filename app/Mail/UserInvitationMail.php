<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(public array $data) {}

    public function build()
    {
        $userName = $this->data['userName'] ?? 'Team Member';
        $roleName = $this->data['role'] ?? 'Team';

        return $this->subject("Welcome to JMOS: Set Up Your Password ({$userName} — {$roleName})")
            ->view('emails.user-invitation')
            ->with($this->data);
    }
}
