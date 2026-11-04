<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvoiMotDePasseOublieMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public $resetUrl;

    public $expires;

    public $supportEmail;

    public $supportPhone;

    /**
     * Crée une nouvelle instance de message.
     *
     * @param  string  $resetUrl
     * @param  array  $options  (expires, supportEmail, supportPhone)
     */
    public function __construct($resetUrl, $options = [])
    {
        $this->resetUrl = $resetUrl;
        $this->expires = $options['expires'] ?? '60 minutes';
        $this->supportEmail = $options['supportEmail'] ?? config('mail.from.address');
        $this->supportPhone = $options['supportPhone'] ?? null;
    }

    /**
     * Construire le mail.
     */
    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
            ->view('emails.reset-password')
            ->with([
                'resetUrl' => $this->resetUrl,
                'expires' => $this->expires,
                'supportEmail' => $this->supportEmail,
                'supportPhone' => $this->supportPhone,
            ]);
    }
}
