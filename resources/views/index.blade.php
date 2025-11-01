<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Billets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="bg-white w-full max-w-7xl mx-auto px-6 py-6 my-10 rounded-lg shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Gestion des Billets</h2>
            <button onclick="openModal('storeModal')" class="mt-3 md:mt-0 bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg shadow">
                + Ajouter un billet
            </button>
        </div>

        <!-- Barre de recherche et filtre -->
        <div class="flex flex-wrap gap-3 mb-4">
            <input type="text" id="searchInput" placeholder="Rechercher..." class="border border-gray-300 rounded-lg px-3 py-2 w-full md:w-80 focus:ring-2 focus:ring-emerald-400">
            <select id="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-400">
                <option value="">Tous les statuts</option>
                <option value="disponible">Disponible</option>
                <option value="épuisé">Épuisé</option>
                <option value="annulé">Annulé</option>
            </select>
        </div>

        <!-- Tableau -->
        <div class="overflow-x-auto shadow-md rounded-lg">
            <table id="billetTable" class="min-w-full text-sm text-gray-800">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Événement</th>
                        <th class="px-4 py-3 text-left font-semibold">Type</th>
                        <th class="px-4 py-3 text-left font-semibold">Quantité</th>
                        <th class="px-4 py-3 text-left font-semibold">Prix</th>
                        <th class="px-4 py-3 text-center font-semibold">Statut</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Exemple de données -->
                    <tr class="border-b hover:bg-gray-50" data-status="disponible">
                        <td class="px-4 py-3">1</td>
                        <td class="px-4 py-3">Concert Gospel</td>
                        <td class="px-4 py-3">VIP</td>
                        <td class="px-4 py-3">100</td>
                        <td class="px-4 py-3">50 000 FC</td>
                        <td class="px-4 py-3 text-center">
                            <span class="status px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 cursor-pointer" onclick="toggleStatus(this)">
                                Disponible
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button onclick="openModal('updateModal1')" class="text-blue-600 hover:underline">Éditer</button>
                            <button onclick="openModal('deleteModal1')" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Ajout -->
    <div id="storeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
            <h3 class="text-lg font-semibold mb-4">Ajouter un billet</h3>
            <form>
                <input type="text" placeholder="Nom de l'événement" class="w-full border rounded px-3 py-2 mb-2" required>
                <input type="text" placeholder="Type de billet" class="w-full border rounded px-3 py-2 mb-2" required>
                <input type="number" placeholder="Quantité" class="w-full border rounded px-3 py-2 mb-2" required>
                <input type="number" step="0.01" placeholder="Prix" class="w-full border rounded px-3 py-2 mb-2" required>
                <select class="w-full border rounded px-3 py-2 mb-4">
                    <option value="disponible">Disponible</option>
                    <option value="épuisé">Épuisé</option>
                    <option value="annulé">Annulé</option>
                </select>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('storeModal')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Exemple Modals édition / suppression -->
    <div id="updateModal1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
            <h3 class="text-lg font-semibold mb-4">Modifier le billet</h3>
            <form>
                <input type="text" placeholder="Concert Gospel" class="w-full border rounded px-3 py-2 mb-2">
                <input type="text" placeholder="VIP" class="w-full border rounded px-3 py-2 mb-2">
                <input type="number" placeholder="100" class="w-full border rounded px-3 py-2 mb-2">
                <input type="number" placeholder="50000" class="w-full border rounded px-3 py-2 mb-2">
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('updateModal1')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Modifier</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
            <h3 class="text-lg font-semibold mb-4">Confirmation</h3>
            <p>Voulez-vous supprimer le billet <strong>VIP</strong> ?</p>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="closeModal('deleteModal1')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                <button type="button" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer</button>
            </div>
        </div>
    </div>

    <!-- JS Modal + Filtre + Changement de statut -->
    <script>
    function openModal(id){document.getElementById(id).classList.remove('hidden');}
    function closeModal(id){document.getElementById(id).classList.add('hidden');}

    // Recherche / filtre
    document.addEventListener("DOMContentLoaded",()=>{
        const search=document.getElementById('searchInput');
        const filter=document.getElementById('statusFilter');
        const table=document.getElementById('billetTable');
        function normalize(str){return str?str.toLowerCase().trim():"";}
        function filterTable(){
            const term=normalize(search.value);
            const status=normalize(filter.value);
            table.querySelectorAll('tbody tr').forEach(row=>{
                const matchSearch=row.innerText.toLowerCase().includes(term);
                const matchStatus=!status || normalize(row.dataset.status)===status;
                row.style.display=(matchSearch&&matchStatus)?'':'none';
            });
        }
        search.addEventListener('input',filterTable);
        filter.addEventListener('change',filterTable);
    });

    // Changement de statut (cliquable)
    function toggleStatus(el){
        const current=el.textContent.trim().toLowerCase();
        let next='';
        if(current==='disponible'){next='épuisé';el.className="status px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 cursor-pointer";}
        else if(current==='épuisé'){next='annulé';el.className="status px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 cursor-pointer";}
        else{next='disponible';el.className="status px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 cursor-pointer";}
        el.textContent=next.charAt(0).toUpperCase()+next.slice(1);
        el.closest('tr').dataset.status=next;
    }
    </script>
</body>
</html>
