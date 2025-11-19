@extends('layouts.main')

@section('content')
<div class="container mx-auto px-6 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800">💸 Gestion des Retraits</h1>
        <button id="openModalBtn" 
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg shadow transition">
             Nouveau Retrait
        </button>
    </div>

    {{-- Tableau des retraits --}}
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg border border-gray-200">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="py-3 px-6 text-left">#</th>
                    <th class="py-3 px-6 text-left">Organisateur</th>
                    <th class="py-3 px-6 text-left">Nom du détenteur</th>
                    <th class="py-3 px-6 text-left">Montant</th>
                    <th class="py-3 px-6 text-left">Date</th>
                    <th class="py-3 px-6 text-left">Statut</th>
                    
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($retraits as $retrait)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-6">{{ $retrait->id }}</td>
                        <td class="py-3 px-6">{{ $retrait->organisateur->name ?? 'Inconnu' }}</td>
                        <td class="py-3 px-6 font-medium text-gray-800">{{ $retrait->nom_detenteur }}</td>
                        <td class="py-3 px-6 text-green-700 font-semibold">
                            {{ number_format($retrait->montant, 2, ',', ' ') }} FC
                        </td>
                        <td class="py-3 px-6">
                            {{ \Carbon\Carbon::parse($retrait->date)->format('d/m/Y') }}
                        </td>

                        {{-- Statut modifiable --}}
                        <td class="py-3 px-6">
                        
                           {{ $retrait->statut }}
                        </td>

                      
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-500 italic">
                        Aucun retrait enregistré pour le moment.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL : Ajouter un nouveau retrait --}}
<div id="createModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <button id="closeModalBtn" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">✖</button>
        <h2 class="text-xl font-bold mb-4 text-gray-800"> Nouveau Retrait</h2>

        <form action="{{ route('retraits.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="organisateur_id" value="{{ Auth::user()->organisateur->id ?? '11' }}">
            <input type="hidden" name="statut" value="en attente">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du détenteur</label>
                <input type="text" name="nom_detenteur" class="w-full border-gray-300 rounded-lg" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FC)</label>
                <input type="number" name="montant" class="w-full border-gray-300 rounded-lg" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" class="w-full border-gray-300 rounded-lg" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script du modal --}}
<script>
    const modal = document.getElementById('createModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');

    openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    window.addEventListener('click', e => { if(e.target === modal) modal.classList.add('hidden') });
</script>
@endsection
