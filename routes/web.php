<?php

use Illuminate\Support\Facades\Route;
use MarkMatusek74\ReportingEngine\Http\Controllers\ReportController;

$middleware = config('reporting-engine.routes.middleware', ['web', 'auth']);
$prefix = config('reporting-engine.routes.prefix', 'reporting');

Route::middleware($middleware)->prefix($prefix)->group(function () {
    
    // Report management routes
    Route::get('/', [ReportController::class, 'index'])->name('reporting.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reporting.reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reporting.reports.create');
    Route::get('/reports/{report:slug}', [ReportController::class, 'show'])->name('reporting.reports.show');
    Route::get('/reports/{report:slug}/edit', [ReportController::class, 'edit'])->name('reporting.reports.edit');
    
    // API routes for AJAX requests
    Route::prefix('api')->group(function () {
        Route::get('/reports/{report}/data', [ReportController::class, 'getData'])->name('reporting.api.reports.data');
        Route::post('/reports/{report}/export', [ReportController::class, 'export'])->name('reporting.api.reports.export');
    });

});