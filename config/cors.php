<?php

/*
 * Les variables "liste" du .env sont séparées par des virgules : "a, b" devient ['a', 'b']
 * (espaces et valeurs vides ignorés).
 */
$liste = static fn (string $cle, string $defaut = ''): array => array_values(array_filter(
    array_map('trim', explode(',', (string) env($cle, $defaut))),
    static fn (string $valeur): bool => $valeur !== '',
));

$origines = $liste('CORS_ALLOWED_ORIGINS');

// Garde-fou : les cookies ne sont acceptés qu'avec des origines explicites. Avec "*", la librairie
// renverrait l'origine de n'importe quel site accompagnée de Access-Control-Allow-Credentials.
$credentials = filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN)
    && $origines !== []
    && ! in_array('*', $origines, true);

return [

    /*
    |--------------------------------------------------------------------------
    | Chemins concernés
    |--------------------------------------------------------------------------
    |
    | Seules les URL qui correspondent reçoivent les en-têtes CORS : toute
    | l'API (dont /api/v1/evenements) et le cookie CSRF de Sanctum. Les routes
    | web (Blade) restent en même origine, donc hors CORS.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Méthodes HTTP autorisées (CORS_ALLOWED_METHODS)
    |--------------------------------------------------------------------------
    |
    | Annoncées au navigateur lors du preflight. L'API n'expose que GET et POST ;
    | OPTIONS est la méthode du preflight lui-même.
    |
    */

    'allowed_methods' => $liste('CORS_ALLOWED_METHODS', 'GET,POST,OPTIONS'),

    /*
    |--------------------------------------------------------------------------
    | Origines autorisées (CORS_ALLOWED_ORIGINS)
    |--------------------------------------------------------------------------
    |
    | Liste blanche d'origines exactes : schéma + domaine + port, sans "/" final.
    | Vide = aucune origine externe, tout est bloqué. Un joker de sous-domaine
    | ("https://*.exemple.com") est accepté mais déconseillé en production.
    | Ne jamais mettre "*" : il ouvre l'API à tous les sites.
    |
    */

    'allowed_origins' => $origines,

    /*
    |--------------------------------------------------------------------------
    | Origines par expression régulière
    |--------------------------------------------------------------------------
    |
    | Inutilisé : les jokers de CORS_ALLOWED_ORIGINS suffisent, et une regex
    | dans un .env est fragile (virgules, "#" pris pour un commentaire).
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | En-têtes autorisés (CORS_ALLOWED_HEADERS)
    |--------------------------------------------------------------------------
    |
    | En-têtes que le front a le droit d'envoyer : Content-Type (JSON),
    | Authorization (jeton Sanctum), X-Requested-With, X-CSRF-TOKEN et
    | X-XSRF-TOKEN (session). Un en-tête absent de la liste fait échouer
    | le preflight côté navigateur.
    |
    */

    'allowed_headers' => $liste(
        'CORS_ALLOWED_HEADERS',
        'Content-Type,Accept,Authorization,X-Requested-With,X-CSRF-TOKEN,X-XSRF-TOKEN',
    ),

    /*
    |--------------------------------------------------------------------------
    | En-têtes de réponse lisibles par le front (CORS_EXPOSED_HEADERS)
    |--------------------------------------------------------------------------
    |
    | Par défaut, JavaScript ne lit qu'une poignée d'en-têtes de réponse.
    | Content-Disposition est exposé pour récupérer le nom du fichier renvoyé
    | par /api/v1/transactions/{reference}/download-billet.
    |
    */

    'exposed_headers' => $liste('CORS_EXPOSED_HEADERS', 'Content-Disposition'),

    /*
    |--------------------------------------------------------------------------
    | Cache du preflight en secondes (CORS_MAX_AGE)
    |--------------------------------------------------------------------------
    |
    | Durée pendant laquelle le navigateur réutilise la réponse du preflight au
    | lieu d'envoyer OPTIONS avant chaque requête. Les navigateurs plafonnent
    | la valeur (Chrome : 7200). Mettre 0 en développement si la config change.
    |
    */

    'max_age' => (int) env('CORS_MAX_AGE', 3600),

    /*
    |--------------------------------------------------------------------------
    | Credentials (CORS_SUPPORTS_CREDENTIALS)
    |--------------------------------------------------------------------------
    |
    | true autorise l'envoi des cookies en cross-origin (session Sanctum d'un
    | front SPA sur un autre domaine). Inutile tant que l'API est stateless :
    | un jeton Bearer n'en a pas besoin. Voir le garde-fou en haut de fichier.
    |
    */

    'supports_credentials' => $credentials,

];
