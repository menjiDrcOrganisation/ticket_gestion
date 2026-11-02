<?php

use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard/admin', [AdminDashboardController::class, 'index'])
    ->name('dashboard.admin.viewDash')->middleware(['auth']);
