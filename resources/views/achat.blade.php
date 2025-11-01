<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Achats de Billets</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; }
.sidebar-link {
  @apply flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 text-gray-700 hover:bg-blue-50 hover:text-blue-600;
}
.sidebar-link.active { @apply bg-blue-100 text-blue-600 font-medium; }
.status-badge { @apply px-3 py-1 rounded-full text-xs font-medium; }
.card-hover { @apply transition-all duration-300 hover:shadow-md hover:-translate-y-1; }
.action-btn { @apply px-3 py-1.5 rounded-md transition-all duration-200 font-medium; }
</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Header mobile -->
<header class="flex items-center justify-between bg-white shadow-sm px-4 py-3 md:hidden sticky top-0 z-30">
  <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors"><i class="fas fa-bars"></i></button>
  <h1 class="font-semibold text-lg text-gray-800">Achats de Billets</h1>
  <div class="h-8 w-8 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
    TM
  </div>
</header>

<!-- Overlay mobile -->
<div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40 md:hidden" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-sm transform -translate-x-full md:translate-x-0 md:relative z-50 transition-transform duration-300">
  <div class="flex items-center justify-center py-6 border-b">
    <div class="flex items-center gap-3">
      <div class="h-10 w-10 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center text-white font-bold">TM</div>
      <span class="font-bold text-lg text-gray-800">TicketMaster</span>
    </div>
  </div>
  <nav class="p-4 space-y-1">
    <a href="{{ route('dashboard') }}" class="sidebar-link"> <i class="fas fa-chart-pie w-5 text-center"></i> Tableau de bord</a>
    <a href="{{ route('evenements.index') }}" class="sidebar-link"> <i class="fas fa-calendar-plus w-5 text-center"></i> Événements</a>
    <a href="{{ route('billets.index') }}" class="sidebar-link"> <i class="fas fa-ticket-alt w-5 text-center"></i> Billets</a>
    <a href="{{ route('achats.index') }}" class="sidebar-link active"> <i class="fas fa-shopping-cart w-5 text-center"></i> Achats</a>
    <button class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600 w-full text-left mt-6" onclick="alert('Déconnexion');">
      <i class="fas fa-sign-out-alt w-5 text-center"></i> Déconnexion
    </button>
  </nav>
</aside>

