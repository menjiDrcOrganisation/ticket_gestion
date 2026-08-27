<?php

namespace App\Http\Requests\Retrait;

use Illuminate\Foundation\Http\FormRequest;

class StoreRetraitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organisateur_id' => 'required|exists:organisateurs,id',
            'nom_detenteur' => 'required|string|max:255',
            'montant' => 'required|numeric',
            'date' => 'required|date',
            'statut' => 'required|string|max:50',
        ];
    }
}
