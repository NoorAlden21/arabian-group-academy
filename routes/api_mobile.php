<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::get('profile', [ProfileController::class, 'show']);
    });

});
