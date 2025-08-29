<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStudentAbsencesRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    // GET /api/admin/attendance/meta
    public function meta()
    {
        try {
            $data = $this->attendanceService->getMeta();
            return response()->json($data, 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to load attendance meta.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function storeStudentAbsences(CreateStudentAbsencesRequest $request)
    {
        try {
            $summary = $this->attendanceService->storeStudentAbsencesBulk($request->validated());

            return response()->json([
                'message' => 'Attendance saved.',
                'summary' => $summary,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to save attendance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
