<?php
use App\Http\Controllers\Web\Admin\PortefeulleController;
Route::prefix('portefeulle')->name('portefeulle.')->group(function () {
        Route::get('/', [PortefeulleController::class, 'showMontantEvent'])->name('showMontantEvent');      
       
    })->middleware(['auth']);





