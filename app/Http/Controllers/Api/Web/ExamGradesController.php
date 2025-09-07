<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpsertExamGradesRequest;
use App\Models\Classroom;
use App\Models\Exam;
use App\Services\ExamGradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamGradesController extends Controller
{
    public function classrooms(Exam $exam)
    {
        $classrooms = Classroom::query()
            ->where('class_type_id', $exam->class_type_id)
            ->orderBy('name')
            ->get(['id', 'name', 'year']);

        return response()->json([
            'exam_id'       => $exam->id,
            'class_type_id' => $exam->class_type_id,
            'classrooms'    => $classrooms,
        ]);
    }

    public function students(Exam $exam, Request $request, ExamGradeService $service): JsonResponse
    {
        try {
            $classId     = $request->query('class_id');
            $onlyMissing = filter_var($request->query('only_missing', '1'), FILTER_VALIDATE_BOOLEAN);

            $students = $service->studentsForExam($exam->id, $classId, $onlyMissing);

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
