<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignClassroomRequest as RequestsAssignClassroomRequest;
use App\Http\Requests\Student\AssignClassroomRequest;
use App\Models\StudentProfile;
use App\Services\ClassroomStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentClassroomController extends Controller
{
    public function __construct(private ClassroomStudentService $service) {}

    public function candidates(StudentProfile $student, Request $request): JsonResponse
    {
        try {
            $q = $request->string('q')->toString();
            $data = $this->service->candidateClassroomsForStudent($student, $q, perPage: 20);
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json(['message'=>'Failed to load candidates','error'=>$e->getMessage()], 500);
        }
    }

    // تعيين صف لهذا الطالب (يسمح بإعادة التعيين)
    public function assign(StudentProfile $student, RequestsAssignClassroomRequest $request): JsonResponse
    {
        try {
            $classroomId = $request->validated()['classroom_id'];
            $result = $this->service->assignClassroomToStudent($student, $classroomId);
            return response()->json($result, 200);
        } catch (\Throwable $e) {
            return response()->json(['message'=>'Failed to assign classroom','error'=>$e->getMessage()], 500);
        }
    }
}
