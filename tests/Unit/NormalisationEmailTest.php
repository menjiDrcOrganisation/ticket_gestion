<?php

namespace Tests\Unit;

use App\Services\EvenementCreationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NormalisationEmailTest extends TestCase
{
    public static function emails(): array
    {
        return [
            'déjà normalisé' => ['orga@example.com', 'orga@example.com'],
            'majuscules' => ['ORGA@Example.COM', 'orga@example.com'],
            'espaces' => ["  orga@example.com \t", 'orga@example.com'],
            'accents' => ['ÉLODIE@example.com', 'élodie@example.com'],
            'vide' => ['', ''],
            'null' => [null, ''],
        ];
    }

    #[DataProvider('emails')]
    public function test_normalisation_de_l_email_organisateur(?string $saisie, string $attendu): void
    {
        $this->assertSame($attendu, EvenementCreationService::normalizeEmail($saisie));
    }
}
