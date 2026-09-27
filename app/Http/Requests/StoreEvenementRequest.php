<?php

namespace App\Http\Requests;

use App\Services\EvenementCreationService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvenementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom_evenement' => 'required|string|max:255',
            'type_evenement_id' => 'nullable|integer|exists:type_evenements,id|required_without:type_evenement_nom',
            'type_evenement_nom' => 'nullable|string|max:255|required_without:type_evenement_id',
            // Nom et téléphone ne sont exigés que pour un nouvel organisateur :
            // un organisateur existant est identifié par son e-mail et ses informations sont réutilisées.
            'nom_organisateur' => [Rule::requiredIf(fn () => !$this->organisateurExiste()), 'nullable', 'string', 'max:255'],
            'email_organisateur' => ['required', 'email', 'max:255', function (string $attribute, mixed $value, Closure $fail) {
                $user = EvenementCreationService::findUserByEmail($value);
                if ($user && $user->role !== 'organisateur') {
                    $fail('Cette adresse e-mail est déjà utilisée par un compte qui n\'est pas un organisateur.');
                }
            }],
            'adresse' => 'required|string|max:255',
            'salle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'ticket_type_id' => 'required|array|min:1',
            'ticket_type_id.*' => 'required|integer|distinct|exists:type_billets,id',
            'quantite' => 'required|array',
            'quantite.*' => 'nullable|integer|min:0',
            'prix' => 'required|array',
            'prix.*' => 'nullable|numeric|min:0',
            'telephone' => [Rule::requiredIf(fn () => !$this->organisateurExiste()), 'nullable', 'string', 'max:30'],
            'nom_artiste'=> 'required|string|max:255',
            'acroche'=> 'required|string|max:255',
            'a_propos'=> 'required|string',
            'photo_affiche'=> 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'devise'=> 'required|array',
            'devise.*' => 'required|in:USD,CDF',
        ];
    }

    private ?bool $organisateurExiste = null;

    /**
     * Indique si l'e-mail saisi correspond déjà à un compte organisateur.
     */
    protected function organisateurExiste(): bool
    {
        return $this->organisateurExiste ??= EvenementCreationService::findUserByEmail($this->input('email_organisateur'))?->role === 'organisateur';
    }
}
