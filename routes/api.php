<?php

use App\Http\Controllers\SyncLogController;
use Illuminate\Support\Facades\Route;

Route::get('/sync/logs', [SyncLogController::class, 'index']);
Route::get('/sync/logs/{syncLog}', [SyncLogController::class, 'show']);
