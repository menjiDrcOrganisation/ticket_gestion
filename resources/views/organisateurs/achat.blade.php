@extends('layouts.org')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Billetterie</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-800">Billets vendus</h1>
        <p class="mt-1 text-sm text-slate-500">Suivi des achats, statuts et régénération des billets.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-indic-dashboard :value="number_format($totalAchat, 0, ',', ' ')" title="Total des achats" subtitle="Billets" icon="shopping-cart" tone="blue" />
        <x-indic-dashboard :value="number_format($totalRestant, 0, ',', ' ')" title="Total restant" subtitle="Disponibilité" icon="archive-box" tone="slate" />
        <x-indic-dashboard :value="number_format($totalCDF, 0, ',', ' ') . ' FC'" title="Total en CDF" subtitle="Encaissement" icon="banknotes" tone="emerald" />
        <x-indic-dashboard :value="number_format($totalUSD, 0, ',', ' ') . ' USD'" title="Total en USD" subtitle="Encaissement" icon="currency-dollar" tone="amber" />
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('billet.all') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
            <div class="md:col-span-4">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
                <x-app-input id="q" name="q" :value="$search" placeholder="Client, type, code billet..." wrapperClass="mb-0" />
            </div>

            <div class="md:col-span-3">
                <label for="event_id" class="mb-1.5 block text-sm font-medium text-slate-600">Événement</label>
                <x-app-select id="event_id" name="event_id" wrapperClass="mb-0">
                    <option value="0" @selected((int) $selectedEventId === 0)>Tous mes événements</option>
                    @foreach($evenementsOrganisateur as $eventOption)
                        <option value="{{ $eventOption->id }}" @selected((int) $selectedEventId === (int) $eventOption->id)>
                            {{ $eventOption->nom }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-1">
                <label for="statut" class="mb-1.5 block text-sm font-medium text-slate-600">Statut</label>
                <x-app-select id="statut" name="statut" wrapperClass="mb-0">
                    <option value="">Tous</option>
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected((string) $status === (string) $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-1">
                <label for="devise" class="mb-1.5 block text-sm font-medium text-slate-600">Devise</label>
                <x-app-select id="devise" name="devise" wrapperClass="mb-0">
                    <option value="">Toutes</option>
                    @foreach($deviseOptions as $deviseOption)
                        <option value="{{ $deviseOption }}" @selected((string) $devise === (string) $deviseOption)>
                            {{ $deviseOption }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-1">
                <label for="type" class="mb-1.5 block text-sm font-medium text-slate-600">Type billet</label>
                <x-app-select id="type" name="type" wrapperClass="mb-0">
                    <option value="">Tous</option>
                    @foreach($typeOptions as $typeOption)
                        <option value="{{ $typeOption }}" @selected((string) $type === (string) $typeOption)>
                            {{ $typeOption }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-1">
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
                <a href="{{ route('billet.all') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Reinitialiser
                </a>
            </div>
        </form>
    </div>

    <x-app-table minWidth="1100px" stickyHeader>
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Client</x-app-th>
                <x-app-th>Type</x-app-th>
                <x-app-th align="right">Prix unitaire</x-app-th>
                <x-app-th align="center">Qte achetee</x-app-th>
                <x-app-th align="center">Qte restante</x-app-th>
                <x-app-th align="right">Total</x-app-th>
                <x-app-th align="center">Statut</x-app-th>
                <x-app-th align="center">Date achat</x-app-th>
                <x-app-th align="right">Actions</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse($detailleParBillet as $billet)
                @php
                    $statusLower = strtolower((string) ($billet['statut'] ?? ''));
                    $statusClass = match ($statusLower) {
                        'paye', 'payé', 'valide', 'validé' => 'bg-emerald-100 text-emerald-700',
                        'en attente', 'pending' => 'bg-amber-100 text-amber-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                    $dateAchat = !empty($billet['date']) ? \Carbon\Carbon::parse($billet['date'])->format('d/m/Y H:i') : '—';
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <x-app-td nowrap>{{ ($detailleParBillet->firstItem() ?? 0) + $loop->index }}</x-app-td>
                    <x-app-td>{{ $billet['auteur'] ?? 'N/A' }}</x-app-td>
                    <x-app-td>{{ $billet['type'] ?? '—' }}</x-app-td>
                    <x-app-td align="right" nowrap>{{ number_format((float) ($billet['prix_unitaire'] ?? 0), 2, ',', ' ') }} {{ $billet['devise'] ?? '' }}</x-app-td>
                    <x-app-td align="center">{{ (int) ($billet['quantite'] ?? 0) }}</x-app-td>
                    <x-app-td align="center">{{ (int) ($billet['quantite_fictif'] ?? 0) }}</x-app-td>
                    <x-app-td align="right" nowrap>{{ number_format((float) ($billet['total'] ?? 0), 2, ',', ' ') }} {{ $billet['devise'] ?? '' }}</x-app-td>
                    <x-app-td align="center" nowrap>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $billet['statut'] ?? '—' }}
                        </span>
                    </x-app-td>
                    <x-app-td align="center" nowrap>{{ $dateAchat }}</x-app-td>
                    <x-app-td align="right" nowrap>
                        <div class="inline-flex items-center gap-2">
                            <button onclick="openModal('detailsModal{{ $billet['id'] }}')"
                                    class="rounded-lg p-1.5 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800"
                                    title="Voir details">
                                <i data-lucide="eye" class="h-4 w-4"></i>
                            </button>

                            <form action="{{ route('billet.destroy', $billet['id']) }}" method="POST" class="inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce billet ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg p-1.5 text-rose-600 transition hover:bg-rose-50 hover:text-rose-800"
                                        title="Supprimer">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </x-app-td>
                </tr>
            @empty
                <tr>
                    <x-app-td colspan="10" class="py-8 text-center text-slate-500">Aucun achat trouve.</x-app-td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-app-table>

    @if($detailleParBillet->hasPages())
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            {{ $detailleParBillet->links() }}
        </div>
    @endif
</div>

@forelse($detailleParBillet as $billet)
    <div id="detailsModal{{ $billet['id'] }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-6">
            <h3 class="mb-4 text-lg font-semibold">Details du billet</h3>

            <form action="{{ route('billet.regenerer', $billet['id']) }}" method="POST" class="mb-4">
                @csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-yellow-100 px-3 py-2 text-sm text-yellow-700 transition hover:bg-yellow-200"
                        title="Regenerer billet">
                    <i class="fas fa-sync text-xs"></i>
                    <span>Regenerer</span>
                </button>
            </form>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><span class="font-medium">Client :</span> <span class="text-right">{{ $billet['auteur'] ?? 'N/A' }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Type :</span> <span class="text-right">{{ $billet['type'] ?? '—' }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Quantite achetee :</span> <span>{{ (int) ($billet['quantite'] ?? 0) }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Quantite restante :</span> <span>{{ (int) ($billet['quantite_fictif'] ?? 0) }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Total :</span> <span>{{ number_format((float) ($billet['total'] ?? 0), 2, ',', ' ') }} {{ $billet['devise'] ?? '' }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Prix unitaire :</span> <span>{{ number_format((float) ($billet['prix_unitaire'] ?? 0), 2, ',', ' ') }} {{ $billet['devise'] ?? '' }}</span></div>
                <div class="flex justify-between gap-4"><span class="font-medium">Statut :</span> <span>{{ $billet['statut'] ?? '—' }}</span></div>

                @if(!empty($billet['code']))
                    <div class="mt-4 border-t border-gray-200 pt-4 text-center">
                        <p class="mb-3 text-sm text-gray-600">QR Code du billet</p>
                        <div id="qrcode-{{ $billet['id'] }}" class="mx-auto w-fit rounded-md border bg-white p-2"></div>

                        @if(!empty($billet['billetImage']))
                            <a href="{{ env('ENV_POINT_URL') }}/storage/app/public/{{ $billet['billetImage'] }}"
                               target="_blank"
                               class="mt-3 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700">
                                Telecharger QR Code
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="closeModal('detailsModal{{ $billet['id'] }}')"
                        class="rounded-lg bg-gray-100 px-4 py-2 transition hover:bg-gray-200">
                    Fermer
                </button>
            </div>
        </div>
    </div>
@empty
@endforelse

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

document.addEventListener('DOMContentLoaded', function () {
    @foreach($detailleParBillet as $billet)
        @if(!empty($billet['code']))
            const qrElement{{ $billet['id'] }} = document.getElementById('qrcode-{{ $billet['id'] }}');
            if (qrElement{{ $billet['id'] }}) {
                new QRCode(qrElement{{ $billet['id'] }}, {
                    text: "{{ $billet['code'] }}",
                    width: 120,
                    height: 120
                });
            }
        @endif
    @endforeach
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endsection
