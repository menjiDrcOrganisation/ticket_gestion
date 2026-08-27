@extends('layouts.org')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mes événements</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-800">Gestion des événements</h1>
        <p class="mt-1 text-sm text-slate-500">Recherche, filtres et consultation des informations avancées (scanneur, billetterie, URL publique).</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-indic-dashboard :value="$totalEvenements" title="Mes événements" subtitle="Total" icon="calendar-days" tone="blue" />
        <x-indic-dashboard :value="$evenementsEncours" title="En cours" subtitle="Actifs" icon="play-circle" tone="emerald" />
        <x-indic-dashboard :value="$evenementsFermes" title="Fermés" subtitle="Terminés" icon="lock-closed" tone="slate" />
        <x-indic-dashboard :value="$evenementsSansScanneur" title="Sans scanneur" subtitle="Affectation" icon="user-minus" tone="amber" />
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('event_scanner.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
                <x-app-input id="q" name="q" :value="$search" placeholder="Nom, URL, adresse, salle, scanneur..." wrapperClass="mb-0" />
            </div>

            <div class="md:col-span-2">
                <label for="statut" class="mb-1.5 block text-sm font-medium text-slate-600">Statut</label>
                <x-app-select id="statut" name="statut" wrapperClass="mb-0">
                    <option value="">Tous</option>
                    <option value="encours" @selected($status === 'encours')>En cours</option>
                    <option value="ferme" @selected($status === 'ferme')>Fermé</option>
                    <option value="a venir" @selected($status === 'a venir')>À venir</option>
                </x-app-select>
            </div>

            <div class="md:col-span-3">
                <label for="type_evenement_id" class="mb-1.5 block text-sm font-medium text-slate-600">Type d'événement</label>
                <x-app-select id="type_evenement_id" name="type_evenement_id" wrapperClass="mb-0">
                    <option value="">Tous les types</option>
                    @foreach($typeEvenements as $typeEvenement)
                        <option value="{{ $typeEvenement->id }}" @selected((string) $typeEvenementId === (string) $typeEvenement->id)>
                            {{ $typeEvenement->nom_type }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-2">
                <label for="per_page" class="mb-1.5 block text-sm font-medium text-slate-600">Afficher</label>
                <x-app-select id="per_page" name="per_page" wrapperClass="mb-0">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) $perPage === (int) $size)>{{ $size }}</option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-12 flex flex-wrap gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Filtrer
                </button>
                <a href="{{ route('event_scanner.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <x-app-table minWidth="1120px" stickyHeader>
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Événement</x-app-th>
                <x-app-th>Type</x-app-th>
                <x-app-th>Date</x-app-th>
                <x-app-th>Scanneur</x-app-th>
                <x-app-th align="center">Billets types</x-app-th>
                <x-app-th align="center">Statut</x-app-th>
                <x-app-th>URL</x-app-th>
                <x-app-th align="right">Actions</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse($evenements as $evenement)
                @php
                    $statusClass = match ($evenement->statut) {
                        'encours' => 'bg-emerald-100 text-emerald-700',
                        'ferme' => 'bg-rose-100 text-rose-700',
                        default => 'bg-amber-100 text-amber-700',
                    };
                    $publicUrl = rtrim((string) env('ACHAT_URL', ''), '/') . '/' . ltrim((string) $evenement->url_evenement, '/');
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <x-app-td nowrap>{{ ($evenements->firstItem() ?? 0) + $loop->index }}</x-app-td>
                    <x-app-td>
                        <p class="font-semibold text-slate-800">{{ $evenement->nom }}</p>
                        <p class="text-xs text-slate-500">{{ $evenement->adresse ?? '—' }}{{ $evenement->salle ? ', '.$evenement->salle : '' }}</p>
                    </x-app-td>
                    <x-app-td>{{ $evenement->typeEvenement->nom_type ?? '—' }}</x-app-td>
                    <x-app-td nowrap>
                        {{ $evenement->date_debut ? \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') : '—' }}
                    </x-app-td>
                    <x-app-td>
                        @if($evenement->scanneur?->user)
                            <p class="text-sm text-slate-700">{{ $evenement->scanneur->user->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $evenement->scanneur->user->email ?? '—' }}</p>
                        @else
                            <span class="text-xs text-amber-700">Non affecté</span>
                        @endif
                    </x-app-td>
                    <x-app-td align="center">{{ $evenement->typeBillets->count() }}</x-app-td>
                    <x-app-td align="center" nowrap>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $evenement->statut }}</span>
                    </x-app-td>
                    <x-app-td class="max-w-[180px] truncate">
                        <a href="{{ $publicUrl }}" target="_blank" class="text-blue-600 hover:underline">{{ $evenement->url_evenement }}</a>
                    </x-app-td>
                    <x-app-td align="right" nowrap>
                        <button type="button"
                                onclick="openModal('event-details-{{ $evenement->id }}')"
                                class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                                title="Voir les informations supplémentaires">
                            <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                            Détails
                        </button>
                    </x-app-td>
                </tr>
            @empty
                <tr>
                    <x-app-td colspan="9" class="py-8 text-center text-slate-500">Aucun événement trouvé.</x-app-td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-app-table>

    @if($evenements->hasPages())
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            {{ $evenements->links() }}
        </div>
    @endif
