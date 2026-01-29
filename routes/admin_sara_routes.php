<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SaraConfigController;

// Admin routes for Sara AI configuration
Route::middleware(['auth', 'admin'])->group(function () {
    // Web routes
    Route::get('/admin/sara/config', [SaraConfigController::class, 'index'])->name('admin.sara.config');

    // API routes
    Route::prefix('api/v1/admin/sara')->group(function () {
        Route::post('/config', [SaraConfigController::class, 'saveConfig']);
        Route::get('/config', [SaraConfigController::class, 'getConfig']);
        Route::post('/test', [SaraConfigController::class, 'testSara']);
        Route::get('/statistics', [SaraConfigController::class, 'getStatistics']);
    });
});
