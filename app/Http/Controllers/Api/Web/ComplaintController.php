<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Http\Resources\ComplaintAdminResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ComplaintController extends Controller
{
    public function __construct(private ComplaintService $complaintService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'topic', 'target_type',
                'complainant_type', 'complainant_profile',
                'target_type', 'target_profile'
            ]);

            $complaints = $this->complaintService->adminIndex($filters, 20);

            return response()->json([
                'complaints' => ComplaintAdminResource::collection($complaints),
                'meta' => [
                    'current_page' => $complaints->currentPage(),
                    'last_page'    => $complaints->lastPage(),
                    'total'        => $complaints->total(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch complaints.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Complaint $complaint): JsonResponse
    {
        try {
            $complaint->load(['complainantable', 'targetable', 'handler']);
            return response()->json([
                'complaint' => new ComplaintAdminResource($complaint),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to fetch complaint.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): JsonResponse
    {
        try {
            $updated = $this->complaintService->updateStatus($complaint, $request->validated()['status'], $request->user());
            return response()->json([
                'message'   => 'تم تحديث حالة الشكوى.',
                'complaint' => new ComplaintAdminResource($updated),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to update complaint status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Complaint $complaint): JsonResponse
    {
        try {
            $this->complaintService->delete($complaint);
            return response()->json(['message' => 'تم حذف الشكوى.']);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete complaint.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
