<?php

namespace App\Http\Requests\Organisateur;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminOrganisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'required|string|max:15',
        ];
    }
}