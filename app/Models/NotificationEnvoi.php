<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Trace d'une notification mise en file d'attente (voir App\Services\NotificationQueueService).
 */
class NotificationEnvoi extends Model
{
    public const EN_ATTENTE = 'en_attente';
    public const EN_COURS = 'en_cours';
    public const ENVOYE = 'envoye';
    public const ECHOUE = 'echoue';

    public const STATUTS = [self::EN_ATTENTE, self::EN_COURS, self::ENVOYE, self::ECHOUE];

    protected $fillable = [
        'type',
        'canal',
        'destinataire',
        'cle',
        'cle_active',
        'statut',
        'sujet_type',
        'sujet_id',
        'tentatives',
        'derniere_erreur',
        'job_uuid',
        'envoye_at',
        'echoue_at',
    ];

    protected function casts(): array
    {
        return [
            'tentatives' => 'integer',
            'envoye_at' => 'datetime',
            'echoue_at' => 'datetime',
        ];
    }

    public function sujet(): MorphTo
    {
        return $this->morphTo();
    }

    public function estTerminee(): bool
    {
        return in_array($this->statut, [self::ENVOYE, self::ECHOUE], true);
    }

    public function marquerEnCours(?string $jobUuid): void
    {
        $this->update([
            'statut' => self::EN_COURS,
            'tentatives' => $this->tentatives + 1,
            'job_uuid' => $jobUuid ?? $this->job_uuid,
        ]);
    }

    public function marquerEnvoyee(): void
    {
        $this->update([
            'statut' => self::ENVOYE,
            'cle_active' => null,
            'derniere_erreur' => null,
            'envoye_at' => now(),
            'echoue_at' => null,
        ]);
    }

    /** Échec d'une tentative : la tâche sera rejouée automatiquement par le worker. */
    public function marquerTentativeEchouee(string $erreur): void
    {
        $this->update([
            'statut' => self::EN_ATTENTE,
            'derniere_erreur' => $erreur,
        ]);
    }

    /** Échec définitif : toutes les tentatives sont épuisées. */
    public function marquerEchouee(string $erreur): void
    {
        $this->update([
            'statut' => self::ECHOUE,
            'cle_active' => null,
            'derniere_erreur' => $erreur,
            'echoue_at' => now(),
        ]);
    }
}
