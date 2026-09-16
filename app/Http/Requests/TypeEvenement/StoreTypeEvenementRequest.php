<?php

namespace App\Http\Requests\TypeEvenement;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeEvenementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_type' => 'required|string|max:255',
        ];
    }
}
