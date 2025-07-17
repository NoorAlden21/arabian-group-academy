<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use App\Http\Controllers\Api\Mobile\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::get('profile', [ProfileController::class, 'show']);


        Route::get('schedules/my', [ScheduleController::class, 'mySchedule']);
        Route::get('schedules/my/today', [ScheduleController::class, 'show']);

    });
});
