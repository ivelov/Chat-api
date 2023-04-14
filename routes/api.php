<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Route;

Route::post('/email/verification-notification', [EmailController::class, 'sendVerificationMail'])
    ->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::post('/email/verify/{id}/{hash}', [EmailController::class, 'verify'])
    ->middleware(['auth:api', 'signed'])->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:api');
