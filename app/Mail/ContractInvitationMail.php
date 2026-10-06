<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contract $contract, public ?string $personalMessage = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Agreement for your signature: {$this->contract->contract_number} — {$this->contract->title} (Jeota Media)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-invitation',
            with: [
                'subject' => 'Agreement for your signature',
                'contract' => $this->contract,
                'personalMessage' => $this->personalMessage,
                'signUrl' => $this->contract->signUrl(),
            ],
        );
    }
}
