<?php

namespace App\Http\Requests\Evenement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvenementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'adresse' => 'nullable|string|max:255',
            'salle' => 'nullable|string|max:255',
            'url_evenement' => 'nullable|string|max:255',
            'photo_affiche' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
