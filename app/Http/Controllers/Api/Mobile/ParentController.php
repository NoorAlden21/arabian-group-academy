<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChildBasicResource;
use App\Http\Resources\HomeworkResource;
use App\Http\Resources\ScheduleDetailsResource;
use App\Http\Resources\GradeResource;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\ParentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ParentController extends Controller
{
    protected ParentService $parentService;

    public function __construct(ParentService $parentService)
    {
        $this->parentService = $parentService;
    }

    /**
     * Display a list of children linked to the authenticated parent.
     * This is the initial screen for the parent.
     */
    public function getChildren(Request $request): JsonResponse
    {
        try {
            $children = $this->parentService->getParentChildren($request->user());
            return ChildBasicResource::collection($children)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("Failed to fetch children for parent ID {$request->user()->id}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch children.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }

    /**
     * Display the schedule for a specific child.
     */
    public function getChildSchedule(int $childId): JsonResponse
    {
        try {
            $schedules = $this->parentService->getChildSchedule(Auth::user(), $childId);

            // Group the schedules by day for a cleaner response
            $groupedSchedules = $schedules->groupBy('day')->map(function ($daySchedules) {
                return ScheduleDetailsResource::collection($daySchedules);
            });

            return response()->json(['data' => $groupedSchedules], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Child not found or unauthorized.'], 404);
        } catch (\Exception $e) {
            Log::error("Failed to fetch child's schedule for child ID {$childId}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch child\'s schedule.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }

    /**
     * Display the homework for a specific child.
     */
    public function getChildHomework(int $childId): JsonResponse
    {
        try {
            $homeworks = $this->parentService->getChildHomework(Auth::user(), $childId);
            return HomeworkResource::collection($homeworks)->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Child not found or unauthorized.'], 404);
        } catch (\Exception $e) {
            Log::error("Failed to fetch child's homework for child ID {$childId}: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch child\'s homework.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
            ], 500);
        }
    }

    /**
     * Display the grades for a specific child.
     */
    // public function getChildGrades(int $childId): JsonResponse
    // {
    //     try {
    //         $grades = $this->parentService->getChildGrades(Auth::user(), $childId);
    //         return GradeResource::collection($grades)->response()->setStatusCode(200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['message' => 'Child not found or unauthorized.'], 404);
    //     } catch (\Exception $e) {
    //         Log::error("Failed to fetch child's grades for child ID {$childId}: " . $e->getMessage());
    //         return response()->json([
    //             'message' => 'Failed to fetch child\'s grades.',
    //             'error' => config('app.debug') ? $e->getMessage() : 'Server Error'
    //         ], 500);
    //     }
    // }
}
