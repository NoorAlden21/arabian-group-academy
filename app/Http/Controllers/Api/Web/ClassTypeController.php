<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateClassTypeRequest;
use App\Http\Requests\UpdateClassTypeRequest;
use App\Http\Resources\ClassTypeResource;
use App\Services\ClassTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClassTypeController extends Controller
{
    protected ClassTypeService $classTypeService;

    public function __construct(ClassTypeService $classTypeService)
    {
        $this->classTypeService = $classTypeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $classTypes = $this->classTypeService->getAllClassTypes();
            return ClassTypeResource::collection($classTypes)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch class types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateClassTypeRequest $request): JsonResponse
    {
        try {
            $classType = $this->classTypeService->createClassType($request->validated());
            return (new ClassTypeResource($classType))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create class type',
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
            $classType = $this->classTypeService->getClassTypeById($id);
            return (new ClassTypeResource($classType))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Class type not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch class type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassTypeRequest $request, int $id): JsonResponse
    {
        try {
            $classType = $this->classTypeService->updateClassType($id, $request->validated());
            return (new ClassTypeResource($classType))->response()->setStatusCode(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Class type not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update class type',
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
            $this->classTypeService->deleteClassType($id);
            return response()->json(['message' => 'Class type deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Class type not found.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete class type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
