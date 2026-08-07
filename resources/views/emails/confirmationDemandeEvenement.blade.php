
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <title>Confirmation de votre demande</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 30px;">

    <div style="
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
    ">

        <h2 style="color: #333;">
            Confirmation de votre demande
        </h2>

        <p>
            Bonjour,
        </p>

        <p>
            Nous vous confirmons que votre demande d'organisation
            d'événement a bien été reçue.
        </p>

        <h3>Détails de votre demande</h3>

        <p>
            <strong>Événement :</strong>
            {{ $demande->nom_evenement }}
        </p>

        <p>
            <strong>Type d'événement :</strong>
            {{ $demande->type_evenement }}
        </p>

        <p>
            <strong>Contact :</strong>
            {{ $demande->contact_organisateur }}
        </p>

        <p>
            <strong>Statut :</strong>
            {{ $demande->statut }}
        </p>

        <p>
            Votre demande sera examinée par notre équipe.
            Vous serez informé de la suite donnée à votre demande.
        </p>

        <p>
            Merci pour votre confiance.
        </p>

        <p>
            Cordialement,<br>
            <strong>L'équipe d'organisation</strong>
        </p>

    </div>

</body>
</html>

