<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

// Setup routes (not protected by auth, only by setup complete check)
Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout route (authenticated users only)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DatabaseController::class, 'index'])->name('dashboard');

    Route::get('/databases/create', [DatabaseController::class, 'create'])->name('databases.create');
    Route::post('/databases/test-connection', [DatabaseController::class, 'testConnection'])->name('databases.test-connection');
    Route::post('/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::get('/databases/{database}', [DatabaseController::class, 'show'])->name('databases.show');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');
    Route::get('/backups/{backup}/download', [DatabaseController::class, 'download'])->name('backups.download');
    Route::get('/backups/{backup}/restore', [DatabaseController::class, 'restore'])->name('backups.restore');
    Route::post('/backups/{backup}/restore', [DatabaseController::class, 'processRestore'])->name('backups.process-restore');
    Route::delete('/backups/{backup}', [DatabaseController::class, 'destroyBackup'])->name('backups.destroy');
});

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
