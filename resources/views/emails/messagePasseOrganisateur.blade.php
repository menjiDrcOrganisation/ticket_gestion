@extends('emails.layouts.base')

@section('title', 'Bienvenue sur '.config('app.name'))
@section('heading', 'Votre espace organisateur est prêt')
@section('greeting', 'Bonjour '.$nom_client.',')

@section('content')
    <p>Votre compte organisateur a été créé. Vous pouvez accéder à votre espace de gestion ici :</p>
    <p><a href="{{ route('login') }}">Se connecter à mon espace organisateur</a></p>

    <ul>
        <li><strong>Adresse e-mail :</strong> {{ $email }}</li>
        <li><strong>Mot de passe temporaire :</strong> {{ $mot_de_passe }}</li>
    </ul>

    <p><strong>Important :</strong> ce mot de passe temporaire doit être changé lors de votre première connexion.</p>

    @include('emails.partials.evenement-scanneur')

    <p>Vous pourrez créer vos prochains événements avec la même adresse e-mail ; ils seront rattachés à ce compte.</p>
@endsection
