<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de Bord - TicketMaster</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          primary: '#4f46e5',
          secondary: '#7c3aed',
          success: '#10b981',
          warning: '#f59e0b',
          dark: '#1e293b',
        }
      }
    }
  }
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
  body { 
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  }
  .sidebar-link {
    @apply flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-600 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-600 hover:shadow-sm;
  }
  .sidebar-link.active { 
    @apply bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 font-semibold shadow-sm border-r-3 border-indigo-500;
  }
  .status-badge { 
    @apply px-3 py-1.5 rounded-full text-xs font-medium;
  }
  .dashboard-card {
    @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300;
  }
  .stat-card {
    @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 relative overflow-hidden;
  }
  .gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  .progress-bar {
    @apply w-full bg-gray-200 rounded-full h-2 overflow-hidden;
  }
  .progress-fill {
    @apply h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-1000 ease-out;
  }
  .chart-container {
    @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100;
  }
</style>
</head>

<body class="min-h-screen flex">

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-80 bg-white border-r border-gray-200 flex flex-col shadow-lg z-50">
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-4">
      <div class="h-14 w-14 gradient-bg rounded-2xl flex items-center justify-center text-white shadow-lg">
        <i class="fas fa-ticket-alt text-xl"></i>
      </div>
      <div>
        <span class="font-bold text-2xl text-gray-900">TicketMaster</span>
        <p class="text-sm text-gray-500 mt-1">Plateforme de gestion</p>
      </div>
    </div>
  </div>

  <nav class="flex-1 p-6 space-y-2">
    <a href="{{ route('dashboard') }}" class="sidebar-link active">
      <div class="h-10 w-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center">
        <i class="fas fa-chart-pie text-indigo-600 text-lg"></i>
      </div>
      <span class="flex-1 text-base font-medium">Tableau de bord</span>
      <div class="h-2 w-2 bg-indigo-500 rounded-full"></div>
    </a>
    <a href="{{ route('evenements.index') }}" class="sidebar-link">
      <div class="h-10 w-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
        <i class="fas fa-calendar-plus text-purple-600 text-lg"></i>
      </div>
      <span class="flex-1 text-base font-medium">Événements</span>
    </a>
    <a href="{{ route('billets.index') }}" class="sidebar-link">
      <div class="h-10 w-10 bg-gradient-to-br from-green-100 to-emerald-100 rounded-xl flex items-center justify-center">
        <i class="fas fa-ticket-alt text-green-600 text-lg"></i>
      </div>
      <span class="flex-1 text-base font-medium">Billets</span>
    </a>
    <a href="{{ route('achats.index') }}" class="sidebar-link">
      <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
        <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
      </div>
      <span class="flex-1 text-base font-medium">Achats</span>
    </a>
  </nav>

  <div class="p-6 border-t border-gray-100">
    <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl">
      <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold">
        <i class="fas fa-user text-lg"></i>
      </div>
      <div class="flex-1">
        <p class="font-semibold text-gray-800">Admin User</p>
        <p class="text-xs text-gray-500">Administrateur</p>
      </div>
      <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
        <i class="fas fa-cog text-lg"></i>
      </button>
    </div>
  </div>
</aside>

