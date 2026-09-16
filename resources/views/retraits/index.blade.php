@extends('layouts.org')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Opérations</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-800">Gestion des retraits</h1>
            </div>
            <button id="openModalBtn"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Nouveau retrait
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-indic-dashboard :value="$evenementsCount" title="Mes événements" subtitle="Périmètre" icon="calendar-days" tone="slate" />
        <x-indic-dashboard :value="$retraits->count()" title="Demandes" subtitle="Total" icon="clipboard-document-list" tone="blue" />
        <x-indic-dashboard :value="$retraits->where('statut', 'en attente')->count()" title="En attente" subtitle="Traitement" icon="clock" tone="amber" />
        <x-indic-dashboard :value="number_format((float) $retraits->sum('montant'), 0, ',', ' ') . ' FC'" title="Montant cumulé" subtitle="Demandé" icon="banknotes" tone="emerald" />
    </div>

    <p class="text-sm text-slate-500">Les demandes de retrait sont globales à votre compte organisateur (tous vos événements).</p>

    <x-app-table minWidth="860px">
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Organisateur</x-app-th>
                <x-app-th>Nom du détenteur</x-app-th>
                <x-app-th align="right">Montant</x-app-th>
                <x-app-th align="center">Date</x-app-th>
                <x-app-th align="center">Statut</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse($retraits as $retrait)
                <tr class="hover:bg-slate-50 transition">
                    <x-app-td nowrap>{{ $loop->iteration }}</x-app-td>
                    <x-app-td>{{ $retrait->organisateur->user->name ?? 'Inconnu' }}</x-app-td>
                    <x-app-td class="font-medium">{{ $retrait->nom_detenteur }}</x-app-td>
                    <x-app-td align="right" class="font-semibold text-emerald-700" nowrap>
                        {{ number_format($retrait->montant, 2, ',', ' ') }} FC
                    </x-app-td>
                    <x-app-td align="center" nowrap>{{ \Carbon\Carbon::parse($retrait->date)->format('d/m/Y') }}</x-app-td>
                    <x-app-td align="center" nowrap>
                        @php
                            $status = strtolower((string) $retrait->statut);
                            $statusClass = match ($status) {
                                'valide', 'validé' => 'bg-emerald-100 text-emerald-700',
                                'rejete', 'rejeté' => 'bg-rose-100 text-rose-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $retrait->statut }}</span>
                    </x-app-td>
                </tr>
            @empty
                <tr>
                    <x-app-td colspan="6" class="py-8 text-center text-slate-500">Aucun retrait enregistré pour le moment.</x-app-td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-app-table>
</div>

{{-- MODAL : Ajouter un nouveau retrait --}}
<div id="createModal" class="fixed inset-0 hidden bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">✖</button>
        <h2 class="text-xl font-bold mb-4 text-gray-800"> Nouveau Retrait</h2>

        <form action="{{ route('retraits.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="organisateur_id" value="{{ Auth::user()->organisateur->id ?? '11' }}">
            <input type="hidden" name="statut" value="en attente">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du détenteur</label>
                <x-app-input name="nom_detenteur" placeholder="Nom du détenteur" required wrapperClass="mb-0" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FC)</label>
                <x-app-input type="number" name="montant" placeholder="Montant" required wrapperClass="mb-0" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <x-app-input type="date" name="date" required wrapperClass="mb-0" />
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script du modal --}}
<script>
    const modal = document.getElementById('createModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');

    openBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
    closeBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
    window.addEventListener('click', e => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
</script>
@endsection
