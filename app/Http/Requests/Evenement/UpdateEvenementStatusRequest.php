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
            'statut' => 'required|string|in:encours,ferme,actif,inactif',
        ];
    }
}
