@extends('layouts.org')
@section('content')

<!-- Overlay mobile -->
<div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40 md:hidden" onclick="toggleSidebar()"></div>

<div class="p-4 md:p-6 mt-14 md:mt-0">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Achats de Billets</h2>
                    <p class="text-gray-500 mt-1">Tous les achats de billets pour vos événements</p>
                </div>

            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total des achats</p>
                            <h3 class="text-xl font-bold text-gray-800">{{$totalAchat}}</h3>
                        </div>
                        <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total restant</p>
                            <h3 class="text-xl font-bold text-gray-800">{{$totalRestant}}</h3>
                        </div>
                        <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total en CDF</p>
                            <h3 class="text-xl font-bold text-gray-800">{{ number_format($totalCDF, 0, ',', ' ') }} FC</h3>
                        </div>
                        <div class="h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-money-bill text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">Total en Dollars</p>
                            <h3 class="text-xl font-bold text-gray-800">${{ number_format($totalUSD, 0, ',', ' ') }}</h3>
                        </div>
                        <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and controls -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search input -->
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Rechercher par nom..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>

                <!-- Items per page selector -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Afficher :</span>
                    <select id="itemsPerPage" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main content card -->
        <div class="bg-white w-full p-4 md:p-6 rounded-xl shadow-sm border border-gray-100">

            <!-- Desktop table -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
                <table class="min-w-full text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium">Client</th>
                            <th class="px-6 py-4 text-left font-medium">Type</th>
                            <th class="px-6 py-4 text-left font-medium">Prix unitaire</th>
                            <th class="px-6 py-4 text-center font-medium">Quantité acheté</th>
                            <th class="px-6 py-4 text-center font-medium">Quantité restant</th>
                            <th class="px-6 py-4 text-center font-medium">Total</th>
                            <th class="px-6 py-4 text-center font-medium">Statut</th>
                            <th class="px-6 py-4 text-center font-medium">Date</th>
                            <th class="px-6 py-4 text-center font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100" id="tableBody">
                        @forelse($detailleParBillet as $billet)
                        <tr class="hover:bg-gray-50 transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}">
                            <td class="px-6 py-4">{{ $billet["auteur"] ?? "N/A" }}</td>
                            <td class="px-6 py-4">{{ $billet["type"] }}</td>
                            <td class="px-6 py-4">{{ $billet["prix_unitaire"] }} {{ $billet["devise"] }}</td>
                            <td class="px-6 py-4 text-center">{{ $billet["quantite"] }}</td>
                            <td class="px-6 py-4 text-center">{{ $billet["quantite_fictif"] }}</td>
                            <td class="px-6 py-4 text-center">{{ $billet["total"] }} {{ $billet['devise'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                    {{ $billet["statut"] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">{{ $billet["date"] }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button onclick="openModal('detailsModal{{ $billet['id'] }}')" 
                                            class="w-8 h-8 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                         @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-gray-500">
                                Aucun achat trouvé.
                            </td>
                        </tr>
                        @endforelse
                       
                    </tbody>
                </table>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden space-y-4" id="mobileCards">
                @forelse($detailleParBillet as $billet)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $billet["auteur"] ?? "N/A" }}</h3>
                            <p class="text-sm text-gray-600">{{ $billet["type"] }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                            {{ $billet["statut"] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">Prix unitaire</p>
                            <p class="font-medium">{{ $billet["prix_unitaire"] }} {{ $billet["devise"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Quantité acheté</p>
                            <p class="font-medium text-center">{{ $billet["quantite"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Quantité restant</p>
                            <p class="font-medium text-center">{{ $billet["quantite_fictif"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total</p>
                            <p class="font-medium">{{ $billet["total"] }} {{ $billet['devise'] }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">
                        <span class="text-sm text-gray-500">{{ $billet["date"] }}</span>
                        <div class="flex gap-2">
                            <button onclick="openModal('detailsModal{{ $billet['id'] }}')" 
                                    class="w-8 h-8 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-gray-500">
                                Aucun achat trouvé.
                            </td>
                        </tr>
                @endforelse
               
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    Affichage de <span id="startItem">1</span> à <span id="endItem">10</span> sur <span id="totalItems">{{ count($detailleParBillet) }}</span> résultats
                </div>
                
                <div class="flex items-center gap-2">
                    <button id="prevPage" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div id="paginationNumbers" class="flex gap-1">
                        <!-- Les numéros de page seront générés ici par JavaScript -->
                    </div>
                    
                    <button id="nextPage" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ============================
     MODALS POUR CHAQUE BILLET
============================ -->

@forelse($detailleParBillet as $billet)

<!-- Modal Réenvoyer -->
<div id="resendModal{{ $billet['id'] }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
    <div class="bg-white p-6 rounded-xl max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Réenvoyer le billet</h3>
        <p class="text-gray-600">Renvoyer à <b>{{ $billet['auteur'] }}</b> ?</p>
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeModal('resendModal{{ $billet['id'] }}')" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Annuler</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Confirmer</button>
        </div>
    </div>
</div>

<!-- Modal Détails -->
<div id="detailsModal{{ $billet['id'] }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
    <div class="bg-white p-6 rounded-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Détails du billet</h3>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="font-medium">Client :</span> <span>{{ $billet['auteur'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Type :</span> <span>{{ $billet['type'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Quantité achetée :</span> <span>{{ $billet['quantite'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Quantité restante :</span> <span>{{ $billet['quantite_fictif'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Total :</span> <span>{{ $billet['total'] }} {{ $billet['devise'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Prix unitaire :</span> <span>{{ $billet['prix_unitaire'] }} {{ $billet['devise'] }}</span></div>
            <div class="flex justify-between"><span class="font-medium">Statut :</span> <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">{{ $billet['statut'] }}</span></div>

            @if(!empty($billet["code"]))
            <div class="flex flex-col items-center mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-3">QR Code du billet</p>
                <div id="qrcode-{{ $billet['id'] }}" class="border p-2 rounded-md bg-white"></div>
                <button onclick="downloadQRCode('{{ $billet['id'] }}')" 
                        class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                    Télécharger QR Code
                </button>
            </div>
            @endif

            <div class="flex justify-between mt-4 pt-4 border-t border-gray-200">
                <span class="font-medium">Date :</span> <span>{{ $billet['date'] }}</span>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button onclick="closeModal('detailsModal{{ $billet['id'] }}')" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Fermer</button>
        </div>
    </div>
</div>

   @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-gray-500">
                                Aucun achat trouvé.
                            </td>
                        </tr>
                @endforelse

<!-- JS -->
<script>
// Variables globales pour la pagination
let currentPage = 1;
let itemsPerPage = 10;
let filteredData = [];
let allData = [];

function openModal(id){ 
    document.getElementById(id).classList.remove('hidden'); 
    document.body.style.overflow = 'hidden';
}

function closeModal(id){ 
    document.getElementById(id).classList.add('hidden'); 
    document.body.style.overflow = 'auto';
}

// Fermer les modals en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        e.target.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Stocker toutes les données initiales
    const tableRows = document.querySelectorAll('#tableBody tr');
    const mobileCards = document.querySelectorAll('#mobileCards > div');
    
    allData = Array.from(tableRows).map((row, index) => ({
        element: row,
        mobileElement: mobileCards[index],
        client: row.getAttribute('data-client')
    }));

    filteredData = [...allData];
    
    initializePagination();
    setupEventListeners();
    
    // Générer les QR codes
    // @foreach($detailleParBillet as $billet)
    //     @if(!empty($billet["code"]))
    //         new QRCode(document.getElementById("qrcode-{{ $billet['id'] }}"), {
    //             text: "{{ $billet['code'] }}",
    //             width: 120,
    //             height: 120
    //         });
    //     @endif
    // @endforeach
});

function setupEventListeners() {
    // Recherche
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        filterData(searchTerm);
    });

    // Items par page
    document.getElementById('itemsPerPage').addEventListener('change', function(e) {
        itemsPerPage = parseInt(e.target.value);
        currentPage = 1;
        updateDisplay();
    });

    // Boutons précédent/suivant
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            updateDisplay();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateDisplay();
        }
    });
}

function filterData(searchTerm) {
    if (searchTerm === '') {
        filteredData = [...allData];
    } else {
        filteredData = allData.filter(item => 
            item.client.includes(searchTerm)
        );
    }
    
    currentPage = 1;
    updateDisplay();
}

function initializePagination() {
    updateDisplay();
}

function updateDisplay() {
    const totalItems = filteredData.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    // Calculer les indices de début et fin
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    
    // Masquer tous les éléments
    allData.forEach(item => {
        item.element.style.display = 'none';
        if (item.mobileElement) {
            item.mobileElement.style.display = 'none';
        }
    });
    
    // Afficher seulement les éléments de la page courante
    for (let i = startIndex; i < endIndex; i++) {
        if (filteredData[i]) {
            filteredData[i].element.style.display = '';
            if (filteredData[i].mobileElement) {
                filteredData[i].mobileElement.style.display = '';
            }
        }
    }
    
    // Mettre à jour les informations de pagination
    document.getElementById('startItem').textContent = totalItems === 0 ? 0 : startIndex + 1;
    document.getElementById('endItem').textContent = endIndex;
    document.getElementById('totalItems').textContent = totalItems;
    
    // Mettre à jour les boutons de pagination
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
    
    // Générer les numéros de page
    generatePaginationNumbers(totalPages);
}

function generatePaginationNumbers(totalPages) {
    const paginationContainer = document.getElementById('paginationNumbers');
    paginationContainer.innerHTML = '';
    
    if (totalPages === 0) return;
    
    // Afficher maximum 5 pages autour de la page courante
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Ajuster si on est près du début
    if (currentPage <= 3) {
        endPage = Math.min(5, totalPages);
    }
    
    // Ajuster si on est près de la fin
    if (currentPage >= totalPages - 2) {
        startPage = Math.max(1, totalPages - 4);
    }
    
    // Bouton première page
    if (startPage > 1) {
        const firstPageBtn = createPageButton(1);
        paginationContainer.appendChild(firstPageBtn);
        
        if (startPage > 2) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-3 py-2 text-gray-500';
            ellipsis.textContent = '...';
            paginationContainer.appendChild(ellipsis);
        }
    }
    
    // Boutons des pages
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = createPageButton(i);
        paginationContainer.appendChild(pageBtn);
    }
    
    // Bouton dernière page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('span');
            ellipsis.className = 'px-3 py-2 text-gray-500';
            ellipsis.textContent = '...';
            paginationContainer.appendChild(ellipsis);
        }
        
        const lastPageBtn = createPageButton(totalPages);
        paginationContainer.appendChild(lastPageBtn);
    }
}

function createPageButton(pageNumber) {
    const button = document.createElement('button');
    button.className = `px-3 py-2 border rounded-lg transition ${
        pageNumber === currentPage 
            ? 'bg-blue-600 text-white border-blue-600' 
            : 'border-gray-300 hover:bg-gray-50 text-gray-700'
    }`;
    button.textContent = pageNumber;
    
    button.addEventListener('click', function() {
        currentPage = pageNumber;
        updateDisplay();
    });
    
    return button;
}

function downloadQRCode(id){
    const canvas = document.querySelector("#qrcode-" + id + " canvas");
    if(!canvas) return alert("QR Code introuvable");
    const link = document.createElement("a");
    link.download = "qrcode_billet_" + id + ".png";
    link.href = canvas.toDataURL("image/png");
    link.click();
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

@endsection