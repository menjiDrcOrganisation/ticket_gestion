<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InitierTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_billet' => 'required|integer',
            'nombre_reel' => 'required|integer|min:1',
            'nom_complet_client' => 'required|string|max:255',
            'numero_client' => 'required|string|max:25',
            'service' => 'required|string|max:50',
            'id_evenement' => 'required|integer',
            'devise' => 'required|string|in:CDF,USD',
        ];
    }
}
