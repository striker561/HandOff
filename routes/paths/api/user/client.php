<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientController;

Route::prefix('clients')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [ClientController::class, 'store']);
    Route::post('{client}/resend-invitation', [ClientController::class, 'resendInvitation']);
});
