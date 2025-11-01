@extends('layouts.main')
@section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Créer un Événement</h2>

    <!-- Mini-formulaire Organisateur -->
   

    <!-- Formulaire Événement -->
    <form action="{{ route('evenements.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Nom -->
        <div>
            <label for="nom" class="block font-semibold text-gray-700 mb-1">Nom de l'événement</label>
            <input type="text" name="nom_evenement" id="nom"
                   class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="date_debut" class="block font-semibold text-gray-700 mb-1">Date de début</label>
                <input type="date" name="date_debut" id="date_debut"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
            </div>
            <div>
                <label for="date_fin" class="block font-semibold text-gray-700 mb-1">Date de fin</label>
                <input type="date" name="date_fin" id="date_fin"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
            </div>
        </div>

        <!-- Adresse -->
        <div>
            <label for="adresse" class="block font-semibold text-gray-700 mb-1">Adresse</label>
            <input type="text" name="adresse" id="adresse"
                   class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
        </div>

        <!-- Salle -->
        <div>
            <label for="salle" class="block font-semibold text-gray-700 mb-1">Salle</label>
            <input type="text" name="salle" id="salle"
                   class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
        </div>

        <!-- Heures -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="heure_debut" class="block font-semibold text-gray-700 mb-1">Heure de début</label>
                <input type="time" name="heure_debut" id="heure_debut"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
            </div>
            <div>
                <label for="heure_fin" class="block font-semibold text-gray-700 mb-1">Heure de fin</label>
                <input type="time" name="heure_fin" id="heure_fin"
                       class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
            </div>
        </div>

        <!-- Nombre de billets -->
       <div class="mb-4">
        <label class="block font-semibold text-gray-700 mb-2">Nombre de billets par type</label>

        <!-- VIP -->
        <div class="mb-2">
            <label for="vip" class="block text-gray-600 mb-1">VIP</label>
            <input type="number" name="vip" id="vip" min="0" value="0"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-yellow-400 focus:outline-none">
        </div>

        <!-- Standard -->
        <div class="mb-2">
            <label for="standard" class="block text-gray-600 mb-1">Standard</label>
            <input type="number" name="standard" id="standard" min="0" value="0"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Gratuit -->
        <div class="mb-2">
            <label for="vvip" class="block text-gray-600 mb-1">VVIP</label>
            <input type="number" name="vvip" id="gratuit" min="0" value="0"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none">
        </div>
    </div>


         <div class="mb-6 bg-gray-50 p-4 rounded border border-gray-200 shadow-sm">
        <p class="text-sm font-semibold mb-2 text-gray-700">Ajouter un nouvel organisateur :</p>
      
            <input type="text" name="nom_organisateur" placeholder="Nom de l'organisateur"
                   class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
                   <input type="email" name="email_organisateur" placeholder="email"
                   class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
                   
                   <input type="phone" name="telephone" placeholder="telephone"
                   class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
    </div>

        <!-- Bouton Submit -->
        <div class="text-center">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Enregistrer l’événement
            </button>
        </div>
    </form>
</div>
@endsection
