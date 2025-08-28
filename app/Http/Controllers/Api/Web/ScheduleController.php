<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\ScheduleService;

class ScheduleController extends Controller
{

    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }
    public function index(): JsonResponse
    {
        try {
            // Get all schedules with their related data
            $schedules = $this->scheduleService->getAllSchedules();

            // Group the schedules by day and transform them using the ScheduleResource
            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleResource::collection($daySchedules);
            });

            // Return the grouped data in a clean JSON format
            return response()->json([
                'data' => $groupedSchedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->createSchedule($request->validated());
            return (new ScheduleResource($schedule))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->getScheduleById($id);
            return (new ScheduleResource($schedule))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, int $id): JsonResponse
    {
        try {
            $schedule = $this->scheduleService->updateSchedule($id, $request->validated());
            return (new ScheduleResource($schedule))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->scheduleService->deleteSchedule($id);
            return response()->json(['message' => 'Schedule deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getClassroomSchedules(int $id): JsonResponse
    {
        try {
            $schedules = $this->scheduleService->getSchedulesByClassroomId($id);

            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleResource::collection($daySchedules);
            });

            return response()->json([
                'data' => $groupedSchedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch classroom schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schedules for a specific teacher.
     */
    public function getTeacherSchedules(int $id): JsonResponse
    {
        try {
            $schedules = $this->scheduleService->getSchedulesByTeacherId($id);

            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleResource::collection($daySchedules);
            });

            return response()->json([
                'data' => $groupedSchedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch teacher schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
