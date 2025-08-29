<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleDetailsResource;
use App\Http\Resources\TeacherAssignedSubjectsResource;
use App\Services\ScheduleService;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    protected TeacherService $teacherService;
    protected ScheduleService $scheduleService;

    public function __construct(TeacherService $teacherService,ScheduleService $scheduleService)
    {
        $this->teacherService = $teacherService;
        $this->scheduleService = $scheduleService;
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

    public function mySchedule(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $schedules = $this->scheduleService->getUserSchedule($user);

            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleDetailsResource::collection($daySchedules);
            });

            return response()->json(['data' => $groupedSchedules], 200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve teacher schedule for user ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while retrieving your schedule.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }
}
