<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SaraConfigController;

// Admin Sara AI Configuration routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/sara/config', [SaraConfigController::class, 'index'])->name('admin.sara.config');
    Route::post('/admin/sara/config/save', [SaraConfigController::class, 'saveConfig'])->name('admin.sara.config.save');
    Route::post('/admin/sara/config/test-api', [SaraConfigController::class, 'testApiConnection'])->name('admin.sara.config.test-api');
    Route::get('/admin/sara/config/stats', [SaraConfigController::class, 'getStats'])->name('admin.sara.config.stats');
});
