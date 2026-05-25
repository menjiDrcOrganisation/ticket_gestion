<?php

namespace App\Services;

use App\Models\Billet;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketPdfService
{
    public function generate(Billet $billet, float $prixUnitaire, string $devise, float $total, string $transactionReference): string
    {
        $billet->loadMissing(['evenement.ressource', 'type_billet']);

        $qrSvg = QrCode::format('svg')
            ->size(300)
            ->generate($billet->code_billet);

        $qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $data = [
            'ticket' => [
                'user_name' => $billet->nom_auteur,
                'event_name' => $billet->evenement->nom ?? 'Evenement',
                'location' => $billet->evenement->adresse ?? 'Non definie',
                'type' => $billet->type_billet->nom_type ?? 'Standard',
                'quantity' => $billet->quantite,
                'price' => number_format($prixUnitaire, 2, ',', ' '),
                'devise' => $devise,
                'total' => number_format($total, 2, ',', ' '),
                'qrcode_url' => $qrCodeUrl,
                'purchase_date' => Carbon::parse($billet->date_achat)->format('d/m/Y H:i'),
                'event_date' => Carbon::parse($billet->evenement->date_debut)->format('d/m/Y'),
                'event_time' => Carbon::parse($billet->evenement->heure_debut)->format('H:i'),
                'photo_affiche' => $billet->evenement->ressource[0]->photo_affiche ?? null,
                'ticket_id' => strtoupper(substr(md5($billet->code_billet), 0, 8)),
                'transaction_ref' => $transactionReference,
            ],
        ];

        $pdf = Pdf::loadView('billetPdf.billet', $data);

        $fileName = 'billets/' . $billet->code_billet . '.pdf';

        if (Storage::disk('public')->exists($fileName)) {
            Storage::disk('public')->delete($fileName);
        }

        Storage::disk('public')->put($fileName, $pdf->output());

        $billet->update([
            'billetImage' => $fileName,
        ]);

        return $fileName;
    }
}
