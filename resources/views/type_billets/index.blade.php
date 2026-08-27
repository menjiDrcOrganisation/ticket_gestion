@extends('layouts.main')
@section('title', 'Types de billets')

@section('content')
<div class="container mx-auto px-6 py-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800">Gestion des types de billets</h1>
        <button onclick="openModal()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow transition">
            + Ajouter un Type de Billet
        </button>
    </div>

    <form method="GET" action="{{ route('type_billet.index') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-12 md:items-end">
        <div class="md:col-span-8">
            <label for="q" class="mb-1.5 block text-sm font-medium text-slate-600">Recherche</label>
            <x-app-input id="q" name="q" :value="$search" placeholder="Rechercher un type de billet..." wrapperClass="mb-0" />
        </div>

        <div class="md:col-span-4 flex gap-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Filtrer
            </button>
            <a href="{{ route('type_billet.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Réinitialiser
            </a>
        </div>
    </form>

    {{-- Tableau des types --}}
    <x-app-table minWidth="900px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
        <x-slot:head>
            <tr>
                <x-app-th>#</x-app-th>
                <x-app-th>Nom</x-app-th>
                <x-app-th>Date de création</x-app-th>
            </tr>
        </x-slot:head>

        <x-slot:body>
                @forelse($typeBillets as $typeBillet)
                    <tr class="transition">
                        <x-app-td>{{ ($typeBillets->firstItem() ?? 0) + $loop->index }}</x-app-td>
                        <x-app-td class="font-medium">{{ $typeBillet->nom_type }}</x-app-td>
                        <x-app-td :nowrap="true">{{ $typeBillet->created_at->format('d/m/Y') }}</x-app-td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-500 italic">
                            Aucun type de billet trouvé.
                        </td>
                    </tr>
                @endforelse
        </x-slot:body>
    </x-app-table>

    <div class="mt-5">
        {{ $typeBillets->links() }}
    </div>
</div>

{{-- ✅ MODAL : Enregistrer un nouveau type --}}
<div id="modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ajouter un Type de Billet</h2>

        <form action="{{ route('type_billet.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-gray-700 font-medium mb-1">Nom du type</label>
                <x-app-input type="text" name="nom_type" placeholder="Ex : VIP, Standard..." required wrapperClass="mb-0" />
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 border border-gray-400 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>

        {{-- Bouton de fermeture (croix) --}}
        <button onclick="closeModal()" 
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            ✖
        </button>
    </div>
</div>

{{-- ✅ Script Modal --}}
<script>
    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
        document.getElementById('modal').classList.remove('flex');
    }
</script>
@endsection
