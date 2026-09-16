<?php

use App\Http\Controllers\Web\Admin\OrganisateurController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin/organisateurs')->name('admin.organisateurs.')->group(function () {
    Route::get('/', [OrganisateurController::class, 'index'])->name('index');
    Route::post('/', [OrganisateurController::class, 'store'])->middleware([AuditTrail::class])->name('store');
    Route::put('/{organisateur}', [OrganisateurController::class, 'update'])->middleware([AuditTrail::class])->name('update');
    Route::delete('/{organisateur}', [OrganisateurController::class, 'destroy'])->middleware([AuditTrail::class])->name('destroy');
});