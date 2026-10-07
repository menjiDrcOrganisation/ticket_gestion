@props([
    'vendus' => 0,
    'capacite' => 0,
    'compact' => false,
])

@php
    $taux = \App\Models\Evenement::calculerTauxRemplissage((int) $vendus, (int) $capacite);

    // Vert : places disponibles, ambre : bien rempli, rouge : presque complet.
    $couleur = match (true) {
        $taux >= 90 => 'bg-rose-500',
        $taux >= 70 => 'bg-amber-500',
        default => 'bg-emerald-500',
    };
    $tauxAffiche = rtrim(rtrim(number_format($taux, 1, ',', ''), '0'), ',');
@endphp

<div {{ $attributes->merge(['class' => $compact ? 'min-w-[120px]' : '']) }}>
    @if((int) $capacite <= 0)
        <span class="text-xs text-slate-500">Capacité non définie</span>
    @else
        <div class="flex items-baseline justify-between gap-2 {{ $compact ? 'text-xs' : 'text-sm' }}">
            <span class="font-semibold text-slate-800">{{ $tauxAffiche }} %</span>
            <span class="text-slate-500">{{ number_format((int) $vendus, 0, ',', ' ') }} / {{ number_format((int) $capacite, 0, ',', ' ') }}</span>
        </div>
        <div class="mt-1 w-full overflow-hidden rounded-full bg-slate-200 {{ $compact ? 'h-1.5' : 'h-2.5' }}"
             role="progressbar"
             aria-label="Taux de remplissage"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="{{ $taux }}">
            <div class="h-full rounded-full {{ $couleur }}" style="width: {{ $taux }}%"></div>
        </div>
    @endif
</div>
