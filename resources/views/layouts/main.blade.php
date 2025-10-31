<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <title>Gestion Ticker</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

body {
    font-family: 'Roboto', sans-serif;
}

  </style>
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Nucleo Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />

    <link href="{{ asset('assets/css/style.css')}}" rel="stylesheet" />
    <!-- Popper -->

    <head>
        <!-- Tailwind via CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Font Awesome via CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
            integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <!-- Main Styling -->
    <link href="{{ asset('assets/css/argon-dashboard-tailwind.css?v=1.0.1') }}" rel="stylesheet" />
    <!-- Tailwind déjà présent dans ton projet (j'imagine) -->
<!-- Lucide icons (CDN) -->
<script src="https://unpkg.com/lucide@latest/dist/lucide.min.js"></script>

</head>

<body
    class="m-0 font-roboto  text-base antialiased font-normal leading-default bg-gray-50 text-slate-500">
    {{-- <div class="absolute w-full bg-blue-500 min-h-12"></div> --}}
    <!-- sidenav  -->
    {{-- <header class="flex items-center justify-between px-4 py-3 bg-white shadow md:hidden">
        <div class="flex items-center gap-3">
          <button id="sidebarToggle" aria-expanded="false" aria-controls="sidebar"
                  class="p-2 rounded-md hover:bg-gray-100">
            <i data-lucide="menu" class="w-5 h-5"></i>
          </button>
          <a href="{{ route('medicaments.index') }}" class="flex items-center gap-2">
            <img src="{{ asset('assets/img/logo.png') }}" class="h-8" alt="logo">
            <span class="font-semibold">Opharma</span>
          </a>
        </div>
      </header> --}}
      
    @include('layouts.aside')
    <!-- end sidenav -->

    <main class="relative h-full max-h-screen transition-all duration-200 ease-in-out xl:ml-68 rounded-xl">
        <!-- Navbar  -->
      

        <!-- end Navbar -->

        <!-- cards -->
        @yield('content')
        <!-- end cards -->
    </main>

</body>


<!-- plugin for charts  -->
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}" async></script>
<!-- plugin for scrollbar  -->
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}" async></script>
<!-- main script file  -->
<script src="{{ asset('assets/js/argon-dashboard-tailwind.js?v=1.0.1') }}" async></script>
<script>
    // initialisation Lucide
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  
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
  
    toggle?.addEventListener('click', (e) => {
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      if (expanded) closeSidebar();
      else openSidebar();
    });
  
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);
  
    // Fermer sidebar quand on resize >= md (pour remettre l'état si nécessaire)
    window.addEventListener('resize', () => {
      if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('hidden');
        toggle?.setAttribute('aria-expanded', 'true');
      } else {
        // par défaut sur mobile : fermé
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        toggle?.setAttribute('aria-expanded', 'false');
      }
    });
  </script>
  

</html>
