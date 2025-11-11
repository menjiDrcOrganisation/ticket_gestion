@extends('layouts.org')

@section('content')
<div class="container mx-auto px-6 py-10">
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-2xl p-8 border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">🎉 Détails de l'Événement</h1>
            <a href="{{ route('evenements.index') }}" 
               class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded-md transition">
                ← Retour
            </a>
        </div>

        {{-- Informations principales --}}
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">{{ $evenement->nom }}</h2>
                <p class="text-gray-500 text-sm">{{ $evenement->url_evenement }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">📅 Date de début :</p>
                    <p>{{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">📅 Date de fin :</p>
                    <p>{{ \Carbon\Carbon::parse($evenement->date_fin)->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">🕒 Heure de début :</p>
                    <p>{{ $evenement->heure_debut }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">🕓 Heure de fin :</p>
                    <p>{{ $evenement->heure_fin }}</p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="font-medium text-gray-600">📍 Adresse :</p>
                <pre class="whitespace-pre-wrap text-gray-800">{{ $evenement->adresse }}</pre>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">🏛️ Salle :</p>
                    <p>{{ $evenement->salle }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium text-gray-600">📌 Statut :</p>
                    <span class="px-3 py-1 rounded-full text-sm 
                        @if($evenement->statut == 'encours') bg-yellow-100 text-yellow-800
                        @elseif($evenement->statut == 'terminé') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ ucfirst($evenement->statut) }}
                    </span>
                </div>
            </div>

            {{-- Informations liées à l'organisateur --}}
            <div class="p-4 bg-gray-100 rounded-lg border border-gray-200 mt-4">
                <h3 class="font-semibold text-gray-800 mb-2">👤 Organisateur</h3>
                <p class="text-gray-700">
                    <span class="font-medium">ID :</span> {{ $evenement->organisateur_id }}
                </p>
            </div>

            {{-- Date de création et mise à jour --}}
            <div class="flex justify-between text-sm text-gray-500 mt-6">
                <p>Créé le : {{ $evenement->created_at->format('d/m/Y à H:i') }}</p>
                <p>Mis à jour le : {{ $evenement->updated_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
