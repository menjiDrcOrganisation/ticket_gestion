<?php
use App\Http\Controllers\DemandeEvenementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\EvenementBilletTypeBilletController;
use App\Http\Controllers\EvenementController;

Route::get('dashboard/admin', [EvenementController::class, 'index'])
    ->name('dashboard_admin.show')->middleware(['auth']);

    
    


    