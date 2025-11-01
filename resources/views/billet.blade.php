<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Achats de Billets</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3b82f6',
            secondary: '#10b981',
            dark: '#1f2937',
          }
        }
      }
    }
  </script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; }
    
    .sidebar-link {
      @apply flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 text-gray-700 hover:bg-blue-50 hover:text-primary;
    }
    
    .sidebar-link.active {
      @apply bg-blue-100 text-primary font-medium;
    }
    
    .action-btn {
      @apply px-3 py-1.5 rounded-md transition-all duration-200 font-medium;
    }
    
    .status-badge {
      @apply px-3 py-1 rounded-full text-xs font-medium;
    }
    
    .card-hover {
      @apply transition-all duration-300 hover:shadow-md hover:-translate-y-1;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

  <!-- Barre du haut (mobile) -->
  <header class="flex items-center justify-between bg-white shadow-sm px-4 py-3 md:hidden sticky top-0 z-30">
    <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
      <i class="fas fa-bars text-gray-700"></i>
    </button>
    <h1 class="font-semibold text-lg text-dark">Achats de Billets</h1>
    <div class="h-8 w-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center text-white font-bold text-sm">
      TM
    </div>
  </header>

  <!-- Overlay mobile -->
  <div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40 md:hidden" onclick="toggleSidebar()"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-sm transform -translate-x-full md:translate-x-0 md:relative md:block z-50 transition-transform duration-300 ease-in-out">
    <div class="flex items-center justify-center py-6 border-b">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center text-white font-bold">
          TM
        </div>
        <span class="font-bold text-lg text-dark">TicketMaster</span>
      </div>
    </div>

    <nav class="p-4 space-y-1">
      <a href="{{ route('dashboard') }}" class="sidebar-link">
        <i class="fas fa-chart-pie w-5 text-center"></i> Tableau de bord
      </a>
      <a href="{{ route('evenements.index') }}" class="sidebar-link">
        <i class="fas fa-calendar-plus w-5 text-center"></i> Événements
      </a>
      <a href="{{ route('dashboard') }}" class="sidebar-link">
        <i class="fas fa-ticket-alt w-5 text-center"></i> Billets
      </a>
      <a href="{{ route('dashboard') }}" class="sidebar-link active">
        <i class="fas fa-shopping-cart w-5 text-center"></i> Achats
      </a>
      <button class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600 w-full text-left mt-6"
              onclick="openModal('logoutModal')">
        <i class="fas fa-sign-out-alt w-5 text-center"></i> Déconnexion
      </button>
    </nav>
  </aside>

  <!-- Contenu principal -->
  <main class="flex-1 p-4 md:p-6 md:ml-0 lg:ml-64 transition-all duration-300">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white w-full p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-dark">Achats de Billets</h2>
            <p class="text-gray-500 mt-1">Gérez tous les achats de billets de vos événements</p>
          </div>
          <button onclick="openModal('addModal')" class="mt-4 md:mt-0 bg-gradient-to-r from-primary to-blue-500 hover:from-blue-600 hover:to-blue-500 text-white px-5 py-2.5 rounded-lg shadow-sm transition-all duration-200 flex items-center gap-2 w-full md:w-auto justify-center">
            <i class="fas fa-plus"></i> Nouvel achat
          </button>
        </div>

        <!-- Recherche et filtres -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text" placeholder="Rechercher un client, un événement..."
                   class="border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
          </div>
          <div class="flex gap-2">
            <select class="border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
              <option>Tous les statuts</option>
              <option>Payé</option>
              <option>En attente</option>
              <option>Annulé</option>
            </select>
            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg transition-colors flex items-center gap-2">
              <i class="fas fa-filter"></i> Filtres
            </button>
          </div>
        </div>

        <!-- Liste mobile (cartes) -->
        <div class="md:hidden space-y-4">
          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-dark">Jean Mulumba</h3>
                <p class="text-sm text-gray-600 mt-1">jean.mulumba@example.com</p>
              </div>
              <span class="status-badge bg-green-100 text-green-800">Payé</span>
            </div>
            <div class="mt-3">
              <p class="text-sm text-gray-700"><i class="fas fa-music text-gray-400 mr-2"></i>Concert Gospel</p>
              <p class="text-sm text-gray-700 mt-1"><i class="fas fa-tag text-gray-400 mr-2"></i>VIP (2 billets)</p>
              <p class="font-medium text-gray-900 mt-2"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>100 000 FC</p>
            </div>
            <div class="flex justify-between mt-4 pt-3 border-t border-gray-100">
              <span class="text-xs text-gray-500">12 Nov 2023</span>
              <div class="flex gap-2">
                <button onclick="openModal('resendModal1')" class="text-blue-600 hover:text-blue-800 transition-colors">
                  <i class="fas fa-paper-plane"></i>
                </button>
                <button onclick="openModal('detailsModal1')" class="text-gray-600 hover:text-dark transition-colors">
                  <i class="fas fa-eye"></i>
                </button>
                <button onclick="openModal('deleteModal1')" class="text-red-500 hover:text-red-700 transition-colors">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-dark">Marie Kabasele</h3>
                <p class="text-sm text-gray-600 mt-1">marie.k@example.com</p>
              </div>
              <span class="status-badge bg-yellow-100 text-yellow-800">En attente</span>
            </div>
            <div class="mt-3">
              <p class="text-sm text-gray-700"><i class="fas fa-theater-masks text-gray-400 mr-2"></i>Pièce de Théâtre</p>
              <p class="text-sm text-gray-700 mt-1"><i class="fas fa-tag text-gray-400 mr-2"></i>Standard (1 billet)</p>
              <p class="font-medium text-gray-900 mt-2"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>25 000 FC</p>
            </div>
            <div class="flex justify-between mt-4 pt-3 border-t border-gray-100">
              <span class="text-xs text-gray-500">10 Nov 2023</span>
              <div class="flex gap-2">
                <button class="text-blue-600 hover:text-blue-800 transition-colors">
                  <i class="fas fa-paper-plane"></i>
                </button>
                <button class="text-gray-600 hover:text-dark transition-colors">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="text-red-500 hover:text-red-700 transition-colors">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tableau pour écrans moyens et grands -->
        <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
          <table class="min-w-full text-gray-700">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-4 text-left font-medium text-dark">Client</th>
                <th class="px-6 py-4 text-left font-medium text-dark">Événement</th>
                <th class="px-6 py-4 text-left font-medium text-dark">Type</th>
                <th class="px-6 py-4 text-center font-medium text-dark">Quantité</th>
                <th class="px-6 py-4 text-center font-medium text-dark">Total</th>
                <th class="px-6 py-4 text-center font-medium text-dark">Statut</th>
                <th class="px-6 py-4 text-center font-medium text-dark">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-dark">Jean Mulumba</div>
                    <div class="text-sm text-gray-500">jean.mulumba@example.com</div>
                  </div>
                </td>
                <td class="px-6 py-4">Concert Gospel</td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">VIP</span>
                </td>
                <td class="px-6 py-4 text-center">2</td>
                <td class="px-6 py-4 text-center font-medium">100 000 FC</td>
                <td class="px-6 py-4 text-center">
                  <span class="status-badge bg-green-100 text-green-800">Payé</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex justify-center gap-2">
                    <button onclick="openModal('resendModal1')" class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200" title="Réenvoyer">
                      <i class="fas fa-paper-plane"></i>
                    </button>
                    <button onclick="openModal('detailsModal1')" class="action-btn bg-gray-100 text-gray-700 hover:bg-gray-200" title="Voir détails">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="openModal('deleteModal1')" class="action-btn bg-red-100 text-red-700 hover:bg-red-200" title="Supprimer">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-dark">Marie Kabasele</div>
                    <div class="text-sm text-gray-500">marie.k@example.com</div>
                  </div>
                </td>
                <td class="px-6 py-4">Pièce de Théâtre</td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">Standard</span>
                </td>
                <td class="px-6 py-4 text-center">1</td>
                <td class="px-6 py-4 text-center font-medium">25 000 FC</td>
                <td class="px-6 py-4 text-center">
                  <span class="status-badge bg-yellow-100 text-yellow-800">En attente</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex justify-center gap-2">
                    <button class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200" title="Réenvoyer">
                      <i class="fas fa-paper-plane"></i>
                    </button>
                    <button class="action-btn bg-gray-100 text-gray-700 hover:bg-gray-200" title="Voir détails">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn bg-red-100 text-red-700 hover:bg-red-200" title="Supprimer">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6">
          <p class="text-gray-600 text-sm">Affichage de 1 à 2 sur 12 résultats</p>
          <div class="flex gap-1">
            <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1.5 rounded-lg bg-primary text-white">1</button>
            <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">2</button>
            <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">3</button>
            <button class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Réenvoi -->
  <div id="resendModal1" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
    <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
      <div class="flex items-center gap-3 mb-4">
        <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
          <i class="fas fa-paper-plane text-blue-600"></i>
        </div>
        <h3 class="text-lg font-semibold text-dark">Réenvoyer le billet</h3>
      </div>
      <p class="text-gray-600 mb-6">Renvoyer le billet pour le <span class="font-medium">Concert Gospel</span> à <span class="font-medium">Jean Mulumba</span> ?</p>
      <div class="flex justify-end gap-3">
        <button onclick="closeModal('resendModal1')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">Annuler</button>
        <button class="px-4 py-2.5 bg-gradient-to-r from-primary to-blue-500 hover:from-blue-600 hover:to-blue-500 text-white rounded-lg transition-all duration-200 flex items-center gap-2">
          <i class="fas fa-paper-plane"></i> Confirmer
        </button>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script>
    function toggleSidebar(){
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    }
    function openModal(id){
      document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id){
      document.getElementById(id).classList.add('hidden');
    }
    
    // Fermer les modals en cliquant à l'extérieur
    document.addEventListener('click', function(event) {
      const modals = document.querySelectorAll('[id$="Modal"]');
      modals.forEach(modal => {
        if (event.target === modal) {
          modal.classList.add('hidden');
        }
      });
    });
  </script>

</body>
</html>