<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes (login, register, etc.)
require __DIR__ . '/paths/auth.php';

// Admin-only API routes
require __DIR__ . '/paths/admin/client.php';