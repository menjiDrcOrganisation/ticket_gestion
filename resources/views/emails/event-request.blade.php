@extends('emails.layouts.base')

@section('title', 'Nouvelle demande d’événement')
@section('heading', 'Nouvelle demande d’événement')
@section('greeting', 'Bonjour équipe,')

@section('content')
    <p>Une demande d’événement a été soumise par <strong>{{ $event->contact_organisateur }}</strong>.</p>
    <h2 style="font-size:18px;">Détails de l’événement</h2>
    <p><strong>Nom :</strong> {{ $event->nom_evenement }}</p>
    <p><strong>Type :</strong> {{ $event->type_evenement }}</p>
    <p><strong>Description :</strong><br>{{ $event->description }}</p>
    <p><strong>Statut :</strong> {{ $event->statut }}</p>
    @if($event->affiche)
        <p><a href="{{ asset('storage/'.$event->affiche) }}">Consulter l’affiche de l’événement</a></p>
    @endif
    <p>Merci d’examiner cette demande.</p>
@endsection
