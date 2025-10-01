<?php

use App\Http\Controllers\Api\Web\ExamController;
use App\Http\Controllers\Api\Web\ExamTermController;
use App\Http\Controllers\Api\Web\AttendanceController;
use App\Http\Controllers\Api\Web\ScheduleController;
use App\Http\Controllers\Api\Web\ClassroomController;
use App\Http\Controllers\Api\Web\ClassroomStudentController;
use App\Http\Controllers\Api\Web\ClassSubjectTeacherController;
use App\Http\Controllers\Api\Web\ClassTypeController;
use App\Http\Controllers\Api\Web\ComplaintController;
use App\Http\Controllers\Api\Web\ExamGradesController;
use App\Http\Controllers\Api\Web\StudentClassroomController;
use App\Http\Controllers\Api\Web\StudentController;
use App\Http\Controllers\Api\Web\SubjectController;
use App\Http\Controllers\Api\Web\TeacherController;
use App\Http\Controllers\Api\Web\UserController;

use App\Http\Controllers\ParentController;
use Illuminate\Support\Facades\Route;

//public routes
Route::post('login', [UserController::class, 'login']);


//protected routes
Route::middleware(['api', 'auth:sanctum'])->group(function () {

    Route::post('/logout', [UserController::class, 'logout']);


    //admin routes

    //Students
    Route::prefix('/admin/students')
        ->middleware('role:admin')
        ->controller(StudentController::class)
        ->group(function () {
            Route::post('/import', 'import');
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/search', 'searchStudents');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/restore/{id}', 'restoreStudent');
            Route::delete('/force-delete/{id}', 'forceDeleteStudent');
        });

    Route::prefix('/admin/students')
        ->middleware('role:admin')
        ->controller(StudentClassroomController::class)
        ->group(function () {
            Route::get('/{student}/classrooms/candidates', 'candidates');
            Route::post('/{student}/classrooms/assign', 'assign');
        });

    Route::prefix('/admin/attendance/students')
        ->middleware('role:admin')
        ->controller(AttendanceController::class)
        ->group(function () {
            Route::get('', 'meta');
            Route::post('', 'storeStudentAbsences');
        });

    Route::prefix('/admin/parents')
        ->middleware('role:admin')
        ->controller(ParentController::class)
        ->group(function () {
            Route::get('/{id}', 'show');
        });

    //teachers
    Route::prefix('/admin/teachers')
        ->middleware('role:admin')
        ->controller(TeacherController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/class-type-subjects/grouped', 'classTypeSubjectsGrouped');
            Route::get('/search', 'searchTeachers');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/restore/{id}', 'restoreTeacher');
            Route::delete('/force-delete/{id}', 'forceDeleteTeacher');
        });


    //classrooms
    Route::prefix('/admin/classrooms')
        ->middleware('role:admin')
        ->controller(ClassroomController::class)
        ->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/search', 'search');
            Route::get('/trashed', 'trashed');
            Route::get('/{classroom}', 'show');
            Route::put('/{classroom}', 'update');
            Route::delete('/{classroom}', 'destroy');
            Route::post('/restore/{classroom}', 'restore');
            Route::delete('/force-delete/{classroom}', 'forceDelete');
            Route::get('/{id}/assign-teachers', 'fetchTeachers');
            Route::post('{id}/assign-teachers', 'assignTeachers');
        });

    Route::prefix('/admin/classrooms')
        ->middleware('role:admin')
        ->controller(ClassroomStudentController::class)
        ->group(function () {
            Route::get('/{classroom}/students', 'index');
            Route::get('/{classroom}/students/candidates', 'candidates');
            Route::post('/{classroom}/students/assign', 'assign');
        });

    //ExamsTerms
    Route::prefix('/admin/exam-terms')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->controller(ExamTermController::class)
        ->group(function () {
            Route::get('', 'index');
            Route::post('', 'store');
            Route::patch('/{examTerm}', 'update');
            Route::post('/{examTerm}/publish', 'publish');
        });
    Route::post('admin/exam-terms/{examTerm}/exams/bulk-upsert', [ExamController::class, 'bulkUpsert'])->middleware('role:admin');

    // Exams
    Route::prefix('/admin/exams')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->controller(ExamController::class)
        ->group(function () {
            Route::get('', [ExamController::class, 'index']);
            Route::post('/{exam}/publish', [ExamController::class, 'publish']);
        });

    //grades
    Route::get('/admin/exams/{exam}/students', [ExamGradesController::class, 'students']);
    Route::post('/admin/exams/{exam}/grades/bulk-upsert', [ExamGradesController::class, 'bulkUpsert']);
    Route::post('/admin/exams/{exam}/grades/publish', [ExamGradesController::class, 'publish']);
    Route::get('/admin/exams/{exam}/classrooms', [ExamGradesController::class, 'classrooms'])
        ->middleware('role:admin');

    //class types
    Route::middleware(['role:admin'])
        ->prefix('/admin')
        ->group(function () {
            Route::apiResource('class-types', ClassTypeController::class);
            Route::get('class-types/{id}/subjects', [ClassTypeController::class, 'subjects']);
        });

    //complaints
    Route::prefix('admin/complaints')
        ->controller(ComplaintController::class)
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('{complaint}', 'show');
            Route::patch('{complaint}/status', 'updateStatus');
            Route::delete('{complaint}', 'destroy');
        });

    Route::middleware(['role:admin'])
        ->prefix('/admin')
        ->group(function () {
            Route::apiResource('subjects', SubjectController::class);
        });

    Route::middleware(['role:admin'])
        ->prefix('/admin')
        ->group(function () {
            Route::apiResource('schedules', ScheduleController::class);
            Route::get('classrooms/{id}/schedules', [ScheduleController::class, 'getClassroomSchedules']);
            Route::get('teachers/{id}/schedules', [ScheduleController::class, 'getTeacherSchedules']);
            Route::get('class-types', [ClassTypeController::class, 'index']);
            Route::get('subjects', [SubjectController::class, 'index']);
            Route::get('classrooms', [ClassroomController::class, 'index']);
            Route::get('teachers', [TeacherController::class, 'index']);
            Route::get('class-subject-teachers', [ClassSubjectTeacherController::class, 'index']);
        });
});
