@extends('emails.layouts.base')

@section('title', 'Réinitialisation de votre mot de passe')
@section('heading', 'Réinitialisation de votre mot de passe')
@section('greeting', 'Bonjour,')

@section('content')
    <p>Nous avons reçu une demande de réinitialisation du mot de passe de votre compte. Utilisez le lien ci-dessous pour en choisir un nouveau :</p>
    <p style="margin:24px 0;">
        <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background:#0f62fe;color:#ffffff;text-decoration:none;font-weight:bold;">
            Réinitialiser mon mot de passe
        </a>
    </p>
    <p>Ce lien expirera dans <strong>{{ $expires }}</strong>. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail ; votre mot de passe restera inchangé.</p>
    <p>Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :</p>
    <p style="word-break:break-all;"><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
    @if($supportEmail || $supportPhone)
        <p>
            Besoin d’aide ?
            @if($supportEmail)<a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>@endif
            @if($supportPhone) — {{ $supportPhone }}@endif
        </p>
    @endif
@endsection
