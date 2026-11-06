<?php

/*
|--------------------------------------------------------------------------
| Fichiers téléversés
|--------------------------------------------------------------------------
|
| Référentiel unique des fichiers acceptés par la plateforme. Chaque type
| définit les extensions et types MIME autorisés ainsi que la taille
| maximale. Ces règles sont lues à la fois par la validation backend
| (App\Rules\FichierTeleverse) et par le composant <x-app-file-input>.
|
| La taille réellement appliquée ne dépasse jamais la limite PHP
| (upload_max_filesize / post_max_size) : voir App\Support\TypeFichier.
|
*/

return [

    'types' => [

        // Affiche d'un événement ou d'une demande d'événement.
        // SVG exclu volontairement : il peut embarquer du JavaScript.
        'affiche' => [
            'libelle' => "l'affiche",
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_ko' => 5120,
            'image' => true,
        ],

    ],

    // Champs de formulaire qui reçoivent un fichier, et leur type.
    'champs' => [
        'affiche' => 'affiche',
        'photo_affiche' => 'affiche',
    ],

];
