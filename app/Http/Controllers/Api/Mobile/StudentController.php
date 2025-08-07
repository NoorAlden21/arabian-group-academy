<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Http\Resources\StudentBasicInfoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentController extends Controller
{
    protected StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function getStudentsInClassroom(int $classroomId): JsonResponse
    {
        try {
            $students = $this->studentService->getStudentsInClassroom($classroomId);

            return StudentBasicInfoResource::collection($students)->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch students.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
