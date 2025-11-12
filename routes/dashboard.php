<?php
use App\Http\Controllers\DemandeEvenementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\organisateur\DashboardController as OrganisateurDashboardController;
use App\Http\Controllers\EvenementBilletTypeBilletController;


Route::get('dashboard/admin', [AdminDashboardController::class, 'index'])
    ->name('dashboard_admin.show')->middleware(['auth']);

Route::get('dashboard/organisateur', [OrganisateurDashboardController::class, 'index'])
    ->name('dashboard_orginasateur.show')->middleware(['auth']);

 
    


    