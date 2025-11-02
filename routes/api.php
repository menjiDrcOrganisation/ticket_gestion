<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\DemandeEvenementController;

Route::post('/demande-evenement', [DemandeEvenementController::class, 'storeDemande']);

require __DIR__.'/evenementApi.php';