<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $title = $this->data['eventTitle'] ?? 'Upcoming Meeting / Shoot';

        return $this->subject("📅 Schedule Reminder: {$title}")
            ->view('emails.meeting-reminder')
            ->with($this->data);
    }
}
