<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de bord</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- Barre du haut (mobile) -->
  <header class="flex items-center justify-between bg-white shadow px-4 py-3 md:hidden">
    <button onclick="toggleSidebar()" class="p-2 bg-gray-100 rounded-lg">
      ☰
    </button>
    <h1 class="font-semibold text-lg">Tableau de bord</h1>
    <img src="{{ asset('assets/img/logo.png') }}" class="h-8" alt="logo">
  </header>

  <!-- Overlay mobile -->
  <div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40" onclick="toggleSidebar()"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-lg transform -translate-x-full md:translate-x-0 md:relative md:block z-50 transition-transform duration-300 ease-in-out">
    <div class="flex items-center justify-center py-5 border-b">
      <img src="{{ asset('assets/img/logo.png') }}" alt="logo" class="h-10">
    </div>

    <nav class="p-4 space-y-2">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-green-100 text-green-700 font-semibold">
        <img src="{{ asset('icons/dashboard.png') }}" class="w-5 h-5" alt=""> Tableau de bord
      </a>
      <a href="{{ route('evenements.index') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-100 text-gray-700">
        <img src="{{ asset('icons/add.png') }}" class="w-5 h-5" alt=""> Événements
      </a>
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-100 text-gray-700">
        <img src="{{ asset('icons/ticket.png') }}" class="w-5 h-5" alt=""> Billets
      </a>
      <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-100 text-gray-700">
        <img src="{{ asset('icons/cart.png') }}" class="w-5 h-5" alt=""> Achats
      </a>
      <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-green-100 text-gray-700">
        <img src="{{ asset('icons/setting.png') }}" class="w-5 h-5" alt=""> Profil
      </a>
    </nav>
  </aside>

  <!-- Contenu principal -->
  <main class="flex-1 p-4 md:p-6 md:ml-64">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Tableau de bord</h2>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-5 flex items-center justify-between">
        <div>
          <h3 class="text-gray-600 text-sm">Événements</h3>
          <p class="text-2xl font-bold text-gray-800">12</p>
        </div>
        <img src="{{ asset('icons/add.png') }}" class="w-10 h-10 opacity-70" alt="">
      </div>

      <div class="bg-white rounded-xl shadow p-5 flex items-center justify-between">
        <div>
          <h3 class="text-gray-600 text-sm">Billets vendus</h3>
          <p class="text-2xl font-bold text-gray-800">340</p>
        </div>
        <img src="{{ asset('icons/ticket.png') }}" class="w-10 h-10 opacity-70" alt="">
      </div>

      <div class="bg-white rounded-xl shadow p-5 flex items-center justify-between">
        <div>
          <h3 class="text-gray-600 text-sm">Revenus (FC)</h3>
          <p class="text-2xl font-bold text-gray-800">4.2M</p>
        </div>
        <img src="{{ asset('icons/money.png') }}" class="w-10 h-10 opacity-70" alt="">
      </div>

      <div class="bg-white rounded-xl shadow p-5 flex items-center justify-between">
        <div>
          <h3 class="text-gray-600 text-sm">Utilisateurs</h3>
          <p class="text-2xl font-bold text-gray-800">127</p>
        </div>
        <img src="{{ asset('icons/user.png') }}" class="w-10 h-10 opacity-70" alt="">
      </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Ventes -->
      <div class="bg-white rounded-xl shadow p-5">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Ventes de billets (7 derniers jours)</h3>
        <canvas id="salesChart" height="150"></canvas>
      </div>

      <!-- Répartition -->
      <div class="bg-white rounded-xl shadow p-5">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Répartition des types de billets</h3>
        <canvas id="typeChart" height="150"></canvas>
      </div>
    </div>
  </main>

  <!-- JS Sidebar + Chart -->
  <script>
    function toggleSidebar(){
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
    }

    // Graphique 1 - Ventes
    new Chart(document.getElementById('salesChart'), {
      type: 'line',
      data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
          label: 'Billets vendus',
          data: [50, 70, 65, 90, 120, 80, 110],
          borderColor: '#10B981',
          backgroundColor: 'rgba(16,185,129,0.1)',
          fill: true,
          tension: 0.3
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // Graphique 2 - Types
    new Chart(document.getElementById('typeChart'), {
      type: 'doughnut',
      data: {
        labels: ['VIP', 'Standard', 'Étudiant'],
        datasets: [{
          data: [35, 50, 15],
          backgroundColor: ['#10B981', '#3B82F6', '#F59E0B']
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  </script>

</body>
</html>
