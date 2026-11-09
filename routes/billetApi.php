<?php

use App\Http\Controllers\Api\V1\BilletController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('billet')->name('billet.')->group(function () {
    Route::post('/achatBillet', [BilletController::class, 'achatbillet'])
        ->middleware('throttle:paiement')
        ->name('achatbillet');
});

Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::post('/initier', [TransactionController::class, 'initier'])
        ->middleware('throttle:paiement')
        ->name('initier');

    Route::get('/{reference}/recapitulatif', [TransactionController::class, 'recapitulatif'])
        ->name('recapitulatif');

    Route::post('/{reference}/valider-paiement', [TransactionController::class, 'validerPaiement'])
        ->middleware('throttle:paiement')
        ->name('valider_paiement');

    Route::post('/callback', [TransactionController::class, 'callback'])
        ->middleware('throttle:webhook-paiement')
        ->withoutMiddleware('throttle:api')
        ->name('callback');

    Route::get('/{reference}/confirmation', [TransactionController::class, 'confirmation'])
        ->name('confirmation');

    Route::get('/{reference}/download-billet', [TransactionController::class, 'telechargerBillet'])
        ->name('download');
});
