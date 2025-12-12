<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvoiMotDePasseOublieMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $token;
    public $resetUrl;
    public $appName;
    public $expires;
    public $supportEmail;
    public $supportPhone;
    public $logo;

    /**
     * Crée une nouvelle instance de message.
     *
     * @param string $email
     * @param string $token
     * @param string $resetUrl
     * @param array|null $options (appName, expires, supportEmail, supportPhone, logo)
     */
    public function __construct($email, $token, $resetUrl, $options = [])
    {
        $this->email = $email;
        $this->token = $token;
        $this->resetUrl = $resetUrl;
        $this->appName = $options['appName'] ?? config('app.name');
        $this->expires = $options['expires'] ?? '60 minutes';
        $this->supportEmail = $options['supportEmail'] ?? config('mail.from.address');
        $this->supportPhone = $options['supportPhone'] ?? '+243 84 747 374 5';
        $this->logo = $options['logo'] ?? null;
    }

    /**
     * Construire le mail.
     */
    public function build()
    {
        return $this->subject("Réinitialisation du mot de passe — {$this->appName}")
                    ->view('emails.reset-password')          // HTML
                    ->with([
                        'email' => $this->email,
                        'token' => $this->token,
                        'resetUrl' => $this->resetUrl,
                        'appName' => $this->appName,
                        'expires' => $this->expires,
                        'supportEmail' => $this->supportEmail,
                        'supportPhone' => $this->supportPhone,
                        'logo' => $this->logo,
                    ]);
    }
}
