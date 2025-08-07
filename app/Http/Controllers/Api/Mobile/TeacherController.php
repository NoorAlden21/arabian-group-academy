<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherAssignedSubjectsResource;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected TeacherService $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    /**
     * Display the list of classes and subjects assigned to the authenticated teacher.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAssignedClassesAndSubjects(Request $request): JsonResponse
    {
        $teacherUser = $request->user();

        if (!$teacherUser || !$teacherUser->hasRole('teacher')) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        try {
            $assignedItems = $this->teacherService->getAssignedClassesAndSubjects($teacherUser);

            return TeacherAssignedSubjectsResource::collection($assignedItems)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching assigned items.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
