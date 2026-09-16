@extends('layouts.main')
@section('title', 'Portefeuilles')

@section('content')

<div class="max-w-6xl mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-6">Portefeuille </h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-indic-dashboard :value="$totalEnCdf" title="Total encaissé en franc" subtitle="CDF" icon="banknotes" tone="emerald" />
        <x-indic-dashboard :value="$totalEnUsd" title="Total encaissé en dollar" subtitle="USD" icon="currency-dollar" tone="blue" />
    </div>

    <form method="GET" action="{{ route('portefeulle.showMontantEvent') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-12 md:items-end">
        <div class="md:col-span-8">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche événement</label>
            <x-app-input id="q" name="q" :value="$search" placeholder="Nom de l'événement..." wrapperClass="mb-0" />
        </div>

        <div class="md:col-span-4">
            <label for="devise" class="mb-1.5 block text-sm font-medium text-slate-600">Devise</label>
            <x-app-select id="devise" name="devise" wrapperClass="mb-0">
                <option value="">Toutes</option>
                <option value="CDF" @selected($devise === 'CDF')>CDF</option>
                <option value="USD" @selected($devise === 'USD')>USD</option>
            </x-app-select>
        </div>

        <div class="md:col-span-12 flex flex-wrap gap-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Filtrer
            </button>
            <a href="{{ route('portefeulle.showMontantEvent') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Réinitialiser
            </a>
        </div>
    </form>

    <!-- Tableau détaillé par événement -->
    <x-app-table minWidth="1050px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Événement</x-app-th>
                <x-app-th>Date</x-app-th>
                <x-app-th>Billets vendus</x-app-th>
                <x-app-th>Prix unitaire</x-app-th>
                <x-app-th>Montant CDF</x-app-th>
                <x-app-th>Montant USD</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse ($pagedEvents as $event)
                <tr class="transition">
                    <x-app-td>{{ ($pagedEvents->firstItem() ?? 0) + $loop->index }}</x-app-td>
                    <x-app-td>{{ $event['nom'] }}</x-app-td>
                    <x-app-td :nowrap="true">{{ $event['date'] ?? '-' }}</x-app-td>
                    <x-app-td :nowrap="true">{{ $event['nb_billets'] }}</x-app-td>
                    <x-app-td>
                        @foreach ($event['types'] as $type => $count)
                            <div>{{ $type }}: {{ $event['prix_unitaire'][$type] }} {{ $event['devise'][$type] }}</div>
                        @endforeach
                    </x-app-td>
                    <x-app-td :nowrap="true">{{ number_format($event['CDF'], 0, ',', ' ') }} CDF</x-app-td>
                    <x-app-td :nowrap="true">{{ number_format($event['USD'], 0, ',', ' ') }} USD</x-app-td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-gray-500">
                        Aucun billet trouvé.
                    </td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-app-table>

    <div class="mt-5">
        {{ $pagedEvents->links() }}
    </div>

</div>

@endsection
