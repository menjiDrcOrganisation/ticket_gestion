<?php 
use App\Http\Controllers\DemandeEvenementController;
use App\Http\Controllers\RetraitController; 
use Illuminate\Support\Facades\Route;

Route::get('/retraits', [RetraitController::class, 'index'])->name('retraits.index');
Route::post('/retraits', [RetraitController::class, 'store'])->name('retraits.store');
Route::get('/retraits/{retrait}/edit', [RetraitController::class, 'edit'])->name('retraits.edit');
Route::put('/retraits/{retrait}', [RetraitController::class, 'update'])->name('retraits.update');
Route::delete('/retraits/{retrait}', [RetraitController::class, 'destroy'])->name('retraits.destroy');  

Route::patch('/retraits/{id}/statut', [RetraitController::class, 'updateStatut'])
    ->name('retraits.updateStatut');



?>