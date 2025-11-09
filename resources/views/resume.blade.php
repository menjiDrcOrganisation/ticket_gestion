@extends('layouts.main')
 @section('content')
   <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-8 py-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4 mb-4 lg:mb-0">
          <div class="h-14 w-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-calendar-alt text-xl"></i>
          </div>
          <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $evenement->nom }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-2">
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-map-marker-alt text-gray-400"></i>
                <span>{{ $evenement->lieu ?? 'Lieu non défini' }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-clock text-gray-400"></i>
                <span>{{ \Carbon\Carbon::parse($evenement->date)->translatedFormat('d F Y') }}</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <i class="fas fa-users text-gray-400"></i>
                <span>{{ $totalBilletsVendus }} participants</span>
              </div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all flex items-center gap-2">
            <i class="fas fa-download"></i>
            Exporter
          </button>
          <a href="{{ route('evenements.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all flex items-center gap-2 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            Retour
          </a>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="stat-card group">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-full -mr-4 -mt-4"></div>
        <div class="flex items-center justify-between mb-4">
          <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-ticket-alt text-lg"></i>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
            <i class="fas fa-arrow-up mr-1"></i>12%
          </span>
        </div>
        <p class="text-sm text-gray-500 mb-2">Billets vendus</p>
        <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $totalBilletsVendus }}</h3>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
          <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full" style="width: {{ min(($totalBilletsVendus / 1000) * 100, 100) }}%"></div>
        </div>
      </div>

      <div class="stat-card group">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-500/10 to-emerald-500/10 rounded-full -mr-4 -mt-4"></div>
        <div class="flex items-center justify-between mb-4">
          <div class="h-12 w-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-money-bill-wave text-lg"></i>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
            <i class="fas fa-arrow-up mr-1"></i>8%
          </span>
        </div>
        <p class="text-sm text-gray-500 mb-2">Revenus CDF</p>
        <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ number_format($revenusCDF, 0, ',', ' ') }} FC</h3>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
          <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-1.5 rounded-full" style="width: {{ min(($revenusCDF / 1000000) * 100, 100) }}%"></div>
        </div>
      </div>

      <div class="stat-card group">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-full -mr-4 -mt-4"></div>
        <div class="flex items-center justify-between mb-4">
          <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-dollar-sign text-lg"></i>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
            <i class="fas fa-arrow-up mr-1"></i>15%
          </span>
        </div>
        <p class="text-sm text-gray-500 mb-2">Revenus USD</p>
        <h3 class="text-3xl font-bold text-gray-900 mb-2">${{ number_format($revenusUSD, 2, ',', ' ') }}</h3>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
          <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-1.5 rounded-full" style="width: {{ min(($revenusUSD / 1000) * 100, 100) }}%"></div>
        </div>
      </div>

      <div class="stat-card group">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-500/10 to-amber-500/10 rounded-full -mr-4 -mt-4"></div>
        <div class="flex items-center justify-between mb-4">
          <div class="h-12 w-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-chart-pie text-lg"></i>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
            <i class="fas fa-arrow-up mr-1"></i>5%
          </span>
        </div>
        <p class="text-sm text-gray-500 mb-2">Taux de remplissage</p>
        <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $tauxRemplissage }}%</h3>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
          <div class="bg-gradient-to-r from-orange-500 to-amber-500 h-1.5 rounded-full" style="width: {{ $tauxRemplissage }}%"></div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 px-8">
      <!-- Types de Billets -->
      <div class="dashboard-card">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">Types de Billets</h3>
          <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
            {{ count($typesBillets) }} types
          </span>
        </div>
        <div class="space-y-4">
          @foreach ($typesBillets as $type)
          <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-100 hover:border-indigo-200 transition-all group">
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white">
                <i class="fas fa-ticket-alt text-sm"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                  {{ $type->type_billet->nom_type }}
                </p>
                <p class="text-xs text-gray-500">{{ $type->nombre_billet }} disponibles</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-bold text-gray-900 text-lg">{{ number_format($type->prix_unitaire, 0, ',', ' ') }} {{ $type->devise }}</p>
              <p class="text-xs text-gray-500 mt-1">Prix unitaire</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Graphique de répartition -->
      <div class="dashboard-card">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">Répartition des Ventes</h3>
          <select class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
            <option>7 derniers jours</option>
            <option>30 derniers jours</option>
            <option>Tout le temps</option>
          </select>
        </div>
        <div class="h-64 flex items-end justify-between gap-3 pt-8">
          @php
            $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            $heights = [60, 80, 45, 90, 75, 95, 70];
            $colors = ['from-indigo-500 to-purple-400', 'from-indigo-500 to-purple-400', 'from-indigo-500 to-purple-400', 
                      'from-indigo-500 to-purple-400', 'from-indigo-500 to-purple-400', 'from-green-500 to-emerald-400', 
                      'from-green-500 to-emerald-400'];
          @endphp
          @foreach($days as $index => $day)
            <div class="flex-1 flex flex-col items-center">
              <div 
                class="w-full bg-gradient-to-t {{ $colors[$index] }} rounded-t-lg transition-all hover:opacity-80 cursor-pointer" 
                style="height: {{ $heights[$index] }}%"
                title="{{ $heights[$index] }} ventes"
              ></div>
              <span class="text-xs text-gray-500 mt-2">{{ $day }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Derniers Achats -->
    <div class="px-8 mt-8 mb-10">
      <div class="dashboard-card">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">Derniers Achats</h3>
          <button class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2">
            Voir tout <i class="fas fa-arrow-right text-xs"></i>
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="pb-4 text-left font-semibold text-gray-600">Client</th>
                <th class="pb-4 text-left font-semibold text-gray-600">Type</th>
                <th class="pb-4 text-center font-semibold text-gray-600">Quantité</th>
                <th class="pb-4 text-center font-semibold text-gray-600">Montant</th>
                <th class="pb-4 text-center font-semibold text-gray-600">Date</th>
                <th class="pb-4 text-center font-semibold text-gray-600">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse ($derniersAchats as $achat)
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center">
                      <i class="fas fa-user text-indigo-600 text-sm"></i>
                    </div>
                    <div>
                      <div class="font-semibold text-gray-900">{{ $achat->billet->nom_auteur ?? 'Inconnu' }}</div>
                      <div class="text-xs text-gray-500">{{ $achat->billet->email ?? '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="py-4">
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                    {{ $achat->type_billet->nom_type ?? 'Standard' }}
                  </span>
                </td>
                <td class="py-4 text-center font-medium text-gray-900">
                  {{ $achat->quantite }}
                </td>
                <td class="py-4 text-center">
                  <span class="font-bold text-gray-900">
                    {{ number_format($achat->prix_unitaire * $achat->quantite, 0, ',', ' ') }} {{ $achat->devise }}
                  </span>
                </td>
                <td class="py-4 text-center text-gray-600">
                  {{ \Carbon\Carbon::parse($achat->date_achat)->format('d/m/Y') }}
                </td>
                <td class="py-4 text-center">
                  <span class="status-badge {{ $achat->statut == 'payé' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    <i class="fas {{ $achat->statut == 'payé' ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                    {{ ucfirst($achat->statut) }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="py-8 text-center">
                  <i class="fas fa-shopping-cart text-3xl text-gray-300 mb-3"></i>
                  <p class="text-gray-500">Aucun achat récent</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>




@endsection