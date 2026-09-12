<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $title = $this->data['taskTitle'] ?? 'New Task Assigned';
        return $this->subject("JMOS Task Assignment: {$title}")
            ->view('emails.task-assigned')
            ->with($this->data);
    }
}
