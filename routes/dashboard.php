<?php
use App\Http\Controllers\Web\Admin\DemandeEvenementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Organisateur\EvenementBilletTypeBilletController;
use App\Http\Controllers\Web\Admin\EvenementController;

Route::get('dashboard/admin', [AdminDashboardController::class, 'index'])
    ->name('dashboard.admin.viewDash')->middleware(['auth']);

    
    


    






