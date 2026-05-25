<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TicketPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegenerateTicketPdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $transactionId)
    {
    }

    public function handle(TicketPdfService $ticketPdfService): void
    {
        $transaction = Transaction::with(['billet', 'evenement', 'typeBillet'])->find($this->transactionId);

        if (!$transaction || !$transaction->billet) {
            return;
        }

        if (!empty($transaction->billet->billetImage)) {
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

        if (!empty($emails)) {
            Mail::raw(
                'Echec de generation du billet PDF apres 3 tentatives. Reference transaction: '
                    . ($transaction?->reference ?? 'N/A'),
                function ($message) use ($emails): void {
                    $message->to($emails)->subject('Alerte: generation billet en echec');
                }
            );
        }

        Log::error('Echec de regeneration du billet PDF apres 3 tentatives.', [
            'transaction_id' => $this->transactionId,
            'reference' => $transaction?->reference,
            'error' => $exception?->getMessage(),
        ]);
    }
}
