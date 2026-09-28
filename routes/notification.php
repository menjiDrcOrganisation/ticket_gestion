<?php

use App\Http\Controllers\Web\Admin\NotificationController;
use App\Http\Middleware\AuditTrail;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Suivi de la file d'attente des notifications : réservé aux administrateurs.
Route::middleware(['auth', RoleMiddleware::class . ':admin'])
    ->prefix('admin/notifications')
    ->name('admin.notifications.')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/rejouer', [NotificationController::class, 'rejouer'])
            ->middleware([AuditTrail::class])
            ->name('rejouer');
        Route::post('/taches/{uuid}/rejouer', [NotificationController::class, 'rejouerTache'])
            ->middleware([AuditTrail::class])
            ->name('rejouerTache');
    });
