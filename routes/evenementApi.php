<?php
use App\Http\Controllers\Api\V1\EvenementController;


Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::post('/', [EvenementController::class, 'store'])->name('store');
        Route::get('/{short_url}', [EvenementController::class, 'getEvenement'])->name('getEvenement');
        Route::get('/', [EvenementController::class, 'getAll'])->name('getAll');       
        });






