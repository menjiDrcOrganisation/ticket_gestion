<?php

use App\Http\Controllers\Web\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:manage-notifications'])
    ->prefix('admin/notifications')
    ->name('admin.notifications.')
    ->controller(NotificationController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/{campaign}/send', 'send')->name('send');
        Route::post('/{campaign}/cancel', 'cancel')->name('cancel');
    });
