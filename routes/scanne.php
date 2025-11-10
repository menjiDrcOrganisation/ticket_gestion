
<?php
use App\Http\Controllers\ScanneController;
Route::prefix('evenements')->name('evenements.')->group(function () {
        Route::get('/scanne', [ScanneController::class, 'showScanner'])->name('showScanner');      
        Route::post('/scanne', [ScanneController::class, 'processScan'])->name('processScan');  
      
})->middleware(['auth']);
