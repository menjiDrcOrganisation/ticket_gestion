
<?php
use App\Http\Controllers\scanneur\ScanneController;
Route::prefix('scanneur')->name('scanneur.')->group(function () {
        Route::get('/scanne', [ScanneController::class, 'showScanner'])->name('showScanner');      
        Route::post('/scanne', [ScanneController::class, 'processScan'])->name('processScan');  
})->middleware(['auth']);
