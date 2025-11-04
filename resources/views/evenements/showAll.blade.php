@extends('layouts.main') @section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Liste des Événements</h2>

    <!-- Message de succès -->
    @if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session("success") }}
    </div>
    @endif

    <!-- Bouton Créer -->
    <div class="mb-4 text-right">
        <a
            href="{{ route('evenements.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
        >
            + Nouvel Événement
        </a>
    </div>

    <!-- Tableau -->
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 border">#</th>
                    <th class="px-4 py-2 border">Nom événement</th>
                    <th class="px-4 py-2 border">Organisateur</th>
                    <th class="px-4 py-2 border">Date Début</th>
                    <th class="px-4 py-2 border">Date Fin</th>
                    <th class="px-4 py-2 border">Salle</th>
                    <th class="px-4 py-2 border">Billets</th>
                    <th class="px-4 py-2 border">URL</th>
                    <th class="px-4 py-2 border text-center">Statut</th>
                    <th class="px-4 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evenements as $index => $evenement)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border text-center">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-4 py-2 border">{{ $evenement->nom }}</td>
                    <td class="px-4 py-2 border">
                        {{ $evenement->organisateur->user->name ?? '—' }}
                    </td>
                    <td class="px-4 py-2 border">
                        {{ $evenement->date_debut }}
                    </td>
                    <td class="px-4 py-2 border">{{ $evenement->date_fin }}</td>
                    <td class="px-4 py-2 border">{{ $evenement->salle }}</td>
                    <td class="px-4 py-2 border align-top">
    <ul class="divide-y divide-gray-200">
        @foreach ($evenement->typeBillets as $typeBillet)
            <li class="py-1 flex justify-between text-sm">
                <span class="font-semibold">{{ $typeBillet->nom_type }}</span>
                <span>
                    {{ $typeBillet->pivot->nombre_billet }} billets |
                    {{ number_format($typeBillet->pivot->prix_unitaire, 0, ',', ' ') }} Fc
                </span>
            </li>
        @endforeach
    </ul>
</td>

                    
                    <td class="px-4 py-2 border text-center">
                        <a
                            href="https://ticket.menjidrc.com/{{ $evenement->url_evenement }}"
                            class="text-blue-500"
                            >https://ticket.menjidrc.com/{{ $evenement->url_evenement }}</a
                        >
                    </td>

                    <!-- Combo box pour changer le statut -->
                    <td class="px-4 py-2 border text-center">
                        <form
                            action="{{ route('evenements.updateStatus', $evenement->id) }}"
                            method="POST"
                        >
                            @csrf @method('PATCH')
                            <select
                                name="statut"
                                onchange="this.form.submit()"
                                class="border-gray-300 rounded p-1"
                            >
                                <option value="encours" {{ $evenement->
                                    statut === 'encours' ? 'selected' : '' }}>En
                                    cours
                                </option>
                                <option value="ferme" {{ $evenement->
                                    statut === 'ferme' ? 'selected' : ''
                                    }}>Fermé
                                </option>
                            </select>
                        </form>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-2 border text-center">
                        <a
                            href="{{ route('evenements.show', $evenement->id) }}"
                            class="text-blue-600 hover:underline"
                            >Voir</a
                        >
                        |
                        <a
                            href="{{ route('evenements.edit', $evenement->id) }}"
                            class="text-yellow-600 hover:underline"
                            >Modifier</a
                        >
                        |
                        <form
                            action="{{ route('evenements.destroy', $evenement->id) }}"
                            method="POST"
                            class="inline"
                        >
                            @csrf @method('DELETE')
                            <button
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Supprimer cet événement ?')"
                            >
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-4 text-gray-500">
                        Aucun événement enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
