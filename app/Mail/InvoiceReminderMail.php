<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $invNo = $this->data['invoiceNo'] ?? 'Invoice';
        $client = $this->data['clientName'] ?? 'Client';
        return $this->subject("Jeota Media Invoice {$invNo} for {$client}")
            ->view('emails.invoice-reminder')
            ->with($this->data);
    }
}
