<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;


// Auth routes (login, register, etc.)
require __DIR__ . '/paths/auth.php';

// User Detail Route
Route::middleware(['auth:sanctum'])->get('/me', [UserController::class, 'me']);


// Admin-only API routes
require __DIR__ . '/paths/admin/client.php';