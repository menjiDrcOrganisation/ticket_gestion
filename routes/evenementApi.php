<?php
use App\Http\Controllers\Api\EvenementController;


Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::get('/{short_url}', [EvenementController::class, 'getEvenement'])->name('getEvenement');
        Route::get('/', [EvenementController::class, 'getAll'])->name('getAll');       
      
    });