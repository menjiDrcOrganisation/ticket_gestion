@extends('layouts.main')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <x-indic-dashboard :value="$evenementsEncours+$evenementsPasses" title="total des evenements">
        </x-indic-dashboard>

        <x-indic-dashboard :value="$evenementsEncours" title="Événements en cours">
        </x-indic-dashboard>
        
        <x-indic-dashboard :value="$evenementsPasses" title="Événements fermé">
        </x-indic-dashboard>

        <x-indic-dashboard :value="$demandeEvenements" title="demande d'evenements">
        </x-indic-dashboard>

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