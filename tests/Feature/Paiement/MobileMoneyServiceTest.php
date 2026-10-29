<?php

namespace Tests\Feature\Paiement;

use App\Services\MobileMoneyService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Interprétation des réponses de la passerelle Mobile Money (simulée).
 */
class MobileMoneyServiceTest extends TestCase
{
    private function donnees(): array
    {
        return [
            'transaction_reference' => 'CMD-TEST-1',
            'total' => 50.0,
            'devise' => 'USD',
            'nom_complet_client' => 'Awa Client',
            'numero_client' => '0990000003',
            'service' => 'MPESA',
            'callback_url' => 'https://app.example.test/api/v1/transactions/callback',
        ];
    }

    public function test_initiation_reussie(): void
    {
        Http::fake(['*' => Http::response(['reference' => 'PROV-1'], 200)]);

        $resultat = MobileMoneyService::initiatePayment($this->donnees());

        $this->assertTrue($resultat['status']);
        $this->assertFalse($resultat['uncertain']);
        $this->assertSame('CMD-TEST-1', $resultat['reference']);
        $this->assertSame('PROV-1', $resultat['data']['reference']);
    }

    public function test_une_reference_est_generee_si_absente(): void
    {
        Http::fake(['*' => Http::response([], 200)]);
        $donnees = $this->donnees();
        unset($donnees['transaction_reference']);

        $resultat = MobileMoneyService::initiatePayment($donnees);

        $this->assertMatchesRegularExpression('/^TX-\d{14}-\d{4}$/', $resultat['reference']);
    }

    /**
     * Un refus client (4xx hors 408/429) est définitif ; le reste est incertain (le paiement a peut-être abouti).
     */
    public static function codesHttp(): array
    {
        return [
            '400 requête invalide' => [400, false],
            '401 clé invalide' => [401, false],
            '422 données refusées' => [422, false],
            '408 délai dépassé' => [408, true],
            '429 trop de requêtes' => [429, true],
            '500 erreur serveur' => [500, true],
            '503 indisponible' => [503, true],
        ];
    }

    #[DataProvider('codesHttp')]
    public function test_classification_des_echecs(int $code, bool $incertain): void
    {
        Http::fake(['*' => Http::response(['title' => 'Erreur'], $code)]);

        $resultat = MobileMoneyService::initiatePayment($this->donnees());

        $this->assertFalse($resultat['status']);
        $this->assertSame($code, $resultat['http_code']);
        $this->assertSame($incertain, $resultat['uncertain']);
        $this->assertSame('Erreur', $resultat['message']);
    }

    public function test_une_erreur_reseau_est_incertaine(): void
    {
        Http::fake(['*' => fn () => throw new ConnectionException('timeout')]);

        $resultat = MobileMoneyService::initiatePayment($this->donnees());

        $this->assertFalse($resultat['status']);
        $this->assertTrue($resultat['uncertain']);
    }

    public function test_verification_sans_url_configuree(): void
    {
        Http::fake();

        $resultat = MobileMoneyService::verifyPayment('CMD-TEST-1', 50.0);

        $this->assertFalse($resultat['status']);
        Http::assertNothingSent();
    }

    public static function statutsVerification(): array
    {
        return [
            'SUCCESS' => [['transactionStatus' => 'SUCCESS'], true],
            'paid en minuscules' => [['status' => 'paid'], true],
            'COMPLETED' => [['transactionStatus' => 'COMPLETED'], true],
            'PENDING' => [['transactionStatus' => 'PENDING'], false],
            'FAILED' => [['transactionStatus' => 'FAILED'], false],
            'statut absent' => [[], false],
        ];
    }

    #[DataProvider('statutsVerification')]
    public function test_interpretation_de_la_verification(array $reponse, bool $verifie): void
    {
        $_ENV['MOBILE_MONEY_VERIFY_URL'] = $_SERVER['MOBILE_MONEY_VERIFY_URL'] = 'https://gateway.example.test/verify';
        $this->beforeApplicationDestroyed(function (): void {
            $_ENV['MOBILE_MONEY_VERIFY_URL'] = $_SERVER['MOBILE_MONEY_VERIFY_URL'] = '';
        });
        Http::fake(['*' => Http::response($reponse, 200)]);

        $resultat = MobileMoneyService::verifyPayment('CMD-TEST-1', 50.0);

        $this->assertTrue($resultat['status']);
        $this->assertSame($verifie, $resultat['verified']);
    }
}
