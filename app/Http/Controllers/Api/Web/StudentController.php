<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentBasicInfoResource;
use App\Http\Resources\StudentFullInfoResource;
use App\Imports\StudentsImport;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function store(CreateStudentRequest $request)
    {
        try {
            $student = $this->studentService->createStudent($request->validated());

            return response()->json([
                'message' => 'Student and parent created successfully',
                'student_id' => $student->id,
                'parent_id' => $student->studentProfile->parent_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $students = $this->studentService->getAllStudents();

            return response()->json([
                'students' => StudentBasicInfoResource::collection($students),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $student = $this->studentService->getStudentById($id);

            if (!$student) {
                return response()->json([
                    'message' => 'Student not found or does not have the student role.'
                ], 404);
            }

            return response()->json([
                'student' => new StudentFullInfoResource($student)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update($id, UpdateStudentRequest $request)
    {
        try {
            $student = $this->studentService->updateStudent($id, $request->validated());

            return response()->json([
                'message' => 'Student updated successfully',
                'student' => new StudentFullInfoResource($student)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->studentService->deleteStudent($id);

            return response()->json([
                'message' => 'Student deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restoreStudent($id)
    {
        try {
            $student = $this->studentService->restoreStudent($id);

            return response()->json([
                'message' => 'Student restored successfully',
                'student' => new StudentFullInfoResource($student)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore student',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function forceDeleteStudent($id)
    {
        try {
            $this->studentService->forceDeleteStudent($id);

            return response()->json([
                'message' => 'Student permanently deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to permanently delete student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchStudents(Request $request)
    {
        try {
            $students = $this->studentService->searchStudents($request->only([
                'name', 'phone_number', 'level', 'enrollment_year'
            ]));

            return response()->json([
                'students' => StudentBasicInfoResource::collection($students),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to search students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function import(ImportStudentsRequest $request)
    {
        try {
            $file = $request->file('file');
            $dryRun = (bool)$request->boolean('dry_run');

            $import = new StudentsImport($this->studentService, $dryRun);
            Excel::import($import, $file);

            $failures = [];
            foreach ($import->failures() as $failure) {
                $failures[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            return response()->json([
                'message'   => $dryRun ? 'Dry run completed.' : 'Import completed.',
                'processed' => $import->processed,
                'created'   => $import->created,
                'skipped'   => $import->skipped,
                'failures'  => $failures,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Import failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
