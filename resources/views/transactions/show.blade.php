{{-- resources/views/admin/transactions/show.blade.php --}}

@extends('layouts.main')

@section('content')

<div class="container mx-auto px-4 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold">
            Détails transaction
        </h1>
    </div>

    {{-- Infos --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <strong>Référence :</strong>
                {{ $transaction->reference }}
            </div>

            <div>
                <strong>Montant :</strong>
                {{ number_format($transaction->montant, 2, ',', ' ') }}
                {{ $transaction->devise }}
            </div>

            <div>
                <strong>Téléphone :</strong>
                {{ $transaction->numero_telephone }}
            </div>

            <div>
                <strong>Statut :</strong>
                {{ $transaction->statut }}
            </div>

            <div>
                <strong>Méthode :</strong>
                {{ $transaction->methode_paiement }}
            </div>

            <div>
                <strong>Date :</strong>
                {{ $transaction->created_at->format('d/m/Y H:i') }}
            </div>

        </div>

    </div>

    {{-- Timeline --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">

        <h2 class="text-lg font-semibold mb-4">
            Timeline
        </h2>

        <div class="space-y-4">

            @foreach($timeline as $item)

                <div class="border-l-4 border-blue-500 pl-4">

                    <div class="font-medium">
                        {{ $item['titre'] }}
                    </div>

                    <div class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y H:i:s') }}
                    </div>

                </div>

            @endforeach

        </div>

    </div>

    {{-- Logs --}}
    <div class="bg-white shadow rounded-lg p-6">

        <h2 class="text-lg font-semibold mb-4">
            Logs techniques
        </h2>

        <div class="bg-black text-green-400 rounded p-4 text-sm font-mono">

            @foreach($logs as $log)

                <div>
                    {{ $log }}
                </div>

            @endforeach

        </div>

    </div>

</div>

@endsection