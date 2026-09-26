<?php

namespace App\Mail;

use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mail envoyé à un organisateur DÉJÀ inscrit lorsqu'un nouvel événement est créé sur son compte :
 * informations de l'événement et identifiants du nouveau scanneur (aucun mot de passe organisateur).
 */
class EvenementCreeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom_client,
        public Evenement $evenement,
        public string $url,
        public string $email_scanneur,
        public string $mot_de_passe_scanneur,
    ) {
    }

    public function build()
    {
        return $this->subject('Votre événement a été créé avec succès')
                    ->view('emails.messageEvenementCree');
    }
}
