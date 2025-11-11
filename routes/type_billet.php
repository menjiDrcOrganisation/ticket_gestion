<?php
use App\Http\Controllers\TypeBilletController;
use Illuminate\Support\Facades\Route;   

Route::get('/type_billet', [TypeBilletController::class, 'index'])->name('type_billet.index');
Route::post('/type_billet', [TypeBilletController::class, 'store'])->name('type_billet.store');
Route::get('/type_billet/{typeBillet}/edit', [TypeBilletController::class, 'edit'])->name('type_billet.edit');
Route::put('/type_billet/{typeBillet}', [TypeBilletController::class, 'update'])->name('type_billet.update');
Route::delete('/type_billet/{typeBillet}', [TypeBilletController::class, 'destroy'])->name('type_billet.destroy');