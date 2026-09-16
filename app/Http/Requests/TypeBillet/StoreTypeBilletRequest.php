<?php

namespace App\Http\Requests\TypeBillet;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeBilletRequest extends FormRequest
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
