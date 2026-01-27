<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientController;

Route::prefix('admin/client')->middleware(['auth:sanctum', 'ensureAdmin'])->group(function () {
    Route::get('/', [ClientController::class, 'index']);

    Route::post('/', [ClientController::class, 'store']);

    Route::post(
        '{client}/resend-invitation',
        [ClientController::class, 'resendInvitation']
    )->middleware('throttle:5,1'); // 5 per minute per IP
});
