<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Règles d'un type de fichier téléversable, lues depuis config/uploads.php.
 */
final class TypeFichier
{
    private function __construct(
        public readonly string $nom,
        public readonly string $libelle,
        public readonly array $extensions,
        public readonly array $mimes,
        public readonly int $maxOctets,
        public readonly bool $image,
    ) {
    }

    public static function get(string $nom): self
    {
        $config = config("uploads.types.{$nom}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("Type de fichier inconnu : {$nom}");
        }

        return new self(
            nom: $nom,
            libelle: $config['libelle'],
            extensions: array_map('strtolower', $config['extensions']),
            mimes: $config['mimes'],
            maxOctets: min($config['max_ko'] * 1024, self::limitePhp()),
            image: (bool) ($config['image'] ?? false),
        );
    }

    public static function pourChamp(string $champ): ?self
    {
        $nom = config("uploads.champs.{$champ}");

        return $nom ? self::get($nom) : null;
    }

    /** Valeur de l'attribut HTML accept, ex. ".jpg,.png,image/jpeg,image/png". */
    public function accept(): string
    {
        $extensions = array_map(fn ($ext) => '.'.$ext, $this->extensions);

        return implode(',', array_merge($extensions, $this->mimes));
    }

    /** Formats lisibles, ex. "JPG, JPEG, PNG, WEBP". */
    public function formatsLisibles(): string
    {
        return strtoupper(implode(', ', $this->extensions));
    }

    /** Taille maximale lisible, ex. "5 Mo". */
    public function tailleLisible(): string
    {
        $mo = $this->maxOctets / 1048576;

        if ($mo >= 1) {
            return rtrim(rtrim(number_format($mo, 1, ',', ''), '0'), ',').' Mo';
        }

        return (int) round($this->maxOctets / 1024).' Ko';
    }

    public function messageFormat(): string
    {
        return "Le format de {$this->libelle} n'est pas autorisé. Formats acceptés : {$this->formatsLisibles()}.";
    }

    public function messageTaille(): string
    {
        return "Le fichier de {$this->libelle} est trop volumineux. Taille maximale : {$this->tailleLisible()}.";
    }

    /** Plus petite des limites PHP, pour ne jamais annoncer une taille que le serveur refuserait. */
    private static function limitePhp(): int
    {
        $limites = array_filter([
            self::iniEnOctets(ini_get('upload_max_filesize')),
            self::iniEnOctets(ini_get('post_max_size')),
        ]);

        return $limites ? min($limites) : PHP_INT_MAX;
    }

    private static function iniEnOctets(string|false $valeur): int
    {
        if ($valeur === false || trim($valeur) === '') {
            return 0;
        }

        $valeur = trim($valeur);
        $nombre = (int) $valeur;

        return match (strtolower(substr($valeur, -1))) {
            'g' => $nombre * 1024 ** 3,
            'm' => $nombre * 1024 ** 2,
            'k' => $nombre * 1024,
            default => $nombre,
        };
    }
}
