<?php

namespace App\Services;

/**
 * Levée quand une notification identique est déjà en file d'attente (déduplication).
 */
class MailDejaEnFileException extends \RuntimeException
{
}
