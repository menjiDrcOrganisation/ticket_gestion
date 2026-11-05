@extends('emails.layouts.base')

@section('title', 'Génération de billet en échec')
@section('heading', 'Génération de billet en échec')
@section('greeting', 'Bonjour équipe d’administration,')

@section('content')
    <p>La génération d’un billet PDF a échoué après plusieurs tentatives.</p>
    @if($transactionReference)
        <p>Référence de transaction : <strong>{{ $transactionReference }}</strong></p>
    @endif
    <p>Veuillez vérifier la transaction et relancer la génération du billet si nécessaire.</p>
@endsection
