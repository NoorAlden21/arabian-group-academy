<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTeachersRequest;
use App\Http\Requests\CreateClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Resources\ClassroomBasicResource;
use App\Http\Resources\ClassroomFullResource;
use App\Models\Classroom;
use App\Models\ClassSubjectTeacher;
use App\Services\ClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClassroomController extends Controller
{
    protected ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function index(): JsonResponse
    {
        try {
            $classrooms = $this->classroomService->getAllClassrooms();
            return response()->json([
                'classrooms' => ClassroomBasicResource::collection($classrooms)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateClassroomRequest $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->createClassroom($request->validated());
            $classroom->load(['classType']);
            return response()->json([
                'message' => 'Classroom created successfully',
                'classroom' => new ClassroomFullResource($classroom)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id, ['students', 'classSubjectTeachers.teacher', 'classSubjectTeachers.subject']);

            if (!$classroom) {
                return response()->json([
                    'message' => 'Classroom not found.'
                ], 404);
            }
            return response()->json([
                'classroom' => new ClassroomFullResource($classroom)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Classroom not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch classroom details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateClassroomRequest $request, int $id): JsonResponse
    {
        try {
            $classroom = $this->classroomService->updateClassroom($id, $request->validated());
            return response()->json([
                'message' => 'Classroom updated successfully',
                'classroom' => new ClassroomFullResource($classroom)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Classroom not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->classroomService->deleteClassroom($id);
            return response()->json([
                'message' => 'Classroom deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Classroom not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $classroom = $this->classroomService->restoreClassroom($id);
            return response()->json([
                'message' => 'Classroom restored successfully',
                'classroom' => new ClassroomFullResource($classroom)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Deleted classroom not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete(int $id): JsonResponse
    {
        try {
            $this->classroomService->forceDeleteClassroom($id);
            return response()->json([
                'message' => 'Classroom permanently deleted'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Classroom not found for permanent deletion.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to permanently delete classroom',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'level', 'year']);
            $classrooms = $this->classroomService->searchClassrooms($filters, $request->get('per_page', 10));

            return response()->json([
                'classrooms' => ClassroomBasicResource::collection($classrooms),
                'pagination' => [
                    'current_page' => $classrooms->currentPage(),
                    'last_page' => $classrooms->lastPage(),
                    'per_page' => $classrooms->perPage(),
                    'total' => $classrooms->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to search classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function trashed(): JsonResponse
    {
        try {
            $classrooms = $this->classroomService->getOnlyTrashedClassrooms();

            return response()->json([
                'classrooms' => ClassroomBasicResource::collection($classrooms)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch trashed classrooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchTeachers($id){
        try{
            $data = $this->classroomService->getEligibleTeachers($id);
             return response()->json([
                'message' => 'Eligible teachers fetched successfully.',
                'data' => $data
            ], 200);
        } 
        catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Classroom not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch teachers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignTeachers(AssignTeachersRequest $request,$id){
        try {
        $classroom = Classroom::findOrFail($id);

        ClassSubjectTeacher::where('classroom_id', $classroom->id)->delete();

        foreach ($request->assignments as $assignment) {
            ClassSubjectTeacher::create([
                'classroom_id' => $classroom->id,
                'subject_id' => $assignment['subject_id'],
                'teacher_profile_id' => $assignment['teacher_profile_id'],
            ]);
        }

        return response()->json([
            'message' => 'Teachers assigned successfully.'
        ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign teachers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

