@extends('layouts.main') @section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">
        Créer un Événement
    </h2>

    <div id="create_type"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">ajouter un type billet</h3>
            <form action="{{route('type_billet.store')}}" method="POST"
                >
                @csrf
                <label for="nom"> Ajouter un type</label>
                <input type="text" name="nom_type" value=""
                    placeholder="Nom de l'événement" id="nom" class="w-full border rounded px-3 py-2 mb-2" required>
            
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('create_type')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ajouter</button>
                </div>
            </form>
        </div>
    </div>


<form enctype="multipart/form-data" action="{{ route('evenements.store') }}" method="POST" class="space-y-5">
    @csrf

    <!-- Nom -->
    <div>
        <label for="nom" class="block font-semibold text-gray-700 mb-1">Nom de l'événement</label>
        <input type="text" name="nom_evenement" id="nom"
               value="{{ old('nom_evenement') }}"
               class="w-full border border-gray-300 rounded p-2">

        @error('nom_evenement')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Dates -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Date de début</label>
            <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                   class="w-full border border-gray-300 rounded p-2">

            @error('date_debut')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Date de fin</label>
            <input type="date" name="date_fin" value="{{ old('date_fin') }}"
                   class="w-full border border-gray-300 rounded p-2">

            @error('date_fin')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Adresse -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Adresse</label>
        <input type="text" name="adresse" value="{{ old('adresse') }}"
               class="w-full border border-gray-300 rounded p-2">

        @error('adresse')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Salle -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Salle</label>
        <input type="text" name="salle" value="{{ old('salle') }}"
               class="w-full border border-gray-300 rounded p-2">

        @error('salle')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Heures -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Heure de début</label>
            <input type="time" name="heure_debut" value="{{ old('heure_debut') }}"
                   class="w-full border border-gray-300 rounded p-2">

            @error('heure_debut')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Heure de fin</label>
            <input type="time" name="heure_fin" value="{{ old('heure_fin') }}"
                   class="w-full border border-gray-300 rounded p-2">

            @error('heure_fin')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <h2>Type billet</h2>
    <div class="flex">
        <select id="billet_type" class="w-full border border-gray-300 rounded p-2">
            @foreach ($typeBillets as $ticket)
                <option value="{{ $ticket['id'] }}-ticket">{{ $ticket->nom_type }}</option>
            @endforeach
        </select>
        <div onclick="openModal('create_type')" class="text-3xl cursor-pointer">+</div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($typeBillets as $index => $ticket)
            <div class="hidden type_billet_element" id="{{ $ticket['id'] }}-ticket">
                <div class="flex justify-between">
                    <label class="block text-gray-600 font-medium mb-2">{{ $ticket->nom_type }}</label>
                    <div class="close cursor-pointer">X</div>
                </div>

                <input type="hidden" name="ticket_type_id[]" value="{{ $ticket['id'] }}" />

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Quantité</label>
                        <input type="number" name="quantite[]"
                               value="{{ old('quantite.'.$index) }}"
                               min="0"
                               class="w-full border border-gray-300 rounded p-2">

                        @error("quantite.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Prix</label>
                        <input type="number" name="prix[]"
                               value="{{ old('prix.'.$index) }}"
                               min="0"
                               class="w-full border border-gray-300 rounded p-2">

                        @error("prix.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Devise</label>
                        <select name="devise[]" class="w-full border border-gray-300 rounded p-2">
                            <option value="CDF">CDF</option>
                            <option value="USD">USD</option>
                        </select>

                        @error("devise.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    <p class="text-sm font-semibold mb-2 text-gray-700">Ajouter un nouvel organisateur :</p>

    <div class="mb-6 p-4 rounded border border-gray-200 shadow-sm">
        <input type="text" name="nom_organisateur"
               value="{{ old('nom_organisateur') }}"
               placeholder="Nom de l'organisateur"
               class="w-full border border-gray-300 rounded p-2 mb-2">

        @error('nom_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

        <input type="email" name="email_organisateur"
               value="{{ old('email_organisateur') }}"
               placeholder="email"
               class="w-full border border-gray-300 rounded p-2 mb-2">

        @error('email_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

        <input type="text" name="telephone"
               value="{{ old('telephone') }}"
               placeholder="telephone"
               class="w-full border border-gray-300 rounded p-2">

        @error('telephone')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Partie artiste -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Nom de l'artiste</label>
        <input type="text" name="nom_artiste"
               value="{{ old('nom_artiste') }}"
               class="w-full border border-gray-300 rounded p-2">

        @error('nom_artiste')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Phrase d'accroche</label>
        <textarea name="acroche" class="w-full border border-gray-300 rounded p-2">{{ old('acroche') }}</textarea>

        @error('acroche')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">À propos</label>
        <textarea name="a_propos" class="w-full border border-gray-300 rounded p-2">{{ old('a_propos') }}</textarea>

        @error('a_propos')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Photo de l'affiche</label>
        <input type="file" name="photo_affiche"
               class="w-full border border-gray-300 rounded p-2">

        @error('photo_affiche')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div class="text-center">
        <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Enregistrer l’événement
        </button>
    </div>
</form>

</div>
<script>

    function openModal(id){document.getElementById(id).classList.remove('hidden');}
    function closeModal(id){document.getElementById(id).classList.add('hidden');}

    const type_billet_element = document.getElementsByClassName("type_billet_element")

    const type_billet_action = document.getElementById("billet_type")


    if (type_billet_action.options.length > 0) {
    type_billet_action.selectedIndex = 0;

    // Récupérer la valeur du premier
    const firstId = type_billet_action.value;

    // Afficher automatiquement le premier bloc
    const firstBlock = document.getElementById(firstId);
    if (firstBlock) firstBlock.classList.remove("hidden");
    }

    type_billet_action.addEventListener("change", function (e) {
        document.getElementById(e.target.value).classList.remove("hidden")
    })

    document.querySelectorAll(".type_billet_element .close").forEach(btn => {
        btn.addEventListener("click", function (e) {
            const parent = e.target.closest(".type_billet_element");
             parent.querySelectorAll("input").forEach(input => {
            input.value = 0;
        });

        parent.querySelectorAll("select").forEach(select => {
            select.selectedIndex = 0; 
        });
            parent.classList.add("hidden");
        });
    });
</script>

@endsection