<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class EmailDeliveryService
{
    public function send(Mailable $mail, string $recipient): bool
    {
        $pending = ! in_array(config('queue.default'), ['sync', 'null'], true);

        if ($pending) {
            Mail::to($recipient)->queue($mail);
        } else {
            Mail::to($recipient)->send($mail);
        }

        return $pending;
    }
}
