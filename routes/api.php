<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DemandeEvenementController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/demande-evenement', [DemandeEvenementController::class, 'storeDemande']);


require __DIR__.'/evenementApi.php';
require __DIR__.'/billetApi.php';