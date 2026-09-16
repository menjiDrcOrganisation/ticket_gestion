<?php

use App\Http\Controllers\Web\Organisateur\EventScannerController;
use Illuminate\Support\Facades\Route;

Route::get('/event-scanner', 
    [EventScannerController::class, 'index'])
    ->name('event_scanner.index')->middleware(['auth']);





