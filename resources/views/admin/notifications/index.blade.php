@extends('layouts.main')
@section('title', 'Notifications')

@php
    $libellesStatut = [
        'en_attente' => ['En attente', 'bg-slate-100 text-slate-700'],
        'en_cours' => ['En cours', 'bg-blue-100 text-blue-700'],
        'envoye' => ['Envoyé', 'bg-emerald-100 text-emerald-700'],
        'echoue' => ['Échoué', 'bg-rose-100 text-rose-700'],
    ];
@endphp

@section('content')
<div class="bg-white w-full px-6 py-6 mx-auto rounded-2xl shadow">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b pb-4">
        <h1 class="text-2xl font-semibold text-slate-800">File d'attente des notifications</h1>
        <p class="text-sm text-slate-500">E-mails envoyés en arrière-plan par le worker</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
        @foreach($libellesStatut as $cle => [$libelle, $classes])
            <a href="{{ route('admin.notifications.index', ['statut' => $cle]) }}"
               class="rounded-xl border border-slate-200 p-4 hover:bg-slate-50 {{ $statut === $cle ? 'ring-2 ring-emerald-500' : '' }}">
                <p class="text-xs text-slate-500">{{ $libelle }}</p>
                <p class="text-2xl font-semibold text-slate-800">{{ (int) ($compteurs[$cle] ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.notifications.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
        <x-app-select name="statut" wrapperClass="">
            <option value="">Tous les statuts</option>
            @foreach($libellesStatut as $cle => [$libelle])
                <option value="{{ $cle }}" @selected($statut === $cle)>{{ $libelle }}</option>
            @endforeach
        </x-app-select>

        <x-app-select name="type" wrapperClass="">
            <option value="">Tous les types</option>
            @foreach($types as $t)
                <option value="{{ $t }}" @selected($type === $t)>{{ $t }}</option>
            @endforeach
        </x-app-select>

        <x-app-input name="q" :value="$search" placeholder="Destinataire" wrapperClass="" />

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-500">Filtrer</button>
            <a href="{{ route('admin.notifications.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm text-slate-800 hover:bg-slate-300">Réinitialiser</a>
        </div>
    </form>

    <div class="mt-6">
        <x-app-table minWidth="1000px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
            <x-slot:head>
                <tr>
                    <x-app-th>#</x-app-th>
                    <x-app-th>Créée le</x-app-th>
                    <x-app-th>Type</x-app-th>
                    <x-app-th>Destinataire</x-app-th>
                    <x-app-th>Statut</x-app-th>
                    <x-app-th>Tentatives</x-app-th>
                    <x-app-th>Dernière erreur</x-app-th>
                    <x-app-th>Action</x-app-th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                @forelse($notifications as $notification)
                    @php [$libelle, $classes] = $libellesStatut[$notification->statut] ?? [$notification->statut, 'bg-slate-100 text-slate-700']; @endphp
                    <tr class="align-top transition">
                        <x-app-td :nowrap="true">{{ $notification->id }}</x-app-td>
                        <x-app-td :nowrap="true">{{ $notification->created_at?->format('d/m/Y H:i:s') }}</x-app-td>
                        <x-app-td>{{ $notification->type }}</x-app-td>
                        <x-app-td>{{ $notification->destinataire }}</x-app-td>
                        <x-app-td :nowrap="true">
                            <span class="px-3 py-1 rounded-full text-xs {{ $classes }}">{{ $libelle }}</span>
                            @if($notification->envoye_at)
                                <div class="text-[11px] text-slate-500 mt-1">{{ $notification->envoye_at->format('d/m/Y H:i') }}</div>
                            @elseif($notification->echoue_at)
                                <div class="text-[11px] text-slate-500 mt-1">{{ $notification->echoue_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </x-app-td>
                        <x-app-td :nowrap="true">{{ $notification->tentatives }}</x-app-td>
                        <x-app-td class="text-xs text-slate-600 max-w-xs break-words">{{ $notification->derniere_erreur ?? '-' }}</x-app-td>
                        <x-app-td :nowrap="true">
                            @if($notification->statut === 'echoue')
                                <form method="POST" action="{{ route('admin.notifications.rejouer', $notification) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs text-white hover:bg-indigo-500">Rejouer</button>
                                </form>
                            @else
                                -
                            @endif
                        </x-app-td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">Aucune notification pour ces filtres.</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-app-table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

    <div class="mt-10 border-t pt-6">
        <h2 class="text-lg font-semibold text-slate-800">Autres tâches échouées</h2>
        <p class="text-sm text-slate-500 mb-4">Tâches asynchrones hors notifications (ex. génération de billets PDF) ayant épuisé leurs tentatives.</p>

        <x-app-table minWidth="900px" tableClass="[&>tbody>tr:hover]:bg-slate-50">
            <x-slot:head>
                <tr>
                    <x-app-th>Échouée le</x-app-th>
                    <x-app-th>Tâche</x-app-th>
                    <x-app-th>File</x-app-th>
                    <x-app-th>Erreur</x-app-th>
                    <x-app-th>Action</x-app-th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($tachesEchouees as $tache)
                    <tr class="align-top transition">
                        <x-app-td :nowrap="true">{{ $tache->failed_at }}</x-app-td>
                        <x-app-td>{{ $tache->nom }}</x-app-td>
                        <x-app-td>{{ $tache->file }}</x-app-td>
                        <x-app-td class="text-xs text-slate-600 max-w-md break-words">{{ $tache->erreur }}</x-app-td>
                        <x-app-td :nowrap="true">
                            <form method="POST" action="{{ route('admin.notifications.rejouerTache', $tache->uuid) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs text-white hover:bg-indigo-500">Rejouer</button>
                            </form>
                        </x-app-td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">Aucune autre tâche échouée.</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-app-table>
    </div>
</div>
@endsection
