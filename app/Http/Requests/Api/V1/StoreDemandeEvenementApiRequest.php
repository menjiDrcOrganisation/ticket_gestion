<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeEvenementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_evenement' => 'required|string|max:255',
            'contact_organisateur' => 'required|string|max:255',
            'description' => 'required|string',
            'type_evenement' => 'required|string|max:255',
            'affiche' => 'nullable|image|max:2048',
            'statut' => 'required|in:en_attente,valide,ferme',
        ];
    }
}
