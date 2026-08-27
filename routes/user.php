    <?php

use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Middleware\AuditTrail;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/user', [UserController::class, 'index'])
        ->name('user.index');

    Route::post('/user', [UserController::class, 'store'])
        ->middleware([AuditTrail::class])
        ->name('users.store');

    Route::put('/user/{user}', [UserController::class, 'update'])
        ->middleware([AuditTrail::class])
        ->name('users.update');

    Route::delete('/user/{user}', [UserController::class, 'destroy'])
        ->middleware([AuditTrail::class])
        ->name('users.destroy');
});





