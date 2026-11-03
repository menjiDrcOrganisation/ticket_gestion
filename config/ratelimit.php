<?php

// Une valeur absente, vide ou <= 0 retombe sur le défaut : une faute de frappe dans le .env
// ne peut donc pas désactiver la protection.
$limite = static function (string $cle, int $defaut): int {
    $valeur = (int) env($cle, $defaut);

    return $valeur > 0 ? $valeur : $defaut;
};

return [

    /*
    |--------------------------------------------------------------------------
    | Rate limiting de l'API : requêtes par minute et par adresse IP
    |--------------------------------------------------------------------------
    |
    | Au-delà du plafond, l'API répond 429 en JSON avec l'en-tête Retry-After.
    | Les compteurs sont stockés dans le cache (CACHE_STORE). Les limiteurs
    | eux-mêmes sont déclarés dans AppServiceProvider.
    |
    */

    // Limiteur "api" : toutes les routes api/*, sauf le webhook de paiement.
    'api' => $limite('RATE_LIMIT_API', 60),

    // Limiteur "creation-evenement" : POST /evenements et POST /demande-evenement
    // (écrivent en base et envoient des emails). S'ajoute au limiteur "api".
    'creation_evenement' => $limite('RATE_LIMIT_CREATION_EVENEMENT', 5),

    // Limiteur "paiement" : achatBillet, transactions/initier et valider-paiement
    // (déclenchent une demande de paiement mobile money). S'ajoute au limiteur "api".
    'paiement' => $limite('RATE_LIMIT_PAIEMENT', 20),

    // Limiteur "webhook-paiement" : POST /transactions/callback. Plafond large, car ce sont
    // les notifications du fournisseur : en bloquer une ferait perdre une confirmation de paiement.
    'webhook' => $limite('RATE_LIMIT_WEBHOOK', 120),

];
