<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//public routes
Route::post('login',[UserController::class,'login']);


//protected routes
Route::middleware(['api','auth:sanctum'])->group(function(){

    Route::post('/logout',[UserController::class,'logout']);


    //admin routes
    Route::middleware('role:admin')->group(function(){
        Route::post('/admin/students',[AdminController::class,'addNewStudent']);
        Route::get('/admin/students',[AdminController::class,'getAllStudents']);
        Route::get('/admin/students/search',[AdminController::class,'searchStudents']);
        Route::get('/admin/students/{id}',[AdminController::class,'showStudent']);
        Route::patch('/admin/students/{id}',[AdminController::class,'updateStudent']);
        Route::delete('/admin/students/{id}',[AdminController::class,'deleteStudent']);
        Route::patch('/admin/students/{id}/restore', [AdminController::class, 'restoreStudent']);
        Route::delete('/admin/students/{id}/force', [AdminController::class, 'forceDeleteStudent']);
    });


});