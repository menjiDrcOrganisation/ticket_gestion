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
                            <h3 class="text-xl font-bold text-gray-800">{{ $totalAchat }}</h3>
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
                            <h3 class="text-xl font-bold text-gray-800">{{ $totalRestant }}</h3>
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
                <!-- Conteneur interne avec largeur minimale pour forcer le défilement -->
                <div class="min-w-[800px]">
                    <table class="min-w-full text-gray-700">
                        <thead class="bg-gray-50 border-b border-gray-100">
                             <tr>
                                <th class="px-6 py-4 text-left font-medium">#</th>
                                <th class="px-6 py-4 text-left font-medium">Client</th>
                                <th class="px-6 py-4 text-left font-medium">Type</th>
                                <th class="px-6 py-4 text-left font-medium">Prix unitaire</th>
                                <th class="px-6 py-4 text-center font-medium">Quantité acheté</th>
                                <th class="px-6 py-4 text-center font-medium">Quantité restant</th>
                                <th class="px-6 py-4 text-center font-medium">Total</th>
                                <th class="px-6 py-4 text-center font-medium">Statut</th>
                                <th class="px-6 py-4 text-center font-medium">Date d'achat</th>
                                <th class="px-6 py-4 text-center font-medium">Actions</th>
                             </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100" id="tableBody">
                            @forelse($detailleParBillet as $billet)
                            <tr class="hover:bg-gray-50 transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}" data-item-id="{{ $loop->index }}">
                                <td class="px-6 py-4">{{ $loop->iteration }}</td>
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

                                        <button
                                            class="w-8 h-8 bg-red-100 text-red-700 rounded-full flex items-center justify-center hover:bg-red-200 transition"
                                            data-delete-id="{{ $billet['id'] }}">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="py-6 text-center text-gray-500">
                                    Aucun achat trouvé.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden space-y-4" id="mobileCards">
                @forelse($detailleParBillet as $billet)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}" data-item-id="{{ $loop->index }}">
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
                            <button
                                class="w-8 h-8 bg-red-100 text-red-700 rounded-full flex items-center justify-center hover:bg-red-200 transition"
                                data-delete-id="{{ $billet['id'] }}">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    Aucun achat trouvé.
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    Affichage de <span id="startItem">0</span> à <span id="endItem">0</span> sur <span id="totalItems">0</span> résultats
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
               <a href="{{ env('ENV_POINT_URL') }}/storage/{{ $billet['billetImage'] }}"
   target="_blank"
   class="mt-3 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
    Télécharger QR Code
</a>
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
<!-- Aucun billet : pas de modale à afficher -->
@endforelse

<!-- JS -->
<script>
// Variables globales pour la pagination
let currentPage = 1;
let itemsPerPage = 10; // Changé de 20 à 10 pour correspondre à l'option par défaut
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

function filterData(searchTerm) {
    if (searchTerm === '') {
        filteredData = [...allData];
    } else {
        filteredData = allData.filter(item => 
            item.client && item.client.toLowerCase().includes(searchTerm)
        );
    }

    currentPage = 1;
    updateDisplay();
}

function updateDisplay() {
    const totalItems = filteredData.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    
    // Ajuster la page courante si elle dépasse le nombre total de pages
    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
    }
    if (currentPage < 1) {
        currentPage = 1;
    }

    // Calculer les indices de début et fin
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

    // D'abord, masquer TOUS les éléments
    const allTableRows = document.querySelectorAll('#tableBody tr');
    const allMobileCards = document.querySelectorAll('#mobileCards > div');
    
    allTableRows.forEach(row => {
        // Ne pas masquer la ligne "aucun résultat"
        if (!row.querySelector('td[colspan]')) {
            row.style.display = 'none';
        }
    });
    
    allMobileCards.forEach(card => {
        if (!card.classList.contains('text-center')) {
            card.style.display = 'none';
        }
    });

    // Afficher uniquement les éléments de la page courante
    for (let i = startIndex; i < endIndex; i++) {
        if (filteredData[i]) {
            if (filteredData[i].element) {
                filteredData[i].element.style.display = '';
            }
            if (filteredData[i].mobileElement) {
                filteredData[i].mobileElement.style.display = '';
            }
        }
    }

    // Mettre à jour les informations de pagination
    const startItemSpan = document.getElementById('startItem');
    const endItemSpan = document.getElementById('endItem');
    const totalItemsSpan = document.getElementById('totalItems');
    
    if(startItemSpan) startItemSpan.textContent = totalItems === 0 ? 0 : startIndex + 1;
    if(endItemSpan) endItemSpan.textContent = endIndex;
    if(totalItemsSpan) totalItemsSpan.textContent = totalItems;

    // Mettre à jour les boutons de pagination
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    
    if(prevBtn) prevBtn.disabled = currentPage === 1 || totalPages === 0;
    if(nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;

    // Générer les numéros de page
    generatePaginationNumbers(totalPages);
}

