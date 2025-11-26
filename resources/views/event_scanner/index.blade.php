@extends('layouts.org')

@section('content')
<div class="container mx-auto px-6 py-10">

    <!-- Titre -->
    <h1 class="text-4xl font-extrabold text-gray-800 mb-8">
        🎉 Événement : {{ $evenement->nom ??  " " }}
    </h1>

    <!-- Carte principale -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Informations de l'événement -->
        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">📌 Informations</h2>

            <p><span class="font-semibold">Nom :</span> {{ $evenement->nom }}</p>
            <p><span class="font-semibold">Adresse :</span> {{ $evenement->adresse }}</p>
            <p><span class="font-semibold">Salle :</span> {{ $evenement->salle }}</p>

            <p><span class="font-semibold">Date début :</span> {{ $evenement->date_debut }}</p>
            <p><span class="font-semibold">Date fin :</span> {{ $evenement->date_fin }}</p>

            <p><span class="font-semibold">Heure début :</span> {{ $evenement->heure_debut }}</p>
            <p><span class="font-semibold">Heure fin :</span> {{ $evenement->heure_fin }}</p>

            <span class="px-3 mt-4 inline-block py-1 rounded-full text-white
                {{ $evenement->statut == 'encours' ? 'bg-green-600' : 'bg-gray-500' }}">
                {{ ucfirst($evenement->statut) }}
            </span>
        </div>

        <!-- Organisateur & Scanneur -->
        <div class="bg-white shadow-lg rounded-xl p-6 border">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">👤 Organisateur</h2>

            <p><span class="font-semibold">Téléphone :</span> {{ $evenement->organisateur->telephone }}</p>
            <p><span class="font-semibold">Email :</span> {{ $evenement->organisateur->user->email ?? " " }}</p>    

            <hr class="my-4">

            <h3 class="text-xl font-bold text-gray-700 mb-2">🔍 Scanneur</h3>

            @if($evenement->scanneur)
                <p>Nom : {{ $evenement->scanneur->nom }}</p>
                <p>Téléphone : {{ $evenement->scanneur->telephone }}</p>
            @else
                <p class="text-gray-500 italic">Aucun scanneur assigné.</p>
            @endif
        </div>

    </div>

    <!-- Types de billets -->
    <div class="bg-white shadow-lg rounded-xl p-6 border mt-10">
        <h2 class="text-2xl font-bold text-gray-700 mb-4">🎫 Types de billets</h2>

        @forelse($evenement->typeBillets as $billet)
            <div class="p-3 border rounded-lg mb-2">
                <p class="text-lg font-semibold">{{ $billet->nom_type }}</p>
            </div>
        @empty
            <p class="text-gray-500 italic">Aucun type de billet ajouté.</p>
        @endforelse
    </div>

    <!-- URL de l'événement -->
    <div class="mt-10">
        <h2 class="text-xl font-bold text-gray-700 mb-2">🔗 Lien public</h2>
        <a href="/event/{{ $evenement->url_evenement }}"
           class="text-blue-600 font-semibold underline">
            https://kimiaticket.com/{{ $evenement->url_evenement }}
        </a>
    </div>

</div>
@endsection
