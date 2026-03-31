@extends('layouts.org')
@section('content')

<div class="p-4 md:p-6 mt-14 md:mt-0">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Achats de Billets</h2>
            <p class="text-gray-500 mt-1">Tous les achats de billets pour vos événements</p>
        </div>

        <!-- Search et items per page -->
        <div class="mb-6 flex flex-col md:flex-row md:justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <input type="text" id="searchInput" placeholder="Rechercher par nom..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Afficher :</span>
                <select id="itemsPerPage" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Tableau Desktop -->
        <div class="bg-white w-full p-4 md:p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
                <div class="min-w-[800px]">
                    <table class="min-w-full text-gray-700">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th>#</th>
                                <th class="px-6 py-4 text-left font-medium">Client</th>
                                <th class="px-6 py-4 text-left font-medium">Type</th>
                                <th class="px-6 py-4 text-left font-medium">Prix unitaire</th>
                                <th class="px-6 py-4 text-center font-medium">Quantité acheté</th>
                                <th class="px-6 py-4 text-center font-medium">Quantité restant</th>
                                <th class="px-6 py-4 text-center font-medium">Total</th>
                                <th class="px-6 py-4 text-center font-medium">Statut</th>
                                <th class="px-6 py-4 text-center font-medium">Date d'achat</th>
                                <th class="px-6 py-4 text-center font-medium">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100" id="tableBody">
                            @forelse($detailleParBillet as $billet)
                                <tr class="hover:bg-gray-50 transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}">
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4">{{ $billet["auteur"] ?? "N/A" }}</td>
                                    <td class="px-6 py-4">{{ $billet["type"] }}</td>
                                    <td class="px-6 py-4">{{ $billet["prix_unitaire"] }} {{ $billet["devise"] }}</td>
                                    <td class="px-6 py-4 text-center">{{ $billet["quantite"] }}</td>
                                    <td class="px-6 py-4 text-center">{{ $billet["quantite_fictif"] }}</td>
                                    <td class="px-6 py-4 text-center">{{ $billet["total"] }} {{ $billet['devise'] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                            {{ $billet["statut"] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">{{ $billet["date"] }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button onclick="openModal('detailsModal{{ $billet['id'] }}')"
                                                    class="w-8 h-8 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>

                                            <button class="w-8 h-8 bg-red-100 text-red-700 rounded-full flex items-center justify-center hover:bg-red-200 transition"
                                                    data-delete-id="{{ $billet['id'] }}">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-6 text-center text-gray-500">
                                        Aucun achat trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden space-y-4" id="mobileCards">
                @forelse($detailleParBillet as $billet)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition" data-client="{{ strtolower($billet['auteur'] ?? '') }}">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $billet["auteur"] ?? "N/A" }}</h3>
                            <p class="text-sm text-gray-600">{{ $billet["type"] }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                            {{ $billet["statut"] }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">Prix unitaire</p>
                            <p class="font-medium">{{ $billet["prix_unitaire"] }} {{ $billet["devise"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Quantité acheté</p>
                            <p class="font-medium text-center">{{ $billet["quantite"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Quantité restant</p>
                            <p class="font-medium text-center">{{ $billet["quantite_fictif"] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total</p>
                            <p class="font-medium">{{ $billet["total"] }} {{ $billet['devise'] }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">
                        <span class="text-sm text-gray-500">{{ $billet["date"] }}</span>
                        <div class="flex gap-2">
                            <button onclick="openModal('detailsModal{{ $billet['id'] }}')"
                                    class="w-8 h-8 bg-gray-100 text-gray-700 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            <button class="w-8 h-8 bg-red-100 text-red-700 rounded-full flex items-center justify-center hover:bg-red-200 transition"
                                    data-delete-id="{{ $billet['id'] }}">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    Aucun achat trouvé.
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-gray-200" id="paginationWrapper">
                <div class="text-sm text-gray-600">
                    Affichage de <span id="startItem">1</span> à <span id="endItem">10</span> sur <span id="totalItems">{{ count($detailleParBillet) }}</span> résultats
                </div>

                <div class="flex items-center gap-2">
                    <button id="prevPage" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div id="paginationNumbers" class="flex gap-1">
                        <!-- Numéros de page générés par JS -->
                    </div>

                    <button id="nextPage" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Pagination, recherche et QRCode -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let currentPage = 1;
let itemsPerPage = 10;
let allData = [];
let filteredData = [];

function openModal(id){ document.getElementById(id).classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeModal(id){ document.getElementById(id).classList.add('hidden'); document.body.style.overflow='auto'; }

document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('#tableBody tr');
    const mobileCards = document.querySelectorAll('#mobileCards > div:not(.text-center)');
    
    if(tableRows.length === 0) return;

    allData = Array.from(tableRows).map((row,i)=>({
        element: row,
        mobileElement: mobileCards[i],
        client: row.getAttribute('data-client')
    }));
    filteredData = [...allData];

    // QRCode
    @foreach($detailleParBillet as $billet)
        @if(!empty($billet['code']))
            new QRCode(document.getElementById("qrcode-{{ $billet['id'] }}"), { text: "{{ $billet['code'] }}", width: 120, height: 120 });
        @endif
    @endforeach

    setupEventListeners();
    updateDisplay();
});

function setupEventListeners() {
    document.getElementById('searchInput').addEventListener('input', e => filterData(e.target.value));
    document.getElementById('itemsPerPage').addEventListener('change', e => { itemsPerPage = +e.target.value; currentPage=1; updateDisplay(); });
    document.getElementById('prevPage').addEventListener('click', ()=> { if(currentPage>1){currentPage--;updateDisplay();} });
    document.getElementById('nextPage').addEventListener('click', ()=> { const totalPages=Math.ceil(filteredData.length/itemsPerPage); if(currentPage<totalPages){currentPage++;updateDisplay();} });

    // Suppression
    document.querySelectorAll('button[data-delete-id]').forEach(btn=>{
        btn.addEventListener('click', e=>{
            const id=btn.getAttribute('data-delete-id');
            if(confirm('Êtes-vous sûr de vouloir supprimer ce billet ?')){
                const form=document.createElement('form');
                form.method='POST'; form.action=`/billet/${id}`;
                form.innerHTML=`<input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form); form.submit();
            }
        });
    });
}

function filterData(term){
    const search=term.toLowerCase().trim();
    filteredData=search?allData.filter(d=>d.client.includes(search)):[...allData];
    currentPage=1;
    updateDisplay();
}

function updateDisplay(){
    const totalItems=filteredData.length;
    const totalPages=Math.ceil(totalItems/itemsPerPage);
    const start=(currentPage-1)*itemsPerPage;
    const end=Math.min(start+itemsPerPage,totalItems);

    allData.forEach(d=>{if(d.element)d.element.style.display='none'; if(d.mobileElement)d.mobileElement.style.display='none';});
    for(let i=start;i<end;i++){ if(filteredData[i]){ filteredData[i].element.style.display=''; filteredData[i].mobileElement.style.display=''; } }

    document.getElementById('startItem').textContent = totalItems===0?0:start+1;
    document.getElementById('endItem').textContent = end;
    document.getElementById('totalItems').textContent = totalItems;

    document.getElementById('prevPage').disabled=currentPage===1;
    document.getElementById('nextPage').disabled=currentPage===totalPages||totalPages===0;

    generatePaginationNumbers(totalPages);
}

function generatePaginationNumbers(totalPages){
    const container=document.getElementById('paginationNumbers'); container.innerHTML='';
    if(totalPages===0) return;

    let startPage=Math.max(1,currentPage-2), endPage=Math.min(totalPages,currentPage+2);
    if(currentPage<=3) endPage=Math.min(5,totalPages);
    if(currentPage>=totalPages-2) startPage=Math.max(1,totalPages-4);

    if(startPage>1){
        container.appendChild(createPageButton(1));
        if(startPage>2){ const e=document.createElement('span'); e.className='px-3 py-2 text-gray-500'; e.textContent='...'; container.appendChild(e); }
    }
    for(let i=startPage;i<=endPage;i++) container.appendChild(createPageButton(i));
    if(endPage<totalPages){
        if(endPage<totalPages-1){ const e=document.createElement('span'); e.className='px-3 py-2 text-gray-500'; e.textContent='...'; container.appendChild(e); }
        container.appendChild(createPageButton(totalPages));
    }
}

function createPageButton(n){
    const btn=document.createElement('button');
    btn.className=`px-3 py-2 border rounded-lg transition ${n===currentPage?'bg-blue-600 text-white border-blue-600':'border-gray-300 hover:bg-gray-50 text-gray-700'}`;
    btn.textContent=n;
    btn.addEventListener('click',()=>{ currentPage=n; updateDisplay(); });
    return btn;
}

function downloadQRCode(id){
    const canvas=document.querySelector("#qrcode-"+id+" canvas");
    if(!canvas) return alert("QR Code introuvable");
    const link=document.createElement("a");
    link.download="qrcode_billet_"+id+".png";
    link.href=canvas.toDataURL("image/png");
    link.click();
}
</script>
@endsection