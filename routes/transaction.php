<?php

use App\Http\Controllers\Web\Admin\TransactionController;
use App\Http\Middleware\AuditTrail;
use App\Http\Middleware\RoleMiddleware;

// Supervision des transactions : réservée aux administrateurs.
Route::prefix('admin')
    ->middleware(['auth', RoleMiddleware::class . ':admin'])
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
        )->middleware([AuditTrail::class])->name('transactions.force-generate');

        Route::post(
            '/transactions/{id}/refund',
            [TransactionController::class, 'markAsRefunded']
        )->middleware([AuditTrail::class])->name('transactions.refund');
    });
