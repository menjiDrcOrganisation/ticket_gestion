<?php

namespace App\Http\Requests\DemandeEvenement;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeEvenementRequest extends FormRequest
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
            'statut' => 'required|string|in:en_attente,valide,ferme',
            'affiche' => 'nullable|image|max:2048',
        ];
    }
}
