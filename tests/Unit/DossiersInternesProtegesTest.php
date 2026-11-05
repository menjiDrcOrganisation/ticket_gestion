<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Sur l'hébergement, la racine web est la racine du projet : chaque dossier interne doit refuser tout accès HTTP,
 * sinon le journal, les vues ou la configuration sont téléchargeables.
 */
class DossiersInternesProtegesTest extends TestCase
{
    public static function dossiers(): array
    {
        return [
            'journaux' => ['storage/logs'],
            'cache et vues compilées' => ['storage/framework'],
            'fichiers privés' => ['storage/app/private'],
            'code applicatif' => ['app'],
            'bootstrap' => ['bootstrap'],
            'configuration' => ['config'],
            'base de données' => ['database'],
            'vues et ressources' => ['resources'],
            'routes' => ['routes'],
            'tests' => ['tests'],
            'documentation' => ['docs'],
            'dépendances' => ['vendor'],
        ];
    }

    #[DataProvider('dossiers')]
    public function test_le_dossier_refuse_tout_acces_web(string $dossier): void
    {
        $fichier = dirname(__DIR__, 2).'/'.$dossier.'/.htaccess';

        $this->assertFileExists($fichier);
        $this->assertStringContainsString('Require all denied', (string) file_get_contents($fichier));
    }

    public function test_les_fichiers_publics_ne_sont_pas_bloques(): void
    {
        // Les affiches et billets sont servis depuis storage/app/public : aucun .htaccess de blocage ne doit s'y trouver.
        $this->assertFileDoesNotExist(dirname(__DIR__, 2).'/storage/app/public/.htaccess');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2).'/storage/app/.htaccess');
    }
}
