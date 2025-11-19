@extends('layouts.main')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <x-indic-dashboard :value="$evenementsEncours+$evenementsPasses" title="total des evenements">
        </x-indic-dashboard>

        <x-indic-dashboard :value="$evenementsEncours" title="Événements en cours">
        </x-indic-dashboard>
        
        <x-indic-dashboard :value="$evenementsPasses" title="Événements passés">
        </x-indic-dashboard>

        <x-indic-dashboard :value="$demandeEvenements" title="demande d'evenements">
        </x-indic-dashboard>

</div>

<div class="mt-10 bg-white p-6 rounded-2xl shadow">
    <h2 class="text-xl font-bold mb-4">Événements Populaires</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom de l'événement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billets vendus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de début</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($evenementsPopulaires as $ep)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $ep->evenement->nom }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ep->total_billets }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($ep->evenement->date_debut)->format('d M Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<div class="mt-10 bg-white p-6 rounded-2xl shadow">
    <canvas id="eventsChart"></canvas>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    const data = @json(array_values($eventsPerMonth)); // valeurs pour chaque mois

    new Chart(document.getElementById('eventsChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Événements par mois',
                data: data,
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>






@endsection