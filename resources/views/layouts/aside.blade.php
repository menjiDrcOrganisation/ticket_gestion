<!-- Overlay (visible sur mobile quand sidebar ouvert) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 hidden z-40 md:hidden"></div>

<!-- SIDEBAR -->
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full md:translate-x-0 transition-transform duration-300 bg-white shadow-lg border-r border-gray-200 rounded-r-2xl overflow-y-auto">

    <!-- Header logo + close (mobile) -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
        <a class="flex items-center gap-3">
            <img src="{{ asset('assets/img/logo.png') }}" class="h-8" alt="logo">
            <span class="text-lg font-semibold text-gray-800"></span>
        </a>
        <button id="sidebarClose" class="md:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Close sidebar">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Menu -->
    <nav class="px-3 py-6 space-y-1">
        <!-- Tableau de bord -->
        <a href="{{ route('dashboard_admin.show') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg font-roboto font-light text-sm transition-colors
                  {{ request()->routeIs('dashboard_admin') ? 'bg-gradient-to-r from-green-200 to-green-200/70 text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
            <img src="{{ asset('icons/dashboard.png') }}" class="w-5 h-5" alt="Dashboard">
            <span>Tableau de bord</span>
        </a>

        <!-- Demande d'événement -->
        <a href="{{ route('demandeEvenement.index') }}"
           class="flex items-center gap-3 px-4 py-2 font-roboto font-light rounded-lg text-sm transition-colors
                  {{ request()->routeIs('demandeEvenement.*') ? 'bg-gradient-to-r from-green-200 to-green-200/70 text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
            <img src="{{ asset('icons/pharmacy.png') }}" class="w-5 h-5" alt="Demande">
            <span>Demande d'événement</span>
        </a>

        <!-- Ajouter un événement -->
        <a href="{{ route('evenements.index') }}"
           class="flex items-center gap-3 px-4 py-2 font-roboto font-light rounded-lg text-sm transition-colors
                  {{ request()->routeIs('evenements.*') ? 'bg-gradient-to-r from-green-200 to-green-200/70 text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
            <img src="{{ asset('icons/add.png') }}" class="w-5 h-5" alt="Ajouter">
            <span>Événement</span>
        </a>

        <!-- Profil -->
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 px-4 py-2 font-roboto font-light rounded-lg text-sm transition-colors
                  {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-green-200 to-green-200/70 text-white' : 'text-gray-700 hover:text-white hover:bg-gradient-to-r hover:from-green-200 hover:to-green-200/70 transition-all duration-300' }}">
            <img src="{{ asset('icons/setting.png') }}" class="w-5 h-5" alt="Profil">
            <span>Profil</span>
        </a>

        <!-- Déconnexion -->
        <button onclick="document.getElementById('logout-dialog').showModal();"
                class="w-full text-left flex items-center font-light gap-3 px-4 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:text-white hover:from-green-200 hover:to-green-200/70 transition-all duration-300">
            <img src="{{ asset('icons/turn-off.png') }}" class="w-5 h-5" alt="Déconnexion">
            <span>Déconnexion</span>
        </button>
    </nav>
</aside>

<!-- Modal Déconnexion -->
<dialog id="logout-dialog" class="rounded-2xl p-0 w-full max-w-md">
    <form method="POST" action="{{ route('logout') }}" class="flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden">
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
