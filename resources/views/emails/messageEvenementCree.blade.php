<!DOCTYPE html>
<html>
  <body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Bonjour {{ $nom_client }},</h2>
    <p><strong>Votre événement a été créé avec succès.</strong></p>
    <p>Il a été rattaché à votre compte organisateur existant. Connectez-vous avec vos identifiants habituels pour le gérer :</p>
    <a href="{{ route('login') }}">{{ route('login') }}</a>

    @include('emails.partials.evenement-scanneur')

    <p>Merci d’utiliser nos services.</p>

    <br>
    <p>Cordialement,</p>
    <p><strong>L’équipe MenjiDrc</strong></p>
  </body>
</html>
