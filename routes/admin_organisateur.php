<?php

use App\Http\Controllers\Web\Admin\OrganisateurController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/organisateurs')->name('admin.organisateurs.')->group(function () {
    Route::get('/', [OrganisateurController::class, 'index'])->name('index');
    Route::post('/', [OrganisateurController::class, 'store'])->name('store');
    Route::put('/{organisateur}', [OrganisateurController::class, 'update'])->name('update');
    Route::delete('/{organisateur}', [OrganisateurController::class, 'destroy'])->name('destroy');
});