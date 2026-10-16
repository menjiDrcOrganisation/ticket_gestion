@props([
    'name' => null,
    'id' => null,
    'type' => null,
    'required' => false,
    'disabled' => false,
    'accept' => null,
    'wrapperClass' => 'mb-3',
    'inputClass' => '',
])

@php
    $fileId = $id ?? $name;
    $baseClasses = trim('w-full border border-slate-300 rounded px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-slate-700 hover:file:bg-slate-200 ' . $inputClass);

    // Règles partagées avec la validation backend (config/uploads.php).
    $typeFichier = $type
        ? \App\Support\TypeFichier::get($type)
        : ($name ? \App\Support\TypeFichier::pourChamp($name) : null);
    $accept = $accept ?? $typeFichier?->accept();
@endphp

<div class="{{ $wrapperClass }}">
    <input
        type="file"
        @if($fileId) id="{{ $fileId }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($accept) accept="{{ $accept }}" @endif
        @if($typeFichier)
            data-extensions="{{ implode(',', $typeFichier->extensions) }}"
            data-max-octets="{{ $typeFichier->maxOctets }}"
            data-message-format="{{ $typeFichier->messageFormat() }}"
            data-message-taille="{{ $typeFichier->messageTaille() }}"
            onchange="validerFichierTeleverse(this)"
        @endif
        @required($required)
        @disabled($disabled)
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >

    @if($typeFichier)
        <p class="mt-1 text-xs text-slate-500">
            Formats acceptés : {{ $typeFichier->formatsLisibles() }} — {{ $typeFichier->tailleLisible() }} maximum.
        </p>
        <p class="mt-1 hidden text-sm text-red-600" data-erreur-fichier role="alert"></p>
    @endif

    @if($name)
        @error($name)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

@once
    <script>
        function validerFichierTeleverse(input) {
            const erreur = input.parentElement.querySelector('[data-erreur-fichier]');
            const fichier = input.files[0];
            let message = '';

            if (fichier) {
                const extensions = input.dataset.extensions.split(',');
                const extension = fichier.name.includes('.') ? fichier.name.split('.').pop().toLowerCase() : '';

                if (!extensions.includes(extension)) {
                    message = input.dataset.messageFormat;
                } else if (fichier.size > Number(input.dataset.maxOctets)) {
                    message = input.dataset.messageTaille;
                }
            }

            // Vider le champ : un fichier refusé ne doit jamais partir avec le formulaire.
            if (message) {
                input.value = '';
            }

            if (erreur) {
                erreur.textContent = message;
                erreur.classList.toggle('hidden', !message);
            }
        }
    </script>
@endonce
