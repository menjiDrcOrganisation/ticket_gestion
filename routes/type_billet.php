<?php
use App\Http\Controllers\Web\Admin\TypeBilletController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;   

Route::prefix('type_billet')->name('type_billet.')->group(function () {
    Route::get('/', [TypeBilletController::class, 'index'])->name('index');
    Route::post('/', [TypeBilletController::class, 'store'])->middleware([AuditTrail::class])->name('store');
    Route::get('/{typeBillet}/edit', [TypeBilletController::class, 'edit'])->name('edit');
    Route::put('/{typeBillet}', [TypeBilletController::class, 'update'])->middleware([AuditTrail::class])->name('update');
    Route::delete('/{typeBillet}', [TypeBilletController::class, 'destroy'])->middleware([AuditTrail::class])->name('destroy');
})->middleware(['auth']);






