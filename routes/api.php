<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SyncLogController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/sync/logs', [SyncLogController::class, 'index']);
Route::get('/sync/logs/{syncLog}', [SyncLogController::class, 'show']);
