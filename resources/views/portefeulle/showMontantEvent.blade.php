@extends('layouts.main')

@section('content')

<div class="max-w-6xl mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-6">Portefeuille </h1>
    <div class="flex gap-4 mb-6">
        <x-indic-dashboard :value="$totalEnCdf" title="Total encaissé en franc"></x-indic-dashboard>
        <x-indic-dashboard :value="$totalEnUsd" title="Total encaissé en dollar"></x-indic-dashboard>
    </div>

    <!-- Tableau détaillé par événement -->
    <table class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 border text-left">Événement</th>
                <th class="p-3 border text-left">Date</th>
                <th class="p-3 border text-left">Billets vendus</th>
                <th class="p-3 border text-left">Montant CDF</th>
                <th class="p-3 border text-left">Montant USD</th>
                
               
                <th class="p-3 border text-left">Billets par type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($montantParEvenement as $event)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 border">{{ $event['nom'] }}</td>
                    <td class="p-3 border">{{ $event['date'] ?? '-' }}</td>
                    <td class="p-3 border">{{ $event['nb_billets'] }}</td>
                    <td class="p-3 border">{{ number_format($event['CDF'], 0, ',', ' ') }} CDF</td>
                    <td class="p-3 border">{{ number_format($event['USD'], 0, ',', ' ') }} USD</td>
                  
                    
                    <td class="p-3 border">
                        @foreach ($event['types'] as $type => $count)
                            {{ $type }}: {{ $count }}<br>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center p-4 text-gray-500">
                        Aucun billet trouvé.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection
