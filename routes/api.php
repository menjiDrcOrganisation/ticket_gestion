<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DemandeEvenementController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/demande-evenement', [DemandeEvenementController::class, 'storeDemande']);

    require __DIR__.'/evenementApi.php';
    require __DIR__.'/billetApi.php';
});






