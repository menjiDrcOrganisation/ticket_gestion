<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\StoreEvenementRequest;
use Illuminate\Contracts\Validations\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEvenementApiRequest extends StoreEvenementRequest
{
    public function expectsJson(): bool
    {
        return true;
    }

    public function wantsJson(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $validator->errors(),
        ], 422));
    }
}
