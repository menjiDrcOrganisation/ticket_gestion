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


    <!-- Formulaire Événement -->
    <form enctype="multipart/form-data" action="{{ route('evenements.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Nom -->
        <div>
            <label for="nom" class="block font-semibold text-gray-700 mb-1">Nom de l'événement</label>
            <input type="text" name="nom_evenement" id="nom"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required />
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="date_debut" class="block font-semibold text-gray-700 mb-1">Date de début</label>
                <input type="date" name="date_debut" id="date_debut"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
            </div>
            <div>
                <label for="date_fin" class="block font-semibold text-gray-700 mb-1">Date de fin</label>
                <input type="date" name="date_fin" id="date_fin"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
            </div>
        </div>

        <!-- Adresse -->
        <div>
            <label for="adresse" class="block font-semibold text-gray-700 mb-1">Adresse</label>
            <input type="text" name="adresse" id="adresse"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required />
        </div>

        <!-- Salle -->
        <div>
            <label for="salle" class="block font-semibold text-gray-700 mb-1">Salle</label>
            <input type="text" name="salle" id="salle"
                class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required />
        </div>

        <!-- Heures -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="heure_debut" class="block font-semibold text-gray-700 mb-1">Heure de début</label>
                <input type="time" name="heure_debut" id="heure_debut"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
            </div>
            <div>
                <label for="heure_fin" class="block font-semibold text-gray-700 mb-1">Heure de fin</label>
                <input type="time" name="heure_fin" id="heure_fin"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
            </div>
        </div>

        <h2>Type billet</h2>
        <div class="flex">
            <select id="billet_type" class="w-full border border-gray-300 rounded p-2  focus:outline-none">
                @foreach ($typeBillets as $ticket)
                <option value="{{$ticket['id']}}-ticket">{{$ticket->nom_type}}</option>
                @endforeach
            </select>
            <div onclick="openModal('create_type')" class="text-3xl">+</div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($typeBillets as $ticket)
            <div class="hidden type_billet_element " id="{{$ticket['id']}}-ticket">
                <div class="flex justify-between">
                    <label class="block text-gray-600 font-medium mb-2">{{ $ticket->nom_type }}</label>
                    <div class="close">X</div>
                </div>

                <input type="hidden" name="ticket_type_id[]" value="{{ $ticket['id'] }}" />
                <div class="grid grid-cols-2 gap-2 ">
                    <div>
                        <label for="quantite_{{ $ticket['id'] }}"
                            class="block text-gray-500 text-sm mb-1">Quantité</label>
                        <input type="number" name="quantite[]" id="quantite_{{ $ticket['id'] }}" min="0" value="0"
                            placeholder="Nombre"
                            class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-{{ $ticket['color'] }}-400 focus:outline-none" />
                    </div>
                    <div>
                        <label for="prix_{{ $ticket['id'] }}" class="block text-gray-500 text-sm mb-1">Prix</label>
                        <input type="number" name="prix[]" id="prix_{{ $ticket['id'] }}" min="0" value="0"
                            placeholder="Prix"
                            class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-{{ $ticket['color'] }}-400 focus:outline-none" />
                    </div>

                    <div>
                        <label for="devise" class="block text-gray-500 text-sm mb-1">Devise</label>
                        <select name="devise[]" id="devise"
                            class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-{{ $ticket['color'] }}-400 focus:outline-none">
                            <option value="CDF">CDF</option>
                            <option value="USD">USD</option>

                        </select>

                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-sm font-semibold mb-2 text-gray-700">
            Ajouter un nouvel organisateur :
        </p>

        <div class="mb-6 p-4 rounded border border-gray-200 shadow-sm">

            <p>
                Les informations de l'organisateur

            </p>
            <input type="text" name="nom_organisateur" placeholder="Nom de l'organisateur"
                class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                required />
            <input type="email" name="email_organisateur" placeholder="email"
                class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                required />

            <input type="phone" name="telephone" placeholder="telephone"
                class="flex-1 border border-gray-300 rounded p-2 focus:ring-2 focus:ring-green-400 focus:outline-none"
                required />
        </div>


        <!-- Partie artiste -->

        <div>
            <div>
                <label for="nom_artiste" class="block font-semibold text-gray-700 mb-1">Nom de l'artiste</label>
                <input name="nom_artiste" id="nom_artiste"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />

            </div>

            <div>
                <label for="acroche" class="block font-semibold text-gray-700 mb-1">Phrase d'acroche</label>
                <textarea name="acroche" id="acroche"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
                </textarea>
            </div>


            <div>
                <label for="a_propos" class="block font-semibold text-gray-700 mb-1">A propos</label>
                <textarea name="a_propos" id="a_propos"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
                </textarea>
            </div>

            <div>
                <label for="nom_artiste" class="block font-semibold text-gray-700 mb-1">Photo de l'affiche</label>
                <input name="photo_affiche" id="photo_affiche" type="file"
                    class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    required />
                </textarea>
            </div>
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
<script>

    function openModal(id){document.getElementById(id).classList.remove('hidden');}
    function closeModal(id){document.getElementById(id).classList.add('hidden');}

    const type_billet_element = document.getElementsByClassName("type_billet_element")

    const type_billet_action = document.getElementById("billet_type")
    type_billet_action.addEventListener("change", function (e) {
        document.getElementById(e.target.value).classList.remove("hidden")
    })

    document.querySelectorAll(".type_billet_element .close").forEach(btn => {
        btn.addEventListener("click", function (e) {
            const parent = e.target.closest(".type_billet_element");
            parent.classList.add("hidden");
        });
    });

</script>
@endsection