<!-- Contenu principal -->
<main class="flex-1 p-8 ml-80 bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen">
  <!-- En-tête -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-4xl font-bold text-gray-900 mb-2">Tableau de Bord</h1>
      <p class="text-gray-600 text-lg">Bienvenue, voici un aperçu de votre activité</p>
    </div>
    <div class="flex items-center gap-4">
      <div class="relative">
        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" placeholder="Rechercher..." 
               class="pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition-all">
      </div>
      <button class="p-3 bg-white border border-gray-200 rounded-2xl hover:shadow-md transition-all">
        <i class="fas fa-bell text-gray-600 text-lg"></i>
      </button>
    </div>
  </div>

  <!-- Statistiques principales -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="stat-card group">
      <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-full -mr-4 -mt-4"></div>
      <div class="flex items-center justify-between mb-4">
        <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white">
          <i class="fas fa-calendar-check text-lg"></i>
        </div>
        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
          <i class="fas fa-arrow-up mr-1"></i>12%
        </span>
      </div>
      <p class="text-sm text-gray-500 mb-2">Événements Actifs</p>
      <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $evenementsActifs }}</h3>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full" style="width: 75%"></div>
      </div>
    </div>

    <div class="stat-card group">
      <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-500/10 to-emerald-500/10 rounded-full -mr-4 -mt-4"></div>
      <div class="flex items-center justify-between mb-4">
        <div class="h-12 w-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center text-white">
          <i class="fas fa-ticket-alt text-lg"></i>
        </div>
        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
          <i class="fas fa-arrow-up mr-1"></i>8%
        </span>
      </div>
      <p class="text-sm text-gray-500 mb-2">Billets Vendus</p>
      <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $billetsVendus }}</h3>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-1.5 rounded-full" style="width: 65%"></div>
      </div>
    </div>

    <div class="stat-card group">
      <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-full -mr-4 -mt-4"></div>
      <div class="flex items-center justify-between mb-4">
        <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center text-white">
          <i class="fas fa-money-bill-wave text-lg"></i>
        </div>
        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
          <i class="fas fa-arrow-up mr-1"></i>23%
        </span>
      </div>
      <p class="text-sm text-gray-500 mb-2">Revenus Totaux</p>
      <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($revenusCDF, 0, ',', ' ') }} FC</h3>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full" style="width: 85%"></div>
      </div>
    </div>

    <div class="stat-card group">
      <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-500/10 to-amber-500/10 rounded-full -mr-4 -mt-4"></div>
      <div class="flex items-center justify-between mb-4">
        <div class="h-12 w-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center text-white">
          <i class="fas fa-chart-line text-lg"></i>
        </div>
        <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
          <i class="fas fa-arrow-up mr-1"></i>5%
        </span>
      </div>
      <p class="text-sm text-gray-500 mb-2">Taux de Remplissage</p>
      <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $tauxRemplissage }}%</h3>
      <div class="w-full bg-gray-200 rounded-full h-1.5">
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 h-1.5 rounded-full" style="width: {{ $tauxRemplissage }}%"></div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <!-- Événements populaires -->
    <div class="dashboard-card">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900">Événements Populaires</h3>
        <button class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2">
          Voir tout <i class="fas fa-arrow-right text-xs"></i>
        </button>
      </div>
      <div class="space-y-4">
        @forelse ($evenementsPopulaires as $index => $pop)
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-100 hover:border-indigo-200 transition-all group">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-sm">
              {{ $index + 1 }}
            </div>
            <div>
              <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                {{ $pop->evenement->nom ?? 'Inconnu' }}
              </p>
              <p class="text-xs text-gray-500">{{ $pop->total }} billets vendus</p>
            </div>
          </div>
          <div class="text-right">
            <span class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-xs px-3 py-1.5 rounded-full font-medium">
              {{ round(($pop->total / $billetsVendus) * 100, 1) }}%
            </span>
          </div>
        </div>
        @empty
        <div class="text-center py-8">
          <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
          <p class="text-gray-500">Aucun événement enregistré</p>
        </div>
        @endforelse
      </div>
    </div>

    <!-- Graphique simple -->
    <div class="dashboard-card">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900">Performance des Ventes</h3>
        <select class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
          <option>7 derniers jours</option>
          <option>30 derniers jours</option>
          <option>3 derniers mois</option>
        </select>
      </div>
      <div class="h-64 flex items-end justify-between gap-2 pt-8">
        <!-- Barres du graphique simplifié -->
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-500" style="height: 60%"></div>
          <span class="text-xs text-gray-500 mt-2">Lun</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-500" style="height: 80%"></div>
          <span class="text-xs text-gray-500 mt-2">Mar</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-500" style="height: 45%"></div>
          <span class="text-xs text-gray-500 mt-2">Mer</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-500" style="height: 90%"></div>
          <span class="text-xs text-gray-500 mt-2">Jeu</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-indigo-500 to-purple-400 rounded-t-lg transition-all hover:from-indigo-600 hover:to-purple-500" style="height: 75%"></div>
          <span class="text-xs text-gray-500 mt-2">Ven</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-green-500 to-emerald-400 rounded-t-lg transition-all hover:from-green-600 hover:to-emerald-500" style="height: 95%"></div>
          <span class="text-xs text-gray-500 mt-2">Sam</span>
        </div>
        <div class="flex-1 flex flex-col items-center">
          <div class="w-full bg-gradient-to-t from-green-500 to-emerald-400 rounded-t-lg transition-all hover:from-green-600 hover:to-emerald-500" style="height: 70%"></div>
          <span class="text-xs text-gray-500 mt-2">Dim</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Derniers achats -->
  <div class="dashboard-card">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-bold text-gray-900">Derniers Achats</h3>
      <button class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2">
        Voir tout <i class="fas fa-arrow-right text-xs"></i>
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="pb-4 text-left font-semibold text-gray-600">Client</th>
            <th class="pb-4 text-left font-semibold text-gray-600">Événement</th>
            <th class="pb-4 text-center font-semibold text-gray-600">Quantité</th>
            <th class="pb-4 text-center font-semibold text-gray-600">Montant</th>
            <th class="pb-4 text-center font-semibold text-gray-600">Statut</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($derniersAchats as $achat)
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-4">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center">
                  <i class="fas fa-user text-indigo-600 text-sm"></i>
                </div>
                <div>
                  <div class="font-semibold text-gray-900">{{ $achat->billet->nom_auteur ?? 'Inconnu' }}</div>
                  <div class="text-xs text-gray-500">{{ $achat->billet->email ?? '-' }}</div>
                </div>
              </div>
            </td>
            <td class="py-4">
              <div class="font-medium text-gray-800">{{ $achat->evenement->nom ?? 'Événement inconnu' }}</div>
              <div class="text-xs text-gray-500">{{ $achat->created_at->format('d/m/Y H:i') }}</div>
            </td>
            <td class="py-4 text-center">
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                {{ $achat->quantite }} billet(s)
              </span>
            </td>
            <td class="py-4 text-center font-bold text-gray-900">
              {{ number_format($achat->prix_unitaire * $achat->quantite, 0, ',', ' ') }} {{ $achat->devise }}
            </td>
            <td class="py-4 text-center">
              <span class="status-badge {{ $achat->statut === 'payé' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                <i class="fas {{ $achat->statut === 'payé' ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                {{ ucfirst($achat->statut) }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-8 text-center">
              <i class="fas fa-shopping-cart text-3xl text-gray-300 mb-3"></i>
              <p class="text-gray-500">Aucun achat récent</p>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</main>
</body>
</html>