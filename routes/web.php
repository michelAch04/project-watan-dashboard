<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.show');
    Route::post('/verify-otp', [LoginController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('/resend-otp', [LoginController::class, 'resendOTP'])->name('otp.resend');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Redirect root to appropriate page
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

require __DIR__.'/auth.php';
