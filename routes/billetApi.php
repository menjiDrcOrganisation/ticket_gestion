
<?php
use App\Http\Controllers\Api\BilletController;
use App\Http\Controllers\Api\TransactionController;

Route::prefix('billet')->name('billet.')->group(function () {
        Route::post('/achatBillet', [BilletController::class, 'achatbillet'])->name('achatbillet');      
    });

Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::post('/initier', [TransactionController::class, 'initier'])->name('initier');
        Route::get('/{reference}/recapitulatif', [TransactionController::class, 'recapitulatif'])->name('recapitulatif');
        Route::post('/{reference}/valider-paiement', [TransactionController::class, 'validerPaiement'])->name('valider_paiement');
        Route::post('/callback', [TransactionController::class, 'callback'])->name('callback');
        Route::get('/{reference}/confirmation', [TransactionController::class, 'confirmation'])->name('confirmation');
        Route::get('/{reference}/download-billet', [TransactionController::class, 'telechargerBillet'])->name('download');
    });