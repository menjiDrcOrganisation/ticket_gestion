<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord') - Kimia ticket</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
   
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Configuration des couleurs -->
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

    <!-- Styles personnalisés -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        /* Liens du menu latéral */
        .sidebar-link {
            @apply flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-600 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-600 hover:shadow-sm;
        }

        .sidebar-link.active {
            @apply bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 font-semibold shadow-sm border-r-4 border-indigo-500;
        }

        /* Cartes & éléments du tableau de bord */
        .dashboard-card {
            @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="min-h-screen flex">

    <!-- ==================== SIDEBAR ==================== -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-80 bg-white border-r border-gray-200 flex flex-col shadow-lg z-50">

        <!-- Logo & titre -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 gradient-bg rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-ticket-alt text-xl"></i>
                </div>
                <div>
                    <h1 class="font-bold text-2xl text-gray-900">TicketMaster</h1>
                    <p class="text-sm text-gray-500 mt-1">Plateforme de gestion</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-6 space-y-2  overflow-y-auto">
            <a href="{{ route('dashboard.admin.viewDash') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="h-10 w-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-indigo-600 text-lg"></i>
                </div>
                <span class="flex-1 text-base font-medium">Tableau de bord</span>
                @if (request()->routeIs('dashboard'))
                    <div class="h-2 w-2 bg-indigo-500 rounded-full"></div>
                @endif
            </a>

            <a href="{{ route('evenements.index') }}" class="sidebar-link {{ request()->routeIs('evenements.*') ? 'active' : '' }}">
                <div class="h-10 w-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-plus text-purple-600 text-lg"></i>
                </div>
                <span class="flex-1 text-base font-medium">Événements</span>
            </a>

        
            <a href="{{ route('type_billet.index') }}" class="sidebar-link {{ request()->routeIs('achats.*') ? 'active' : '' }}">
                <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                </div>
                <span class="flex-1 text-base font-medium">Type billet</span>
            </a>

             <a href="{{ route('demandeEvenement.index') }}" class="sidebar-link {{ request()->routeIs('achats.*') ? 'active' : '' }}">
                <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                </div>
                <span class="flex-1 text-base font-medium">Demande evenement</span>
            </a>

            <a href="{{ route('portefeulle.showMontantEvent') }}" class="sidebar-link {{ request()->routeIs('achats.*') ? 'active' : '' }}">
                <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                </div>
                <span class="flex-1 text-base font-medium">Portefeuilles</span>
            </a>
        </nav>

        <!-- Profil utilisateur -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl">
                <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold">
                    <i class="fas fa-user text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">{{ Auth::user()->name ?? 'Utilisateur' }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role ?? 'invité') }}</p>
                </div>
                <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-cog text-lg"></i>
                </button>
            </div>

            <!-- Bouton de déconnexion -->
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button 
                    type="submit" 
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:shadow-md transition"
                >
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    
    <main class="flex-1 p-8 ml-80 bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen">
        @yield('content')
    </main>
     <!-- JS Sidebar -->
    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        function openSidebar(){sidebar.classList.remove('-translate-x-full');overlay.classList.remove('hidden');}
        function closeSidebar(){sidebar.classList.add('-translate-x-full');overlay.classList.add('hidden');}
        toggle?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);
    </script>
    <!-- Scripts -->
    <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}" async></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}" async></script>
    <script src="{{ asset('assets/js/argon-dashboard-tailwind.js?v=1.0.1') }}" async></script>

    <script>
        // Initialisation des icônes Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Gestion de la sidebar
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            toggle?.setAttribute('aria-expanded', 'true');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            toggle?.setAttribute('aria-expanded', 'false');
        }

        toggle?.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            expanded ? closeSidebar() : openSidebar();
        });

        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Ferme la sidebar sur les écrans larges si nécessaire
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                toggle?.setAttribute('aria-expanded', 'true');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                toggle?.setAttribute('aria-expanded', 'false');
            }
        });
    </script>

</body>
</html>
