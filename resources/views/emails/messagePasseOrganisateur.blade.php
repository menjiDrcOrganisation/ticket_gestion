<!DOCTYPE html>
<html>
  <body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Bonjour {{ $nom_client }},</h2>
    <p>Bienvenue sur notre plateforme ! Votre compte organisateur a été créé.</p>
    <p>Voici vos identifiants de connexion pour la partie gestion à ce lien :</p>
    <a href="{{ route('login') }}">{{ route('login') }}</a>

    <ul>
      <li><strong>Email :</strong> {{ $email }}</li>
      <li><strong>Mot de passe temporaire :</strong> {{ $mot_de_passe }}</li>
    </ul>

    <p style="padding: 10px; background: #fff7ed; border: 1px solid #fdba74; border-radius: 6px;">
      <strong>Important :</strong> ce mot de passe est temporaire. Vous devrez obligatoirement le changer lors de votre première connexion.
    </p>

    @include('emails.partials.evenement-scanneur')

    <p>Vous pourrez créer vos prochains événements avec la même adresse e-mail : ils seront automatiquement rattachés à ce compte.</p>
    <p>Merci d’utiliser nos services.</p>

    <br>
    <p>Cordialement,</p>
    <p><strong>L’équipe MenjiDrc</strong></p>
  </body>
</html>
