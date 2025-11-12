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
  @yield('content')
</main>

<!-- Modals pour les actions -->
</body>
</html>