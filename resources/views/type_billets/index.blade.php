@extends('layouts.main')

@section('content')
<div class="container mx-auto px-6 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800">🎟️ Gestion des Types de Billets</h1>
        <button onclick="openModal()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow transition">
            + Ajouter un Type de Billet
        </button>
    </div>

    {{-- Tableau des types --}}
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg border border-gray-200">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Nom</th>
                    <th class="py-3 px-6 text-left">Date de création</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($typeBillets as $typeBillet)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-6">{{ $typeBillet->id }}</td>
                        <td class="py-3 px-6 font-medium">{{ $typeBillet->nom_type }}</td>
                        <td class="py-3 px-6">{{ $typeBillet->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-6 text-center flex justify-center space-x-4">
                            <a href="{{ route('type_billet.edit', $typeBillet->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium transition">
                               ✏️ Éditer
                            </a>
                            <form action="{{ route('type_billet.destroy', $typeBillet->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type de billet ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 font-medium transition">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500 italic">
                            Aucun type de billet trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ✅ MODAL : Enregistrer un nouveau type --}}
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ajouter un Type de Billet</h2>

        <form action="{{ route('type_billet.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-medium mb-1">Nom du type</label>
                <input type="text" name="nom_type" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ex : VIP, Standard..." required>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 border border-gray-400 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

        {{-- Bouton de fermeture (croix) --}}
        <button onclick="closeModal()" 
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            ✖
        </button>
    </div>
</div>

{{-- ✅ Script Modal --}}
<script>
    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
        document.getElementById('modal').classList.remove('flex');
    }
</script>
@endsection
