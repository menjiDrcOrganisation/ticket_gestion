<?php

use App\Http\Controllers\Api\V1\BilletController;
use App\Http\Controllers\Api\V1\TransactionController;

Route::prefix('billet')->name('billet.')->group(function () {
    Route::post('/achatBillet', [BilletController::class, 'achatbillet'])
        ->name('achatbillet')
        ->middleware('throttle:paiement');
});

Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::post('/initier', [TransactionController::class, 'initier'])
        ->name('initier')
        ->middleware('throttle:paiement');
    Route::get('/{reference}/recapitulatif', [TransactionController::class, 'recapitulatif'])->name('recapitulatif');
    Route::post('/{reference}/valider-paiement', [TransactionController::class, 'validerPaiement'])
        ->name('valider_paiement')
        ->middleware('throttle:paiement');
    // Webhook du fournisseur : exclu du plafond général "api" (une confirmation perdue = un paiement sans billet),
    // mais borné par son propre limiteur.
    Route::post('/callback', [TransactionController::class, 'callback'])
        ->name('callback')
        ->middleware('throttle:webhook-paiement')
        ->withoutMiddleware('throttle:api');
    Route::get('/{reference}/confirmation', [TransactionController::class, 'confirmation'])->name('confirmation');
    Route::get('/{reference}/download-billet', [TransactionController::class, 'telechargerBillet'])->name('download');
});
