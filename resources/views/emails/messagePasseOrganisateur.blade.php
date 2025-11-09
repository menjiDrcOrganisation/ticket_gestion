<!DOCTYPE html>
<html>
  <body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Bonjour {{ $nom_client }},</h2>
    <p>Bienvenue sur notre plateforme !</p>
    <p>Voici vos identifiants de connexion :</p>

    <ul>
      <li><strong>Email :</strong> {{ $email }}</li>
      <li><strong>Mot de passe :</strong> {{ $mot_de_passe }}</li>

      <li><strong>le lien de votre site pour acheter le  billet :
        
      </strong><link rel="stylesheet" href="{{ $url }}"> {{ $url }}</li>


      
    </ul>

    <p>Nous vous recommandons de modifier votre mot de passe après votre première connexion.</p>
    <p>Merci d’utiliser nos services.</p>

    <br>
    <p>Cordialement,</p>
    <p><strong>L’équipe MenjiDrc</strong></p>
  </body>
</html>
