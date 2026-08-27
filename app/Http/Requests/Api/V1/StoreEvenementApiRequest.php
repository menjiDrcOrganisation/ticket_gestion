<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\StoreEvenementRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEvenementApiRequest extends StoreEvenementRequest
{
    protected function failedValidation(Validator $validator)
    {
        if (!$this->expectsJson() && !$this->is('api/*')) {
            parent::failedValidation($validator);
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $validator->errors(),
        ], 422));
    }
}
