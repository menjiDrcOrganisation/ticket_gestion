<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfimartionDemandeEvent extends Mailable
{
    use Queueable, SerializesModels;
          public $event;
    /**
     * Create a new message instance.
     */
    public function __construct($event)
    {
           $this->event = $event;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Confirmation de votre demande d\'événement')
                    ->view('emails.confirmationDemandeEvent'
                    )->with([
                        'event' => $this->event
                    ]);

    }
 
}
