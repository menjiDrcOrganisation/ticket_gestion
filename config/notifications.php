<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File d'attente des notifications
    |--------------------------------------------------------------------------
    |
    | Nom de la file sur laquelle les e-mails sont poussés. Le worker doit l'écouter :
    |   php artisan queue:work --queue=notifications,default
    |
    */

    'queue' => env('NOTIFICATIONS_QUEUE', 'notifications'),

    /*
    | Nombre total de tentatives avant qu'une notification soit considérée
    | comme définitivement échouée.
    */

    'tentatives' => (int) env('NOTIFICATIONS_TENTATIVES', 3),

    /*
    | Délais (en secondes) entre deux tentatives : 1 min, puis 5 min, puis 15 min.
    */

    'backoff' => array_map('intval', explode(',', (string) env('NOTIFICATIONS_BACKOFF', '60,300,900'))),

];
