<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHomeworkRequest;
use App\Http\Requests\UpdateHomeworkRequest;
use App\Http\Resources\HomeworkResource;
use App\Services\DeviceTokenService;
use App\Services\HomeworkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    protected HomeworkService $homeworkService;
    protected DeviceTokenService $deviceTokenService;

    public function __construct(HomeworkService $homeworkService, DeviceTokenService $deviceTokenService)
    {
        $this->homeworkService = $homeworkService;
        $this->deviceTokenService = $deviceTokenService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $homeworks = $this->homeworkService->getTeacherHomeworks($request->user());
            return HomeworkResource::collection($homeworks)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch homeworks.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateHomeworkRequest $request): JsonResponse
    {
        try {
            $homework = $this->homeworkService->createHomework($request->user(), $request->validated());
            return (new HomeworkResource($homework))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create homework.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // public function store(CreateHomeworkRequest $request): JsonResponse
    // {
    //     try {
    //         // Get the authenticated teacher
    //         $teacherUser = $request->user();

    //         // Create the homework and get the result, which also gets the associated classroom
    //         $homework = $this->homeworkService->createHomework($teacherUser, $request->validated());

    //         // Get the classroom associated with the homework
    //         $classSubjectTeacher = $homework->classSubjectTeacher;
    //         $classroom = $classSubjectTeacher->classroom;

    //         // Send notification to all students in that classroom
    //         if ($classroom) {
    //             foreach ($classroom->students as $studentProfile) {
    //                 if ($studentProfile->user) {
    //                     $this->deviceTokenService->sendNotification(
    //                         $studentProfile->user,
    //                         "واجب جديد",
    //                         "تم إضافة واجب جديد في مادة {$classSubjectTeacher->subject->name}."
    //                     );
    //                 }
    //             }
    //         }

    //         return (new HomeworkResource($homework))->response()->setStatusCode(201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Failed to create homework.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function update(UpdateHomeworkRequest $request, int $id): JsonResponse
    {
        try {
            $homework = $this->homeworkService->updateHomework($request->user(), $id, $request->validated());
            return (new HomeworkResource($homework))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Homework not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->homeworkService->deleteHomework($request->user(), $id);
            return response()->json(['message' => 'Homework deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Homework not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function getTeacherHomeworks(Request $request): JsonResponse
    {
        try {
            $homeworks = $this->homeworkService->getTeacherHomeworks($request->user());
            return HomeworkResource::collection($homeworks)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch homeworks.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudentHomeworks(Request $request): JsonResponse
    {
        try {
            $homeworks = $this->homeworkService->getStudentHomeworks(Auth::user(), $request->all());

            if ($request->has('status')) {
                $status = $request->get('status');
                if (in_array($status, ['completed', 'pending'])) {
                    $isCompleted = $status === 'completed';
                    $homeworks = $homeworks->filter(fn($hw) => $hw->is_completed === $isCompleted);
                }
            }

            return HomeworkResource::collection($homeworks)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch student homeworks.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $this->homeworkService->toggleHomeworkStatus(Auth::user(), $id);
            return response()->json(['message' => 'Homework status toggled successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Homework not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
