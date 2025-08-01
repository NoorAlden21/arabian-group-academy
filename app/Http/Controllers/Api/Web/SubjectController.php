<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Services\SubjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubjectController extends Controller
{
    protected SubjectService $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    public function index(): JsonResponse
    {
        try {
            $subjects = $this->subjectService->getAllSubjects();
            return SubjectResource::collection($subjects)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateSubjectRequest $request): JsonResponse
    {
        try {
            $subject = $this->subjectService->createSubject($request->validated());
            return (new SubjectResource($subject))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $subject = $this->subjectService->getSubjectById($id);
            return (new SubjectResource($subject))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        try {
            $subject = $this->subjectService->updateSubject($id, $request->validated());
            return (new SubjectResource($subject))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->subjectService->deleteSubject($id);
            return response()->json(['message' => 'Subject deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
