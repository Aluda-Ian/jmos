<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewChatMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $senderName = $this->data['senderName'] ?? 'Team Member';
        $threadTitle = $this->data['threadTitle'] ?? 'New Chat';
        $subject = ($this->data['isDirect'] ?? true)
            ? "New Direct Message from {$senderName} — JMOS"
            : "New message in {$threadTitle} by {$senderName} — JMOS";

        return $this->subject($subject)
            ->view('emails.new-chat-message')
            ->with($this->data);
    }
}
