<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $deviceName = $request->header('User-Agent', 'mobile-device');

            $result = $this->authService->login(
                $validatedData['phone_number'],
                $validatedData['password'],
                $deviceName
            );

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'phone_number' => $result['user']->phone_number,
                    'roles' => $result['user']->getRoleNames(),
                ],
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
            $this->authService->logout($request->user());

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
