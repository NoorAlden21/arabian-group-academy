<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\DeviceTokenController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Api\Mobile\HomeworkController;
use App\Http\Controllers\Api\Mobile\ParentController;
use App\Http\Controllers\Api\Mobile\StudentController;
use App\Http\Controllers\Api\Mobile\TeacherController;
use App\Http\Controllers\Api\Mobile\ClassSubjectTeacherController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TestNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/test-notification', [TestNotificationController::class, 'send']);
Route::prefix('mobile')->group(function () {




    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/notifications', [NotificationController::class, 'index']);

        Route::get('profile', [ProfileController::class, 'show']);


        //quizz routes
        Route::prefix('/quizz')->controller(QuizController::class)->group(function () {
            //teacher routes
            Route::prefix('/teacher')->middleware('role:teacher')->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{quiz}', 'show');
                Route::put('/{quiz}', 'update')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::delete('/{quiz}', 'destroy')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::get('/{quiz}/assign', 'assignableClassrooms')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::post('/{quiz}/assign', 'assign')->middleware(['is.owner:quiz,teacher_profile_id']);
                Route::post('/{quiz}/publish', 'publish')->middleware(['is.owner:quiz,teacher_profile_id']);

                //student routes
                Route::prefix('/student')->middleware('role:student')->group(function () {
                    Route::get('/{quiz}', 'showForStudent');
                    Route::get('/', 'studentQuizzes');
                });
            });
        });

        Route::group(['prefix' => 'teacher', 'middleware' => 'role:teacher'], function () {
            Route::get('/my-schedule', [TeacherController::class, 'mySchedule']);
            Route::get('class-subject-teachers', [ClassSubjectTeacherController::class, 'index']);

            Route::get('/homeworks', [HomeworkController::class, 'index']);
            Route::post('/homeworks', [HomeworkController::class, 'store']);
            Route::put('/homeworks/{id}', [HomeworkController::class, 'update']);
            Route::patch('/homeworks/{id}', [HomeworkController::class, 'update']);
            Route::delete('/homeworks/{id}', [HomeworkController::class, 'destroy']);

            Route::get('/assigned-classes-and-subjects', [TeacherController::class, 'getAssignedClassesAndSubjects']);
            Route::get('/students/{classroomId}', [StudentController::class, 'getStudentsInClassroom']);
        });

        Route::group(['prefix' => 'parent', 'middleware' => 'role:parent'], function () {
            Route::get('/children', [ParentController::class, 'getChildren']);
            Route::get('/children/{childId}/schedule', [ParentController::class, 'getChildSchedule']);


            Route::get('/children/{childId}/homeworks', [ParentController::class, 'getChildHomework']);
            // Route::get('/children/{childId}/grades', [ParentController::class, 'getChildGrades']);
        });

        Route::group(['prefix' => 'student', 'middleware' => 'role:student'], function () {
            Route::get('/homeworks', [HomeworkController::class, 'getStudentHomeworks']);
            Route::post('/homeworks/{id}/toggle-status', [HomeworkController::class, 'toggleStatus']);
            Route::get('/my-schedule', [StudentController::class, 'mySchedule']);

            Route::get('/exams', [StudentController::class, 'exams']);   // /api/mobile/student/exams?term_id=&status=&from=&to=&upcoming=1
            Route::get('/grades', [StudentController::class, 'grades']); // /api/mobile/student/grades?term_id=&subject_id=
        });
    });
});
