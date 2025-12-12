<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Réinitialisation de mot de passe</title>
  <style>
    body { margin:0; padding:0; background-color:#f3f4f6; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color:#0f172a; }
    .container { width:100%; max-width:680px; margin:0 auto; padding:24px; }
    .card { background:#ffffff; border-radius:12px; padding:28px; box-shadow:0 6px 20px rgba(2,6,23,0.08); }
    h1 { margin:0 0 12px 0; font-size:20px; color:#0b3b82; }
    p { margin:0 0 16px 0; line-height:1.5; color:#334155; }
    .btn { display:inline-block; text-decoration:none; padding:12px 20px; border-radius:10px; font-weight:600; }
    .btn-primary { background: linear-gradient(90deg,#0f62fe,#ff7a18); color:white; }
    .muted { color:#6b7280; font-size:13px; }
    .small { font-size:13px; color:#475569; }
    .footer { text-align:center; font-size:12px; color:#94a3b8; margin-top:18px; }
    .code { display:inline-block; padding:10px 14px; border-radius:8px; background:#f8fafc; border:1px solid #e6eef8; font-family: monospace; }
    @media (max-width:480px) {
      .card { padding:18px; }
      h1 { font-size:18px; }
    }
  </style>
</head>
<body>
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center" style="padding:28px 12px;">
        <div class="container">
          <div style="text-align:center; margin-bottom:18px;">
            <img src="{{ $logo ?? env('APP_URL').'/logo.png' }}" alt="{{ $appName ?? config('app.name') }}" width="120" style="display:block;margin:0 auto 8px;">
          </div>

          <div class="card">
            <h1>Réinitialisation de votre mot de passe</h1>

            <p>Nous avons reçu une demande pour réinitialiser le mot de passe associé à <strong>{{ $email }}</strong>. Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>

            <p style="text-align:center; margin:22px 0;">
              <a class="btn btn-primary" href="{{ $resetUrl }}" target="_blank" rel="noopener">Réinitialiser mon mot de passe</a>
            </p>

            <p class="small">Ce lien expirera dans <strong>{{ $expires ?? '60 minutes' }}</strong>. Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet e-mail — votre mot de passe restera inchangé.</p>

            <hr style="border:none; border-top:1px solid #eef2f7; margin:20px 0;">

            <p class="muted">Si le bouton ne fonctionne pas, copiez-collez l'URL suivante dans votre navigateur :</p>
            <p class="code" style="word-break:break-all;">{{ $resetUrl }}</p>

            @if(!empty($supportEmail) || !empty($supportPhone))
              <div style="margin-top:16px;">
                <p class="small"><strong>Besoin d'aide ?</strong></p>
                <p class="small">Contactez-nous : 
                  @if(!empty($supportEmail)) <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>@endif
                  @if(!empty($supportPhone)) — {{ $supportPhone }}@endif
                </p>
              </div>
            @endif

            <div style="margin-top:20px;">
              <p class="small">Cordialement,<br>{{ $appName ?? config('app.name') }}</p>
            </div>
          </div>

          <div class="footer">
            <p>© {{ date('Y') }} {{ $appName ?? config('app.name') }} — Tous droits réservés.</p>
            <p class="muted">Si vous ne souhaitez plus recevoir ces e-mails, connectez-vous à votre compte et modifiez vos préférences de notification.</p>
          </div>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
