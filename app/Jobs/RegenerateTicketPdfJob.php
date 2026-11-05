<?php

namespace App\Jobs;

use App\Mail\TicketGenerationFailedMail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\EmailDeliveryService;
use App\Services\TicketPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RegenerateTicketPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $transactionId) {}

    public function handle(TicketPdfService $ticketPdfService): void
    {
        $transaction = Transaction::with(['billet', 'evenement', 'typeBillet'])->find($this->transactionId);

        if (! $transaction || ! $transaction->billet) {
            return;
        }

        if (! empty($transaction->billet->billetImage)) {
            return;
        }

        $ticketPdfService->generate(
            $transaction->billet,
            (float) $transaction->montant_unitaire,
            $transaction->devise,
            (float) $transaction->montant,
            $transaction->reference
        );

        $transaction->update([
            'statut' => 'paye',
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        $transaction = Transaction::find($this->transactionId);

        if ($transaction) {
            $transaction->update([
                'statut' => 'paye_sans_billet',
            ]);
        }

        $emails = User::whereHas('admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if (! empty($emails)) {
            $emailDelivery = app(EmailDeliveryService::class);
            foreach ($emails as $email) {
                $emailDelivery->send(new TicketGenerationFailedMail($transaction?->reference), $email);
            }
        }

        Log::error('Echec de regeneration du billet PDF apres 3 tentatives.', [
            'transaction_id' => $this->transactionId,
            'reference' => $transaction?->reference,
            'error' => $exception?->getMessage(),
        ]);
    }
}
