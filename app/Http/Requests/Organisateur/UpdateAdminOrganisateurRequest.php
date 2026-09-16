<?php

namespace App\Http\Requests\Organisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminOrganisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organisateur = $this->route('organisateur');
        $userId = is_object($organisateur) ? $organisateur->user_id : null;

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'telephone' => 'required|string|max:15',
            'password' => 'nullable|string|min:8',
        ];
    }
}