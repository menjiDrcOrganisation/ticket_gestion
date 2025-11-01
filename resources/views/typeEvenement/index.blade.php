@extends('layouts.main')

@section('content')
<div class="bg-white w-full px-6 py-6 mx-auto">
    <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col mb-6 bg-white shadow-xl rounded-2xl">

                <!-- Header -->
                <div class="p-6 border-b rounded-t-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h6 class="text-xl font-semibold flex items-center gap-2">
                        Gestion des Types d'événements
                    </h6>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Input recherche -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Rechercher..."
                                class="w-96 rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            <span class="absolute left-2.5 top-2.5">
                                <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" class="w-4 h-4 opacity-70" alt="search">
                            </span>
                        </div>

                        <!-- Bouton Ajouter -->
                        <button onclick="openModal('storeModal')"
                            class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="flex-auto px-0 pt-4 pb-2">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-gray-800">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">N°</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nom du type d'événement</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Créé le</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($typeEvenements as $index => $type)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4">{{ $type->nom_type }}</td>
                                        <td class="px-6 py-4 text-center">{{ $type->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                                            <button onclick="openModal('updateModal{{ $type->id }}')" class="text-blue-600 hover:underline">Éditer</button>

                                            <button onclick="openModal('deleteModal{{ $type->id }}')" class="text-red-600 hover:underline">Supprimer</button>
                                        </td>
                                    </tr>

                                    <!-- Update Modal -->
                                    <div id="updateModal{{ $type->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Modifier le type d'événement</h3>
                                            <form action="{{ route('typeEvenement.update', $type->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="nom_type" value="{{ $type->nom_type }}" class="w-full border rounded px-3 py-2 mb-4">
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="closeModal('updateModal{{ $type->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div id="deleteModal{{ $type->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Confirmer la suppression</h3>
                                            <p class="mb-4">Voulez-vous vraiment supprimer <strong>{{ $type->nom_type }}</strong> ?</p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="closeModal('deleteModal{{ $type->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                <form action="{{ route('typeEvenement.destroy', $type->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-gray-500">Aucun type d'événement trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Store Modal -->
<div id="storeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h3 class="text-lg font-semibold mb-4">Ajouter un type d'événement</h3>
        <form action="{{ route('typeEvenement.store') }}" method="POST">
            @csrf
            <input type="text" name="nom_type" placeholder="Nom du type" class="w-full border rounded px-3 py-2 mb-4">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('storeModal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>

@endsection
