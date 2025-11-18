<?php

use App\Http\Controllers\RetraitController;
use Illuminate\Support\Facades\Route;

Route::get('/retrait', 
    [RetraitController::class, 'index'])
    ->name('retrait.index')->middleware(['auth']);