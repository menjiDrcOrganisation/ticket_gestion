<!DOCTYPE html>
<html>
  <body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Bonjour {{ $nom_client }},</h2>
    <p>Bienvenue sur notre plateforme !</p>
    <p>Voici vos identifiants de connexion pour la partie gestion à ce lien :</p>
    <a href="{{ request()->getSchemeAndHttpHost() }}">{{ request()->getSchemeAndHttpHost() }}</a>

    <ul>
      <li><strong>Email :</strong> {{ $email }}</li>
      <li><strong>Mot de passe :</strong> {{ $mot_de_passe }}</li>
      <li><strong>Identifiants de vos scanneurs :</strong></li>
      <ul>
        <li>Email : {{ $email_scanneur }}</li>
        <li>Mot de passe : {{ $mot_de_passe_scanneur }}</li>
      </ul>
      <li><strong>Lien pour acheter le billet :</strong>
        <a href="{{ $url }}">{{ $url }}</a>
      </li>
    </ul>

    <p>Nous vous recommandons de modifier votre mot de passe après votre première connexion.</p>
    <p>Merci d’utiliser nos services.</p>

    <br>
    <p>Cordialement,</p>
    <p><strong>L’équipe MenjiDrc</strong></p>
  </body>
</html>
