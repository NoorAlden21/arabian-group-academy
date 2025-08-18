<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreateTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherFullResource;
use App\Http\Resources\TeacherBasicResource;
use App\Services\ClassTypeSubjectService;
use App\Services\TeacherService;

class TeacherController extends Controller
{
    protected $teacherService;
    protected $ctsService;

    public function __construct(TeacherService $teacherService, ClassTypeSubjectService $ctsService)
    {
        $this->teacherService = $teacherService;
        $this->ctsService = $ctsService;
    }

    public function index()
    {
        try {
            $teachers = $this->teacherService->getAllTeachers();
            return response()->json([
                'teachers' => TeacherBasicResource::collection($teachers)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function classTypeSubjectsGrouped(){
        try {
            $data = $this->ctsService->getGroupedByClassType();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch class type subjects grouped by class type',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(CreateTeacherRequest $request)
    {
        try {
            $teacher = $this->teacherService->createTeacher($request->validated());
            return response()->json([
                'message' => 'Teacher created successfully',
                'teacher' => new TeacherFullResource($teacher)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $teacher = $this->teacherService->getTeacherById($id);
            if (!$teacher) {
                return response()->json([
                    'message' => 'Teacher not found'
                ], 404);
            }
            return response()->json([
                'teacher' => new TeacherFullResource($teacher)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update($id, UpdateTeacherRequest $request)
    {
        try {
            $teacher = $this->teacherService->updateTeacher($id, $request->validated());
            return response()->json([
                'message' => 'Teacher updated successfully',
                'teacher' => new TeacherFullResource($teacher)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->teacherService->deleteTeacher($id);
            return response()->json([
                'message' => 'Teacher deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $teacher = $this->teacherService->restoreTeacher($id);
            return response()->json([
                'message' => 'Teacher restored successfully',
                'teacher' => new TeacherFullResource($teacher)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $this->teacherService->forceDeleteTeacher($id);
            return response()->json([
                'message' => 'Teacher permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to permanently delete teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchTeachers(Request $request)
    {
        try {
            $teachers = $this->teacherService->searchTeachers($request->all());

            return response()->json([
                'teachers' => TeacherBasicResource::collection($teachers),
                'pagination' => [
                    'current_page' => $teachers->currentPage(),
                    'last_page' => $teachers->lastPage(),
                    'per_page' => $teachers->perPage(),
                    'total' => $teachers->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to search teachers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
