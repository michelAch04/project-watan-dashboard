<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.show');
    Route::post('/verify-otp', [LoginController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('/resend-otp', [LoginController::class, 'resendOTP'])->name('otp.resend');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');
    
    // User Management Routes (Admin only with password verification)
    Route::prefix('users')->group(function () {
        // Password verification routes (middleware handles admin check + doesn't require password verification)
        Route::middleware(['admin.password'])->group(function () {
            Route::get('/verify-password', [UserController::class, 'showVerifyPassword'])->name('users.verify-password');
            Route::post('/verify-password', [UserController::class, 'verifyPassword'])->name('users.verify-password.submit');
            
            // Index page (viewing only - no password required, but admin check applies)
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            
            // Routes that require password verification
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            
            // Location assignment
            Route::get('/{user}/assign-location', [UserController::class, 'showAssignLocation'])->name('users.assign-location');
            Route::post('/{user}/assign-location', [UserController::class, 'assignLocation'])->name('users.assign-location.store');
            Route::get('/{user}/locations', [UserController::class, 'getAvailableLocations'])->name('users.locations');
        });
    });
    
    // Feature routes (placeholders - we'll create these later)
    Route::get('/financial', function () {
        return view('financial.index');
    })->name('financial.index')->middleware('can:view_financial');
    
    Route::get('/humanitarian', function () {
        return view('humanitarian.index');
    })->name('humanitarian.index')->middleware('can:view_humanitarian');
    
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index')->middleware('can:view_reports');
    
    Route::get('/zones', function () {
        return view('zones.index');
    })->name('zones.index')->middleware('can:view_zones');
    
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index')->middleware('can:manage_settings');
});

// Redirect root to appropriate page
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login.show');
});

require __DIR__.'/auth.php';