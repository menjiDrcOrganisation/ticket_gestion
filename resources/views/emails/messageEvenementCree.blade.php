@extends('emails.layouts.base')

@section('title', 'Votre événement est créé')
@section('heading', 'Votre événement est créé')
@section('greeting', 'Bonjour '.$nom_client.',')

@section('content')
    <p>Votre nouvel événement a été créé et rattaché à votre compte organisateur.</p>
    <p>Connectez-vous avec vos identifiants habituels pour gérer votre compte :</p>
    <p><a href="{{ route('login') }}">Accéder à mon espace organisateur</a></p>

    @include('emails.partials.evenement-scanneur')
@endsection
