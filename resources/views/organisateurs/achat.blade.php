<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Achats de Billets</title>
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
</style>
</head>
<body class="min-h-screen flex">

<!-- Header mobile -->
<header class="md:hidden flex items-center justify-between bg-white shadow-sm px-4 py-3 fixed top-0 left-0 right-0 z-30">
  <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors"><i class="fas fa-bars"></i></button>
  <h1 class="font-semibold text-lg text-gray-800">Achats de Billets</h1>
  <div class="h-8 w-8 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-sm">
    TM
  </div>
</header>

<!-- Overlay mobile -->
<div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40 md:hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar améliorée avec alignement corrigé -->
@include('layouts.asideorg')

<!-- Main content -->
<main class="flex-1 md:ml-0 min-h-screen overflow-auto">
  <div class="p-4 md:p-6 mt-14 md:mt-0">
    <div class="max-w-7xl mx-auto">
      <!-- Header with stats -->
      <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Achats de Billets</h2>
            <p class="text-gray-500 mt-1">Tous les achats de billets pour vos événements</p>
          </div>
          <div class="mt-4 md:mt-0">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg">
              <i class="fas fa-plus"></i> Nouvel achat
            </button>
          </div>
        </div>
        
        <!-- Stats cards corrigées -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Total des achats</p>
                <h3 class="text-xl font-bold text-gray-800">{{ count($achats) }}</h3>
              </div>
              <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shopping-cart text-blue-600"></i>
              </div>
            </div>
          </div>
          <div class="stats-card rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500">Montant total Payé</p>
                <h3 class="text-xl font-bold text-gray-800">{{ number_format($totalPaye, 0, ',', ' ') }} FC</h3>
              </div>
              <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
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

      <!-- Main content card -->
      <div class="bg-white w-full p-6 rounded-xl shadow-sm border border-gray-100">
        <!-- Filters and search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
          <div class="flex flex-col md:flex-row gap-3">
            <div class="relative">
              <input type="text" placeholder="Rechercher..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option>Tous les statuts</option>
              <option>Payé</option>
              <option>En attente</option>
              <option>Annulé</option>
            </select>
          </div>
          <div class="flex gap-2">
            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
              <i class="fas fa-filter"></i> Filtrer
            </button>
            <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2">
              <i class="fas fa-download"></i> Exporter
            </button>
          </div>
        </div>

        <!-- Mobile cards -->
        <div class="md:hidden space-y-4">
          @foreach($achats as $achat)
          <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-gray-800">{{ $achat->billet->nom_auteur ?? "N/A" }}</h3>
                <p class="text-sm text-gray-500">{{ $achat->billet->numero ?? 'N/A' }}</p>
              </div>
              <span class="status-badge {{ $achat->statut=='paye' ? 'bg-green-100 text-green-800' : ($achat->statut=='En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
                {{ $achat->statut }}
              </span>
            </div>
            <div class="mt-3">
              <p class="text-sm text-gray-700"><i class="fas fa-tag text-gray-400 mr-2"></i>{{ $achat->type_billet->nom_type ?? 'N/A' }} ({{ $achat->quantite }} billets)</p>
              @php
                $prixUnitaire = $achat->type_billet->evenement_type_billet->first()?->prix_unitaire ?? 0;
                $total = $prixUnitaire * $achat->quantite;
                $devise = $achat->type_billet->evenement_type_billet->first()?->devise ?? 'FC';
              @endphp
              <p class="font-medium text-gray-900 mt-2"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>{{ number_format($total, 2, ',', ' ') }} {{ $devise }}</p>
            </div>
            <div class="flex justify-between mt-4 pt-3 border-t border-gray-100">
              <span class="text-xs text-gray-500">{{ $achat->created_at->format('d M Y') }}</span>
              <div class="flex gap-2">
                <button onclick="openModal('resendModal{{ $achat->id }}')" class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-50 transition-colors"><i class="fas fa-paper-plane"></i></button>
                <button onclick="openModal('detailsModal{{ $achat->id }}')" class="text-gray-600 hover:text-gray-800 p-1 rounded-full hover:bg-gray-100 transition-colors"><i class="fas fa-eye"></i></button>
                <button onclick="openModal('deleteModal{{ $achat->id }}')" class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50 transition-colors"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
          <table class="min-w-full text-gray-700">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-4 text-left font-medium text-gray-800">Client</th>
                <th class="px-6 py-4 text-left font-medium text-gray-800">Type</th>
                <th class="px-6 py-4 text-center font-medium text-gray-800">Quantité</th>
                <th class="px-6 py-4 text-center font-medium text-gray-800">Total</th>
                <th class="px-6 py-4 text-center font-medium text-gray-800">Statut</th>
                <th class="px-6 py-4 text-center font-medium text-gray-800">Date</th>
                <th class="px-6 py-4 text-center font-medium text-gray-800">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($achats as $achat)
              @php
                $prixUnitaire = $achat->type_billet->evenement_type_billet->first()?->prix_unitaire ?? 0;
                $total = $prixUnitaire * $achat->quantite;
                $devise = $achat->type_billet->evenement_type_billet->first()?->devise ?? 'FC';
              @endphp
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-gray-800">{{ $achat->billet->nom_auteur ?? "N/A" }}</div>
                    <div class="text-sm text-gray-500">{{ $achat->billet->numero ?? 'N/A' }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">{{ $achat->type_billet->nom_type ?? "N/A" }}</td>
                <td class="px-6 py-4 text-center">{{ $achat->quantite }}</td>
                <td class="px-6 py-4 text-center font-medium">{{ number_format($total, 2, ',', ' ') }} {{ $devise }}</td>
                <td class="px-6 py-4 text-center">
                  <span class="status-badge {{ $achat->statut=='Payé' ? 'bg-green-100 text-green-800' : ($achat->statut=='En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
                    {{ $achat->statut }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center text-sm text-gray-500">
                  {{ $achat->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex justify-center gap-2">
                    <button onclick="openModal('resendModal{{ $achat->id }}')" class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200" title="Renvoyer"><i class="fas fa-paper-plane"></i></button>
                    <button onclick="openModal('detailsModal{{ $achat->id }}')" class="action-btn bg-gray-100 text-gray-700 hover:bg-gray-200" title="Détails"><i class="fas fa-eye"></i></button>
                    <button onclick="openModal('deleteModal{{ $achat->id }}')" class="action-btn bg-red-100 text-red-700 hover:bg-red-200" title="Supprimer"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
          <div class="text-sm text-gray-500">
            Affichage de {{ ($achats->currentPage() - 1) * $achats->perPage() + 1 }} à {{ min($achats->currentPage() * $achats->perPage(), $achats->total()) }} sur {{ $achats->total() }} achats
          </div>
          <div class="flex gap-1">
            @if($achats->onFirstPage())
              <button class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
              </button>
            @else
              <button onclick="window.location='{{ $achats->previousPageUrl() }}'" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                <i class="fas fa-chevron-left"></i>
              </button>
            @endif
            
            @foreach(range(1, min(5, $achats->lastPage())) as $page)
              <button onclick="window.location='{{ $achats->url($page) }}'" class="px-3 py-1.5 rounded-md {{ $achats->currentPage() == $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} transition-colors">
                {{ $page }}
              </button>
            @endforeach
            
            @if($achats->hasMorePages())
              <button onclick="window.location='{{ $achats->nextPageUrl() }}'" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                <i class="fas fa-chevron-right"></i>
              </button>
            @else
              <button class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modals pour les actions -->
@foreach($achats as $achat)
<!-- Modal Réenvoyer -->
<div id="resendModal{{ $achat->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
  <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
    <div class="flex items-center gap-3 mb-4">
      <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
        <i class="fas fa-paper-plane text-blue-600"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-800">Réenvoyer le billet</h3>
    </div>
    <p class="text-gray-600 mb-6">Renvoyer le billet à <strong>{{ $achat->billet->nom_auteur ?? "N/A" }}</strong> ?</p>
    <div class="flex justify-end gap-3">
      <button onclick="closeModal('resendModal{{ $achat->id }}')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Annuler</button>
      <button class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">Confirmer</button>
    </div>
  </div>
</div>

<!-- Modal Détails -->
<div id="detailsModal{{ $achat->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
  <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
    <div class="flex items-center gap-3 mb-4">
      <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
        <i class="fas fa-eye text-blue-600"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-800">Détails de l'achat</h3>
    </div>
    @php
      $prixUnitaire = $achat->type_billet->evenement_type_billet->first()?->prix_unitaire ?? 0;
      $total = $prixUnitaire * $achat->quantite;
      $devise = $achat->type_billet->evenement_type_billet->first()?->devise ?? 'FC';
    @endphp
    <div class="space-y-3 text-sm">
      <div class="flex justify-between"><span class="text-gray-600">Client:</span><span class="font-medium">{{ $achat->billet->nom_auteur ?? "N/A" }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Téléphone:</span><span>{{ $achat->billet->numero ?? 'N/A' }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Type billet:</span><span>{{ $achat->type_billet->nom_type ?? "N/A" }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Quantité:</span><span>{{ $achat->quantite }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Prix unitaire:</span><span>{{ number_format($prixUnitaire, 2, ',', ' ') }} {{ $devise }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Total:</span><span class="font-bold">{{ number_format($total, 2, ',', ' ') }} {{ $devise }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Statut:</span><span class="status-badge {{ $achat->statut=='Payé' ? 'bg-green-100 text-green-800' : ($achat->statut=='En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">{{ $achat->statut }}</span></div>
      <div class="flex justify-between"><span class="text-gray-600">Date:</span><span>{{ $achat->created_at->format('d/m/Y H:i') }}</span></div>
    </div>
    <div class="flex justify-end mt-6">
      <button onclick="closeModal('detailsModal{{ $achat->id }}')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Fermer</button>
    </div>
  </div>
</div>

<!-- Modal Supprimer -->
<div id="deleteModal{{ $achat->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
  <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
    <div class="flex items-center gap-3 mb-4">
      <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
        <i class="fas fa-exclamation-triangle text-red-600"></i>
      </div>
      <h3 class="text-lg font-semibold text-gray-800">Supprimer l'achat</h3>
    </div>
    <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer l'achat de <strong>{{ $achat->billet->nom_auteur ?? "N/A" }}</strong> ? Cette action est irréversible.</p>
    <div class="flex justify-end gap-3">
      <button onclick="closeModal('deleteModal{{ $achat->id }}')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Annuler</button>
      <button class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">Supprimer</button>
    </div>
  </div>
</div>
@endforeach

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
// Fermer modals en cliquant à l'extérieur
document.addEventListener('click', function(event){
  const modals = document.querySelectorAll('[id$="Modal"]');
  modals.forEach(modal => { 
    if(event.target === modal){ 
      modal.classList.add('hidden'); 
    } 
  });
});
</script>

</body>
</html>