<?php

namespace App\Http\Requests\Evenement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvenementStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Valeurs autorisées par la colonne evenements.statut (enum).
            'statut' => 'required|string|in:encours,ferme',
        ];
    }
}
