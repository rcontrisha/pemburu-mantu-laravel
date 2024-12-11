<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Auth\ApiEmailVerificationController;
use App\Http\Controllers\Api\ProfileController;

use App\Http\Controllers\AuthController as ControllersAuthController;

Route::post('/login', [ControllersAuthController::class, 'login']);
Route::post('/register', [ControllersAuthController::class, 'register']);
Route::get('email/verify-email-mobile/{id}/{hash}', [ApiEmailVerificationController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('api.verification.verify');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/images', [ImageController::class, 'index']);
    Route::get('/images/{image}', [ImageController::class, 'show']);
    Route::post('/images', [ImageController::class, 'store']);
    Route::put('/images/{image}', [ImageController::class, 'update']);
    Route::delete('/images/{image}', [ImageController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/my-orders', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    
    Route::get('/order-images', [OrderController::class, 'orderImages']);

    Route::get('email/verify', [ApiEmailVerificationController::class, 'showVerificationNotice'])
        ->name('api.verification.notice');

    Route::post('email/verification-notification', [ApiEmailVerificationController::class, 'sendVerificationEmail'])
        ->name('api.verification.send');

    Route::get('/profile', [ProfileController::class, 'show']); // Tampilkan profil
    Route::put('/profile', [ProfileController::class, 'update']); // Perbarui profil
    Route::post('/profile-photo', [ProfileController::class, 'updatePhoto']);
    Route::delete('/profile', [ProfileController::class, 'destroy']); // Hapus akun
});
