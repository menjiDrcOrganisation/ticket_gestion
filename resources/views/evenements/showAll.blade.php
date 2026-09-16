@extends('layouts.main')
@section('title', 'Événements')
@section('content')
<div class="max-w-7xl mx-auto bg-gray-50 p-6 rounded-2xl shadow-sm mb-4">

    <!-- Statistiques -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <x-indic-dashboard :value="$evenements->total()" title="Total des événements" subtitle="Catalogue" icon="calendar-days" tone="blue" />

        <x-indic-dashboard :value="$evenementsEncours" title="Événements en cours" subtitle="Actifs" icon="play-circle" tone="emerald" />

        <x-indic-dashboard :value="$evenementsPasse" title="Événements fermés" subtitle="Terminés" icon="check-circle" tone="slate" />
    </div>
    </div>

    <!-- Recherche et filtres -->
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('evenements.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
                <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
                <x-app-input id="q" name="q" :value="$search" placeholder="Nom, URL, adresse, salle, organisateur..." wrapperClass="mb-0" />
            </div>

            <div class="md:col-span-3">
                <label for="statut" class="mb-1.5 block text-sm font-medium text-slate-600">Statut événement</label>
                <x-app-select id="statut" name="statut" wrapperClass="mb-0">
                    <option value="">Tous les statuts</option>
                    <option value="encours" @selected($status === 'encours')>En cours</option>
                    <option value="ferme" @selected($status === 'ferme')>Fermé</option>
                    <option value="a venir" @selected($status === 'a venir')>À venir</option>
                </x-app-select>
            </div>

            <div class="md:col-span-4">
                <label for="type_evenement_id" class="mb-1.5 block text-sm font-medium text-slate-600">Type d'événement</label>
                <x-app-select id="type_evenement_id" name="type_evenement_id" wrapperClass="mb-0">
                    <option value="">Tous les types</option>
                    @foreach($typeEvenements as $typeEvenement)
                        <option value="{{ $typeEvenement->id }}" @selected((string) $typeEvenementId === (string) $typeEvenement->id)>
                            {{ $typeEvenement->nom_type }}
                        </option>
                    @endforeach
                </x-app-select>
            </div>

            <div class="md:col-span-12 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Filtrer
                    </button>
                    <a href="{{ route('evenements.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Réinitialiser
                    </a>
                </div>

                <a href="{{route('evenements.create')}}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Créer un événement
                </a>
            </div>
        </form>
    </div>

    <!-- Tableau responsive amélioré -->
    <div class="bg-white rounded-2xl shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-4 py-3 whitespace-nowrap">#</th>
                    <th class="px-4 py-3 whitespace-nowrap">Nom de l'événement</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden md:table-cell">Auteur</th>
                    <th class="px-4 py-3 whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Adresse</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Salle</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Type</th>
                    <th class="px-4 py-3 whitespace-nowrap">Statut événement</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Mail</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Tentatives</th>
                    <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">URL</th>
                    <th class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($evenements as $evenement)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-4 whitespace-nowrap">{{ ($evenements->firstItem() ?? 0) + $loop->index }}</td>
                    <td class="px-4 py-4 font-medium whitespace-nowrap">{{ $evenement->nom }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell">{{ $evenement->organisateur->user->name ?? '—' }} <br>
                    {{ $evenement->organisateur->user->email ?? '—' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($evenement->date_debut)->format('d/m/Y') }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">{{ $evenement->adresse }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">{{ $evenement->salle }}</td>
                    <td class="px-4 py-4 whitespace-nowrap hidden lg:table-cell">{{ $evenement->typeEvenement->nom_type ?? '—' }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">
                        @if($evenement->statut === 'encours')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">encours</span>
                        @elseif($evenement->statut === 'ferme')
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">Fermé</span>
                        @else
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs">À venir</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden xl:table-cell">
                        @if($evenement->mail_sent_at)
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs">Envoyé</span>
                        @elseif($evenement->last_mail_error)
                            <span class="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-xs">Échec</span>
                        @else
                            <span class="bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs">En attente</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap hidden xl:table-cell">{{ (int) ($evenement->mail_send_attempts ?? 0) }}</td>
                    <td class="px-4 py-4 max-w-[150px] truncate hidden xl:table-cell">
                        <a href="https://ticket.menjidrc.com/{{ $evenement->url_evenement }}" 
                           target="_blank" 
                           class="text-blue-600 hover:underline">
                            {{ $evenement->url_evenement }}
                        </a>
                    </td>

                    <!-- Actions - Toujours visible -->
                    <td class="px-4 py-4 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex justify-end gap-2">
                            <!-- Voir -->
                            <button 
                                onclick="openModal('modal-{{ $evenement->id }}')" 
                                class="text-blue-600 hover:text-blue-800 transition p-1 rounded hover:bg-blue-50" 
                                title="Voir les détails">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>

                            <!-- Modifier -->
                           <button 
                                onclick="openModal('edit-modal-{{ $evenement->id }}')" 
                                class="text-yellow-500 hover:text-yellow-700 transition p-1 rounded hover:bg-yellow-50"
                                title="Modifier">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <!-- Fermer -->
                            @if($evenement->statut === 'ferme')
                                <button type="button"
                                        class="text-slate-400 transition p-1 rounded cursor-not-allowed"
                                        title="Événement déjà fermé"
                                        disabled>
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </button>
                            @else
                                <form action="{{ route('evenements.updateStatus', $evenement->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Voulez-vous vraiment fermer cet événement ?')"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="statut" value="ferme">
                                    <button type="submit"
                                            class="text-rose-600 hover:text-rose-700 transition p-1 rounded hover:bg-rose-50"
                                            title="Fermer l'événement">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('evenements.resendMail', $evenement->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Renvoyer les identifiants pour cet événement ?')"
                                  class="inline-flex items-center gap-1.5">
                                @csrf
                                <button type="submit"
                                        class="text-indigo-600 hover:text-indigo-800 transition p-1 rounded hover:bg-indigo-50"
                                        title="Renvoyer le mail d'accès">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                </button>
                            </form>
                            </div>

                            @if($evenement->mail_sent_at)
                                <span class="text-[11px] text-emerald-600 whitespace-nowrap">mail envoye</span>
                            @else
                                <span class="text-[11px] text-rose-600 whitespace-nowrap">mail non envoye</span>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Modal amélioré -->
                <div id="modal-{{ $evenement->id }}" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center hidden z-50 p-4">
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

                        @php
                            $affiche = $evenement->ressource->firstWhere('photo_affiche', '!=', null) ?? $evenement->ressource->first();
                        @endphp

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Affiche</h3>
                            @if($affiche && !empty($affiche->photo_affiche))
                                <img
                                    src="{{ asset('storage/' . ltrim($affiche->photo_affiche, '/')) }}"
                                    alt="Affiche de {{ $evenement->nom }}"
                                    class="w-full max-h-72 rounded-lg border border-gray-200 object-cover"
                                    loading="lazy"
                                >
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                                    Aucune affiche disponible pour cet événement.
                                </div>
                            @endif
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
                                                <a href="{{env('ACHAT_URL')}}/{{ $evenement->url_evenement }}" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:underline break-all text-sm">
                                                    {{env('ACHAT_URL')}}/{{ $evenement->url_evenement }}
                                                </a>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3">
                                            <i data-lucide="link" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                            <div>
                                                <p class="font-medium text-gray-700">Type de billet</p>
                                                <ul>
                                                   
                                                @foreach($evenement->typeBillets as $type_billet)

                                                 <li>{{$type_billet->nom_type}} {{$type_billet->pivot->prix_unitaire}} {{$type_billet->pivot->devise}}</li>

                                                @endforeach

                                                
                                                </ul>
                                            </div>
                                        </div>


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
                        </div>
                    </div>
                </div>
                <!-- Modal Modifier -->
                <div id="edit-modal-{{ $evenement->id }}" 
                    class="fixed inset-0 bg-black bg-opacity-40 items-center justify-center hidden z-50 p-4">

                    <div class="bg-white rounded-xl shadow-lg w-full max-w-xl p-6 relative max-h-[90vh] overflow-y-auto">

                        <!-- Close button -->
                        <button onclick="closeModal('edit-modal-{{ $evenement->id }}')" 
                                class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition p-2 rounded-full hover:bg-gray-100">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>

                        <h2 class="text-xl font-bold mb-4 text-gray-800">Modifier l'événement</h2>

                        <form action="{{ route('evenements.update', $evenement->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="grid gap-4 max-h-[65vh] overflow-y-auto pr-1">

                                <div>
                                    <label class="text-sm text-gray-600">Nom événement</label>
                                    <input type="text" name="nom" value="{{ $evenement->nom }}" 
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">Date début</label>
                                    <input type="datetime-local" name="date_debut" 
                                        value="{{ date('Y-m-d\TH:i', strtotime($evenement->date_debut)) }}"
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">Date fin</label>
                                    <input type="datetime-local" name="date_fin"
                                        value="{{ $evenement->date_fin ? date('Y-m-d\TH:i', strtotime($evenement->date_fin)) : '' }}"
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">Adresse</label>
                                    <input type="text" name="adresse" value="{{ $evenement->adresse }}"
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">Salle</label>
                                    <input type="text" name="salle" value="{{ $evenement->salle }}"
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">URL de l'événement</label>
                                    <input type="text" name="url_evenement" value="{{ $evenement->url_evenement }}"
                                        class="w-full border rounded-lg p-2">
                                </div>

                                <div>
                                    <label class="text-sm text-gray-600">Changer la photo de l'affiche</label>
                                    <input type="file" name="photo_affiche" accept="image/*"
                                        class="w-full border rounded-lg p-2">
                                    @if(isset($evenement->ressource[0]) && !empty($evenement->ressource[0]->photo_affiche))
                                        <p class="text-xs text-gray-500 mt-1">Photo actuelle: {{ $evenement->ressource[0]->photo_affiche }}</p>
                                    @endif
                                </div>

                            </div>

                            <div class="flex justify-end mt-6 gap-3">
                                <button type="button" onclick="closeModal('edit-modal-{{ $evenement->id }}')"
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>

                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Enregistrer
                                </button>
                            </div>

                        </form>

                    </div>
                </div>


                @empty
                <tr>
                    <td colspan="12" class="text-center py-6 text-gray-500">Aucun événement trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-600">
            Affichage de {{ $evenements->firstItem() ?? 0 }} à {{ $evenements->lastItem() ?? 0 }} sur {{ $evenements->total() }} événements
        </p>
        <div>
            {{ $evenements->links() ?? '' }}
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.remove('flex');
        event.target.classList.add('hidden');
        document.body.style.overflow = 'auto';
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