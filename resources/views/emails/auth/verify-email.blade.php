@extends('emails.layouts.base')

@section('title', 'Confirmation de votre adresse e-mail')
@section('heading', 'Confirmez votre adresse e-mail')
@section('greeting', 'Bonjour '.$recipientName.',')

@section('content')
    <p>Pour terminer la configuration de votre compte, confirmez votre adresse e-mail à l’aide du lien ci-dessous :</p>
    <p style="margin:24px 0;">
        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 20px;border-radius:8px;background:#0f62fe;color:#ffffff;text-decoration:none;font-weight:bold;">
            Confirmer mon adresse e-mail
        </a>
    </p>
    <p>Si vous n’avez pas créé de compte, ignorez cet e-mail.</p>
@endsection