</div>

@foreach($evenements as $evenement)
    @php
        $publicUrl = rtrim((string) env('ACHAT_URL', ''), '/') . '/' . ltrim((string) $evenement->url_evenement, '/');
    @endphp
    <div id="event-details-{{ $evenement->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-start justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">{{ $evenement->nom }}</h3>
                    <p class="text-sm text-slate-500">{{ $evenement->typeEvenement->nom_type ?? 'Type inconnu' }}</p>
                </div>
                <button type="button" onclick="closeModal('event-details-{{ $evenement->id }}')" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    <i data-lucide="x" class="h-4 w-4"></i>
                    <span class="text-lg font-semibold leading-none" aria-hidden="true">&times;</span>
                    <span class="sr-only">Fermer</span>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Informations événement</h4>
                    <p class="text-sm"><span class="font-semibold">Adresse:</span> {{ $evenement->adresse ?? '—' }}</p>
                    <p class="text-sm"><span class="font-semibold">Salle:</span> {{ $evenement->salle ?? '—' }}</p>
                    <p class="text-sm"><span class="font-semibold">Date début:</span> {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y H:i') }}</p>
                    <p class="text-sm"><span class="font-semibold">Date fin:</span> {{ $evenement->date_fin ? \Carbon\Carbon::parse($evenement->date_fin)->format('d/m/Y H:i') : '—' }}</p>
                    <p class="text-sm"><span class="font-semibold">Statut:</span> {{ $evenement->statut }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Contacts</h4>
                    <p class="text-sm"><span class="font-semibold">Organisateur:</span> {{ $evenement->organisateur?->user?->name ?? '—' }}</p>
                    <p class="text-sm"><span class="font-semibold">Email organisateur:</span> {{ $evenement->organisateur?->user?->email ?? '—' }}</p>
                    <p class="text-sm"><span class="font-semibold">Téléphone:</span> {{ $evenement->organisateur?->telephone ?? '—' }}</p>
                    <hr class="my-3 border-slate-200">
                    <p class="text-sm"><span class="font-semibold">Scanneur:</span> {{ $evenement->scanneur?->user?->name ?? 'Non affecté' }}</p>
                    <p class="text-sm"><span class="font-semibold">Email scanneur:</span> {{ $evenement->scanneur?->user?->email ?? '—' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                    <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Billetterie</h4>
                    <x-app-table minWidth="640px" wrapperClass="border-slate-100" tableClass="min-w-full">
                        <x-slot:head>
                            <tr>
                                <x-app-th>#</x-app-th>
                                <x-app-th>Type billet</x-app-th>
                                <x-app-th align="center">Nombre</x-app-th>
                                <x-app-th align="right">Prix unitaire</x-app-th>
                            </tr>
                        </x-slot:head>
                        <x-slot:body>
                            @forelse($evenement->typeBillets as $typeBillet)
                                <tr>
                                    <x-app-td>{{ $loop->iteration }}</x-app-td>
                                    <x-app-td>{{ $typeBillet->nom_type }}</x-app-td>
                                    <x-app-td align="center">{{ (int) ($typeBillet->pivot->nombre_billet ?? 0) }}</x-app-td>
                                    <x-app-td align="right" nowrap>{{ number_format((float) ($typeBillet->pivot->prix_unitaire ?? 0), 2, ',', ' ') }} {{ $typeBillet->pivot->devise ?? '' }}</x-app-td>
                                </tr>
                            @empty
                                <tr>
                                    <x-app-td colspan="4" class="text-slate-500">Aucun type de billet configuré.</x-app-td>
                                </tr>
                            @endforelse
                        </x-slot:body>
                    </x-app-table>
                </div>

                <div class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                    <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">Lien public</h4>
                    <a href="{{ $publicUrl }}" target="_blank" class="break-all text-blue-600 hover:underline">{{ $publicUrl }}</a>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        e.target.classList.add('hidden');
        e.target.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
});
</script>
@endsection
