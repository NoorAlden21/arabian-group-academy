<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassSubjectTeacherResource;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\JsonResponse;

class ClassSubjectTeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $classSubjectTeachers = ClassSubjectTeacher::with([
            'classroom',
            'subject',
            'teacher' => function ($query) {
                $query->with('user');
            }
        ])->get();

        return ClassSubjectTeacherResource::collection($classSubjectTeachers)->response()->setStatusCode(200);
    }
}
