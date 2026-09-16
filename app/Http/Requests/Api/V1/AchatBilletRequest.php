<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AchatBilletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_billet' => 'required|string',
            'nombre_reel' => 'required|integer|min:1',
            'nom_complet_client' => 'required|string',
            'numero_client' => 'required|string',
            'service' => 'required|string',
            'id_evenement' => 'required|string',
            'devise' => 'required|string',
        ];
    }
}
