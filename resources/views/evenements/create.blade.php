@extends('layouts.main') @section('content')
<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow-md mt-8">
    <h2 class="text-3xl font-bold mb-6 text-left text-gray-800">
        Créer un Événement
    </h2>

    <div id="create_type"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">ajouter un type billet</h3>
            <form id="create-type-form" action="{{ route('type_billet.store') }}" method="POST">
                @csrf
                <label for="nom"> Ajouter un type</label>
                <x-app-input type="text" name="nom_type" value=""
                    placeholder="Nom du type" id="create-type-nom" required wrapperClass="mb-2" />
                <p id="create-type-error" class="hidden text-sm text-red-600 mb-2"></p>
            
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('create_type')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Annuler</button>
                    <button id="create-type-submit" type="submit"
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

<form id="evenement-form" enctype="multipart/form-data" action="{{ route('evenements.web.store') }}" method="POST" class="space-y-5">
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
         <x-app-input type="text" name="nom_evenement" id="nom"
             :value="old('nom_evenement')"
             wrapperClass="" inputClass="rounded p-2" />

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
        <x-app-select id="type_evenement_select" name="type_evenement_id" wrapperClass="" inputClass="rounded p-2">
            <option value="">Selectionner un type</option>
            @foreach(($typeEvenements ?? []) as $typeEvenement)
                <option value="{{ $typeEvenement->id }}" {{ (string) $selectedTypeId === (string) $typeEvenement->id ? 'selected' : '' }}>
                    {{ $typeEvenement->nom_type }}
                </option>
            @endforeach
            <option value="other" {{ $selectedIsOther ? 'selected' : '' }}>Autre</option>
        </x-app-select>

        <div id="type_evenement_other_wrapper" class="mt-3 {{ $selectedIsOther ? '' : 'hidden' }}">
            <label for="type_evenement_nom" class="block font-semibold text-gray-700 mb-1">Préciser le type</label>
                 <x-app-input type="text" name="type_evenement_nom" id="type_evenement_nom"
                     :value="old('type_evenement_nom')"
                     placeholder="Ex: Concert, Conférence, Festival"
                     wrapperClass="" inputClass="rounded p-2" />
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
             <x-app-input type="date" name="date_debut" :value="old('date_debut')"
                 wrapperClass="" inputClass="rounded p-2" />

            @error('date_debut')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Date de fin</label>
             <x-app-input type="date" name="date_fin" :value="old('date_fin')"
                 wrapperClass="" inputClass="rounded p-2" />

            @error('date_fin')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Adresse -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Adresse</label>
         <x-app-input type="text" name="adresse" :value="old('adresse')"
             wrapperClass="" inputClass="rounded p-2" />

        @error('adresse')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Salle -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Salle</label>
         <x-app-input type="text" name="salle" :value="old('salle')"
             wrapperClass="" inputClass="rounded p-2" />

        @error('salle')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Heures -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Heure de début</label>
             <x-app-input type="time" name="heure_debut" :value="old('heure_debut')"
                 wrapperClass="" inputClass="rounded p-2" />

            @error('heure_debut')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Heure de fin</label>
             <x-app-input type="time" name="heure_fin" :value="old('heure_fin')"
                 wrapperClass="" inputClass="rounded p-2" />

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
        <x-app-select id="billet_type" wrapperClass="" inputClass="rounded p-2">
            @foreach ($typeBillets as $ticket)
                <option value="{{ $ticket['id'] }}-ticket">{{ $ticket->nom_type }}</option>
            @endforeach
        </x-app-select>
        <button type="button" onclick="openModal('create_type')" class="ml-2 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50" aria-label="Ajouter un type de billet">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
            </svg>
        </button>
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
                    <button type="button" class="close-ticket inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="Fermer {{ $ticket->nom_type }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <input type="hidden" name="ticket_type_id[]" value="{{ $ticketId }}" class="ticket-id-input" {{ $isCardActive ? '' : 'disabled' }} />

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Quantité</label>
                           <x-app-input type="number" name="quantite[{{ $ticketId }}]"
                               :value="$oldQuantite"
                               min="1"
                               inputClass="rounded p-2"
                               wrapperClass=""
                               :required="$isCardActive"
                               :disabled="!$isCardActive" />

                        @error("quantite.$ticketId")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        @error("quantite.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Prix</label>
                           <x-app-input type="number" name="prix[{{ $ticketId }}]"
                               :value="$oldPrix"
                               min="1"
                               inputClass="rounded p-2"
                               wrapperClass=""
                               :required="$isCardActive"
                               :disabled="!$isCardActive" />

                        @error("prix.$ticketId")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        @error("prix.$index")
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 text-sm mb-1">Devise</label>
                        <x-app-select name="devise[{{ $ticketId }}]" wrapperClass="" inputClass="rounded p-2" :required="$isCardActive" :disabled="!$isCardActive">
                            <option value="CDF" {{ $oldDevise === 'CDF' ? 'selected' : '' }}>CDF</option>
                            <option value="USD" {{ $oldDevise === 'USD' ? 'selected' : '' }}>USD</option>
                        </x-app-select>

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
         <x-app-input type="text" name="nom_organisateur"
             :value="old('nom_organisateur')"
             placeholder="Nom de l'organisateur"
             inputClass="rounded p-2" wrapperClass="mb-2" required />

        @error('nom_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

         <x-app-input type="email" name="email_organisateur"
             :value="old('email_organisateur')"
             placeholder="email"
             inputClass="rounded p-2" wrapperClass="mb-2" required />

        @error('email_organisateur')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

         <x-app-input type="text" name="telephone"
             :value="old('telephone')"
             placeholder="telephone"
             inputClass="rounded p-2" wrapperClass="" required />

        @error('telephone')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <!-- Partie artiste -->
    <div>
        <label class="block font-semibold text-gray-700 mb-1">Nom de l'artiste</label>
         <x-app-input type="text" name="nom_artiste"
             :value="old('nom_artiste')"
             wrapperClass="" inputClass="rounded p-2" />

        @error('nom_artiste')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Phrase d'accroche</label>
        <x-app-textarea name="acroche" :value="old('acroche')" wrapperClass="" inputClass="rounded p-2" />

        @error('acroche')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">À propos</label>
        <x-app-textarea name="a_propos" :value="old('a_propos')" wrapperClass="" inputClass="rounded p-2" />

        @error('a_propos')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Photo de l'affiche</label>
        <x-app-file-input name="photo_affiche" wrapperClass="" inputClass="rounded p-2" />

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
    const createTypeForm = document.getElementById('create-type-form');
    const createTypeInput = document.getElementById('create-type-nom');
    const createTypeError = document.getElementById('create-type-error');
    const createTypeSubmit = document.getElementById('create-type-submit');

    function ticketCloseIconSvg() {
        return `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;
    }

    function createTicketCard(ticketId, ticketLabel) {
        const container = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-3.gap-4');
        if (!container) {
            return null;
        }

        const fieldClass = 'w-full rounded-xl border border-slate-200 bg-white/90 px-3.5 py-2.5 text-sm text-slate-800 shadow-sm transition duration-200 placeholder:text-slate-400 hover:border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400';

        const card = document.createElement('div');
        card.id = `${ticketId}-ticket`;
        card.className = 'type_billet_element hidden';
        card.innerHTML = `
            <div class="flex justify-between">
                <label class="block text-gray-600 font-medium mb-2">${ticketLabel}</label>
                <button type="button" class="close-ticket inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="Fermer ${ticketLabel}">
                    ${ticketCloseIconSvg()}
                </button>
            </div>

            <input type="hidden" name="ticket_type_id[]" value="${ticketId}" class="ticket-id-input" disabled>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-gray-500 text-sm mb-1">Quantité</label>
                    <input type="number" name="quantite[${ticketId}]" min="1" class="${fieldClass}" disabled>
                </div>

                <div>
                    <label class="block text-gray-500 text-sm mb-1">Prix</label>
                    <input type="number" name="prix[${ticketId}]" min="1" class="${fieldClass}" disabled>
                </div>

                <div>
                    <label class="block text-gray-500 text-sm mb-1">Devise</label>
                    <select name="devise[${ticketId}]" class="${fieldClass}" disabled>
                        <option value="CDF" selected>CDF</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
            </div>
        `;

        container.appendChild(card);
        return card;
    }

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

    function bindCloseHandlers(scope = document) {
        scope.querySelectorAll('.type_billet_element .close-ticket').forEach((btn) => {
            if (btn.dataset.bound === '1') {
                return;
            }

            btn.dataset.bound = '1';
            btn.addEventListener('click', function (e) {
                const parent = e.target.closest('.type_billet_element');
                if (!parent) {
                    return;
                }

                parent.querySelectorAll('input[type="number"]').forEach((input) => {
                    input.value = '';
                });

                parent.querySelectorAll('select').forEach((select) => {
                    select.selectedIndex = 0;
                });

                setCardState(parent, false);
            });
        });
    }

    bindCloseHandlers();

    if (createTypeForm) {
        createTypeForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (createTypeError) {
                createTypeError.textContent = '';
                createTypeError.classList.add('hidden');
            }

            const formData = new FormData(createTypeForm);
            const actionUrl = createTypeForm.getAttribute('action');

            createTypeSubmit?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMsg = payload?.errors?.nom_type?.[0];
                    throw new Error(validationMsg || payload?.message || 'Erreur lors de la creation du type.');
                }

                const created = payload?.data;
                if (!created?.id || !created?.nom_type) {
                    throw new Error('Reponse invalide du serveur.');
                }

                const optionValue = `${created.id}-ticket`;
                const option = document.createElement('option');
                option.value = optionValue;
                option.textContent = created.nom_type;
                typeBilletAction.appendChild(option);
                typeBilletAction.value = optionValue;

                let card = document.getElementById(optionValue);
                if (!card) {
                    card = createTicketCard(created.id, created.nom_type);
                    if (card) {
                        bindCloseHandlers(card);
                    }
                }

                setCardState(card, true);

                if (createTypeInput) {
                    createTypeInput.value = '';
                }

                closeModal('create_type');
            } catch (error) {
                if (createTypeError) {
                    createTypeError.textContent = error.message || 'Une erreur est survenue.';
                    createTypeError.classList.remove('hidden');
                }
            } finally {
                createTypeSubmit?.removeAttribute('disabled');
            }
        });
    }

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