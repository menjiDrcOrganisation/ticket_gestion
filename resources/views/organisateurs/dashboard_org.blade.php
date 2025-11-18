@extends('layouts.org')

@section('content')
<div class="max-w-6xl mx-auto mt-10">

    <!-- Titre de l'événement -->
    <h2 class="text-3xl font-bold mb-6"> {{ $evenement->nom }}</h2>

    <!-- Cartes Totaux -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-indic-dashboard :value="$revenusCDF" title="Total encaissé en franc"></x-indic-dashboard>
        <x-indic-dashboard :value="$revenusUSD" title="Total encaissé en dollar"></x-indic-dashboard>
    </div>

    <!-- Informations globales -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <h3 class="text-xl font-semibold mb-3">Informations générales</h3>
        <ul class="space-y-2 text-gray-700">
            <li><strong>Total billets vendus :</strong> {{ $totalBilletsVendus }}</li>
            <li><strong>Billets scannés :</strong> {{ $billetsScannes }}</li>
        </ul>
    </div>

    <!-- Billets par catégorie -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <h3 class="text-xl font-semibold mb-3">Billets par catégorie</h3>
        @if(count($typesBillets) > 0)
            <ul class="space-y-2 text-gray-700">
                @foreach($typesBillets as $type => $count)
                    <li><strong>{{ $type }} :</strong> {{ $count }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">Aucun billet vendu par catégorie.</p>
        @endif
    </div>

    <!-- Derniers achats -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <h3 class="text-xl font-semibold mb-3">Derniers achats</h3>
        @if(count($derniersAchats) > 0)
            <ul class="space-y-2 text-gray-700">
                @foreach($derniersAchats as $achat)
                    <li>
                        {{ $achat->type_billet->first()->nom_type ?? 'Type inconnu' }} -
                        {{ $achat->created_at->format('d/m/Y H:i') }}
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">Aucun achat récent.</p>
        @endif
    </div>

</div>
@endsection
