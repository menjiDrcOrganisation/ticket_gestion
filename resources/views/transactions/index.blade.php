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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

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
                {{-- <div>
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
                </div> --}}

                {{-- Téléphone --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Téléphone
                    </label>

                    <input
                        type="text"
                        name="numero_telephone"
                        value="{{ request('telephone') }}"
                        placeholder="77 123 45 67"
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
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                >
                    Rechercher
                </button>

                <a
                    href="{{ route('transactions.index') }}"
                    class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition"
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
{{-- 
                    <th class="text-left px-4 py-3">
                        Email
                    </th> --}}

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

                    <tr class="border-t hover:bg-gray-50 transition">

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

                        {{-- <td class="px-4 py-3">
                            {{ $transaction->email ?? 'N/A' }}
                        </td> --}}

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

                            <div class="flex justify-center gap-2 flex-wrap">

                                {{-- Voir --}}
                                <a
                                    href="{{ route('transactions.show', $transaction->id) }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                                >
                                    Voir
                                </a>

                                {{-- Génération manuelle du billet --}}
                                @if(!$transaction->billet_id && $transaction->statut === 'completee')
                                    <form
                                        action="{{ route('transactions.force-generate', $transaction->id) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition"
                                            onclick="return confirm('Générer le billet manuellement ?')"
                                        >
                                            Générer billet
                                        </button>
                                    </form>
                                @endif

                                {{-- Remboursement --}}
                                @if($transaction->statut === 'completee')
                                    <form
                                        action="{{ route('transactions.refund', $transaction->id) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition"
                                            onclick="return confirm('Confirmer le remboursement ?')"
                                        >
                                            Rembourser
                                        </button>
                                    </form>
                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center py-6 text-gray-500">
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