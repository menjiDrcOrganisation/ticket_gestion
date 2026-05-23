{{-- resources/views/admin/transactions/index.blade.php --}}

@extends('layouts.main')

@section('content')

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

                        <option value="en_attente"
                            @selected(request('statut') == 'en_attente')
                        >
                            En attente
                        </option>

                        <option value="completee"
                            @selected(request('statut') == 'completee')
                        >
                            Complétée
                        </option>

                        <option value="echouee"
                            @selected(request('statut') == 'echouee')
                        >
                            Échouée
                        </option>

                        <option value="annulee"
                            @selected(request('statut') == 'annulee')
                        >
                            Annulée
                        </option>
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
    <div class="bg-white shadow rounded-lg overflow-hidden">

        <table class="w-full">

            <thead class="bg-gray-100">
                <tr>

                    <th class="text-left px-4 py-3">
                        Référence
                    </th>

                    <th class="text-left px-4 py-3">
                        Montant
                    </th>

                    <th class="text-left px-4 py-3">
                        Téléphone
                    </th>

                    <th class="text-left px-4 py-3">
                        Statut
                    </th>

                    <th class="text-left px-4 py-3">
                        Paiement
                    </th>

                    <th class="text-left px-4 py-3">
                        Date
                    </th>

                    <th class="text-center px-4 py-3">
                        Actions
                    </th>

                </tr>
            </thead>

            <tbody>

                @forelse($transactions as $transaction)

                    <tr class="border-t">

                        <td class="px-4 py-3 font-medium">
                            {{ $transaction->reference }}
                        </td>

                        <td class="px-4 py-3">
                            {{ number_format($transaction->montant, 2, ',', ' ') }}
                            {{ $transaction->devise }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $transaction->numero_telephone }}
                        </td>

                        <td class="px-4 py-3">

                            @switch($transaction->statut)

                                @case('completee')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                        Complétée
                                    </span>
                                @break

                                @case('en_attente')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">
                                        En attente
                                    </span>
                                @break

                                @case('echouee')
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                                        Échouée
                                    </span>
                                @break

                                @default
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                                        Annulée
                                    </span>

                            @endswitch

                        </td>

                        <td class="px-4 py-3">
                            {{ $transaction->methode_paiement }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-4 py-3">

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

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-500">
                            Aucune transaction trouvée.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $transactions->links() }}
    </div>

</div>

@endsection