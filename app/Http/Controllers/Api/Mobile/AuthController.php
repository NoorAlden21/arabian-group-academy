<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\ChildBasicResource;
use App\Services\AuthService;
use App\Services\DeviceTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;
    // protected DeviceTokenService $deviceTokenService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        // $this->deviceTokenService = $deviceTokenService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $deviceName = $request->header('User-Agent', 'mobile-device');
            $fcmToken = $request->input('fcm_token');

            $result = $this->authService->login(
                $validatedData['phone_number'],
                $validatedData['password'],
                $deviceName
            );

            $user = $result['user'];

            // Save FCM token if available
            // if ($fcmToken) {
            //     $this->deviceTokenService->storeToken($user, $fcmToken);
            // }

            // children count (lightweight info only)
            $childrenCount = 0;
            if ($user->hasRole('parent')) {
                $childrenCount = optional($user->parentProfile?->children)->count() ?? 0;
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'roles' => $user->getRoleNames(),
                ],
                'children_count' => $childrenCount,
                'token' => $result['token']
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            // Get the user and their current token
            $user = $request->user();
            $token = $request->bearerToken();

            // Remove the specific device token
            $this->deviceTokenService->removeToken($user, $token);

            // Revoke the current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Successfully logged out from current device.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $this->authService->logoutAllDevices($request->user());

            // You might also want to remove all device tokens here
            $this->deviceTokenService->removeAllTokens($request->user());

            return response()->json([
                'message' => 'Successfully logged out from all devices.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to logout from all devices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
