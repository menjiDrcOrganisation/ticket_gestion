<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande d'événement</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">

    <h2>Nouvelle demande d'événement</h2>

    <p>
        Une nouvelle demande d'événement a été soumise par 
        <strong>{{ $event->contact_organisateur }}</strong>.
    </p>

    <hr>

    <h3>Détails de l'événement</h3>

    <p><strong>Nom de l'événement :</strong> {{ $event->nom_evenement }}</p>

    <p><strong>Type d'événement :</strong> {{ $event->type_evenement }}</p>

    <p><strong>Description :</strong><br>
        {{ $event->description }}
    </p>

    <p><strong>Statut :</strong> {{ $event->statut }}</p>

    @if($event->affiche)
        <p><strong>Affiche :</strong></p>
        <img src="{{ asset('storage/'.$event->affiche) }}" 
             alt="Affiche de l'événement" 
             style="max-width: 300px;">
    @endif

    <hr>

    <p>
        Merci de traiter cette demande dans les plus brefs délais.
    </p>

</body>
</html>