<?php

namespace Database\Seeders;

use App\Models\Billet;
use App\Models\Evenement;
use App\Models\EvenementBilletTypeBillet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class ActiveEventsBilletScenarioSeeder extends Seeder
{
    /**
     * Cree un billet seed pour chaque type de billet de chaque evenement actif.
     */
    public function run(): void
    {
        $activeEvents = Evenement::query()
            ->whereIn('statut', ['encours', 'actif'])
            ->with('typeBillets')
            ->get();

        foreach ($activeEvents as $event) {
            foreach ($event->typeBillets as $typeBillet) {
                $stock = (int) ($typeBillet->pivot->nombre_billet ?? 1);
                $quantite = max(1, min(3, $stock));

                $seedCode = sprintf('SEED-ACTIF-%d-%d', $event->id, $typeBillet->id);
                $imageBasePath = sprintf('seed/billets/%s', strtolower($seedCode));
                $buyerSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $typeBillet->nom_type));
                $buyerSlug = trim($buyerSlug, '.');

                $qrAsset = $this->generateQrAsset($seedCode, $imageBasePath);
                $imagePath = $qrAsset['path'];

                // Genere un vrai QR SVG pour le billet seed.
                if (! Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->put($imagePath, $qrAsset['content']);
                }

                $billet = Billet::updateOrCreate(
                    ['code_billet' => $seedCode],
                    [
                        'date_achat' => now()->subHours(2),
                        'nom_auteur' => 'Client Seed ' . $typeBillet->nom_type,
                        'numero' => '+243970000' . str_pad((string) $typeBillet->id, 3, '0', STR_PAD_LEFT),
                        'email' => sprintf('seed.%s.event%d@example.com', $buyerSlug ?: 'client', $event->id),
                        // Certaines bases en production ont cette colonne en NOT NULL.
                        'billetImage' => $imagePath,
                        'statut' => 'valide',
                        'quantite' => $quantite,
                        'quantite_fictif' => $quantite,
                        'evenement_id' => $event->id,
                        'type_billet_id' => $typeBillet->id,
                    ]
                );

                EvenementBilletTypeBillet::updateOrCreate(
                    [
                        'evenement_id' => $event->id,
                        'billet_id' => $billet->id,
                        'type_billet_id' => $typeBillet->id,
                    ],
                    [
                        'statut' => 'valide',
                        'quantite' => $quantite,
                        'quantite_fictif' => $quantite,
                    ]
                );
            }
        }
    }

    /**
     * @return array{path:string, content:string}
     */
    private function generateQrAsset(string $content, string $imageBasePath): array
    {
        try {
            if (class_exists(QrCode::class)) {
                $svg = QrCode::format('svg')
                    ->size(400)
                    ->margin(1)
                    ->generate($content);

                return [
                    'path' => $imageBasePath . '.svg',
                    'content' => $svg,
                ];
            }
        } catch (Throwable) {
            // Fallback: SVG simple lisible si la lib QR echoue.
        }

        $safeContent = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fallbackSvg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
            . '<rect width="400" height="400" fill="#ffffff" stroke="#111827" stroke-width="4"/>'
            . '<text x="200" y="190" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#111827">QR non disponible</text>'
            . '<text x="200" y="226" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#4b5563">%s</text>'
            . '</svg>',
            $safeContent
        );

        return [
            'path' => $imageBasePath . '.svg',
            'content' => $fallbackSvg,
        ];
    }
}
