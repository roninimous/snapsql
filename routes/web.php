<?php

use App\Http\Controllers\Auth\LoginController;
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
    Route::get('/dashboard', function () {
        $databases = [
            [
                'name' => 'Primary Database',
                'last_backup' => '2025-12-15 14:30',
                'status' => 'success',
            ],
            [
                'name' => 'Analytics Warehouse',
                'last_backup' => '2025-12-14 09:10',
                'status' => 'failed',
            ],
            [
                'name' => 'Staging',
                'last_backup' => null,
                'status' => 'pending',
            ],
        ];

        return view('dashboard', compact('databases'));
    })->name('dashboard');
});

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
