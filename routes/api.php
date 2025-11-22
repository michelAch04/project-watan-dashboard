<?php

use App\Http\Controllers\PushNotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/push/public-key', [PushNotificationController::class, 'getPublicKey']);
    Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe']);
    Route::post('/push/unsubscribe', [PushNotificationController::class, 'unsubscribe']);
});
