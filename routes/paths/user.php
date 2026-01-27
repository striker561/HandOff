<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('user/')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);
});

