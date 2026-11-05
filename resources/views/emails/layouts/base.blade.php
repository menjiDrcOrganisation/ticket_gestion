<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f3f4f6;font-family:Arial,sans-serif;color:#334155;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:12px;">
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 20px;color:#0b3b82;font-size:22px;">@yield('heading', config('app.name'))</h1>
                            @hasSection('greeting')
                                <p style="line-height:1.6;">@yield('greeting')</p>
                            @endif
                            <div style="line-height:1.6;">
                                @yield('content')
                            </div>
                            <p style="margin-top:24px;line-height:1.6;">Cordialement,<br><strong>{{ config('app.name') }}</strong></p>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0;color:#64748b;font-size:12px;">
                    © {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
