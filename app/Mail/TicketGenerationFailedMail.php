<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketGenerationFailedMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(public ?string $transactionReference) {}

    public function build()
    {
        return $this->subject('Un billet n’a pas pu être généré')
            ->view('emails.tickets.generation-failed');
    }
}
