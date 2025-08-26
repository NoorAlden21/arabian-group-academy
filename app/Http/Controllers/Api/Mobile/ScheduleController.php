<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;

use App\Http\Resources\ScheduleDetailsResource;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
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


}
