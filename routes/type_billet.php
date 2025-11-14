<?php
use App\Http\Controllers\TypeBilletController;
use Illuminate\Support\Facades\Route;   

Route::prefix('type_billet')->name('type_billet.')->group(function () {
    Route::get('/', [TypeBilletController::class, 'index'])->name('index');
    Route::post('/', [TypeBilletController::class, 'store'])->name('store');
    Route::get('/{typeBillet}/edit', [TypeBilletController::class, 'edit'])->name('edit');
    Route::put('/{typeBillet}', [TypeBilletController::class, 'update'])->name('update');
    Route::delete('/{typeBillet}', [TypeBilletController::class, 'destroy'])->name('destroy');
})->middleware(['auth']);
