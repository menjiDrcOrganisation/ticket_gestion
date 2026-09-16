@extends('layouts.org')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tableau de bord organisateur</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-800">
                    {{ $selectedEventId > 0 ? ($evenement->nom ?? 'Événement') : 'Tous mes événements' }}
                </h1>
            </div>

            <form method="GET" action="{{ route('dashboard_orginasateur.show') }}" class="w-full lg:w-auto">
                <label for="event_id" class="mb-1.5 block text-sm font-medium text-slate-600">Événement</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <x-app-select id="event_id" name="event_id" wrapperClass="mb-0" inputClass="min-w-[260px]">
                        <option value="0" @selected((int) $selectedEventId === 0)>Tous mes événements</option>
                        @foreach($evenementsOrganisateur as $eventOption)
                            <option value="{{ $eventOption->id }}" @selected((int) $selectedEventId === (int) $eventOption->id)>
                                {{ $eventOption->nom }}
                            </option>
                        @endforeach
                    </x-app-select>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Appliquer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
        <x-indic-dashboard :value="number_format($totalBilletsVendus, 0, ',', ' ')" title="Billets vendus" subtitle="Volume" icon="ticket" tone="slate" />
        <x-indic-dashboard :value="number_format($billetsScannes, 0, ',', ' ')" title="Billets scannés" subtitle="Contrôle" icon="qr-code" tone="blue" />
        <x-indic-dashboard :value="count($typesBillets[0] ?? [])" title="Types de billets utilisés" subtitle="Catégories" icon="rectangle-stack" tone="rose" />
        <x-indic-dashboard :value="number_format($revenusCDF, 0, ',', ' ') . ' CDF'" title="Total encaissé" subtitle="Franc congolais" icon="banknotes" tone="emerald" />
        <x-indic-dashboard :value="number_format($revenusUSD, 2, ',', ' ') . ' USD'" title="Total encaissé" subtitle="Dollar" icon="currency-dollar" tone="amber" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Types de billets utilisés</h2>

            <x-app-table minWidth="520px" wrapperClass="border-slate-100" tableClass="min-w-full">
                <x-slot:head>
                    <tr>
                        <x-app-th>Type</x-app-th>
                        <x-app-th align="center">Quantité</x-app-th>
                        <x-app-th align="right">Prix unitaire</x-app-th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse(($typesBillets[0] ?? []) as $type => $quantite)
                        <tr>
                            <x-app-td nowrap>{{ $type }}</x-app-td>
                            <x-app-td align="center">{{ $quantite }}</x-app-td>
                            <x-app-td align="right" nowrap>
                                {{ number_format((float) ($typesBillets[1][$type] ?? 0), 2, ',', ' ') }} {{ $typesBillets[2][$type] ?? '' }}
                            </x-app-td>
                        </tr>
                    @empty
                        <tr>
                            <x-app-td class="text-slate-500" colspan="3">Aucun billet vendu par catégorie.</x-app-td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-app-table>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-800">Derniers achats</h2>

            <x-app-table minWidth="520px" wrapperClass="border-slate-100" tableClass="min-w-full">
                <x-slot:head>
                    <tr>
                        <x-app-th>#</x-app-th>
                        <x-app-th>Type billet</x-app-th>
                        <x-app-th align="center">Quantité</x-app-th>
                        <x-app-th align="right">Date</x-app-th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                    @forelse($derniersAchats as $achat)
                        <tr>
                            <x-app-td>{{ $loop->iteration }}</x-app-td>
                            <x-app-td>{{ $achat->type_billet->nom_type ?? 'Type inconnu' }}</x-app-td>
                            <x-app-td align="center">{{ (int) ($achat->quantite ?? 0) }}</x-app-td>
                            <x-app-td align="right" nowrap>{{ optional($achat->created_at)->format('d/m/Y H:i') }}</x-app-td>
                        </tr>
                    @empty
                        <tr>
                            <x-app-td class="text-slate-500" colspan="4">Aucun achat récent.</x-app-td>
                        </tr>
                    @endforelse
                </x-slot:body>
            </x-app-table>
        </div>
    </div>
</div>
@endsection
