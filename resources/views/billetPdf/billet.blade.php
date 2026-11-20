@php
    /*
     * Données attendues dans $ticket (toutes optionnelles, valeurs par défaut prévues) :
     * type, event_name, event_subtitle, ticket_id, location, event_date, event_time,
     * quantity, user_name, qrcode_url, total (ou price), devise, payment_method,
     * purchase_date, photo_affiche
     */
    $t = $ticket ?? [];

    // ---- Thème selon la catégorie : VIP / Invitation / Standard (défaut) ----
    $category = $t['type'] ?? 'Standard';
    $key = mb_strtolower(trim($category));
    if (stripos($key, 'vip') !== false) {
        $theme = 'vip';
    } elseif (stripos($key, 'invit') !== false) {
        $theme = 'invitation';
    } else {
        $theme = 'standard';
    }

    // ---- Valeurs affichées ----
    $eventName = $t['event_name'] ?? 'SPECTACLE LIVE';
    $subtitle  = $t['event_subtitle'] ?? '';
    $ticketId  = $t['ticket_id'] ?? ('KM' . rand(10000, 99999));
    $location  = $t['location'] ?? 'Lieu principal';
    $eventDate = $t['event_date'] ?? '--/--/----';
    $eventTime = $t['event_time'] ?? '--:--';
    $userName  = $t['user_name'] ?? 'Participant';
    $qr        = $t['qrcode_url'] ?? '';
    $payment   = $t['payment_method'] ?? 'Mobile Money';
    $purchase  = $t['purchase_date'] ?? date('d/m/Y · H:i');

    $qty      = (int) ($t['quantity'] ?? 1);
    $qtyLabel = $qty . ' billet' . ($qty > 1 ? 's' : '');

    $devise = $t['devise'] ?? 'CDF';
    $amount = $t['total'] ?? ($t['price'] ?? 0);
    if (is_numeric($amount)) {
        $amount = number_format((float) $amount, 0, ',', ' ');
    }
    $totalLabel = $amount . ' ' . $devise;

    // ---- Image d'affiche (optionnelle) en fond de l'en-tête ----
    $posterStyle = '';
    if (!empty($t['photo_affiche'])) {
        $posterUrl = env('ENV_POINT_URL') . '/storage/app/public/' . $t['photo_affiche'];
        $posterStyle = "background-image: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)), url('{$posterUrl}'); background-size: cover; background-position: center;";
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billet {{ $category }} - Kimia Ticket</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<style>
    :root { --page: #EEF0F4; --red: #E8343A; }

    /* ===== Thème STANDARD (clair, en-tête rouge) ===== */
    .theme-standard {
        --card: #FFFFFF;
        --text: #0B0B0C;
        --label: #6E6E73;
        --line: #E6E6E9;
        --dash: #D9DADF;
        --detach: #8A8A8F;
        --uid: #6E6E73;
        --present: #D90000;
        --panel: #F4F3EF;
        --sub: #8A8A8F;
        --qr-border: 1px solid #E6E6E9;
        --hero-bg: linear-gradient(160deg, #3A0000 0%, #700000 50%, #A00000 100%);
        --badge-bg: #E10600;
        --badge-color: #FFFFFF;
        --badge-border: transparent;
        --foot-red: #D90000;
    }
    /* ===== Thème INVITATION (clair, en-tête noir) ===== */
    .theme-invitation {
        --card: #FFFFFF;
        --text: #0B0B0C;
        --label: #6E6E73;
        --line: #E6E6E9;
        --dash: #D9DADF;
        --detach: #8A8A8F;
        --uid: #6E6E73;
        --present: #D90000;
        --panel: #FFF0F0;
        --sub: #8A8A8F;
        --qr-border: 1px solid #E6E6E9;
        --hero-bg: linear-gradient(160deg, #000000 0%, #150000 55%, #2A0505 100%);
        --badge-bg: rgba(0,0,0,.65);
        --badge-color: #FFFFFF;
        --badge-border: rgba(255,255,255,.10);
        --foot-red: #D90000;
    }
    /* ===== Thème VIP (sombre) ===== */
    .theme-vip {
        --card: #111113;
        --text: #FFFFFF;
        --label: #8E8E93;
        --line: rgba(255,255,255,.10);
        --dash: rgba(255,255,255,.18);
        --detach: #7D7D82;
        --uid: #9A9AA0;
        --present: #E8343A;
        --panel: #1A1A1D;
        --sub: #8E8E93;
        --qr-border: 0;
        --hero-bg: linear-gradient(160deg, #0B0000 0%, #260000 45%, #520000 100%);
        --badge-bg: #FFFFFF;
        --badge-color: #111111;
        --badge-border: transparent;
        --foot-red: #E8343A;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: var(--page);
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 40px 16px;
    }

    .ticket {
        position: relative;
        width: 100%;
        max-width: 560px;
        background: var(--card);
        color: var(--text);
        border-radius: 32px;
        overflow: hidden;
    }

    /* ---------- En-tête ---------- */
    .hero {
        position: relative;
        height: 312px;
        padding: 30px 29px 33px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: var(--hero-bg);
        color: #fff;
        overflow: hidden;
    }
    .hero svg.bg { position: absolute; inset: 0; width: 100%; height: 100%; }
    .hero > *:not(svg.bg) { position: relative; z-index: 2; }

    .hero-top { display: flex; justify-content: space-between; align-items: center; }
    .brand-line {
        display: flex; align-items: center; gap: 10px;
        font-size: 12.5px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase;
    }
    .brand-line svg { width: 22px; height: 29px; color: #fff; }

    .ref-pill {
        font-size: 13px; font-weight: 700; letter-spacing: .8px;
        padding: 8px 20px; border-radius: 999px;
        border: 1px solid rgba(255,255,255,.28);
        background: rgba(0,0,0,.35);
    }

    .hero-bottom { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; }
    .event-title {
        font-family: 'Poppins', sans-serif; font-weight: 700;
        font-size: 38px; line-height: 1.1; letter-spacing: -.3px;
        text-transform: uppercase;
    }
    .event-sub { margin-top: 6px; font-size: 14.5px; font-weight: 500; color: rgba(255,255,255,.85); }
    .badge {
        background: var(--badge-bg);
        color: var(--badge-color);
        border: 1px solid var(--badge-border);
        font-size: 13px; font-weight: 800; letter-spacing: 1.6px; text-transform: uppercase;
        padding: 9px 20px; border-radius: 10px; margin-bottom: 26px; white-space: nowrap;
    }

    /* ---------- Informations ---------- */
    .infos { padding: 0 29px; }
    .row { display: grid; padding: 20px 0 14px; border-bottom: 1px solid var(--line); }
    .row:last-child { border-bottom: 0; }
    .row.cols3 { grid-template-columns: 185px 185px 1fr; }
    .row.cols2 { grid-template-columns: 1fr auto; }
    .row.cols2 .right { width: 161px; }
    .lbl {
        font-size: 10.5px; font-weight: 600; letter-spacing: 2px;
        text-transform: uppercase; color: var(--label); margin-bottom: 5px;
    }
    .val { font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 18px; line-height: 1.25; }
    .val.name { font-weight: 700; }
    .val.place { font-size: 17px; }

    .detach {
        text-align: center; font-size: 10.5px; font-weight: 600; letter-spacing: 2.2px;
        text-transform: uppercase; color: var(--detach); margin-top: 24px;
    }

    /* ---------- Perforation ---------- */
    .perf { position: relative; height: 40px; margin-top: 4px; }
    .perf::before, .perf::after {
        content: ''; position: absolute; top: 0; width: 40px; height: 40px;
        border-radius: 50%; background: var(--page);
    }
    .perf::before { left: -20px; }
    .perf::after  { right: -20px; }
    .perf .dash { position: absolute; left: 36px; right: 36px; top: 19px; border-top: 2px dashed var(--dash); }

    /* ---------- QR ---------- */
    .qr-zone { text-align: center; padding: 0 29px; }
    .qr-box {
        position: relative; display: inline-block; width: 278px; height: 278px;
        background: #fff; border: var(--qr-border); border-radius: 24px; padding: 14px; margin-top: 2px;
    }
    .qr-box img.qr { width: 100%; height: 100%; display: block; }
    .qr-box .qr-logo {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 44px; height: 44px; background: #fff; border-radius: 12px; display: grid; place-items: center;
    }
    .qr-box .qr-logo svg { width: 36px; height: 36px; }

    .present {
        margin-top: 22px; font-size: 13px; font-weight: 800; letter-spacing: 1.8px;
        text-transform: uppercase; color: var(--present);
    }
    .uid { margin-top: 8px; font-size: 12.5px; color: var(--uid); }

    /* ---------- Paiement ---------- */
    .pay {
        margin: 22px 21px 0; background: var(--panel); border-radius: 14px; padding: 16px 19px;
        display: grid; grid-template-columns: 171px 171px 1fr;
    }
    .pay .lbl { font-size: 9.5px; margin-bottom: 6px; letter-spacing: 1.6px; }
    .pay .v { font-size: 13.5px; font-weight: 700; }

    /* ---------- Pied ---------- */
    .foot { display: flex; justify-content: space-between; align-items: center; padding: 16px 29px 14px; }
    .foot-left { display: flex; align-items: center; gap: 12px; }
    .foot-left svg { width: 28px; height: 28px; flex: none; }
    .foot-left b { display: block; font-size: 12.5px; font-weight: 700; }
    .foot-left small, .foot-right small { display: block; font-size: 11px; color: var(--sub); margin-top: 2px; }
    .foot-right { text-align: right; }
    .foot-right b { display: block; font-size: 12.5px; font-weight: 800; color: var(--foot-red); }

    @media (max-width: 480px) {
        .event-title { font-size: 30px; }
        .row.cols3 { grid-template-columns: 1fr 1fr 1fr; }
        .qr-box { width: 240px; height: 240px; }
        .pay { grid-template-columns: 1fr 1fr 1.2fr; }
        .foot-left small, .foot-right small { font-size: 9.5px; }
    }
    @media print {
        body { background: #fff; padding: 0; }
        .ticket { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
</head>
<body>

<!-- Logo officiel Kimia Ticket (symbole extrait de la charte graphique) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <symbol id="logo-k" viewBox="145.8 146.6 166.2 217.0">
            <path fill="currentColor" fill-rule="evenodd" d="M 155.660156 259.414062 C 182.550781 256.375 207.574219 244.820312 224.90625 227.570312 C 244.714844 207.847656 256.183594 181.785156 259.007812 150.289062 L 259.269531 147.59375 L 146.753906 147.59375 L 146.753906 260.371094 L 148.402344 260.242188 C 149.273438 260.152344 152.574219 259.761719 155.660156 259.414062 Z M 188.152344 203.289062 C 176.859375 199.246094 173.730469 184.78125 182.464844 176.917969 C 185.589844 174.09375 188.023438 173.09375 192.367188 172.878906 C 197.234375 172.660156 200.101562 173.660156 203.621094 176.960938 C 207.226562 180.304688 208.660156 183.519531 208.660156 188.429688 C 208.660156 191.730469 208.484375 192.46875 207.269531 194.988281 C 204.574219 200.421875 199.667969 203.679688 193.671875 203.9375 C 191.28125 204.070312 189.804688 203.894531 188.152344 203.289062 Z M 188.152344 203.289062 "/>
            <path fill="currentColor" fill-rule="evenodd" d="M 307.707031 343 C 308.488281 341.261719 309.488281 338.566406 309.96875 337.003906 C 310.707031 334.3125 310.75 330.574219 310.75 255.332031 L 310.75 176.484375 L 309.359375 172.355469 C 307.96875 168.273438 304.535156 162.0625 302.753906 160.453125 C 302.234375 159.976562 301.84375 159.714844 301.84375 159.84375 C 301.84375 159.933594 301.453125 162.277344 300.929688 165.015625 C 296.628906 189.125 286.421875 211.5 271 230.785156 C 263.917969 239.648438 250.796875 252.335938 243.28125 257.546875 L 242.285156 258.285156 L 273.910156 302.207031 C 291.285156 326.359375 305.667969 346.128906 305.882812 346.128906 C 306.058594 346.128906 306.882812 344.695312 307.707031 343 Z M 307.707031 343 "/>
            <path fill="currentColor" fill-rule="evenodd" d="M 265.699219 362.246094 C 265.613281 362.070312 252.273438 343 236.027344 319.976562 L 206.488281 278.050781 L 204.316406 278.878906 C 191.066406 284.179688 174.339844 288.261719 158.484375 290.085938 C 154.050781 290.609375 149.621094 291.128906 148.578125 291.214844 L 146.753906 291.433594 L 146.753906 362.636719 L 206.3125 362.636719 C 239.328125 362.636719 265.785156 362.460938 265.699219 362.246094 Z M 265.699219 362.246094 "/>
        </symbol>
    </defs>
</svg>

{{-- TICKET-START --}}
<div class="ticket theme-{{ $theme }}">

    <!-- En-tête -->
    <div class="hero" style="{{ $posterStyle }}">
        <svg class="bg" viewBox="0 0 560 312" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <linearGradient id="beam-{{ $ticketId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#ffffff" stop-opacity=".22"/>
                    <stop offset="1" stop-color="#ffffff" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <polygon points="115,0 155,0 235,312 40,312" fill="url(#beam-{{ $ticketId }})" opacity=".75"/>
            <polygon points="278,0 306,0 400,312 195,312" fill="url(#beam-{{ $ticketId }})" opacity=".8"/>
            <polygon points="445,0 470,0 560,312 360,312" fill="url(#beam-{{ $ticketId }})" opacity=".8"/>
            <circle cx="520" cy="-10" r="330" fill="none" stroke="rgba(255,255,255,.14)" stroke-width="1.2"/>
            <circle cx="520" cy="-10" r="250" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="1.2"/>
            <circle cx="600" cy="-60" r="360" fill="none" stroke="#C4161C" stroke-width="5"/>
            <g fill="#080000">
                <circle cx="-4.0" cy="300.6" r="6.3"/>
                <ellipse cx="-4.0" cy="312.9" rx="9.5" ry="9"/>
                <circle cx="8.9" cy="303.3" r="5.7"/>
                <ellipse cx="8.9" cy="314.9" rx="8.5" ry="9"/>
                <circle cx="20.1" cy="303.1" r="5.6"/>
                <ellipse cx="20.1" cy="314.7" rx="8.5" ry="9"/>
                <circle cx="29.3" cy="300.0" r="6.6"/>
                <ellipse cx="29.3" cy="312.6" rx="9.9" ry="9"/>
                <circle cx="38.9" cy="305.3" r="6.6"/>
                <ellipse cx="38.9" cy="317.8" rx="9.8" ry="9"/>
                <circle cx="48.6" cy="303.9" r="6.1"/>
                <ellipse cx="48.6" cy="316.0" rx="9.1" ry="9"/>
                <circle cx="63.3" cy="302.3" r="6.9"/>
                <ellipse cx="63.3" cy="315.2" rx="10.4" ry="9"/>
                <circle cx="78.2" cy="305.5" r="5.6"/>
                <ellipse cx="78.2" cy="317.1" rx="8.4" ry="9"/>
                <circle cx="88.9" cy="300.3" r="5.9"/>
                <ellipse cx="88.9" cy="312.2" rx="8.8" ry="9"/>
                <circle cx="99.7" cy="300.8" r="7.5"/>
                <ellipse cx="99.7" cy="314.3" rx="11.3" ry="9"/>
                <circle cx="112.2" cy="302.1" r="7.1"/>
                <ellipse cx="112.2" cy="315.2" rx="10.6" ry="9"/>
                <circle cx="124.5" cy="299.9" r="5.7"/>
                <ellipse cx="124.5" cy="311.6" rx="8.5" ry="9"/>
                <circle cx="134.8" cy="302.5" r="7.2"/>
                <ellipse cx="134.8" cy="315.7" rx="10.8" ry="9"/>
                <circle cx="145.6" cy="302.7" r="7.0"/>
                <ellipse cx="145.6" cy="315.6" rx="10.4" ry="9"/>
                <circle cx="156.4" cy="304.4" r="7.5"/>
                <ellipse cx="156.4" cy="317.9" rx="11.2" ry="9"/>
                <circle cx="166.9" cy="303.2" r="6.9"/>
                <ellipse cx="166.9" cy="316.1" rx="10.4" ry="9"/>
                <circle cx="181.2" cy="301.5" r="7.3"/>
                <ellipse cx="181.2" cy="314.8" rx="11.0" ry="9"/>
                <circle cx="196.0" cy="302.4" r="5.8"/>
                <ellipse cx="196.0" cy="314.2" rx="8.7" ry="9"/>
                <circle cx="209.6" cy="302.9" r="5.9"/>
                <ellipse cx="209.6" cy="314.8" rx="8.8" ry="9"/>
                <circle cx="218.8" cy="304.9" r="7.2"/>
                <ellipse cx="218.8" cy="318.0" rx="10.8" ry="9"/>
                <circle cx="231.3" cy="301.7" r="7.7"/>
                <ellipse cx="231.3" cy="315.4" rx="11.5" ry="9"/>
                <circle cx="244.4" cy="303.6" r="7.0"/>
                <ellipse cx="244.4" cy="316.5" rx="10.5" ry="9"/>
                <circle cx="256.2" cy="306.1" r="7.6"/>
                <ellipse cx="256.2" cy="319.7" rx="11.4" ry="9"/>
                <circle cx="268.0" cy="299.9" r="7.2"/>
                <ellipse cx="268.0" cy="313.1" rx="10.7" ry="9"/>
                <circle cx="281.2" cy="306.5" r="7.1"/>
                <ellipse cx="281.2" cy="319.6" rx="10.7" ry="9"/>
                <circle cx="295.1" cy="302.2" r="6.2"/>
                <ellipse cx="295.1" cy="314.4" rx="9.3" ry="9"/>
                <circle cx="308.2" cy="302.7" r="5.6"/>
                <ellipse cx="308.2" cy="314.3" rx="8.3" ry="9"/>
                <circle cx="318.2" cy="299.9" r="5.8"/>
                <ellipse cx="318.2" cy="311.7" rx="8.7" ry="9"/>
                <circle cx="331.8" cy="301.2" r="5.8"/>
                <ellipse cx="331.8" cy="313.1" rx="8.7" ry="9"/>
                <circle cx="343.1" cy="300.1" r="7.7"/>
                <ellipse cx="343.1" cy="313.7" rx="11.5" ry="9"/>
                <circle cx="354.8" cy="305.7" r="6.9"/>
                <ellipse cx="354.8" cy="318.6" rx="10.3" ry="9"/>
                <circle cx="368.7" cy="301.4" r="7.7"/>
                <ellipse cx="368.7" cy="315.1" rx="11.5" ry="9"/>
                <circle cx="380.2" cy="305.7" r="6.4"/>
                <ellipse cx="380.2" cy="318.1" rx="9.6" ry="9"/>
                <circle cx="395.0" cy="300.7" r="5.9"/>
                <ellipse cx="395.0" cy="312.6" rx="8.8" ry="9"/>
                <circle cx="405.4" cy="302.9" r="6.1"/>
                <ellipse cx="405.4" cy="315.0" rx="9.1" ry="9"/>
                <circle cx="417.9" cy="299.5" r="6.2"/>
                <ellipse cx="417.9" cy="311.7" rx="9.2" ry="9"/>
                <circle cx="429.4" cy="303.5" r="6.4"/>
                <ellipse cx="429.4" cy="315.9" rx="9.6" ry="9"/>
                <circle cx="444.1" cy="303.1" r="7.2"/>
                <ellipse cx="444.1" cy="316.3" rx="10.8" ry="9"/>
                <circle cx="456.8" cy="299.9" r="7.2"/>
                <ellipse cx="456.8" cy="313.1" rx="10.8" ry="9"/>
                <circle cx="471.2" cy="305.6" r="7.4"/>
                <ellipse cx="471.2" cy="319.1" rx="11.2" ry="9"/>
                <circle cx="485.0" cy="302.3" r="6.5"/>
                <ellipse cx="485.0" cy="314.8" rx="9.7" ry="9"/>
                <circle cx="494.6" cy="299.9" r="7.1"/>
                <ellipse cx="494.6" cy="313.0" rx="10.6" ry="9"/>
                <circle cx="504.0" cy="300.6" r="6.0"/>
                <ellipse cx="504.0" cy="312.7" rx="9.0" ry="9"/>
                <circle cx="515.1" cy="299.5" r="5.6"/>
                <ellipse cx="515.1" cy="311.1" rx="8.4" ry="9"/>
                <circle cx="525.0" cy="302.0" r="5.8"/>
                <ellipse cx="525.0" cy="313.8" rx="8.6" ry="9"/>
                <circle cx="534.1" cy="303.8" r="7.7"/>
                <ellipse cx="534.1" cy="317.5" rx="11.5" ry="9"/>
                <circle cx="544.0" cy="301.9" r="6.1"/>
                <ellipse cx="544.0" cy="314.1" rx="9.2" ry="9"/>
                <circle cx="555.2" cy="305.4" r="5.8"/>
                <ellipse cx="555.2" cy="317.2" rx="8.7" ry="9"/>
                <rect x="0" y="306" width="560" height="8"/>
            </g>
        </svg>

        <div class="hero-top">
            <div class="brand-line">
                <svg><use href="#logo-k"/></svg>
                <span>Billet électronique</span>
            </div>
            <div class="ref-pill">#{{ $ticketId }}</div>
        </div>

        <div class="hero-bottom">
            <div>
                <div class="event-title">{{ $eventName }}</div>
                <div class="event-sub">{{ $subtitle }}</div>
            </div>
            <div class="badge">{{ $category }}</div>
        </div>
    </div>

    <!-- Informations -->
    <div class="infos">
        <div class="row cols3">
            <div><div class="lbl">Date</div><div class="val">{{ $eventDate }}</div></div>
            <div><div class="lbl">Heure</div><div class="val">{{ $eventTime }}</div></div>
            <div><div class="lbl">Places</div><div class="val">{{ $qtyLabel }}</div></div>
        </div>
        <div class="row">
            <div class="lbl">Lieu</div>
            <div class="val place">{{ $location }}</div>
        </div>
        <div class="row cols2">
            <div><div class="lbl">Titulaire</div><div class="val name">{{ $userName }}</div></div>
            <div class="right"><div class="lbl">Catégorie</div><div class="val">{{ $category }}</div></div>
        </div>
    </div>

    <div class="detach">Détachez-moi · Contrôle à l'entrée</div>

    <!-- Perforation -->
    <div class="perf"><div class="dash"></div></div>

    <!-- QR -->
    <div class="qr-zone">
        <div class="qr-box">
            <img class="qr" alt="QR code du billet" src="{{ $qr }}">
            <div class="qr-logo">
                <svg viewBox="0 0 40 40">
                    <rect x="2" y="2" width="36" height="36" rx="9" fill="#FF0000"/>
                    <use href="#logo-k" x="11" y="9" width="18" height="23" style="color:#fff"/>
                </svg>
            </div>
        </div>
        <div class="present">Présente ce code à l'entrée</div>
        <div class="uid">ID unique : {{ $ticketId }}</div>
    </div>

    <!-- Paiement -->
    <div class="pay">
        <div><div class="lbl">Prix payé</div><div class="v">{{ $totalLabel }}</div></div>
        <div><div class="lbl">Paiement</div><div class="v">{{ $payment }}</div></div>
        <div><div class="lbl">Acheté le</div><div class="v">{{ $purchase }}</div></div>
    </div>

    <!-- Pied -->
    <div class="foot">
        <div class="foot-left">
            <svg viewBox="0 0 40 40"><rect x="0" y="0" width="40" height="40" rx="9" fill="#FF0000"/><use href="#logo-k" x="9" y="7" width="22" height="26" style="color:#fff"/></svg>
            <div>
                <b>Billet sécurisé Kimia Ticket</b>
                <small>Personnel · non transférable · valable une seule fois</small>
            </div>
        </div>
        <div class="foot-right">
            <b>kimiaticket.com</b>
            <small>WhatsApp 0893 572 418</small>
        </div>
    </div>

</div>
{{-- TICKET-END --}}

</body>
</html>
