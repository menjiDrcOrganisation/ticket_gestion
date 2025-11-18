
@extends('layouts.main')
@section('content')

<div class="bg-white w-full px-6 py-6 mx-auto">
    <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col mb-6 bg-white shadow-xl rounded-2xl">

                <!-- Header -->
                <div class="p-6 border-b rounded-t-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h6 class="text-xl font-semibold flex items-center gap-2">
                        Gestion des Utilisateurs
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
                        <button onclick="openModal('storeModal')" class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Nom</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Rôle</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Créé le</th>
                                    
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                @forelse($users as $index => $user)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">{{ $user->name }}</td>
                                        <td class="px-6 py-4">{{ $user->email }}</td>
                                        <td class="px-6 py-4">{{ $user->role ?? '—' }}</td>
                                        <td class="px-6 py-4 text-center">{{ $user->created_at->format('d/m/Y') }}</td>
                                       
                                    </tr>

                                    <!-- Update Modal -->
                                    <div id="updateModal{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Modifier l'utilisateur</h3>
                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $user->name }}" class="w-full border rounded px-3 py-2 mb-3" placeholder="Nom">
                                                <input type="email" name="email" value="{{ $user->email }}" class="w-full border rounded px-3 py-2 mb-3" placeholder="Email">
                                                <input type="text" name="role" value="{{ $user->role }}" class="w-full border rounded px-3 py-2 mb-3" placeholder="Rôle (ex: admin)">
                                                <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-4" placeholder="Mot de passe (laisser vide pour ne pas changer)">
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="closeModal('updateModal{{ $user->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div id="deleteModal{{ $user->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-4">Confirmer la suppression</h3>
                                            <p class="mb-4">Voulez-vous vraiment supprimer <strong>{{ $user->name }}</strong> ?</p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="closeModal('deleteModal{{ $user->id }}')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-gray-500">Aucun utilisateur trouvé.</td>
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
        <h3 class="text-lg font-semibold mb-4">Ajouter un utilisateur</h3>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <input type="text" name="name" placeholder="Nom" class="w-full border rounded px-3 py-2 mb-3" required>
            <input type="email" name="email" placeholder="Email" class="w-full border rounded px-3 py-2 mb-3" required>
            <input type="text" name="role" placeholder="Rôle (ex: admin)" class="w-full border rounded px-3 py-2 mb-3">
            <input type="password" name="password" placeholder="Mot de passe" class="w-full border rounded px-3 py-2 mb-4" required>
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

    // Simple client-side search (filtre par nom ou email)
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(function (tr) {
            const name = tr.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
            const email = tr.querySelector('td:nth-child(3)')?.textContent?.toLowerCase() || '';
            tr.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
        });
    });
</script>
@endsection

