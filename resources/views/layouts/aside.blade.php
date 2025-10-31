<!-- Overlay (visible sur mobile quand sidebar ouvert) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 hidden z-40 md:hidden"></div>
<!-- SIDEBAR -->
<aside id="sidebar"
class="fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 bg-white shadow-lg border-r border-gray-200 rounded-r-2xl overflow-y-auto">
<!-- Header logo + close (mobile) -->

  <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
    <a  class="flex items-center gap-3">
      <img src="{{ asset('assets/img/logo.png') }}" class="h-8" alt="logo">
      <span class="text-lg font-semibold text-gray-800">gestion Ticker</span>
    </a>
    <button id="sidebarClose" class="md:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Close sidebar">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Liste menu -->
  <nav class="px-3 py-6 space-y-1">
    <!-- Exemple d'item : on maintient la logique request()->routeIs pour l'active state -->
    <a href=""
       class="flex items-center gap-3 px-4 py-2 rounded-lg font-roboto font-light text-sm transition-colors
               'bg-gradient-to-r from-green-200 to-green-200/70 p-4 rounded-lg  text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
        <img src="{{ asset('icons/dashboard.png') }}" class="w-5 h-5 hover:text-white" alt="Dashboard">
      <span>Tableau de bord</span>
    </a>

    <a href=""
       class="flex items-center gap-3 px-4 py-2 font-roboto font-light rounded-lg text-sm transition-colors
               'bg-gradient-to-r from-green-200 to-green-200/70 p-4 rounded-lg  text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
        <img src="{{ asset('icons/pharmacy.png') }}" class="w-5 h-5 " alt="Pharmacie">
      <span>Demande d'evenement</span>
    </a>



    <a href=""
       class="flex items-center gap-3 px-4 font-roboto font-light py-2 rounded-lg text-sm transition-colors
              {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-green-200 to-green-200/70 p-4 rounded-lg  text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
        <img src="{{ asset('icons/setting.png') }}" class="w-5 h-5 " alt="Profil">
      <span>Profil</span>
    </a>

    <!-- Bouton déconnexion (ouvre modal) -->
    <button onclick="document.getElementById('logout-dialog').showModal();"
            class="w-full text-left flex items-center  font-light gap-3 px-4 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:text-white hover:from-green-200 hover:to-green-200/70 transition-all duration-300">
      <img src="{{ asset('icons/turn-off.png') }}" class="w-5 h-5 " alt="deconnexion">
      <span>Déconnexion</span>
    </button>
  </nav>
</aside>

<!-- Modal Déconnexion -->
<dialog id="logout-dialog" class="rounded-2xl p-0 w-full max-w-md">
  {{-- action="{{ route('logout') }}" --}}
  <form method="POST"  class="flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden">
    @csrf
    <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-red-400 to-red-400 text-white">
      <h3 class="text-lg font-semibold">Confirmation</h3>
      <button type="button" onclick="document.getElementById('logout-dialog').close();" aria-label="Fermer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="p-6">
      <p class="text-sm text-gray-700">Voulez-vous vraiment vous déconnecter ?</p>
    </div>

    <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50">
      <button type="button" onclick="document.getElementById('logout-dialog').close();" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Annuler</button>
      <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-500">Confirmer</button>
    </div>
  </form>
</dialog>
