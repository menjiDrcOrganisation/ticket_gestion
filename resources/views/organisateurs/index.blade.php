@extends('layouts.main')

@section('content')
<div class="container mx-auto px-6 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800">👥 Gestion des Utilisateurs Organisateurs</h1>
        <button onclick="openModal()" 
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg shadow transition">
            + Ajouter un Organisateur
        </button>
    </div>

    {{-- Tableau des organisateurs --}}
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg border border-gray-200">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="py-3 px-6 text-left">#</th>
                    <th class="py-3 px-6 text-left">Nom complet</th>
                    <th class="py-3 px-6 text-left">Email</th>
                    <th class="py-3 px-6 text-left">Téléphone</th>
                    <th class="py-3 px-6 text-left">Date d’inscription</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($organisateurs as $org)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-6">{{ $org->id }}</td>
                        <td class="py-3 px-6 font-medium">{{ $org->user->name }}</td>
                        <td class="py-3 px-6">{{ $org->user->email }}</td>
                        <td class="py-3 px-6">{{ $org->telephone ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $org->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-6 text-center flex justify-center space-x-4">
                            <a href="{{ route('organisateurs.edit', $org->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium transition">
                               ✏️ Éditer
                            </a>
                            <form action="{{ route('organisateurs.destroy', $org->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Supprimer cet organisateur ?')">
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
                        <td colspan="6" class="py-6 text-center text-gray-500 italic">
                            Aucun organisateur enregistré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ✅ MODAL : Ajouter un organisateur --}}
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ajouter un Organisateur</h2>

        <form action="{{ route('organisateurs.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-medium mb-1">Nom complet</label>
                <input type="text" name="name" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Nom de l’organisateur" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="exemple@mail.com" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Téléphone</label>
                <input type="text" name="telephone" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Ex : +243 000 000 000">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Mot de passe</label>
                <input type="password" name="password" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="••••••••" required>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 border border-gray-400 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

        {{-- Bouton de fermeture --}}
        <button onclick="closeModal()" 
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            ✖
        </button>
    </div>
</div>

{{-- ✅ Script JS pour ouvrir/fermer le modal --}}
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
