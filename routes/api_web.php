<?php

use App\Http\Controllers\Api\Web\StudentController;
use App\Http\Controllers\Api\Web\TeacherController;
use App\Http\Controllers\Api\Web\UserController;

use Illuminate\Support\Facades\Route;

//public routes
Route::post('login',[UserController::class,'login']);


//protected routes
Route::middleware(['api','auth:sanctum'])->group(function(){

    Route::post('/logout',[UserController::class,'logout']);


    //admin routes

    //Students
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

    //Teachers
    Route::prefix('/admin/teachers')
    ->middleware('role:admin')
    ->controller(TeacherController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/search', 'searchTeachers');
        Route::get('/{id}', 'show');
        Route::patch('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/restore/{id}', 'restoreTeacher');
        Route::delete('/force-delete/{id}', 'forceDeleteTeacher');
    });

});