function generatePaginationNumbers(totalPages) {
    const paginationContainer = document.getElementById('paginationNumbers');
    if(!paginationContainer) return;
    
    paginationContainer.innerHTML = '';

    if (totalPages === 0) {
        return;
    }

    // Afficher maximum 10 pages
    let startPage = 1;
    let endPage = Math.min(10, totalPages);

    // Si on est au-delà de la page 10, ajuster l'affichage
    if (currentPage > 10) {
        startPage = currentPage - 5;
        endPage = Math.min(currentPage + 4, totalPages);
        
        // Ajouter le bouton "Première page"
        const firstPageBtn = createPageButton(1);
        paginationContainer.appendChild(firstPageBtn);
        
        const ellipsis1 = document.createElement('span');
        ellipsis1.className = 'px-3 py-2 text-gray-500';
        ellipsis1.textContent = '...';
        paginationContainer.appendChild(ellipsis1);
    }

    // Générer les pages
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = createPageButton(i);
        paginationContainer.appendChild(pageBtn);
    }

    // Ajouter des points de suspension et le bouton dernière page si nécessaire
    if (endPage < totalPages) {
        const ellipsis2 = document.createElement('span');
        ellipsis2.className = 'px-3 py-2 text-gray-500';
        ellipsis2.textContent = '...';
        paginationContainer.appendChild(ellipsis2);
        
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

function setupEventListeners() {
    // Recherche
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            filterData(searchTerm);
        });
    }

    // Items par page
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    if(itemsPerPageSelect) {
        itemsPerPageSelect.addEventListener('change', function(e) {
            itemsPerPage = parseInt(e.target.value);
            currentPage = 1;
            updateDisplay();
        });
    }

    // Boutons précédent/suivant
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    
    if(prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updateDisplay();
            }
        });
    }

    if(nextBtn) {
        nextBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                updateDisplay();
            }
        });
    }
}

function downloadQRCode(id){
    const container = document.querySelector("#qrcode-" + id);
    if(!container) return alert("QR Code introuvable");
    
    const canvas = container.querySelector("canvas");
    if(!canvas) return alert("QR Code introuvable");
    
    const link = document.createElement("a");
    link.download = "qrcode_billet_" + id + ".png";
    link.href = canvas.toDataURL("image/png");
    link.click();
}

// Initialisation principale
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer tous les éléments
    const tableRows = document.querySelectorAll('#tableBody tr');
    const mobileCards = document.querySelectorAll('#mobileCards > div');
    
    // Filtrer les éléments vides (messages "aucun achat")
    const validTableRows = Array.from(tableRows).filter(row => {
        return !row.querySelector('td[colspan]') && row.getAttribute('data-client') !== null;
    });
    
    const validMobileCards = Array.from(mobileCards).filter(card => {
        return !card.classList.contains('text-center') && card.getAttribute('data-client') !== null;
    });
    
    // Créer le tableau allData
    allData = [];
    
    // Utiliser la plus grande longueur
    const maxLength = Math.max(validTableRows.length, validMobileCards.length);
    
    for (let i = 0; i < maxLength; i++) {
        const tableRow = validTableRows[i] || null;
        const mobileCard = validMobileCards[i] || null;
        const clientName = tableRow ? tableRow.getAttribute('data-client') : 
                          (mobileCard ? mobileCard.getAttribute('data-client') : '');
        
        if (tableRow || mobileCard) {
            allData.push({
                element: tableRow,
                mobileElement: mobileCard,
                client: clientName || ''
            });
        }
    }

    filteredData = [...allData];

    // Initialiser la pagination
    setupEventListeners();

    // Générer les QR codes
    @foreach($detailleParBillet as $billet)
        @if(!empty($billet["code"]))
            if(document.getElementById("qrcode-{{ $billet['id'] }}")) {
                new QRCode(document.getElementById("qrcode-{{ $billet['id'] }}"), {
                    text: "{{ $billet['code'] }}",
                    width: 120,
                    height: 120
                });
            }
        @endif
    @endforeach
    
    // Mettre à jour l'affichage initial
    updateDisplay();
});

// Gestion de la suppression
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('button[data-delete-id]');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const billetId = this.getAttribute('data-delete-id');

            if (confirm('Êtes-vous sûr de vouloir supprimer ce billet ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/billet/${billetId}`;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

@endsection