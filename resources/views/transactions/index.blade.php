{{-- resources/views/admin/transactions/index.blade.php --}}

@extends('layouts.main')
@section('title', 'Transactions')

@section('content')

@php
    $statusOptions = [
        'en_attente' => 'En attente',
        'paiement_en_cours' => 'Paiement en cours',
        'paye' => 'Payée',
        'paye_sans_billet' => 'Payée (sans billet)',
        'echoue' => 'Échouée',
        'annulee' => 'Annulée',
        // Valeurs legacy encore présentes dans certaines lignes historiques.
        'completee' => 'Complétée (legacy)',
        'echouee' => 'Échouée (legacy)',
    ];

    $statusStyles = [
        'en_attente' => 'bg-amber-100 text-amber-700',
        'paiement_en_cours' => 'bg-sky-100 text-sky-700',
        'paye' => 'bg-emerald-100 text-emerald-700',
        'paye_sans_billet' => 'bg-lime-100 text-lime-700',
        'echoue' => 'bg-rose-100 text-rose-700',
        'annulee' => 'bg-slate-200 text-slate-700',
        'completee' => 'bg-emerald-100 text-emerald-700',
        'echouee' => 'bg-rose-100 text-rose-700',
    ];
@endphp

<div class="container mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">
            Gestion des transactions
        </h1>
    </div>

    {{-- Filtres --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">

        <form method="GET" action="{{ route('transactions.index') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                {{-- Statut --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Statut
                    </label>

                    <select
                        name="statut"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                        <option value="">Tous</option>

                        @foreach($statusOptions as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(request('statut') == $statusValue)>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Email
                    </label>

                    <input
                        type="text"
                        name="email"
                        value="{{ request('email') }}"
                        placeholder="client@email.com"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                </div>

                {{-- Référence --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Référence
                    </label>

                    <input
                        type="text"
                        name="reference"
                        value="{{ request('reference') }}"
                        placeholder="TRX-XXXX"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                </div>

                {{-- Date début --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Date début
                    </label>

                    <input
                        type="date"
                        name="date_debut"
                        value="{{ request('date_debut') }}"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                </div>

                {{-- Date fin --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Date fin
                    </label>

                    <input
                        type="date"
                        name="date_fin"
                        value="{{ request('date_fin') }}"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                </div>

            </div>

            <div class="mt-4 flex gap-3">

                <button
                    type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg"
                >
                    Rechercher
                </button>

                <a
                    href="{{ route('transactions.index') }}"
                    class="bg-gray-200 px-4 py-2 rounded-lg"
                >
                    Réinitialiser
                </a>

            </div>

        </form>

    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tableau --}}
    <x-app-table minWidth="1120px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Référence</x-app-th>
                <x-app-th>Client</x-app-th>
                <x-app-th>Montant</x-app-th>
                <x-app-th>Téléphone</x-app-th>
                <x-app-th>Statut</x-app-th>
                <x-app-th>Paiement</x-app-th>
                <x-app-th>Date</x-app-th>
                <x-app-th align="center">Actions</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse($transactions as $transaction)
                <tr class="transition">
                    <x-app-td>{{ ($transactions->firstItem() ?? 0) + $loop->index }}</x-app-td>
                    <x-app-td class="font-medium">{{ $transaction->reference }}</x-app-td>
                    <x-app-td>
                        {{ $transaction->nom_complet_client ?? $transaction->billet?->nom_auteur ?? '—' }}
                    </x-app-td>
                    <x-app-td :nowrap="true">
                        {{ number_format($transaction->montant, 2, ',', ' ') }}
                        {{ $transaction->devise }}
                    </x-app-td>
                    <x-app-td :nowrap="true">{{ $transaction->numero_telephone }}</x-app-td>
                    <x-app-td>
                        @php
                            $statusValue = (string) $transaction->statut;
                            $statusLabel = $statusOptions[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue));
                            $statusClass = $statusStyles[$statusValue] ?? 'bg-gray-100 text-gray-700';
                        @endphp

                        <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </x-app-td>
                    <x-app-td :nowrap="true">{{ $transaction->methode_paiement }}</x-app-td>
                    <x-app-td :nowrap="true">{{ $transaction->created_at->format('d/m/Y H:i') }}</x-app-td>
                    <x-app-td align="center">
                        <div class="flex justify-center gap-2">
                            {{-- Voir --}}
                            <a
                                href="{{ route('transactions.show', $transaction->id) }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm"
                            >
                                Voir
                            </a>

                            {{-- Génération manuelle --}}
                            @if(!$transaction->billet_id)
                                <form
                                    action="{{ route('transactions.force-generate', $transaction->id) }}"
                                    method="POST"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm"
                                    >
                                        Forcer
                                    </button>
                                </form>
                            @endif

                            {{-- Remboursement --}}
                            <form
                                action="{{ route('transactions.refund', $transaction->id) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                                >
                                    Rembourser
                                </button>
                            </form>
                        </div>
                    </x-app-td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-6 text-gray-500">
                        Aucune transaction trouvée.
                    </td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-app-table>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $transactions->links() }}
    </div>

</div>

@endsection