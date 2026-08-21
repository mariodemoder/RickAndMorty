<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SyncLogController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->middleware('auth:sanctum');

// Characters
Route::get('/characters', [CharacterController::class, 'index']);
Route::get('/characters/{id}', [CharacterController::class, 'show']);

// Episodes
Route::get('/episodes', [EpisodeController::class, 'index']);
Route::get('/episodes/{id}', [EpisodeController::class, 'show']);

// Locations
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/locations/{id}', [LocationController::class, 'show']);

// Favorites (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);
});

// Sync logs
Route::get('/sync/logs', [SyncLogController::class, 'index']);
Route::get('/sync/logs/{syncLog}', [SyncLogController::class, 'show']);
