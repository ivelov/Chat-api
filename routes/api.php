<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/email/verification-notification', [EmailController::class, 'sendVerificationMail'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', [EmailController::class, 'verify'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
