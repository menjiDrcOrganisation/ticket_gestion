<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvoiMotDePasseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nom_client;
    public $email;
    public $mot_de_passe;
     public $url;

    /**
     * Crée une nouvelle instance du message.
     */
    public function __construct($nom_client, $email, $mot_de_passe,$url)
    {
        $this->nom_client = $nom_client;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->$url = $url;
    }

    /**
     * Construire le message.
     */
    public function build()
    {
        return $this->subject('Vos identifiants de connexion')
                    ->view('emails.messagePasseOrganisateur');
    }
}
