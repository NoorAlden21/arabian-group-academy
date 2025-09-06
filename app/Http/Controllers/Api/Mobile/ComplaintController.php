<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Resources\MyComplaintResource;
use App\Services\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ComplaintController extends Controller
{
    public function __construct(private ComplaintService $complaintService)
    {
    }

    public function topics(): JsonResponse
    {
        return response()->json([
            'topics' => $this->complaintService->topics(),
        ]);
    }

    public function my(Request $request): JsonResponse
    {
        try {
            $complaints = $this->complaintService->listMyComplaints($request->user(), 15);
            return response()->json([
                'complaints' => MyComplaintResource::collection($complaints),
                'meta' => [
                    'current_page' => $complaints->currentPage(),
                    'last_page'    => $complaints->lastPage(),
                    'total'        => $complaints->total(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch your complaints.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        try {
            $complaint = $this->complaintService->createComplaint($request->user(), $request->validated());
            return response()->json([
                'message'   => 'تم إرسال الشكوى بنجاح.',
                'complaint' => new MyComplaintResource($complaint),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to create complaint.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
