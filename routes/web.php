<?php

use App\Http\Controllers\Web\Shared\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\EvenementController;
use App\Http\Controllers\Web\Organisateur\EvenementBilletTypeBilletController;
use App\Http\Controllers\Web\Shared\HomeController;
use App\Http\Controllers\Web\Organisateur\BilletController;


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
require __DIR__ . '/evenement.php';
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
require  __DIR__.'/transaction.php';
require __DIR__.'/admin_organisateur.php';
require __DIR__.'/audit.php';






