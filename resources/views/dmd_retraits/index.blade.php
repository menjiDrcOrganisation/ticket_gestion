@extends('layouts.main')

@section('content')
<div class="container mx-auto px-6 py-10">
    {{-- des gid pour indicateur --}}
    {{-- 'totaldmd', 'totalmontantdmd', 'totalmontantdmdapprouve', 'totalmontantdmdenattente', 'totalmontantdmdrefuse' --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    {{-- <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Total Demandes</h3>
        <p class="text-2xl font-extrabold">{{ $stats['totaldmd'] }}</p>
    </div> --}}

    {{-- <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Total</h3>
        <p class="text-2xl font-extrabold">{{ number_format($stats['totalmontantdmd']) }} FC</p>
    </div> --}}

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Approuvé</h3>
        <p class="text-2xl font-extrabold text-green-600">
            {{ number_format($stats['totalmontantdmdapprouve']) }} FC
        </p>
    </div>

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant en Attente</h3>
        <p class="text-2xl font-extrabold text-yellow-500">
            {{ number_format($stats['totalmontantdmdenattente']) }} FC
        </p>
    </div>

    <div class="p-6 bg-white rounded-xl shadow">
        <h3 class="font-bold text-lg">Montant Refusé</h3>
        <p class="text-2xl font-extrabold text-red-600">
            {{ number_format($stats['totalmontantdmdrefuse']) }} FC
        </p>
    </div>

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
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($retraits as $retrait)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-6">{{ $retrait->id }}</td>
                        <td class="py-3 px-6">{{ $retrait->organisateur->user->email ?? 'Inconnu' }}</td>
                        <td class="py-3 px-6 font-medium text-gray-800">{{ $retrait->nom_detenteur }}</td>
                        <td class="py-3 px-6 text-green-700 font-semibold">
                            {{ number_format($retrait->montant, 2, ',', ' ') }} FC
                        </td>
                        <td class="py-3 px-6">
                            {{ \Carbon\Carbon::parse($retrait->date)->format('d/m/Y') }}
                        </td>

                        {{-- Statut modifiable --}}
                        <td class="py-3 px-6">
                            <form action="{{ route('dmd_retrait.updateStatut', $retrait->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="statut" onchange="this.form.submit()"
                                    class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:ring-2 focus:ring-blue-400">
                                    <option value="en attente" @selected($retrait->statut === 'en attente')>En attente</option>
                                    <option value="approuvé" @selected($retrait->statut === 'approuvé')>Approuvé</option>
                                    <option value="refusé" @selected($retrait->statut === 'refusé')>Refusé</option>
                                </select>
                            </form>
                        </td>

                        <td class="py-3 px-6 text-center space-x-4">
                            <a href="{{ route('dmd_retrait.show', $retrait->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium transition">👁️ Voir</a>
                            <form action="{{ route('dmd_retrait.destroy', $retrait->id) }}" 
                                  method="POST" class="inline"
                                  onsubmit="return confirm('Supprimer ce retrait ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 font-medium transition">🗑️ Supprimer</button>
                            </form>
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
