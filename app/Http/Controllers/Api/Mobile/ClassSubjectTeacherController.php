<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassSubjectTeacherResource;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ClassSubjectTeacherController extends Controller
{
    public function index(): JsonResponse
    {
        // نفترض أن المستخدم الحالي هو "teacher" وعنده profile
        $teacherProfileId = Auth::user()->teacherProfile->id;

        $classSubjectTeachers = ClassSubjectTeacher::with([
            'classroom',
            'subject',
            'teacher' => function ($query) {
                $query->with('user');
            }
        ])
            ->where('teacher_profile_id', $teacherProfileId) // فلترة على المدرس الحالي
            ->get();

        return ClassSubjectTeacherResource::collection($classSubjectTeachers)
            ->response();
    }
}
