@extends('layouts.main')
@section('title','Gestion des demandes d\'événements')

@section('content')

<div class="bg-white w-full px-4 sm:px-6 py-6 mx-auto">
    <div class="flex flex-col">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="text-xl font-semibold">Gestion des demandes d'événements</h2>

            <button onclick="openModal('storeModal')"
                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
            </button>
        </div>

        <form method="GET" action="{{ route('demandeEvenement.index') }}" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
                <x-app-input id="q" name="q" :value="$search" placeholder="Événement, organisateur, description..." wrapperClass="mb-0" />
            </div>

            <div class="md:col-span-3">
                <label for="statut" class="mb-1.5 block text-sm font-medium text-slate-600">Statut</label>
                <x-app-select id="statut" name="statut" wrapperClass="mb-0">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" @selected($status === 'en_attente')>En attente</option>
                    <option value="valide" @selected($status === 'valide')>Validé</option>
                    <option value="ferme" @selected($status === 'ferme')>Fermé</option>
                </x-app-select>
            </div>

            <div class="md:col-span-4">
                <label for="type_evenement" class="mb-1.5 block text-sm font-medium text-slate-600">Type d'événement</label>
                <x-app-select id="type_evenement" name="type_evenement" wrapperClass="mb-0">
                    <option value="">Tous les types</option>
                    @foreach($typeEvenements as $typeOption)
                        <option value="{{ $typeOption }}" @selected($typeEvenement === $typeOption)>{{ $typeOption }}</option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-12 flex flex-wrap gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Filtrer
                </button>
                <a href="{{ route('demandeEvenement.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Réinitialiser
                </a>
            </div>
        </form>

        <!-- Table responsive -->
        <x-app-table minWidth="1000px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
            <x-slot:head>
                <tr>
                    <x-app-th>#</x-app-th>
                    <x-app-th>Événement</x-app-th>
                    <x-app-th>Organisateur</x-app-th>
                    <x-app-th>Type</x-app-th>
                    <x-app-th align="center">Statut</x-app-th>
                    <x-app-th align="center">Actions</x-app-th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                    @forelse($demandeEvenements as $index => $demande)
                    <tr class="transition">
                        <x-app-td>{{ ($demandeEvenements->firstItem() ?? 0) + $loop->index }}</x-app-td>
                        <x-app-td>{{ $demande->nom_evenement }}</x-app-td>
                        <x-app-td>{{ $demande->contact_organisateur }}</x-app-td>
                        <x-app-td>{{ $demande->type_evenement }}</x-app-td>

                        <!-- Statut inline -->
                        <x-app-td align="center">
                            <form action="{{ route('demandeEvenement.changeStatus', $demande->id) }}" method="POST">
                                @csrf
                                @php
                                    $statusSelectClass = match ($demande->statut) {
                                        'en_attente' => 'bg-orange-100 text-orange-600',
                                        'valide' => 'bg-emerald-100 text-emerald-700',
                                        'ferme' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <x-app-select
                                    name="statut"
                                    onchange="this.form.submit()"
                                    wrapperClass=""
                                    :inputClass="'px-2 py-1 rounded-full text-xs font-semibold ' . $statusSelectClass"
                                >
                                    <option value="en_attente" @if($demande->statut=='en_attente') selected @endif>En attente</option>
                                    <option value="valide" @if($demande->statut=='valide') selected @endif>Valide</option>
                                    <option value="ferme" @if($demande->statut=='ferme') selected @endif>Fermé</option>
                                </x-app-select>
                            </form>
                        </x-app-td>

                        <!-- Actions -->
                        <x-app-td align="center">
                            <div class="flex flex-col sm:flex-row justify-center gap-2">
                                <button onclick="openModal('updateModal{{ $demande->id }}')" class="text-blue-600 hover:underline">Éditer</button>
                                <button onclick="openModal('deleteModal{{ $demande->id }}')" class="text-red-600 hover:underline">Supprimer</button>
                                @if($demande->affiche)
                                    <a href="{{ asset(env('COSTUM_URL_IMAGE', 'storage/public/app') . '/' . $demande->affiche) }}"
                                    target="_blank"
                                    class="text-green-600 hover:underline">
                                    Voir l'affiche
                                    </a>
                                @endif
                            </div>
                        </x-app-td>
                    </tr>

                    <!-- Modals -->
                    <!-- Update Modal -->
                    <div id="updateModal{{ $demande->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <h3 class="text-lg font-semibold mb-4">Modifier la demande</h3>
                            <form action="{{ route('demandeEvenement.update', $demande->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <x-app-input type="text" name="nom_evenement" :value="$demande->nom_evenement" placeholder="Nom de l'événement" required wrapperClass="mb-2" />
                                <x-app-input type="text" name="contact_organisateur" :value="$demande->contact_organisateur" placeholder="Contact de l'organisateur" required wrapperClass="mb-2" />
                                <x-app-textarea name="description" placeholder="Description" :value="$demande->description" required wrapperClass="mb-2" />
                                <x-app-input type="text" name="type_evenement" :value="$demande->type_evenement" placeholder="Type d'événement" required wrapperClass="mb-2" />
                                <x-app-file-input name="affiche" wrapperClass="mb-4" />
                                <x-app-select name="statut" wrapperClass="mb-4">
                                    <option value="en_attente" @if($demande->statut=='en_attente') selected @endif>En attente</option>
                                    <option value="valide" @if($demande->statut=='valide') selected @endif>Valide</option>
                                    <option value="ferme" @if($demande->statut=='ferme') selected @endif>Fermé</option>
                                </x-app-select>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="closeModal('updateModal{{ $demande->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Modifier</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div id="deleteModal{{ $demande->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
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
            </x-slot:body>
        </x-app-table>

        <div class="mt-5">
            {{ $demandeEvenements->links() }}
        </div>
    </div>
</div>

<!-- Store Modal -->
<div id="storeModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-4">Ajouter une demande</h3>
        <form action="{{ route('demandeEvenement.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-app-input type="text" name="nom_evenement" placeholder="Nom de l'événement" required wrapperClass="mb-2" />
            <x-app-input type="text" name="contact_organisateur" placeholder="Contact de l'organisateur" required wrapperClass="mb-2" />
            <x-app-textarea name="description" placeholder="Description" required wrapperClass="mb-2" />
            <x-app-input type="text" name="type_evenement" placeholder="Type d'événement" required wrapperClass="mb-2" />
            <x-app-file-input name="affiche" wrapperClass="mb-4" />
            <x-app-select name="statut" wrapperClass="mb-4">
                <option value="en_attente">En attente</option>
                <option value="valide">Valide</option>
                <option value="ferme">Fermé</option>
            </x-app-select>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('storeModal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
function openModal(id){
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeModal(id){
    const modal = document.getElementById(id);
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Recherche et filtre
</script>

@endsection
