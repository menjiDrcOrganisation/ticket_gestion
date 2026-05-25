 <?php

use App\Http\Controllers\Admin\TransactionController;

Route::prefix('admin')
    ->group(function () {

        Route::get(
            '/transactions',
            [TransactionController::class, 'index']
        )->name('transactions.index');

        Route::get(
            '/transactions/{id}',
            [TransactionController::class, 'show']
        )->name('transactions.show');

        Route::post(
            '/transactions/{id}/force-generate',
            [TransactionController::class, 'forceGenerate']
        )->name('transactions.force-generate');

        Route::post(
            '/transactions/{id}/refund',
            [TransactionController::class, 'markAsRefunded']
        )->name('transactions.refund');
    });