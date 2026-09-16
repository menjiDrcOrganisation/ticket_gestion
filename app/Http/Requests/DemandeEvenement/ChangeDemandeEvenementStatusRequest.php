<?php

namespace App\Http\Requests\DemandeEvenement;

use Illuminate\Foundation\Http\FormRequest;

class ChangeDemandeEvenementStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => 'required|string|in:en_attente,valide,ferme',
        ];
    }
}
