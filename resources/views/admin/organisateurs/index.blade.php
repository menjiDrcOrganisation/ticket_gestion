@extends('layouts.main')

@section('content')
<div class="bg-white w-full px-6 py-6 mx-auto">
    <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col mb-6 bg-white shadow-xl rounded-2xl">
                <div class="p-6 border-b rounded-t-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h6 class="text-xl font-semibold">Gestion des Organisateurs</h6>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <x-app-input type="text" id="searchInput" placeholder="Rechercher..." wrapperClass=""
                                inputClass="w-96 pl-4 pr-3" />
                        </div>

                        <button onclick="openModal('storeModal')" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow">
                            Ajouter
                        </button>
                    </div>
                </div>

                <div class="flex-auto px-0 pt-4 pb-2">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-gray-800">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">N°</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Téléphone</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Créé le</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($organisateurs as $organisateur)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">{{ $organisateur->user->name ?? '—' }}</td>
                                        <td class="px-6 py-4">{{ $organisateur->user->email ?? '—' }}</td>
                                        <td class="px-6 py-4">{{ $organisateur->telephone ?? '—' }}</td>
                                        <td class="px-6 py-4 text-center">{{ optional($organisateur->created_at)->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                                            <button onclick="openModal('updateModal{{ $organisateur->id }}')" class="text-blue-600 hover:underline">Éditer</button>
                                            <button onclick="openModal('deleteModal{{ $organisateur->id }}')" class="text-red-600 hover:underline">Supprimer</button>
                                        </td>
                                    </tr>

                                    <div id="updateModal{{ $organisateur->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Modifier l'organisateur</h3>
                                            <form action="{{ route('admin.organisateurs.update', $organisateur->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <x-app-input type="text" name="name" :value="$organisateur->user->name ?? ''" placeholder="Nom" required />
                                                <x-app-input type="email" name="email" :value="$organisateur->user->email ?? ''" placeholder="Email" required />
                                                <x-app-input type="text" name="telephone" :value="$organisateur->telephone" placeholder="Téléphone" required />
                                                <x-app-input type="password" name="password" placeholder="Mot de passe (laisser vide pour ne pas changer)" wrapperClass="mb-4" />
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="closeModal('updateModal{{ $organisateur->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div id="deleteModal{{ $organisateur->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Confirmer la suppression</h3>
                                            <p class="mb-4">Voulez-vous vraiment supprimer <strong>{{ $organisateur->user->name ?? 'cet organisateur' }}</strong> ?</p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="closeModal('deleteModal{{ $organisateur->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                <form action="{{ route('admin.organisateurs.destroy', $organisateur->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-500">Aucun organisateur trouvé.</td>
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

<div id="storeModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-96 p-6">
        <h3 class="text-lg font-semibold mb-4">Ajouter un organisateur</h3>
        <form action="{{ route('admin.organisateurs.store') }}" method="POST">
            @csrf
            <x-app-input type="text" name="name" placeholder="Nom" required />
            <x-app-input type="email" name="email" placeholder="Email" required />
            <x-app-input type="text" name="telephone" placeholder="Téléphone" required />
            <x-app-input type="password" name="password" placeholder="Mot de passe" required wrapperClass="mb-4" />
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
        document.getElementById(id).classList.add('flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('flex');
        document.getElementById(id).classList.add('hidden');
    }

    document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(function (row) {
            const name = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
            const email = row.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '';
            const telephone = row.querySelector('td:nth-child(4)')?.textContent?.toLowerCase() || '';
            row.style.display = (name.includes(query) || email.includes(query) || telephone.includes(query)) ? '' : 'none';
        });
    });
</script>
@endsection