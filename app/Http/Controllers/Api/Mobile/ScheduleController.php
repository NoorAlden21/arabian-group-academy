<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleDetailsResource;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display the authenticated user's weekly schedule.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function mySchedule(Request $request): JsonResponse
    {
        $user = $request->user();


        if (!$user->hasAnyRole(['student', 'teacher', 'parent'])) {
            return response()->json(['message' => 'Unauthorized to view schedule for this role.'], 403);
        }

        try {
            $schedules = $this->scheduleService->getUserSchedule($user);

            return ScheduleDetailsResource::collection($schedules)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve user schedule for user ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while retrieving your schedule.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $schedules = $this->scheduleService->getAllSchedules();
            return ScheduleResource::collection($schedules)->response()->setStatusCode(200);
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
}
