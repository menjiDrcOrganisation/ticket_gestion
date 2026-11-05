@if($evenement)
    <h2 style="font-size:18px;">Informations sur l’événement</h2>
    <ul>
        <li><strong>Événement :</strong> {{ $evenement->nom }}</li>
        <li><strong>Lieu :</strong> {{ $evenement->salle }} — {{ $evenement->adresse }}</li>
        <li><strong>Début :</strong> {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }} à {{ $evenement->heure_debut }}</li>
        <li><strong>Fin :</strong> {{ \Carbon\Carbon::parse($evenement->date_fin)->format('d/m/Y') }} à {{ $evenement->heure_fin }}</li>
    </ul>
@endif

<p><strong>Billetterie :</strong> <a href="{{ $url }}">Voir la billetterie de l’événement</a></p>

<h2 style="font-size:18px;">Accès du compte de contrôle</h2>
<p>Ce compte est dédié à cet événement. Transmettez ses accès uniquement à la personne chargée du contrôle des billets.</p>
<ul>
    <li><strong>Adresse e-mail :</strong> {{ $email_scanneur }}</li>
    <li><strong>Mot de passe temporaire :</strong> {{ $mot_de_passe_scanneur }}</li>
</ul>
