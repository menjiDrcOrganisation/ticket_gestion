<?php

namespace App\Http\Requests\TypeEvenement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeEvenementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_type' => 'sometimes|required|string|max:255',
        ];
    }
}
