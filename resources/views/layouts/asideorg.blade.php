<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 w-72 border-r transform -translate-x-full md:translate-x-0 md:static z-50 transition-transform duration-300 flex flex-col">
  <!-- En-tête avec logo -->
  <div class="p-6 border-b border-gray-100">
    <div class="flex items-center gap-3">
      <div class="h-12 w-12 gradient-bg rounded-xl flex items-center justify-center text-white font-bold shadow-md">
        <i class="fas fa-ticket-alt text-lg"></i>
      </div>
      <div>
        <span class="font-bold text-xl text-gray-800">TicketMaster</span>
        <p class="text-xs text-gray-500 mt-1">Gestion des billets</p>
      </div>
    </div>
  </div>
  
  <!-- Navigation principale -->
  <nav class="flex-1 p-4 space-y-1">
    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Navigation principale</div>
    
    <a href="{{ route('dashboard') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-blue-50">
        <i class="fas fa-chart-pie text-blue-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Tableau de bord</span>
    </a>
    
    <a href="{{ route('evenements.index') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-purple-50">
        <i class="fas fa-calendar-plus text-purple-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Événements</span>
    </a>
    
    <a href="{{ route('billets.index') }}" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-green-50">
        <i class="fas fa-ticket-alt text-green-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Billets</span>
    </a>
    
    <a href="{{ route('achats.index') }}" class="sidebar-link active">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-indigo-50">
        <i class="fas fa-shopping-cart text-indigo-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Achats</span>
      <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full min-w-[1.5rem] text-center">12</span>
    </a>
    
    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2">Outils</div>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-orange-50">
        <i class="fas fa-chart-bar text-orange-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Rapports</span>
    </a>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-pink-50">
        <i class="fas fa-cog text-pink-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Paramètres</span>
    </a>
    
    <a href="#" class="sidebar-link">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-teal-50">
        <i class="fas fa-question-circle text-teal-600 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Aide & Support</span>
    </a>
  </nav>
  
  <!-- Section utilisateur -->
  <div class="user-profile border-t border-gray-100 p-4">
    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-white transition-colors cursor-pointer">
      <div class="h-10 w-10 gradient-bg rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
        TM
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 truncate text-sm">Thomas Martin</p>
        <p class="text-xs text-gray-500 truncate">Administrateur</p>
      </div>
      <button class="p-1.5 rounded-lg hover:bg-white transition-colors text-gray-500">
        <i class="fas fa-chevron-down text-xs"></i>
      </button>
    </div>
    
    <button class="sidebar-link text-red-500 hover:bg-red-50 hover:text-red-600 w-full text-left mt-2" onclick="alert('Déconnexion');">
      <div class="sidebar-icon h-9 w-9 rounded-lg bg-red-50">
        <i class="fas fa-sign-out-alt text-red-500 text-sm"></i>
      </div>
      <span class="flex-1 text-sm">Déconnexion</span>
    </button>
  </div>
</aside>