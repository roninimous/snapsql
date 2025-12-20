<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
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

    Route::post('/databases', [DatabaseController::class, 'store'])->name('databases.store');
    Route::get('/databases/{database}', [DatabaseController::class, 'show'])->name('databases.show');
    Route::get('/databases/{database}/edit', [DatabaseController::class, 'edit'])->name('databases.edit');
    Route::put('/databases/{database}', [DatabaseController::class, 'update'])->name('databases.update');
    Route::patch('/databases/{database}/toggle', [DatabaseController::class, 'toggle'])->name('databases.toggle');
    Route::post('/databases/{database}/move-up', [DatabaseController::class, 'moveUp'])->name('databases.move-up');
    Route::post('/databases/{database}/move-down', [DatabaseController::class, 'moveDown'])->name('databases.move-down');
    Route::post('/databases/{database}/backup', [DatabaseController::class, 'createManualBackup'])->name('databases.backup');
    Route::delete('/databases/{database}', [DatabaseController::class, 'destroy'])->name('databases.destroy');
    Route::delete('/databases/{database}/cloud-backup', [DatabaseController::class, 'destroyCloudBackup'])->name('databases.cloud-backup.destroy');
    Route::post('/databases/test-connection', [DatabaseController::class, 'testConnection'])->name('databases.test-connection');
    Route::post('/databases/test-cloud-connection', [DatabaseController::class, 'testCloudConnection'])->name('databases.test-cloud-connection');
    Route::get('/backups/{backup}/download', [DatabaseController::class, 'download'])->name('backups.download');
    Route::get('/backups/{backup}/restore', [DatabaseController::class, 'restore'])->name('backups.restore');
    Route::post('/backups/{backup}/restore', [DatabaseController::class, 'processRestore'])->name('backups.process-restore');
    Route::delete('/backups/{backup}', [DatabaseController::class, 'destroyBackup'])->name('backups.destroy');

    // Account Routes
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('password.update');

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications', [NotificationController::class, 'update'])->name('notifications.update');
    Route::post('/notifications/test-discord', [NotificationController::class, 'testDiscord'])->name('notifications.test-discord');

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/check-update', function () {
        return view('check-update');
    })->name('check-update');
    Route::get('/about', function () {
        return view('about');
    })->name('about');
});

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
