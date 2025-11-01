@extends('layouts.main')
@section('title','Gestion des demandes d\'événements')

@section('content')

<div class="bg-white w-full px-4 sm:px-6 py-6 mx-auto">
    <div class="flex flex-col">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-xl font-semibold">Gestion des demandes d'événements</h2>
            <div class="flex flex-wrap items-center gap-3">
                <!-- Recherche -->
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Rechercher..."
                        class="w-64 sm:w-96 rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                    <span class="absolute left-2.5 top-2.5">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" class="w-4 h-4 opacity-70" alt="search">
                    </span>
                </div>

                <!-- Filtre statut -->
                <select id="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none text-dark">
                    <option value="">Tous les statuts</option>
                    <option value="valide">Valide</option>
                    <option value="ferme">Fermé</option>
                    <option value="en_attente">En attente</option>
                </select>

                <!-- Ajouter -->
                <button onclick="openModal('storeModal')"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
            </div>
        </div>

        <!-- Table responsive -->
        <div class="overflow-x-auto">
            <table id="demandeTable" class="min-w-full border-collapse text-gray-800">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase">#</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase">Événement</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase">Organisateur</th>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase">Type</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase">Statut</th>
                        <th class="px-4 py-2 text-center text-xs font-bold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandeEvenements as $index => $demande)
                    <tr class="border-b hover:bg-gray-50" data-status="{{ $demande->statut }}">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $demande->nom_evenement }}</td>
                        <td class="px-4 py-2">{{ $demande->contact_organisateur }}</td>
                        <td class="px-4 py-2">{{ $demande->type_evenement }}</td>

                        <!-- Statut inline -->
                        <td class="px-4 py-2 text-center">
                            <form action="{{ route('demandeEvenement.changeStatus', $demande->id) }}" method="POST">
                                @csrf
                                <select name="statut" onchange="this.form.submit()" class="px-2 py-1 rounded-full text-xs font-semibold
                                    @switch($demande->statut)
                                        @case('en_attente') bg-orange-100 text-orange-600 @break
                                        @case('valide') bg-emerald-100 text-emerald-700 @break
                                        @case('ferme') bg-red-100 text-red-700 @break
                                        @default bg-gray-100 text-gray-700
                                    @endswitch">
                                    <option value="en_attente" @if($demande->statut=='en_attente') selected @endif>En attente</option>
                                    <option value="valide" @if($demande->statut=='valide') selected @endif>Valide</option>
                                    <option value="ferme" @if($demande->statut=='ferme') selected @endif>Fermé</option>
                                </select>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-2 text-center flex flex-col sm:flex-row justify-center gap-2">
                            <button onclick="openModal('updateModal{{ $demande->id }}')" class="text-blue-600 hover:underline">Éditer</button>
                            <button onclick="openModal('deleteModal{{ $demande->id }}')" class="text-red-600 hover:underline">Supprimer</button>
                            @if($demande->affiche)
                                <a href="{{ asset('storage/'.$demande->affiche) }}" target="_blank" class="text-green-600 hover:underline">Voir l'affiche</a>
                            @endif
                        </td>
                    </tr>

                    <!-- Modals -->
                    <!-- Update Modal -->
                    <div id="updateModal{{ $demande->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <h3 class="text-lg font-semibold mb-4">Modifier la demande</h3>
                            <form action="{{ route('demandeEvenement.update', $demande->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nom_evenement" value="{{ $demande->nom_evenement }}" placeholder="Nom de l'événement" class="w-full border rounded px-3 py-2 mb-2" required>
                                <input type="text" name="contact_organisateur" value="{{ $demande->contact_organisateur }}" placeholder="Contact de l'organisateur" class="w-full border rounded px-3 py-2 mb-2" required>
                                <textarea name="description" placeholder="Description" class="w-full border rounded px-3 py-2 mb-2" required>{{ $demande->description }}</textarea>
                                <input type="text" name="type_evenement" value="{{ $demande->type_evenement }}" placeholder="Type d'événement" class="w-full border rounded px-3 py-2 mb-2" required>
                                <input type="file" name="affiche" class="w-full mb-4">
                                <select name="statut" class="w-full border rounded px-3 py-2 mb-4">
                                    <option value="en_attente" @if($demande->statut=='en_attente') selected @endif>En attente</option>
                                    <option value="valide" @if($demande->statut=='valide') selected @endif>Valide</option>
                                    <option value="ferme" @if($demande->statut=='ferme') selected @endif>Fermé</option>
                                </select>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="closeModal('updateModal{{ $demande->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Modifier</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div id="deleteModal{{ $demande->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <h3 class="text-lg font-semibold mb-4">Confirmation de suppression</h3>
                            <p class="mb-4">Voulez-vous vraiment supprimer la demande <strong>{{ $demande->nom_evenement }}</strong> ?</p>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="closeModal('deleteModal{{ $demande->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                <form action="{{ route('demandeEvenement.destroy', $demande->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">Aucune demande trouvée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Store Modal -->
<div id="storeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-4">Ajouter une demande</h3>
        <form action="{{ route('demandeEvenement.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="text" name="nom_evenement" placeholder="Nom de l'événement" class="w-full border rounded px-3 py-2 mb-2" required>
            <input type="text" name="contact_organisateur" placeholder="Contact de l'organisateur" class="w-full border rounded px-3 py-2 mb-2" required>
            <textarea name="description" placeholder="Description" class="w-full border rounded px-3 py-2 mb-2" required></textarea>
            <input type="text" name="type_evenement" placeholder="Type d'événement" class="w-full border rounded px-3 py-2 mb-2" required>
            <input type="file" name="affiche" class="w-full mb-4">
            <select name="statut" class="w-full border rounded px-3 py-2 mb-4">
                <option value="en_attente">En attente</option>
                <option value="valide">Valide</option>
                <option value="ferme">Fermé</option>
            </select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('storeModal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
function openModal(id){document.getElementById(id).classList.remove('hidden');}
function closeModal(id){document.getElementById(id).classList.add('hidden');}

// Recherche et filtre
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('demandeTable');

    function normalize(str){return str?str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim():"";}

    function filterTable(){
        const search = normalize(searchInput.value);
        const status = normalize(statusFilter.value);
        const rows = table.querySelectorAll('tbody tr');
        let anyVisible = false;

        rows.forEach(row => {
            if(row.classList.contains('no-results-row')) return;
            const rowText = normalize(row.innerText);
            const rowStatus = normalize(row.dataset.status);
            const matchSearch = search === "" || rowText.includes(search);
            const matchStatus = status === "" || rowStatus === status;
            row.style.display = (matchSearch && matchStatus) ? "" : "none";
            if(matchSearch && matchStatus) anyVisible = true;
        });

        let noRow = table.querySelector('.no-results-row');
        if(!noRow){
            const colCount = table.querySelectorAll('thead th').length;
            noRow = document.createElement('tr');
            noRow.className='no-results-row';
            noRow.innerHTML = `<td colspan="${colCount}" class="text-center italic py-4">Aucun résultat trouvé.</td>`;
            table.querySelector('tbody').appendChild(noRow);
        }
        noRow.style.display = anyVisible ? "none" : "";
    }

    searchInput.addEventListener("input", filterTable);
    statusFilter.addEventListener("change", filterTable);
    filterTable();
});
</script>

@endsection
