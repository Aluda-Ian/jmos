<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DealWonAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $dealTitle = $this->data['dealTitle'] ?? 'New Project';
        return $this->subject("🎉 Deal Won: {$dealTitle} — JMOS Operations Active")
            ->view('emails.deal-won')
            ->with($this->data);
    }
}
