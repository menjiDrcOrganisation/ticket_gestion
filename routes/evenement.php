

<?php
use App\Http\Controllers\EvenementController;
Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::get('/', [EvenementController::class, 'index'])->name('index');      
        Route::get('/create', [EvenementController::class, 'create'])->name('create');  
        Route::post('/', [EvenementController::class, 'store'])->name('store');         
        Route::get('/{id}', [EvenementController::class, 'show'])->name('show');        
        Route::get('/{id}/edit', [EvenementController::class, 'edit'])->name('edit');   
        Route::put('/{id}', [EvenementController::class, 'update'])->name('update');    
        Route::delete('/{id}', [EvenementController::class, 'destroy'])->name('destroy');
        Route::patch('/evenements/{id}/update-status', [EvenementController::class, 'updateStatus'])->name('updateStatus');
    })->middleware(['auth']);
