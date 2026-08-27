

<?php
use App\Http\Controllers\Web\Admin\EvenementController;
use App\Http\Middleware\AuditTrail;
Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::get('/', [EvenementController::class, 'index'])->name('index');      
        Route::get('/create', [EvenementController::class, 'create'])->name('create');  
        Route::post('/', [EvenementController::class, 'store'])->middleware([AuditTrail::class])->name('web.store');         
        Route::get('/{id}', [EvenementController::class, 'show'])->name('show');        
        Route::get('/{id}/edit', [EvenementController::class, 'edit'])->name('edit');   
        Route::put('/{id}', [EvenementController::class, 'update'])->middleware([AuditTrail::class])->name('update');    
        Route::delete('/{id}', [EvenementController::class, 'destroy'])->middleware([AuditTrail::class])->name('destroy');
            Route::patch('/{id}/update-status', [EvenementController::class, 'updateStatus'])->middleware([AuditTrail::class])->name('updateStatus');
        Route::post('/{id}/resend-mail', [EvenementController::class, 'resendMail'])->middleware([AuditTrail::class])->name('resendMail');
    })->middleware(['auth']);







