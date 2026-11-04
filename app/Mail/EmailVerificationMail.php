<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(public string $recipientName, public string $verificationUrl) {}

    public function build()
    {
        return $this->subject('Confirmez votre adresse e-mail')
            ->view('emails.auth.verify-email');
    }
}
