
<?php
use App\Http\Controllers\Api\BilletController;

Route::prefix('billet')->name('billet.')->group(function () {
        Route::post('/achatBillet', [BilletController::class, 'achatbillet'])->name('achatbillet');      
    });