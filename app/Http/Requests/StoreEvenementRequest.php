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
            'nom_organisateur' => 'required|string|max:255',
            'email_organisateur' => 'required|email|max:255|unique:users,email',
            'adresse' => 'required|string|max:255',
            'salle' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'ticket_type_id' => 'required|array|min:1',
            'ticket_type_id.*' => 'required|integer|distinct|exists:type_billets,id',
            'quantite' => 'required|array',
            'quantite.*' => 'nullable|integer|min:0',
            'prix' => 'required|array',
            'prix.*' => 'nullable|numeric|min:0',
            'telephone' => 'required|string|max:30',
            'nom_artiste'=> 'required|string|max:255',
            'acroche'=> 'required|string|max:255',
            'a_propos'=> 'required|string',
            'photo_affiche'=> 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'devise'=> 'required|array',
            'devise.*' => 'required|in:USD,CDF',
        ];
    }
}
