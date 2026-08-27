<?php

namespace App\Http\Requests\Organisateur;

use Illuminate\Foundation\Http\FormRequest;

class RegisterOrganisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'required|string|max:15',
            'role' => 'required|string|in:organisateur',
        ];
    }
}
