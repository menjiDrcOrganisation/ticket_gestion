<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de Bord - TicketMaster</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body { 
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
}
.sidebar-link {
  @apply flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 hover:shadow-sm;
}
.sidebar-link.active { 
  @apply bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-600 font-semibold shadow-sm border-l-4 border-blue-500; 
}
.status-badge { 
  @apply px-3 py-1 rounded-full text-xs font-medium;
}
.card-hover { 
  @apply transition-all duration-300 hover:shadow-lg hover:-translate-y-1;
}
.action-btn { 
  @apply px-3 py-1.5 rounded-md transition-all duration-200 font-medium;
}
.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.stats-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}
.sidebar {
  background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
  box-shadow: 0 0 40px rgba(120, 116, 255, 0.08);
}
.user-profile {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
.sidebar-icon {
  @apply flex items-center justify-center min-w-[2.25rem];
}
.dashboard-card {
  @apply bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300;
}
</style>
</head>
<body class="min-h-screen flex">

<!-- Header mobile -->
<header class="md:hidden flex items-center justify-between bg-white shadow-sm px-4 py-3 fixed top-0 left-0 right-0 z-30">
  <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors"><i class="fas fa-bars"></i></button>
  <h1 class="font-semibold text-lg text-gray-800">Tableau de Bord</h1>
  <div class="h-8 w-8 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-sm">
    TM
  </div>
</header>

<!-- Overlay mobile -->
<div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40 md:hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar améliorée avec alignement corrigé -->
<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 w-72 border-r transform -translate-x-full md:translate-x-0 md:static z-50 transition-transform duration-300 flex flex-col">
  <!-- En-tête avec logo -->
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-3">
      <div class="h-12 w-12 gradient-bg rounded-xl flex items-center justify-center text-white font-bold shadow-md">
        <i class="fas fa-ticket-alt text-lg"></i>
      </div>
      <div>
        <span class="font-bold text-xl text-gray-800">TicketMaster</span>
        <p class="text-xs text-gray-500 mt-1">Gestion des billetssss</p>
      </div>
    </div>
  </div>
  
  <!-- Navigation principale -->
  <nav class="flex-1 p-4 space-y-1">
    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Navigation principale</div>
    
    <a href="{{ route('dashboard') }}" class="sidebar-link active">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-blue-50">
        <i class="fas fa-chart-pie text-blue-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Tableau de bord</span>
    </a>
    
    <a href="{{ route('evenements.index') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-purple-50">
        <i class="fas fa-calendar-plus text-purple-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Événements</span>
    </a>
    
    <a href="{{ route('billets.index') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-green-50">
        <i class="fas fa-ticket-alt text-green-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Billets</span>
    </a>
    
    <a href="{{ route('achats.index') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-indigo-50">
        <i class="fas fa-shopping-cart text-indigo-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Achats</span>
      <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full min-w-[1.5rem] text-center">12</span>
    </a>
    
    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Outils</div>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-orange-50">
        <i class="fas fa-chart-bar text-orange-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Rapports</span>
    </a>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-pink-50">
        <i class="fas fa-cog text-pink-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Paramètres</span>
    </a>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-teal-50">
        <i class="fas fa-question-circle text-teal-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Aide & Support</span>
    </a>
  </nav>
  
  <!-- Section utilisateur -->
  <div class="user-profile border-t border-gray-100 p-4">
    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-white transition-colors cursor-pointer">
      <div class="h-10 w-10 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
        TM
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 truncate text-sm">Thomas Martin</p>
        <p class="text-xs text-gray-500 truncate">Administrateur</p>
      </div>
      <button class="p-1.5 rounded-lg hover:bg-white transition-colors text-gray-500">
        <i class="fas fa-chevron-down text-xs"></i>
      </button>
    </div>
    
    <button class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600 w-full text-left mt-2" onclick="alert('Déconnexion');">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-red-50">
        <i class="fas fa-sign-out-alt text-red-500 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Déconnexion</span>
    </button>
  </div>
</aside>

<!-- Main content - Tableau de bord -->
<main class="flex-1 md:ml-0 min-h-screen overflow-auto">
  <div class="p-4 md:p-6 mt-14 md:mt-0">
    <div class="max-w-7xl mx-auto">
      <!-- Header avec actions rapides -->
      <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">Tableau de Bord</h1>
            <p class="text-gray-500 mt-2">Bienvenue Thomas, voici un aperçu de votre activité</p>
          </div>
          <div class="flex gap-3 mt-4 md:mt-0">
            <button class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg">
              <i class="fas fa-plus"></i> Nouvel Événement
            </button>
            <button class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors flex items-center gap-2">
              <i class="fas fa-download"></i> Exporter
            </button>
          </div>
        </div>

        <!-- Stats principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="dashboard-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Événements Actifs</p>
                <h3 class="text-2xl font-bold text-gray-800">24</h3>
                <p class="text-xs text-green-600 mt-1 flex items-center">
                  <i class="fas fa-arrow-up mr-1"></i> +12% ce mois
                </p>
              </div>
              <div class="h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-check text-blue-600 text-lg"></i>
              </div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Billets Vendus</p>
                <h3 class="text-2xl font-bold text-gray-800">1,248</h3>
                <p class="text-xs text-green-600 mt-1 flex items-center">
                  <i class="fas fa-arrow-up mr-1"></i> +8% cette semaine
                </p>
              </div>
              <div class="h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-ticket-alt text-green-600 text-lg"></i>
              </div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Revenus Totaux</p>
                <h3 class="text-2xl font-bold text-gray-800">248,750 FC</h3>
                <p class="text-xs text-green-600 mt-1 flex items-center">
                  <i class="fas fa-arrow-up mr-1"></i> +15% ce mois
                </p>
              </div>
              <div class="h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-purple-600 text-lg"></i>
              </div>
            </div>
          </div>

          <div class="dashboard-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500 mb-1">Taux de Remplissage</p>
                <h3 class="text-2xl font-bold text-gray-800">78%</h3>
                <p class="text-xs text-green-600 mt-1 flex items-center">
                  <i class="fas fa-arrow-up mr-1"></i> +5% cette semaine
                </p>
              </div>
              <div class="h-12 w-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-line text-orange-600 text-lg"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Graphiques et données -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Graphique des ventes -->
        <div class="dashboard-card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Ventes par Mois</h3>
            <select class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option>12 derniers mois</option>
              <option>6 derniers mois</option>
              <option>3 derniers mois</option>
            </select>
          </div>
          <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
            <div class="text-center text-gray-500">
              <i class="fas fa-chart-bar text-4xl mb-3 text-gray-300"></i>
              <p>Graphique des ventes</p>
              <p class="text-sm">Données visuelles des performances</p>
            </div>
          </div>
        </div>

        <!-- Événements populaires -->
        <div class="dashboard-card">
          <h3 class="text-lg font-semibold text-gray-800 mb-6">Événements Populaires</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-music text-blue-600"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-800">Concert Jazz</p>
                  <p class="text-xs text-gray-500">128 billets vendus</p>
                </div>
              </div>
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">95%</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-theater-masks text-purple-600"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-800">Théâtre Classique</p>
                  <p class="text-xs text-gray-500">96 billets vendus</p>
                </div>
              </div>
              <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">88%</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-microphone text-orange-600"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-800">Conférence Tech</p>
                  <p class="text-xs text-gray-500">84 billets vendus</p>
                </div>
              </div>
              <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">75%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Dernières activités et statistiques -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Derniers achats -->
        <div class="dashboard-card lg:col-span-2">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Derniers Achats</h3>
            <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">Voir tout</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="pb-3 text-left font-medium text-gray-600">Client</th>
                  <th class="pb-3 text-left font-medium text-gray-600">Événement</th>
                  <th class="pb-3 text-center font-medium text-gray-600">Montant</th>
                  <th class="pb-3 text-center font-medium text-gray-600">Statut</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr>
                  <td class="py-3">
                    <div class="font-medium text-gray-800">Marie Dubois</div>
                    <div class="text-xs text-gray-500">marie.dubois@email.com</div>
                  </td>
                  <td class="py-3">Concert Jazz</td>
                  <td class="py-3 text-center font-medium">25,000 FC</td>
                  <td class="py-3 text-center">
                    <span class="status-badge bg-green-100 text-green-800">Payé</span>
                  </td>
                </tr>
                <tr>
                  <td class="py-3">
                    <div class="font-medium text-gray-800">Jean Martin</div>
                    <div class="text-xs text-gray-500">jean.martin@email.com</div>
                  </td>
                  <td class="py-3">Théâtre Classique</td>
                  <td class="py-3 text-center font-medium">18,000 FC</td>
                  <td class="py-3 text-center">
                    <span class="status-badge bg-yellow-100 text-yellow-800">En attente</span>
                  </td>
                </tr>
                <tr>
                  <td class="py-3">
                    <div class="font-medium text-gray-800">Sophie Lambert</div>
                    <div class="text-xs text-gray-500">sophie.lambert@email.com</div>
                  </td>
                  <td class="py-3">Conférence Tech</td>
                  <td class="py-3 text-center font-medium">15,000 FC</td>
                  <td class="py-3 text-center">
                    <span class="status-badge bg-green-100 text-green-800">Payé</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="dashboard-card">
          <h3 class="text-lg font-semibold text-gray-800 mb-6">Statistiques Rapides</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="fas fa-users text-blue-600"></i>
                <span class="text-sm font-medium text-gray-700">Nouveaux clients</span>
              </div>
              <span class="font-bold text-blue-600">42</span>
            </div>

            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="fas fa-ticket-alt text-green-600"></i>
                <span class="text-sm font-medium text-gray-700">Billets en attente</span>
              </div>
              <span class="font-bold text-green-600">18</span>
            </div>

            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="fas fa-star text-purple-600"></i>
                <span class="text-sm font-medium text-gray-700">Événements à venir</span>
              </div>
              <span class="font-bold text-purple-600">7</span>
            </div>

            <div class="flex items-center justify-between p-4 bg-orange-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-orange-600"></i>
                <span class="text-sm font-medium text-gray-700">Problèmes signalés</span>
              </div>
              <span class="font-bold text-orange-600">3</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
function toggleSidebar(){
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  sidebar.classList.toggle('-translate-x-full');
  overlay.classList.toggle('hidden');
}
</script>

</body>
</html>