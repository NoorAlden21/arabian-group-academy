<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//public routes
Route::post('login',[UserController::class,'login']);


//protected routes
Route::middleware(['api','auth:sanctum'])->group(function(){

    Route::post('/logout',[UserController::class,'logout']);


    //admin routes
    Route::prefix('/admin/students')
    ->middleware('role:admin')
    ->controller(StudentController::class)
    ->group(function(){
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/search', 'searchStudents');
        Route::get('/{id}', 'show');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/restore/{id}', 'restoreStudent');
        Route::delete('/force-delete/{id}', 'forceDeleteStudent');
    });

});