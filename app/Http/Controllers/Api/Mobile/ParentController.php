<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChildBasicResource;
use App\Http\Resources\HomeworkResource;
use App\Http\Resources\ScheduleDetailsResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\StudentBasicInfoResource;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\ParentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParentController extends Controller
{
    protected ParentService $parentService;

    public function __construct(ParentService $parentService)
    {
        $this->parentService = $parentService;
    }

    /**
     * Display a list of children linked to the authenticated parent.
     */
    public function getChildren(Request $request): JsonResponse
    {
        try {
            $children = $this->parentService->getParentChildren($request->user());
            return StudentBasicInfoResource::collection($children)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch children.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the schedule for a specific child.
     */
  public function getChildSchedule(User $parentUser, int $childId): Collection
    {
        $child = $this->getChildIfAuthorized($parentUser, $childId);

        if (!$child || !$child->classroom) {
            return collect();
        }

        // تحميل العلاقات بشكل يدوي وواضح بدلاً من الاعتماد على علاقة معقدة في الموديل
        $child->classroom->load('classSubjectTeachers.schedules');

        // تجميع كل الجداول الزمنية من كل ربط
        $schedules = collect();
        foreach ($child->classroom->classSubjectTeachers as $cst) {
            $schedules = $schedules->merge($cst->schedules);
        }

        // ترتيب الجداول وإرجاعها
        return $schedules->sortBy([
            ['day', 'asc'],
            ['start_time', 'asc']
        ])->values();
    }

        protected function getChildIfAuthorized(User $parentUser, int $childId): ?StudentProfile
    {
        if (!$parentUser->hasRole('parent') || !$parentUser->parentProfile) {
            return null;
        }

        $child = $parentUser->parentProfile->children()->where('id', $childId)->first();

        return $child;
    }
    /**
     * Display the homework for a specific child.
     */
    public function getChildHomework(Request $request, int $childId): JsonResponse
    {
        try {
            $homeworks = $this->parentService->getChildHomework($request->user(), $childId);
            if ($homeworks->isEmpty()) {
                return response()->json(['message' => 'Child not found or no homework available.'], 404);
            }
            return HomeworkResource::collection($homeworks)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch child\'s homework.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the grades for a specific child.
     */
    // public function getChildGrades(Request $request, int $childId): JsonResponse
    // {
    //     try {
    //         $grades = $this->parentService->getChildGrades($request->user(), $childId);
    //         if ($grades->isEmpty()) {
    //             return response()->json(['message' => 'Child not found or no grades available.'], 404);
    //         }
    //         return GradeResource::collection($grades)->response()->setStatusCode(200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Failed to fetch child\'s grades.', 'error' => $e->getMessage()], 500);
    //     }
    // }
}
