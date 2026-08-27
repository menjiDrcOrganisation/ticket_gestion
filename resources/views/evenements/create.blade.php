@extends('layouts.main') @section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">
        Créer un Événement
    </h2>

    <div id="create_type"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
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


@php
    $hasStepTwoErrors = $errors->hasAny([
        'ticket_type_id',
        'quantite',
        'prix',
        'devise',
        'nom_organisateur',
        'email_organisateur',
        'telephone',
        'nom_artiste',
        'acroche',
        'a_propos',
        'photo_affiche',
    ]);
@endphp

<form id="evenement-form" enctype="multipart/form-data" action="{{ route('evenements.store') }}" method="POST" class="space-y-5">
    @csrf

    <div class="mb-4">
        <div class="flex items-center gap-2 text-sm font-medium">
            <span id="wizard-dot-1" class="h-7 w-7 rounded-full bg-blue-600 text-white flex items-center justify-center">1</span>
            <span class="text-gray-600">Informations événement</span>
            <span class="text-gray-400">/</span>
            <span id="wizard-dot-2" class="h-7 w-7 rounded-full bg-gray-300 text-gray-700 flex items-center justify-center">2</span>
            <span class="text-gray-600">Billetterie et organisateur</span>
        </div>
    </div>

    <div id="step-1" class="space-y-5">

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

    @php
        $selectedTypeId = old('type_evenement_id');
        $selectedIsOther = old('type_evenement_nom') && !$selectedTypeId;
    @endphp
    <div>
        <label for="type_evenement_select" class="block font-semibold text-gray-700 mb-1">Type d'événement</label>
        <select id="type_evenement_select" name="type_evenement_id" class="w-full border border-gray-300 rounded p-2">
            <option value="">Selectionner un type</option>
            @foreach(($typeEvenements ?? []) as $typeEvenement)
                <option value="{{ $typeEvenement->id }}" {{ (string) $selectedTypeId === (string) $typeEvenement->id ? 'selected' : '' }}>
                    {{ $typeEvenement->nom_type }}
                </option>
            @endforeach
            <option value="other" {{ $selectedIsOther ? 'selected' : '' }}>Autre</option>
        </select>

        <div id="type_evenement_other_wrapper" class="mt-3 {{ $selectedIsOther ? '' : 'hidden' }}">
            <label for="type_evenement_nom" class="block font-semibold text-gray-700 mb-1">Préciser le type</label>
            <input type="text" name="type_evenement_nom" id="type_evenement_nom"
                   value="{{ old('type_evenement_nom') }}"
                   placeholder="Ex: Concert, Conférence, Festival"
                   class="w-full border border-gray-300 rounded p-2">
            <p class="text-xs text-gray-500 mt-1">Ce type sera créé automatiquement s'il n'existe pas.</p>
        </div>

        @error('type_evenement_nom')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
        @error('type_evenement_id')
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

    <div class="flex justify-end">
        <button id="go-step-2" type="button" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Suivant
        </button>
    </div>
    </div>

    <div id="step-2" class="space-y-5 hidden">
    <h2>Type billet</h2>
    @error('ticket_type_id')
        <p class="text-red-600 text-sm mb-2">{{ $message }}</p>
    @enderror
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
            @php
                $ticketId = $ticket['id'];
                $oldQuantite = old('quantite.' . $ticketId, old('quantite.' . $index));
                $oldPrix = old('prix.' . $ticketId, old('prix.' . $index));
                $oldDevise = old('devise.' . $ticketId, old('devise.' . $index, 'CDF'));
                $isCardActive = $oldQuantite !== null || $oldPrix !== null;
            @endphp
            <div class="{{ $isCardActive ? '' : 'hidden' }} type_billet_element" id="{{ $ticketId }}-ticket">
                <div class="flex justify-between">
                    <label class="block text-gray-600 font-medium mb-2">{{ $ticket->nom_type }}</label>
                    <div class="close cursor-pointer">X</div>
                </div>

                <input type="hidden" name="ticket_type_id[]" value="{{ $ticketId }}" class="ticket-id-input" {{ $isCardActive ? '' : 'disabled' }} />

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Quantité</label>
                        <input type="number" name="quantite[{{ $ticketId }}]"
                               value="{{ $oldQuantite }}"
                               min="1"
                               class="w-full border border-gray-300 rounded p-2"
                               {{ $isCardActive ? 'required' : 'disabled' }}>

                        @error("quantite.$ticketId")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        @error("quantite.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Prix</label>
                        <input type="number" name="prix[{{ $ticketId }}]"
                               value="{{ $oldPrix }}"
                               min="1"
                               class="w-full border border-gray-300 rounded p-2"
                               {{ $isCardActive ? 'required' : 'disabled' }}>

                        @error("prix.$ticketId")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        @error("prix.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Devise</label>
                        <select name="devise[{{ $ticketId }}]" class="w-full border border-gray-300 rounded p-2" {{ $isCardActive ? 'required' : 'disabled' }}>
                            <option value="CDF" {{ $oldDevise === 'CDF' ? 'selected' : '' }}>CDF</option>
                            <option value="USD" {{ $oldDevise === 'USD' ? 'selected' : '' }}>USD</option>
                        </select>

                        @error("devise.$ticketId")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
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
               class="w-full border border-gray-300 rounded p-2 mb-2" required>

        @error('nom_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

        <input type="email" name="email_organisateur"
               value="{{ old('email_organisateur') }}"
               placeholder="email"
             class="w-full border border-gray-300 rounded p-2 mb-2" required>

        @error('email_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

        <input type="text" name="telephone"
               value="{{ old('telephone') }}"
               placeholder="telephone"
             class="w-full border border-gray-300 rounded p-2" required>

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

    <div class="text-center flex justify-center gap-3">
        <button id="go-step-1" type="button" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300">
            Précédent
        </button>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Enregistrer l’événement
        </button>
    </div>
    </div>
</form>

</div>
<script>

    function openModal(id){
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id){
        document.getElementById(id).classList.remove('flex');
        document.getElementById(id).classList.add('hidden');
    }

    const createEventForm = document.getElementById('evenement-form');
    const typeBilletAction = document.getElementById('billet_type');
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const goStep2Btn = document.getElementById('go-step-2');
    const goStep1Btn = document.getElementById('go-step-1');
    const wizardDot1 = document.getElementById('wizard-dot-1');
    const wizardDot2 = document.getElementById('wizard-dot-2');
    const typeSelect = document.getElementById('type_evenement_select');
    const otherTypeWrapper = document.getElementById('type_evenement_other_wrapper');
    const otherTypeInput = document.getElementById('type_evenement_nom');

    function showStep(step) {
        const stepOneActive = step === 1;

        step1.classList.toggle('hidden', !stepOneActive);
        step2.classList.toggle('hidden', stepOneActive);

        wizardDot1.classList.toggle('bg-blue-600', stepOneActive);
        wizardDot1.classList.toggle('text-white', stepOneActive);
        wizardDot1.classList.toggle('bg-gray-300', !stepOneActive);
        wizardDot1.classList.toggle('text-gray-700', !stepOneActive);

        wizardDot2.classList.toggle('bg-blue-600', !stepOneActive);
        wizardDot2.classList.toggle('text-white', !stepOneActive);
        wizardDot2.classList.toggle('bg-gray-300', stepOneActive);
        wizardDot2.classList.toggle('text-gray-700', stepOneActive);
    }

    function syncTypeEvenementFields() {
        if (!typeSelect || !otherTypeWrapper) {
            return;
        }

        const isOther = typeSelect.value === 'other';

        otherTypeWrapper.classList.toggle('hidden', !isOther);

        if (isOther) {
            typeSelect.removeAttribute('name');
            if (otherTypeInput) {
                otherTypeInput.required = true;
            }
        } else {
            typeSelect.setAttribute('name', 'type_evenement_id');
            if (otherTypeInput) {
                otherTypeInput.required = false;
                if (!otherTypeInput.dataset.keepValue) {
                    otherTypeInput.value = '';
                }
            }
        }
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            if (otherTypeInput) {
                delete otherTypeInput.dataset.keepValue;
            }
            syncTypeEvenementFields();
        });

        if (otherTypeInput && otherTypeInput.value) {
            otherTypeInput.dataset.keepValue = '1';
        }
        syncTypeEvenementFields();
    }

    if (goStep2Btn) {
        goStep2Btn.addEventListener('click', function () {
            const requiredStep1Fields = [
                ...step1.querySelectorAll('input[name="nom_evenement"], input[name="date_debut"], input[name="date_fin"], input[name="adresse"], input[name="salle"], input[name="heure_debut"], input[name="heure_fin"]')
            ];

            const isTypeValid = (() => {
                if (!typeSelect) {
                    return true;
                }
                if (typeSelect.value === 'other') {
                    return !!otherTypeInput && otherTypeInput.value.trim() !== '';
                }
                return typeSelect.value !== '';
            })();

            const hasEmptyRequired = requiredStep1Fields.some((field) => !field.value);

            if (hasEmptyRequired || !isTypeValid) {
                alert('Veuillez compléter les informations de l\'étape 1 avant de continuer.');
                return;
            }

            showStep(2);
        });
    }

    if (goStep1Btn) {
        goStep1Btn.addEventListener('click', function () {
            showStep(1);
        });
    }

    showStep({{ $hasStepTwoErrors ? 2 : 1 }});

    function setCardState(card, isActive) {
        if (!card) return;

        card.classList.toggle('hidden', !isActive);

        const ticketIdInput = card.querySelector('.ticket-id-input');
        if (ticketIdInput) {
            ticketIdInput.disabled = !isActive;
        }

        card.querySelectorAll('input[type="number"], select').forEach((field) => {
            field.disabled = !isActive;
            field.required = isActive;
        });
    }

    if (typeBilletAction && typeBilletAction.options.length > 0) {
        typeBilletAction.selectedIndex = 0;

        const hasActiveCard = Array.from(document.querySelectorAll('.type_billet_element'))
            .some((card) => !card.classList.contains('hidden'));

        if (!hasActiveCard) {
            const firstId = typeBilletAction.value;
            const firstBlock = document.getElementById(firstId);
            setCardState(firstBlock, true);
        }

        typeBilletAction.addEventListener('change', function (e) {
            const selectedCard = document.getElementById(e.target.value);
            setCardState(selectedCard, true);
        });
    }

    document.querySelectorAll('.type_billet_element .close').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            const parent = e.target.closest('.type_billet_element');

            parent.querySelectorAll('input[type="number"]').forEach((input) => {
                input.value = '';
            });

            parent.querySelectorAll('select').forEach((select) => {
                select.selectedIndex = 0;
            });

            setCardState(parent, false);
        });
    });

    if (createEventForm) {
        createEventForm.addEventListener('submit', function (e) {
            const activeCards = Array.from(document.querySelectorAll('.type_billet_element'))
                .filter((card) => !card.classList.contains('hidden'));

            const hasValidTicket = activeCards.some((card) => {
                const qty = Number(card.querySelector('input[name^="quantite["]')?.value || 0);
                const price = Number(card.querySelector('input[name^="prix["]')?.value || 0);
                return qty > 0 && price > 0;
            });

            if (!hasValidTicket) {
                e.preventDefault();
                alert('Ajoutez au moins un type de billet avec une quantite et un prix superieurs a 0.');
            }
        });
    }
</script>

@endsection