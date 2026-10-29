<?php

namespace App\Mail;

use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mail envoyé à un NOUVEL organisateur : identifiants de connexion (mot de passe temporaire
 * à changer obligatoirement), informations de l'événement et identifiants du scanneur.
 */
class EnvoiMotDePasseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nom_client;
    public $email;
    public $mot_de_passe;
    public $url;
    public $email_scanneur;
    public $mot_de_passe_scanneur;
    public ?Evenement $evenement;

    /**
     * Crée une nouvelle instance du message.
     */
    public function __construct($nom_client, $email, $mot_de_passe, $url, $email_scanneur, $mot_de_passe_scanneur, ?Evenement $evenement = null)
    {
        $this->nom_client = $nom_client;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->url = $url;
        $this->email_scanneur = $email_scanneur;
        $this->mot_de_passe_scanneur = $mot_de_passe_scanneur;
        $this->evenement = $evenement;
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
