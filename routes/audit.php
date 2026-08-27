<?php

use App\Http\Controllers\Web\Admin\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:view-audit-logs'])
    ->prefix('admin/audit')
    ->name('admin.audit.')
    ->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });
