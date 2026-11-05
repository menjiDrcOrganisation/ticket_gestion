@props([
    'evolution',
    'capacite' => 0,
    'limite' => 10,
])

@php
    // Les jours de vente les plus récents, du plus ancien au plus récent.
    $jours = collect($evolution)->take(-$limite)->values();
    $joursMasques = max(0, collect($evolution)->count() - $jours->count());
@endphp

<div {{ $attributes }}>
    @if($jours->isEmpty())
        <p class="text-sm text-slate-500">Aucune vente pour le moment.</p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-slate-500">
                    <th class="py-1 pr-2 font-medium">Date</th>
                    <th class="py-1 pr-2 text-right font-medium">Ventes</th>
                    <th class="py-1 pr-2 text-right font-medium">Cumul</th>
                    <th class="py-1 font-medium">Remplissage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($jours as $jour)
                    <tr>
                        <td class="py-1.5 pr-2 whitespace-nowrap text-slate-700">{{ $jour['date']->format('d/m/Y') }}</td>
                        <td class="py-1.5 pr-2 text-right text-slate-700">+{{ $jour['vendus'] }}</td>
                        <td class="py-1.5 pr-2 text-right text-slate-700">{{ $jour['cumul'] }}</td>
                        <td class="py-1.5">
                            @if((int) $capacite > 0)
                                <x-taux-remplissage :vendus="$jour['cumul']" :capacite="$capacite" compact />
                            @else
                                <span class="text-xs text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($joursMasques > 0)
            <p class="mt-2 text-xs text-slate-500">{{ $joursMasques }} jour(s) de vente plus ancien(s) non affiché(s).</p>
        @endif
    @endif
</div>
