<?php

namespace App\Rules;

use App\Support\TypeFichier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Valide un fichier téléversé selon son type défini dans config/uploads.php.
 *
 * Le type MIME est déterminé à partir du contenu réel du fichier (finfo),
 * pas de l'extension ni de l'en-tête envoyés par le client.
 */
class FichierTeleverse implements ValidationRule
{
    private TypeFichier $type;

    public function __construct(string $type)
    {
        $this->type = TypeFichier::get($type);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail("Le champ {$this->type->libelle} doit être un fichier.");

            return;
        }

        if (! $value->isValid()) {
            $fail(in_array($value->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? $this->type->messageTaille()
                : "Le fichier de {$this->type->libelle} n'a pas pu être téléversé. Veuillez réessayer.");

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());

        if (! in_array($extension, $this->type->extensions, true)
            || ! in_array($value->getMimeType(), $this->type->mimes, true)) {
            $fail($this->type->messageFormat());

            return;
        }

        if ($value->getSize() > $this->type->maxOctets) {
            $fail($this->type->messageTaille());

            return;
        }

        // Écarte les fichiers dont le contenu n'est pas une image décodable.
        if ($this->type->image && @getimagesize($value->getRealPath()) === false) {
            $fail($this->type->messageFormat());
        }
    }
}
