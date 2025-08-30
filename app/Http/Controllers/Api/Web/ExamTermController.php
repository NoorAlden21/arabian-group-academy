<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamTermRequest;
use App\Http\Requests\UpdateExamTermRequest;
use App\Models\ExamTerm;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;

class ExamTermController extends Controller
{
    public function index(): JsonResponse
    {
        $terms = ExamTerm::latest()->paginate(20);
        return response()->json(['terms' => $terms]);
    }

    public function store(StoreExamTermRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $term = ExamTerm::create($data);
            return response()->json(['term' => $term], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to create exam term'], 500);
        }
    }

    public function update(ExamTerm $examTerm, UpdateExamTermRequest $request): JsonResponse
    {
        try {
            $examTerm->update($request->validated());
            return response()->json(['term' => $examTerm->fresh()]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to update exam term'], 500);
        }
    }

    public function publish(ExamService $service, ExamTerm $examTerm): JsonResponse
    {
        try {
            $term = $service->publishTerm($examTerm->id);
            return response()->json(['term' => $term]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Failed to publish exam term'], 500);
        }
    }
}
