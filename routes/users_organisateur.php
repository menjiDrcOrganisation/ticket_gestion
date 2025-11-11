<?php 
use App\Http\Controllers\OrganisateurController;
use Illuminate\Support\Facades\Route;
Route::get('/organisateurs', [OrganisateurController::class, 'users_organisateur'])->name('organisateurs.index');
Route::post('/organisateurs', [OrganisateurController::class, 'store'])->name('organisateurs.store');
Route::get('/organisateurs/{organisateur}/edit', [OrganisateurController::class, 'edit'])->name('organisateurs.edit');
Route::put('/organisateurs/{organisateur}', [OrganisateurController::class, 'update'])->name('organisateurs.update');
Route::delete('/organisateurs/{organisateur}', [OrganisateurController::class, 'destroy'])->name('organisateurs.destroy');  
?>