@extends('layouts.main')
@section('title', 'Demandes de retrait')

@section('content')
<div class="container mx-auto px-6 py-10">
    {{-- des gid pour indicateur --}}
    {{-- 'totaldmd', 'totalmontantdmd', 'totalmontantdmdapprouve', 'totalmontantdmdenattente', 'totalmontantdmdrefuse' --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    {{-- <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Total Demandes</h3>
        <p class="text-2xl font-extrabold">{{ $stats['totaldmd'] }}</p>
    </div> --}}

    {{-- <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Total</h3>
        <p class="text-2xl font-extrabold">{{ number_format($stats['totalmontantdmd']) }} FC</p>
    </div> --}}

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Approuvé</h3>
        <p class="text-2xl font-extrabold text-green-600">
            {{ number_format($stats['totalmontantdmdapprouve']) }} FC
        </p>
    </div>

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant en Attente</h3>
        <p class="text-2xl font-extrabold text-yellow-500">
            {{ number_format($stats['totalmontantdmdenattente']) }} FC
        </p>
    </div>

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Refusé</h3>
        <p class="text-2xl font-extrabold text-red-600">
            {{ number_format($stats['totalmontantdmdrefuse']) }} FC
        </p>
    </div>

</div>

    <form method="GET" action="{{ route('dmd_retrait.index') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-12 md:items-end">
        <div class="md:col-span-5">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
            <x-app-input id="q" name="q" :value="$search" placeholder="Organisateur, détenteur, montant..." wrapperClass="mb-0" />
        </div>

        <div class="md:col-span-3">
            <label for="statut" class="mb-1.5 block text-sm font-medium text-slate-600">Statut</label>
            <x-app-select id="statut" name="statut" wrapperClass="mb-0">
                <option value="">Tous les statuts</option>
                <option value="en_attente" @selected($status === 'en_attente')>En attente</option>
                <option value="approuve" @selected($status === 'approuve')>Approuvé</option>
                <option value="refuse" @selected($status === 'refuse')>Refusé</option>
            </x-app-select>
        </div>

        <div class="md:col-span-2">
            <label for="date_from" class="mb-1.5 block text-sm font-medium text-slate-600">Date début</label>
            <x-app-input id="date_from" type="date" name="date_from" :value="$dateFrom" wrapperClass="mb-0" />
        </div>

        <div class="md:col-span-2">
            <label for="date_to" class="mb-1.5 block text-sm font-medium text-slate-600">Date fin</label>
            <x-app-input id="date_to" type="date" name="date_to" :value="$dateTo" wrapperClass="mb-0" />
        </div>

        <div class="md:col-span-12 flex flex-wrap gap-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Filtrer
            </button>
            <a href="{{ route('dmd_retrait.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Réinitialiser
            </a>
        </div>
    </form>

    {{-- Tableau des retraits --}}
    <x-app-table minWidth="1100px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Organisateur</x-app-th>
                <x-app-th>Nom du détenteur</x-app-th>
                <x-app-th>Montant</x-app-th>
                <x-app-th>Date</x-app-th>
                <x-app-th>Statut</x-app-th>
                <x-app-th align="center">Actions</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
                @forelse($retraits as $retrait)
                    @php
                        $rowStatusClass = match($retrait->statut) {
                            'en_attente', 'en attente', 'attente', 'pending' => 'bg-amber-100 text-amber-700',
                            'approuve', 'approuvé', 'approved' => 'bg-emerald-100 text-emerald-700',
                            'refuse', 'refusé', 'rejected' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <tr class="transition">
                        <x-app-td>{{ $retrait->id }}</x-app-td>
                        <x-app-td>{{ $retrait->organisateur->user->email ?? 'Inconnu' }}</x-app-td>
                        <x-app-td class="font-medium text-gray-800">{{ $retrait->nom_detenteur }}</x-app-td>
                        <x-app-td class="text-green-700 font-semibold" :nowrap="true">
                            {{ number_format($retrait->montant, 2, ',', ' ') }} FC
                        </x-app-td>
                        <x-app-td :nowrap="true">
                            {{ \Carbon\Carbon::parse($retrait->date)->format('d/m/Y') }}
                        </x-app-td>

                        {{-- Statut modifiable --}}
                        <x-app-td>
                            <form action="{{ route('dmd_retrait.updateStatut', $retrait->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <x-app-select
                                    name="statut"
                                    onchange="this.form.submit()"
                                    wrapperClass="mb-0"
                                    :inputClass="'px-2 py-1 rounded-full text-xs font-semibold ' . $rowStatusClass"
                                >
                                    <option value="en_attente" @selected(in_array($retrait->statut, ['en_attente', 'en attente', 'attente', 'pending'], true))>En attente</option>
                                    <option value="approuve" @selected(in_array($retrait->statut, ['approuve', 'approuvé', 'approved'], true))>Approuvé</option>
                                    <option value="refuse" @selected(in_array($retrait->statut, ['refuse', 'refusé', 'rejected'], true))>Refusé</option>
                                </x-app-select>
                            </form>
                        </x-app-td>

                        <x-app-td align="center">
                            <div class="space-x-4">
                            <a href="{{ route('dmd_retrait.show', $retrait->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium transition">👁️ Voir</a>
                            <form action="{{ route('dmd_retrait.destroy', $retrait->id) }}" 
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer ce retrait ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 font-medium transition">🗑️ Supprimer</button>
                            </form>
                            </div>
                        </x-app-td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-500 italic">
                        Aucun retrait enregistré pour le moment.
                    </td></tr>
                @endforelse
        </x-slot:body>
    </x-app-table>

    <div class="mt-5">
        {{ $retraits->links() }}
    </div>
</div>
@endsection
