<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpsertExamsRequest;
use App\Models\Exam;
use App\Models\ExamTerm;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with(['term','classType','subject'])
            ->when($request->term_id, fn($q,$v) => $q->where('exam_term_id', $v))
            ->when($request->class_type_id, fn($q,$v) => $q->where('class_type_id', $v))
            ->when($request->subject_id, fn($q,$v) => $q->where('subject_id', $v));

        return response()->json(['exams' => $query->orderBy('scheduled_at')->paginate(30)]);
    }

    public function bulkUpsert(ExamTerm $examTerm, BulkUpsertExamsRequest $request, ExamService $service): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $service->bulkUpsertForClassType(
                termId: $examTerm->id,
                classTypeId: $data['class_type_id'],
                items: $data['exams'],
            );
            return response()->json(['exams' => $result], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to upsert exams'], 500);
        }
    }

    public function publish(Exam $exam, ExamService $service): JsonResponse
    {
        try {
            $published = $service->publish($exam->id);
            return response()->json(['exam' => $published]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to publish exam'], 500);
        }
    }
}
