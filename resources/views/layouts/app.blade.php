<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>@yield('title', 'Tableau de bord') - TicketMaster</title>

<!-- Favicon : logo dans l'onglet -->
<link rel="icon" href="{{ asset('icons/Icone_Kimia.png') }}" type="image/png" />

<!-- Optionnel : favicon pour Apple touch (iPhone/iPad) -->
<link rel="apple-touch-icon" href="{{ asset('icons/Icone_Kimia.png') }}" />


        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Font Awesome -->
        <link
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            rel="stylesheet"
        />

        <!-- Configuration des couleurs -->
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: "#4f46e5",
                            secondary: "#7c3aed",
                            success: "#10b981",
                            warning: "#f59e0b",
                            dark: "#1e293b",
                        },
                    },
                },
            };
        </script>

        <!-- Styles personnalisés -->
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
            body {
                font-family: "Inter", sans-serif;
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

            /* Animation pour le sidebar mobile */
            .sidebar-mobile {
                transition: transform 0.3s ease-in-out;
            }

            /* Overlay pour mobile */
            .sidebar-overlay {
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
        </style>
    </head>

    <body class="min-h-screen flex">

        <!-- Modal de deconnexion -->
        <div
            id="deconnexion"
            class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">
                    Confirmation de deconnexion
                </h3>
                <p class="mb-4">Voulez-vous vraiment vous deconnecter</p>
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        onclick="closeModal('deconnexion')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    >
                        Annuler
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                        >
                            Deconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Overlay pour mobile -->
        <div
            id="sidebarOverlay"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"
        ></div>

        <aside
            id="sidebar"
            class="sidebar-mobile fixed inset-y-0 left-0 w-80 bg-white border-r border-gray-200 flex flex-col shadow-lg z-50 -translate-x-full lg:translate-x-0"
        >
            <!-- Logo & titre -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                        <img src="{{ asset('icons/Icone_Kimia.png') }}"  class="h-28 w-20">
                    <div>
                        <h1 class="font-bold text-2xl text-gray-900">
                            Kimiaticket
                        </h1>
                        <p class="text-sm text-gray-500 mt-1">
                            Plateforme de gestion
                        </p>
                    </div>
                </div>
                <!-- Bouton fermer pour mobile -->
                <button
                    id="sidebarClose"
                    class="absolute top-6 right-4 text-gray-500 hover:text-gray-700 lg:hidden"
                >
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-6 space-y-2 overflow-y-auto">
                @yield('nav-elements')
            </nav>

            <!-- Profil utilisateur -->
            <div class="p-6 border-t border-gray-100">
                <a href="{{ route('profile.edit') }}">
                    <div
                        class="flex items-center gap-3 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl"
                    >
                        <div
                            class="h-12 w-12 bg-red-500 rounded-xl flex items-center justify-center text-white font-bold"
                        >
                            <i class="fas fa-user text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800">
                                {{ Auth::user()->name ?? 'Utilisateur' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst(Auth::user()->role ?? 'invité') }}
                            </p>
                        </div>
                    </div>
                </a>

                <!-- Bouton de déconnexion -->

                <button
                    onclick="openModal('deconnexion')"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 mt-4 bg-red-500 text-white rounded-lg hover:shadow-md transition"
                >
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT ==================== -->
        <main
            class="flex-1 p-4 lg:p-8 lg:ml-80 bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen"
        >
            <!-- Header avec bouton menu mobile -->
            <div class="flex items-center justify-between mb-6 lg:hidden">
                <button
                    id="sidebarToggle"
                    class="p-2 rounded-lg bg-white shadow"
                >
                    <i class="fas fa-bars text-xl text-gray-700"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800">
                    @yield('title', 'Tableau de bord')
                </h1>
            </div>
            @if(session('success'))
    <div class="mb-4 p-4 text-green-800 bg-green-100 border border-green-300 rounded-lg">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 text-red-800 bg-red-100 border border-red-300 rounded-lg">
        {{ session('error') }}
    </div>
@endif


            <!-- Contenu principal -->
            @yield('content')
        </main>

        <!-- Scripts -->
        <script
            src="{{ asset('assets/js/plugins/chartjs.min.js') }}"
            async
        ></script>
        <script
            src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"
            async
        ></script>
        <script
            src="{{ asset('assets/js/argon-dashboard-tailwind.js?v=1.0.1') }}"
            async
        ></script>

        <!-- Script pour la gestion du sidebar responsive -->
        <script>
            // Éléments DOM
            const sidebar = document.getElementById("sidebar");
            const overlay = document.getElementById("sidebarOverlay");
            const toggle = document.getElementById("sidebarToggle");
            const closeBtn = document.getElementById("sidebarClose");

            // Fonctions pour ouvrir/fermer le sidebar
            function openSidebar() {
                sidebar.classList.remove("-translate-x-full");
                overlay.classList.remove("hidden");
                document.body.style.overflow = "hidden"; // Empêcher le défilement
            }

            function closeSidebar() {
                sidebar.classList.add("-translate-x-full");
                overlay.classList.add("hidden");
                document.body.style.overflow = ""; // Rétablir le défilement
            }

            // Événements
            toggle?.addEventListener("click", openSidebar);
            closeBtn?.addEventListener("click", closeSidebar);
            overlay?.addEventListener("click", closeSidebar);

            // Fermer le sidebar lors du redimensionnement vers desktop
            window.addEventListener("resize", () => {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });

            // Initialisation au chargement de la page
            document.addEventListener("DOMContentLoaded", function () {
                // Fermer le sidebar sur mobile au chargement
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
        </script>
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            //ouvrir le modal
            function openModal(id) {
                console.log(id);
                document.getElementById(id).classList.remove("hidden");
            }
            function closeModal(id) {
                document.getElementById(id).classList.add("hidden");
            }
            //action du modal

            document
                .getElementById("sup")
                .addEventListener("click", function (e) {
                    e.preventDefault();
                    // Soumettre un formulaire
                    document.getElementById("delete-event-form").submit();
                });
        </script>
    </body>
</html>
