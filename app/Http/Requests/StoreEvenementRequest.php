<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvenementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom_evenement' => 'required|string|max:255',
            'nom_organisateur' => 'nullable|string|max:255',
            'email_organisateur' => 'nullable|string|max:255|unique:users,email',
            'adresse' => 'required|string|max:255',
            'salle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'ticket_type_id' => 'required|array',
            'quantite' => 'required|array',
            'prix' => 'required|array',
            'telephone' => 'required',
            'nom_artiste'=> 'required',
            'acroche'=> 'required',
            'a_propos'=> 'required',
            'photo_affiche'=> 'required',
            'devise'=> 'required|array'
        ];
    }
}