<!-- Main content -->
<main class="flex-1 p-4 md:p-6 md:ml-64 transition-all duration-300">
  <div class="max-w-7xl mx-auto">
    <div class="bg-white w-full p-6 rounded-xl shadow-sm border border-gray-100">

      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Achats de Billets</h2>
        <p class="text-gray-500 mt-1 md:mt-0">Tous les achats de billets pour vos événements</p>
      </div>

      <!-- Mobile cards -->
      <div class="md:hidden space-y-4">
        @foreach($achats as $achat)
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 card-hover">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="font-semibold text-gray-800">{{ $achat->billet->nom_auteur ?? "N/A" }}</h3>
              <p class="text-sm text-gray-500">{{ $achat->billet->email ?? 'N/A' }}</p>
            </div>
            <span class="status-badge {{ $achat->statut=='Payé' ? 'bg-green-100 text-green-800' : ($achat->statut=='En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
              {{ $achat->statut }}
            </span>
          </div>
          <div class="mt-3">
            <p class="text-sm text-gray-700"><i class="fas fa-music text-gray-400 mr-2"></i>{{ $achat->evenement->nom ?? "N/A" }}</p>
            <p class="text-sm text-gray-700 mt-1"><i class="fas fa-tag text-gray-400 mr-2"></i>{{ $achat->type_billet->nom_type ?? 'N/A' }} ({{ $achat->quantite_reelle }} billets)</p>
            <p class="font-medium text-gray-900 mt-2"><i class="fas fa-money-bill-wave text-gray-400 mr-2"></i>{{ number_format($achat->prix, 2, ',', ' ') }} FC</p>
          </div>
          <div class="flex justify-between mt-4 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500">{{ $achat->created_at->format('d M Y') }}</span>
            <div class="flex gap-2">
              <button onclick="openModal('resendModal{{ $achat->id }}')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-paper-plane"></i></button>
              <button onclick="openModal('detailsModal{{ $achat->id }}')" class="text-gray-600 hover:text-gray-800"><i class="fas fa-eye"></i></button>
              <button onclick="openModal('deleteModal{{ $achat->id }}')" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
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
              <th class="px-6 py-4 text-left font-medium text-gray-800">Événement</th>
              <th class="px-6 py-4 text-left font-medium text-gray-800">Type</th>
              <th class="px-6 py-4 text-center font-medium text-gray-800">Quantité</th>
              <th class="px-6 py-4 text-center font-medium text-gray-800">Total</th>
              <th class="px-6 py-4 text-center font-medium text-gray-800">Statut</th>
              <th class="px-6 py-4 text-center font-medium text-gray-800">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($achats as $achat)
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-gray-800">{{ $achat->billet->nom_auteur ?? "N/A" }}</div>
                  <div class="text-sm text-gray-500">{{ $achat->billet->email ?? 'N/A' }}</div>
                </div>
              </td>
              <td class="px-6 py-4">{{ $achat->evenement->nom  ?? "N/A"}}</td>
              <td class="px-6 py-4">{{ $achat->type_billet->nom_type ?? "N/A" }}</td>
              <td class="px-6 py-4 text-center">{{ $achat->quantite }}</td>
              <td class="px-6 py-4 text-center">{{ number_format($achat->prix, 2, ',', ' ') }} FC</td>
              <td class="px-6 py-4 text-center">
                <span class="status-badge {{ $achat->statut=='Payé' ? 'bg-green-100 text-green-800' : ($achat->statut=='En attente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700') }}">
                  {{ $achat->statut }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex justify-center gap-2">
                  <button onclick="openModal('resendModal{{ $achat->id }}')" class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200"><i class="fas fa-paper-plane"></i></button>
                  <button onclick="openModal('detailsModal{{ $achat->id }}')" class="action-btn bg-gray-100 text-gray-700 hover:bg-gray-200"><i class="fas fa-eye"></i></button>
                  <button onclick="openModal('deleteModal{{ $achat->id }}')" class="action-btn bg-red-100 text-red-700 hover:bg-red-200"><i class="fas fa-trash"></i></button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Modals -->
      @foreach($achats as $achat)
      <!-- Réenvoyer -->
      <div id="resendModal{{ $achat->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
        <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Réenvoyer le billet</h3>
          <p class="text-gray-600 mb-6">Renvoyer le billet pour <strong>{{ $achat->evenement->nom ?? "N.A" }}</strong> à <strong>{{ $achat->billet->nom_auteur ?? "N/A" }}</strong> ?</p>
          <div class="flex justify-end gap-3">
            <button onclick="closeModal('resendModal{{ $achat->id }}')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg">Annuler</button>
            <button class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Confirmer</button>
          </div>
        </div>
      </div>

      <!-- Détails -->
      <div id="detailsModal{{ $achat->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex justify-center items-center p-4">
        <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails du billet</h3>
          <ul class="text-gray-700 space-y-2">
            <li><strong>Client :</strong> {{ $achat->billet->nom_auteur ?? "N/A" }}</li>
            <li><strong>Email :</strong> {{ $achat->billet->email ?? 'N/A' }}</li>
            <li><strong>Code :</strong> {{ $achat->billet->code_billet  ?? "N/A" }}</li>
            <li><strong>Événement :</strong> {{ $achat->evenement->nom  ?? "N/A" }}</li>
            <li><strong>Type :</strong> {{ $achat->type_billet->nom_type  ?? "N/A" }}</li>
            <li><strong>Quantité :</strong> {{ $achat->quantite_reelle }}</li>
            <li><strong>Prix :</strong> {{ number_format($achat->prix, 2, ',', ' ') }} FC</li>
            <li><strong>Statut :</strong> {{ ucfirst($achat->statut) }}</li>
            <li><strong>Date :</strong> {{ $achat->created_at->format('d M Y H:i') }}</li>
          </ul>
          <div class="flex justify-end mt-4">
            <button onclick="closeModal('detailsModal{{ $achat->id }}')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg">Fermer</button>
          </div>
        </div>
      </div>
      @endforeach

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
function openModal(id){ document.getElementById(id).classList.remove('hidden'); }
function closeModal(id){ document.getElementById(id).classList.add('hidden'); }
// Fermer modals en cliquant à l'extérieur
document.addEventListener('click', function(event){
  const modals = document.querySelectorAll('[id$="Modal"]');
  modals.forEach(modal => { if(event.target === modal){ modal.classList.add('hidden'); } });
});
</script>

</body>
</html>
