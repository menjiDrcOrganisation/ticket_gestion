@extends('layouts.main')
@section('title', 'Dashboard Admin')
@section('content')

@php
    $kpis = $dashboard['kpis'];
    $queues = $dashboard['queues'];
    $lists = $dashboard['lists'];
    $periodDays = $dashboard['periodDays'];
@endphp

<div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Admin</h1>
        <p class="text-sm text-slate-500">Vue consolidée des ventes, opérations et risques.</p>
    </div>

    <form method="GET" action="{{ route('dashboard.admin.viewDash') }}" class="inline-flex items-center gap-2">
        <label for="days" class="text-sm text-slate-600">Période</label>
        <x-app-select id="days" name="days" wrapperClass="" inputClass="min-w-[120px]" onchange="this.form.submit()">
            <option value="7" @selected($periodDays === 7)>7 jours</option>
            <option value="30" @selected($periodDays === 30)>30 jours</option>
            <option value="90" @selected($periodDays === 90)>90 jours</option>
        </x-app-select>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <x-indic-dashboard :value="$kpis['evenementsActifs']" title="Événements actifs" subtitle="En cours" icon="calendar-days" tone="blue" :href="route('evenements.index')" />
    <x-indic-dashboard :value="$kpis['ventesAujourdhui']" title="Billets vendus aujourd'hui" subtitle="Volume" icon="ticket" tone="emerald" :href="route('transactions.index')" />
    <x-indic-dashboard :value="number_format($kpis['caAujourdhuiCDF'], 0, ',', ' ') . ' CDF'" title="CA aujourd'hui" subtitle="CDF" icon="banknotes" tone="amber" :href="route('transactions.index')" />
    <x-indic-dashboard :value="number_format($kpis['caAujourdhuiUSD'], 2, ',', ' ') . ' USD'" title="CA aujourd'hui" subtitle="USD" icon="currency-dollar" tone="slate" :href="route('transactions.index')" />
</div>

<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-slate-800">Demandes d'événements en attente</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">Événement</th>
                        <th class="py-2 pr-3">Organisateur</th>
                        <th class="py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues['demandesEnAttente'] as $demande)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-3 text-slate-700">{{ $demande->nom_evenement }}</td>
                            <td class="py-2 pr-3 text-slate-600">{{ $demande->contact_organisateur }}</td>
                            <td class="py-2 text-slate-500">{{ $demande->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-slate-500">Aucune demande en attente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-slate-800">Retraits en attente</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">Organisateur</th>
                        <th class="py-2 pr-3">Montant</th>
                        <th class="py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues['retraitsEnAttente'] as $retrait)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-3 text-slate-700">{{ $retrait->organisateur?->user?->name ?? '—' }}</td>
                            <td class="py-2 pr-3 text-slate-600">{{ number_format($retrait->montant, 0, ',', ' ') }} CDF</td>
                            <td class="py-2 text-slate-500">{{ $retrait->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-slate-500">Aucun retrait en attente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-slate-800">Top 5 événements</h2>
        <ul class="space-y-2 text-sm">
            @forelse($lists['topEvenements'] as $item)
                <li class="border-b border-slate-100 pb-2">
                    <p class="font-medium text-slate-700">{{ $item->evenement?->nom ?? 'Événement supprimé' }}</p>
                    <p class="text-slate-500">{{ (int) $item->billets_vendus }} billets • {{ number_format((float) $item->montant_total, 0, ',', ' ') }}</p>
                </li>
            @empty
                <li class="text-slate-500">Aucune vente pour la période.</li>
            @endforelse
        </ul>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-3 text-base font-semibold text-slate-800">Activité récente</h2>
        <ul class="space-y-2 text-sm">
            @forelse($lists['activityRecent'] as $log)
                <li class="border-b border-slate-100 pb-2">
                    <p class="font-medium text-slate-700">{{ $log->action }} • {{ $log->entity_type ?? 'Système' }}</p>
                    <p class="text-slate-500">{{ $log->user?->name ?? 'Système' }} • {{ $log->created_at?->format('d/m/Y H:i') }}</p>
                </li>
            @empty
                <li class="text-slate-500">Aucune activité récente.</li>
            @endforelse
        </ul>
    </div>
</div>

@endsection