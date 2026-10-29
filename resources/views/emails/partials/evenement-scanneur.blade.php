@if($evenement)
    <h3>Informations de l'événement</h3>
    <ul>
      <li><strong>Événement :</strong> {{ $evenement->nom }}</li>
      <li><strong>Lieu :</strong> {{ $evenement->salle }} — {{ $evenement->adresse }}</li>
      <li><strong>Début :</strong> {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }} à {{ $evenement->heure_debut }}</li>
      <li><strong>Fin :</strong> {{ \Carbon\Carbon::parse($evenement->date_fin)->format('d/m/Y') }} à {{ $evenement->heure_fin }}</li>
    </ul>
@endif

    <p><strong>Lien pour acheter les billets :</strong>
      <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <h3>Identifiants du scanneur de cet événement</h3>
    <p>Ce compte est dédié uniquement à cet événement. Transmettez-le à la personne chargée du contrôle des billets.</p>
    <ul>
      <li>Email : {{ $email_scanneur }}</li>
      <li>Mot de passe : {{ $mot_de_passe_scanneur }}</li>
    </ul>
