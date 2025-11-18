@extends('layouts.main')
@section('content')
<div class="max-w-7xl mx-auto bg-gray-50 p-6 rounded-2xl shadow-sm mb-4">

    <!-- Statistiques -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">

        <x-indic-dashboard :value="$evenements->count()" title="total des evenements">
        </x-indic-dashboard>

         <x-indic-dashboard :value="$evenementsEncours" title="Événements en cours">
        </x-indic-dashboard>

         <x-indic-dashboard :value="$evenementsPasse" title="Événements passés">
        </x-indic-dashboard>
    </div>
    </div>


    <!-- Recherche -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-3">
        <input type="text"
            placeholder="Rechercher un événement..."
            class="w-full md:w-1/3 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black">
        <select class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-auto">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="fermé">Fermé</option>
            <option value="à venir">À venir</option>
        </select>

        <a href="{{route('evenements.create')}}"><button class="border text-black border-gray-300 bg-blue-500 rounded-lg px-4 py-2 w-full md:w-auto" type="button"> nouvelle evenement</button></a>
    </div>

    <!-- Tableau responsive amélioré -->
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-4 py-3 whitespace-nowrap">Nom événement</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">Auteur</th>
                    <th class="px-4 py-3 whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Adresse</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Salle</th>
                    <th class="px-4 py-3 whitespace-nowrap">Statut</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">URL</th>
                    <th class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($evenements as $evenement)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $evenement->nom }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell">{{ $evenement->organisateur->user->name ?? '—' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">{{ $evenement->adresse }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">{{ $evenement->salle }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($evenement->statut === 'encours')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">Actif</span>
                        @elseif($evenement->statut === 'ferme')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">Fermé</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs">À venir</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 max-w-[150px] truncate hidden xl:table-cell">
                        <a href="https://ticket.menjidrc.com/{{ $evenement->url_evenement }}" 
                           target="_blank" 
                           class="text-blue-600 hover:underline">
                            {{ $evenement->url_evenement }}
                        </a>
                    </td>

                    <!-- Actions - Toujours visible -->
                    <td class="px-4 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <!-- Voir -->
                            <button 
                                onclick="openModal('modal-{{ $evenement->id }}')" 
                                class="text-blue-600 hover:text-blue-800 transition p-1 rounded hover:bg-blue-50" 
                                title="Voir les détails">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>

                            <!-- Modifier -->
                            <a href="{{ route('evenements.edit', $evenement) }}" 
                               class="text-yellow-500 hover:text-yellow-700 transition p-1 rounded hover:bg-yellow-50" 
                               title="Modifier l'événement">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>

                            <!-- Supprimer -->
                            <form action="{{ route('evenements.destroy', $evenement) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet événement ?')"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-500 hover:text-red-700 transition p-1 rounded hover:bg-red-50" 
                                        title="Supprimer l'événement">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Modal amélioré -->
                <div id="modal-{{ $evenement->id }}" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
                        <!-- Bouton fermer -->
                        <button onclick="closeModal('modal-{{ $evenement->id }}')" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition p-2 rounded-full hover:bg-gray-100">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>

                        <!-- En-tête du modal -->
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h2 class="text-2xl font-bold text-gray-800">{{ $evenement->nom }}</h2>
                            <div class="flex items-center gap-2 mt-2">
                                @if($evenement->statut === 'encours')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Actif</span>
                                @elseif($evenement->statut === 'ferme')
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">Fermé</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">À venir</span>
                                @endif
                                <span class="text-gray-500">•</span>
                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <!-- Contenu du modal -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Informations principales -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Informations</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-3">
                                            <i data-lucide="calendar" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Date et heure</p>
                                                <p class="text-gray-600">
                                                    Début: {{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y H:i') }}<br>
                                                    @if($evenement->date_fin)
                                                    Fin: {{ \Carbon\Carbon::parse($evenement->date_fin)->format('d/m/Y H:i') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Lieu</p>
                                                <p class="text-gray-600">
                                                    {{ $evenement->adresse }}<br>
                                                    @if($evenement->salle)
                                                    Salle: {{ $evenement->salle }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <i data-lucide="user" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Organisateur</p>
                                                <p class="text-gray-600">{{ $evenement->organisateur->user->name ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations supplémentaires -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Accès</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-3">
                                            <i data-lucide="link" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">URL de l'événement</p>
                                                <a href="https://ticket.menjidrc.com/{{ $evenement->url_evenement }}" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:underline break-all text-sm">
                                                    https://ticket.menjidrc.com/{{ $evenement->url_evenement }}
                                                </a>
                                            </div>
                                        </div>

                                        @if($evenement->prix)
                                        <div class="flex items-start gap-3">
                                            <i data-lucide="tag" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Prix</p>
                                                <p class="text-gray-600">{{ $evenement->prix }} €</p>
                                            </div>
                                        </div>
                                        @endif

                                        @if($evenement->capacite_max)
                                        <div class="flex items-start gap-3">
                                            <i data-lucide="users" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Capacité</p>
                                                <p class="text-gray-600">{{ $evenement->capacite_max }} personnes</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($evenement->description)
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Description</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $evenement->description }}</p>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
                            <button onclick="closeModal('modal-{{ $evenement->id }}')" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Fermer
                            </button>
                            <a href="{{ route('evenements.edit', $evenement) }}" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                Modifier
                            </a>
                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">Aucun événement trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $evenements->links() ?? '' }}
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
});

// Fermer le modal avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const openModal = document.querySelector('.fixed:not(.hidden)');
        if (openModal) {
            closeModal(openModal.id);
        }
    }
});
</script>

<style>
/* Assurer que le tableau reste lisible sur mobile */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}

/* Animation pour le modal */
.fixed {
    transition: opacity 0.3s ease;
}

   

</style>
@endsection