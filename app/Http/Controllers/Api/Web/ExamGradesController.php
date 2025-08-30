<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpsertExamGradesRequest;
use App\Models\Exam;
use App\Services\ExamGradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamGradesController extends Controller
{
    public function students(Exam $exam, Request $request, ExamGradeService $service): JsonResponse
    {
        try {
            $classId = $request->query('class_id');
            $students = $service->studentsForExam($exam->id, $classId);
            return response()->json(['students' => $students]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to fetch students'], 500);
        }
    }

    public function bulkUpsert(Exam $exam, BulkUpsertExamGradesRequest $request, ExamGradeService $service): JsonResponse
    {
        try {
            $count = $service->bulkUpsert($exam->id, $request->validated()['records']);
            return response()->json(['updated' => $count]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to upsert grades'], 500);
        }
    }

    public function publish(Exam $exam, ExamGradeService $service): JsonResponse
    {
        try {
            $published = $service->publishResults($exam->id);
            return response()->json(['exam' => $published]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to publish results'], 500);
        }
    }
}
