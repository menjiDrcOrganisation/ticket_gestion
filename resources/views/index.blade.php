@extends('layouts.main')
@section('title','Pharmacies')

@section('content')





<div class="bg-white w-full px-6 py-6 mx-auto">
    <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col mb-6 bg-white  shadow-xl rounded-2xl">

                <!-- Header -->
                <div class="p-6 border-b rounded-t-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h6 class="dark:text-dark text-xl font-semibold flex items-center gap-2">
                        {{-- <img src="https://cdn-icons-png.flaticon.com/512/2966/2966489.png" class="w-7 h-7" alt="icon"> --}}
                        Gestion demandes d'evenements
                    </h6>

                    <div class="flex flex-wrap items-center gap-3">

                        <!-- Input recherche -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Rechercher..."
                                class="w-96 rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none dark:bg-white dark:text-dark">
                            <span class="absolute left-2.5 top-2.5">
                                <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" class="w-4 h-4 opacity-70" alt="search">
                            </span>
                        </div>

                        <!-- Select statut -->
                        <select id="statusFilter"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none  text-dark dark:bg-white dark:text-dark">
                            <option value="">Tous les statuts</option>
                            <option value="valide">Valide</option>
                            <option value="ferme">Fermé</option>
                            <option value="en_attente">En attente</option>
                        </select>

                        <!-- Bouton Ajouter -->
                        <button command="show-modal" commandfor="dialog"
                            class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-dark hover:bg-blue-500 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    </div>
                </div>

                {{-- @include("pharmacies.create") --}}

                <!-- Table -->
                <div class="flex-auto px-0 pt-4 pb-2">
                    <div class="overflow-x-auto">
                        <table id="pharmacyTable"
                            class="min-w-full border-collapse text-white dark:text-slate-200">
                            <thead>
                                <tr class="bg-dark dark:bg-slate-700/50">
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Pharmacie</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Adresse & Téléphone</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Statut</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Créée le</th>
                                    <th class="px-6 py-3 text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @foreach ($pharmacies as $pharmacie) --}}
                                {{-- <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/70 transition"
                                    data-status="{{ $pharmacie->statut }}">
                                    <!-- Nom + Gérant --> --}}
                                    <td class="p-4 border-b dark:border-slate-600">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('assets/img/logo.png') }}" class="w-6 h-6" alt="pharmacy">
                                            <div>
                                                {{-- <h6 class="text-sm font-light">{{ ucfirst(strtolower($pharmacie->nom))}}</h6> --}}
                                                <p class="text-xs text-slate-500 font-light dark:text-slate-300">
                                                    Gérant : 
                                                    {{-- {{ ucfirst(strtolower($pharmacie->gerant->user->name))}} <br>
                                                    {{ ucfirst(strtolower($pharmacie->gerant->user->email))}} --}}
                                                     
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Adresse + Téléphone -->
                                    <td class="p-4 border-b dark:border-slate-600">
                                        {{-- <p class="text-sm font-light">{{ ucfirst(strtolower($pharmacie->adresse))}}</p> --}}
                                        {{-- <p class="text-xs text-slate-500 dark:text-slate-300">  {{ $pharmacie->telephone }}</p> --}}
                                    </td>

                                    <!-- Statut -->
                                    <td class="p-4 text-center border-b dark:border-slate-600">
                                        {{-- @php
                                            switch ($pharmacie->statut) {
                                                case 'valide': $btnClass = 'bg-emerald-100 text-emerald-700'; break;
                                                case 'ferme': $btnClass = 'bg-red-100 text-red-700'; break;
                                                case 'en_attent': $btnClass = 'bg-orange-100 text-orange-400'; break;
                                                default: $btnClass = 'bg-orange-100 text-orange-400';
                                            }
                                        @endphp --}}

                                        {{-- <button command="show-modal" commandfor="edit-statut-{{ $pharmacie->id_pharmacie }}"
                                            class="px-3 py-1 rounded-full text-xs font-semibold {{ $btnClass }}">
                                            {{ ucfirst($pharmacie->statut) }}
                                        </button> --}}
                                        {{-- @include('pharmacies.editestatut') --}}
                                    </td>

                                    <!-- Date -->
                                    <td class="p-4 text-center border-b dark:border-slate-600">
                                        <span class="text-xs text-slate-500 dark:text-slate-300">
                                            {{-- {{ $pharmacie->created_at->format('d/m/Y') }} --}}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="p-4 text-center border-b dark:border-slate-600">
                                        {{-- <button command="show-modal" commandfor="edit-pharmacie-{{ $pharmacie->id_pharmacie }}"
                                            class="flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white bg-blue-500 rounded-lg hover:bg-blue-400">
                                            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828911.png" class="w-3 h-3" alt="edit">
                                            Modifier
                                        </button> --}}
                                        {{-- @include('pharmacies.edit') --}}
                                    </td>
                                </tr>
                                {{-- @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Script recherche -->
{{-- <script>
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('pharmacyTable');

    function debounce(fn, delay = 150) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    function normalize(str) {
        if (!str) return "";
        return str
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function ensureNoResultsRow() {
        let tbody = table.querySelector('tbody');
        let noRow = table.querySelector('.no-results-row');
        if (!noRow) {
            const colCount = table.querySelectorAll('thead th').length || (table.rows[0] ? table.rows[0].cells.length : 1);
            noRow = document.createElement('tr');
            noRow.className = 'no-results-row';
            const td = document.createElement('td');
            td.colSpan = colCount;
            td.style.textAlign = 'center';
            td.style.fontStyle = 'italic';
            td.textContent = 'Aucun résultat trouvé.';
            noRow.appendChild(td);
            tbody.appendChild(noRow);
        }
        return noRow;
    }

    function filterTable() {
        const rawSearch = normalize(searchInput.value || '');
        const searchTokens = rawSearch.split(/\s+/).filter(Boolean);
        const rawStatus = normalize(statusFilter.value || '');

        const rows = table.querySelectorAll('tbody tr');
        let anyVisible = false;

        rows.forEach(row => {
            if (row.classList.contains('no-results-row')) return;

            const cells = row.getElementsByTagName('td');
            if (cells.length === 0) {
                row.style.display = 'none';
                return;
            }

            // Statut basé sur data-status
            const statutText = row.dataset && row.dataset.status ? normalize(row.dataset.status) : "";

            const rowText = normalize(row.textContent);

            const matchSearch = searchTokens.length === 0 || searchTokens.every(tok => rowText.includes(tok));
            const matchStatus = rawStatus === '' || rawStatus === 'all' || statutText.includes(rawStatus);

            if (matchSearch && matchStatus) {
                row.style.display = '';
                anyVisible = true;
            } else {
                row.style.display = 'none';
            }
        });

        const noRow = ensureNoResultsRow();
        noRow.style.display = anyVisible ? 'none' : '';
    }

    searchInput.addEventListener('input', debounce(filterTable, 120));
    statusFilter.addEventListener('change', filterTable);

    const observer = new MutationObserver(debounce(filterTable, 120));
    const tbody = table.querySelector('tbody');
    if (tbody) {
        observer.observe(tbody, { childList: true, subtree: true });
    }

    filterTable();
});
</script> --}}

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const table = document.getElementById('pharmacyTable');
    
        function normalize(str) {
            return str
                ? str.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim()
                : "";
        }
    
        function ensureNoResultsRow() {
            const tbody = table.querySelector('tbody');
            let noRow = tbody.querySelector('.no-results-row');
            if (!noRow) {
                const colCount = table.querySelectorAll('thead th').length;
                noRow = document.createElement('tr');
                noRow.className = 'no-results-row';
                noRow.innerHTML = `<td colspan="${colCount}" class="text-center italic py-4">Aucun résultat trouvé.</td>`;
                tbody.appendChild(noRow);
            }
            return noRow;
        }
    
        function filterTable() {
            const search = normalize(searchInput.value);
            const status = normalize(statusFilter.value);
    
            const rows = table.querySelectorAll('tbody tr');
            let anyVisible = false;
    
            rows.forEach(row => {
                if (row.classList.contains('no-results-row')) return;
    
                const rowText = normalize(row.innerText);
                const rowStatus = normalize(row.dataset.status);
    
                const matchSearch = search === "" || rowText.includes(search);
                const matchStatus = status === "" || rowStatus === status;
    
                if (matchSearch && matchStatus) {
                    row.style.display = "";
                    anyVisible = true;
                } else {
                    row.style.display = "none";
                }
            });
    
            // Afficher/masquer "aucun résultat"
            const noRow = ensureNoResultsRow();
            noRow.style.display = anyVisible ? "none" : "";
        }
    
        searchInput.addEventListener("input", filterTable);
        statusFilter.addEventListener("change", filterTable);
    
        filterTable(); // initial
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const alert = document.getElementById("alert-message");
        if (alert) {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500); // Supprime après animation
            }, 3000); // 3 secondes
        }
    });
</script>

    
@endsection
