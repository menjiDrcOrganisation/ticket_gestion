<?php

namespace App\Mail;

use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mail envoyé à un organisateur DÉJÀ inscrit lorsqu'un nouvel événement est créé sur son compte :
 * informations de l'événement et identifiants du nouveau scanneur (aucun mot de passe organisateur).
 */
class EvenementCreeMail extends Mailable implements ShouldBeEncrypted
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom_client,
        public Evenement $evenement,
        public string $url,
        public string $email_scanneur,
        public string $mot_de_passe_scanneur,
    ) {}

    public function build()
    {
        return $this->subject('Votre événement est créé')
            ->view('emails.messageEvenementCree');
    }

    public function send($mailer)
    {
        $result = parent::send($mailer);
        $this->evenement->forceFill([
            'mail_sent_at' => now(),
            'mail_queued_at' => null,
            'last_mail_error' => null,
        ])->save();

        return $result;
    }

    public function failed(\Throwable $exception): void
    {
        $this->evenement->forceFill([
            'mail_sent_at' => null,
            'mail_queued_at' => null,
            'last_mail_error' => 'MAIL_SEND_FAILED',
        ])->save();
    }
}
