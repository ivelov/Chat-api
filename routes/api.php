<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/email/verification-notification', [EmailController::class, 'sendVerificationMail'])
    ->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::post('/email/verify/{id}/{hash}', [EmailController::class, 'verify'])
    ->middleware(['auth:api', 'signed'])->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [UserController::class, 'getUser'])->middleware('auth:api');
Route::post('/user/{id}', [UserController::class, 'update'])->middleware('auth:api');

Route::get('/chats', [ChatController::class, 'index'])->middleware('auth:api');
Route::post('/chats', [ChatController::class, 'store'])->middleware('auth:api');
Route::get('/chats/{id}', [ChatController::class, 'show'])->middleware('auth:api');
Route::post('/chats/{id}', [MessageController::class, 'store'])->middleware('auth:api');