<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
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
    
    // Profile (placeholder - create later)
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');
    
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
    
    Route::get('/users', function () {
        return view('users.index');
    })->name('users.index')->middleware('can:view_users');
    
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