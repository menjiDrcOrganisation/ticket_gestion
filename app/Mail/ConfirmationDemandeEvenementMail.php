<?php

namespace App\Mail;

use App\Models\DemandeEvenement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationDemandeEvenementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $demande;

    /**
     * Crée une nouvelle instance du message.
     */
    public function __construct(DemandeEvenement $demande)
    {
        $this->demande = $demande;
    }

    /**
     * Construire le message.
     */
    public function build()
    {
        return $this
            ->subject('Confirmation de votre demande d’événement')
            ->view('emails.confirmationDemandeEvenement')
            ->with([
                        'demande' => $this->demande
                    ]);
    }
}

