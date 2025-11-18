    <?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'index'])
    ->name('user.index');

Route::post('/user', [UserController::class, 'store'])->name('users.store');

Route::put('/user/{user}', [UserController::class, 'update'])->name('users.update'); 
Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('users.destroy');