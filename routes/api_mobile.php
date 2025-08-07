<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use App\Http\Controllers\Api\Mobile\ScheduleController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Api\Mobile\HomeworkController;
use App\Http\Controllers\Api\Mobile\StudentController;
use App\Http\Controllers\Api\Mobile\TeacherController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::get('profile', [ProfileController::class, 'show']);


        Route::get('schedules/my', [ScheduleController::class, 'mySchedule']);
        Route::get('schedules/my/today', [ScheduleController::class, 'show']);

        //quizz routes
        Route::prefix('/quizz')->controller(QuizController::class)->group(function(){
            //teacher routes
            Route::prefix('/teacher')->middleware('role:teacher')->group(function(){
                Route::get('/','index');
                Route::post('/','store');
                Route::get('/{quiz}','show');
                Route::put('/{quiz}','update')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::delete('/{quiz}', 'destroy')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::get('/{quiz}/assign','assignableClassrooms')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::post('/{quiz}/assign','assign')->middleware(['is.owner:quiz,teacher_profile_id']);

                //student routes
                Route::prefix('/student')->middleware('role:student')->group(function(){
                    Route::get('/{quiz}','showForStudent');
                    Route::get('/', 'studentQuizzes');
                });
            });

        Route::group(['prefix' => 'teacher', 'middleware' => 'role:teacher'], function () {

            Route::get('/homeworks', [HomeworkController::class, 'index']);
            Route::post('/homeworks', [HomeworkController::class, 'store']);
            Route::put('/homeworks/{id}', [HomeworkController::class, 'update']);
            Route::patch('/homeworks/{id}', [HomeworkController::class, 'update']);
            Route::delete('/homeworks/{id}', [HomeworkController::class, 'destroy']);

            Route::get('/assigned-classes-and-subjects', [TeacherController::class, 'getAssignedClassesAndSubjects']);

            Route::get('/students/{classroomId}', [StudentController::class, 'getStudentsInClassroom']);
        });
    });
});

