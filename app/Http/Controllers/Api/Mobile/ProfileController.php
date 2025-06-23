<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\UserResource;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{

    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    /**
     * Display the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user(); // Get the authenticated user via Sanctum

        try {
            $profile = $this->userProfileService->getAuthenticatedUserProfile($user);
            return UserResource::make($profile)->response()->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve authenticated user profile: " . $e->getMessage());
            return response()->json([
                'message' => 'Could not retrieve user profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
