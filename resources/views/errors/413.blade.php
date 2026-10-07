<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fichier trop volumineux</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px; background: #f1f5f9; font-family: system-ui, sans-serif; color: #0f172a; }
        .carte { max-width: 28rem; padding: 24px; border: 1px solid #fecdd3; border-radius: 12px; background: #fff1f2; }
        h1 { margin: 0 0 8px; font-size: 1.125rem; color: #9f1239; }
        p { margin: 0 0 16px; font-size: 0.875rem; color: #881337; }
        a { font-size: 0.875rem; font-weight: 600; color: #2563eb; }
    </style>
</head>
<body>
    <div class="carte">
        <h1>Envoi refusé</h1>
        <p>{{ $message ?? 'Le fichier envoyé est trop volumineux.' }}</p>
        <a href="{{ url()->previous() }}">Revenir au formulaire</a>
    </div>
</body>
</html>
