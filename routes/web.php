<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\EvenementBilletTypeBilletController;



Route::get('/', function () {
    return view('resume');
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/dashboard', [EvenementBilletTypeBilletController::class,  'index'])->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__ . '/evenement.php';
require __DIR__.'/dmd_event.php';
require __DIR__.'/organisateur.php';
require __DIR__.'/scanneur.php';