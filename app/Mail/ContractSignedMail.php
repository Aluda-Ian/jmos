<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Signed agreement {$this->contract->contract_number} — {$this->contract->client_name} (Jeota Media)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-signed',
            with: [
                'subject' => 'Agreement signed',
                'contract' => $this->contract,
                'signUrl' => $this->contract->signUrl(),
            ],
        );
    }
}
