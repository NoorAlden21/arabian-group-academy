<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\MyExamResource;
use App\Http\Resources\MyGradeResource;
use App\Http\Resources\ScheduleDetailsResource;
use App\Services\StudentService;
use App\Http\Resources\StudentBasicInfoResource;
use App\Services\ScheduleService;
use App\Services\StudentPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected StudentService $studentService;
    protected ScheduleService $scheduleService;

    public function __construct(StudentService $studentService, ScheduleService $scheduleService, private StudentPortalService $service)
    {
        $this->studentService = $studentService;
        $this->scheduleService = $scheduleService;
    }
    public function mySchedule(Request $request): JsonResponse
    {
        $user = $request->user();

        // No need to check for role here as the route middleware already handles it
        try {
            $schedules = $this->scheduleService->getUserSchedule($user);

            // Group the schedules by day before sending
            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleDetailsResource::collection($daySchedules);
            });

            return response()->json(['data' => $groupedSchedules], 200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve user schedule for user ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while retrieving your schedule.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
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

    public function exams(Request $request): JsonResponse
    {
        try {
            $termId       = $request->query('term_id') ? (int) $request->query('term_id') : null;
            $status       = $request->query('status', 'published'); // published|done|...
            $from         = $request->query('from'); // optional
            $to           = $request->query('to');   // optional
            $upcomingOnly = filter_var($request->query('upcoming', '1'), FILTER_VALIDATE_BOOLEAN);

            $exams = $this->service->myExams($termId, $status, $from, $to, $upcomingOnly);

            return response()->json([
                'exams' => MyExamResource::collection($exams)
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to fetch my exams'], 500);
        }
    }

    // GET /api/me/grades
    public function grades(Request $request): JsonResponse
    {
        try {
            $termId    = $request->query('term_id') ? (int) $request->query('term_id') : null;
            $subjectId = $request->query('subject_id') ? (int) $request->query('subject_id') : null;

            $grades = $this->service->myExamGrades($termId, $subjectId);

            return response()->json([
                'grades' => MyGradeResource::collection($grades)
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to fetch my grades'], 500);
        }
    }
}
