<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStudentsRequest as RequestsAssignStudentsRequest;
use App\Http\Requests\Classroom\AssignStudentsRequest;
use App\Http\Resources\StudentBasicInfoResource;
use App\Http\Resources\StudentFullInfoResource;
use App\Http\Resources\StudentResource;
use App\Models\Classroom;
use App\Services\ClassroomStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomStudentController extends Controller
{
    public function __construct(private ClassroomStudentService $service) {}

    public function candidates(Classroom $classroom, Request $request): JsonResponse
    {
        try {
            $q = $request->string('q')->toString();
            $data = $this->service->candidateStudentsForClassroom($classroom, $q, perPage: 20);
            return response()->json(StudentResource::collection($data), 200);
        } catch (\Throwable $e) {
            return response()->json(['message'=>'Failed to load candidates','error'=>$e->getMessage()], 500);
        }
    }

    // تعيين مجموعة طلاب لهذا الصف
    public function assign(Classroom $classroom, RequestsAssignStudentsRequest $request): JsonResponse
    {
        try {
            $ids = $request->validated()['student_profile_ids'];
            $result = $this->service->assignStudentsToClassroom($classroom, $ids);
            return response()->json($result, 200);
        } catch (\Throwable $e) {
            return response()->json(['message'=>'Failed to assign students','error'=>$e->getMessage()], 500);
        }
    }
}
