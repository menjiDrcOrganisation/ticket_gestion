<?php

use App\Http\Controllers\Web\Admin\NotificationController;
use App\Http\Controllers\Web\Organisateur\BilletController;
use App\Http\Controllers\Web\Shared\HomeController;
use App\Http\Controllers\Web\Shared\ProfileController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', RoleMiddleware::class.':admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::post('/notifications', [NotificationController::class, 'store'])
            ->name('notifications.store');

        Route::post('/notifications/{campaign}/send', [NotificationController::class, 'send'])
            ->name('notifications.send');

        Route::post('/notifications/{campaign}/cancel', [NotificationController::class, 'cancel'])
            ->name('notifications.cancel');
    });

Route::get('/', [HomeController::class, 'home'])->name('home')->middleware(['auth']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/billet/regenerer/{id}', [BilletController::class, 'regenererDepuisDB'])
    ->name('billet.regenerer');

require __DIR__.'/auth.php';
require __DIR__.'/evenement.php';
require __DIR__.'/dmd_event.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/organisateur.php';
require __DIR__.'/scanneur.php';
require __DIR__.'/type_billet.php';
require __DIR__.'/portefeulle.php';
require __DIR__.'/user.php';
require __DIR__.'/retrait.php';
require __DIR__.'/event_sacnner.php';
require __DIR__.'/dmd_retrait.php';
require __DIR__.'/transaction.php';
require __DIR__.'/admin_organisateur.php';
require __DIR__.'/audit.php';
